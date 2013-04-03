<?php
if(!defined('sugarEntry') || !sugarEntry) die('Not A Valid Entry Point');
/*********************************************************************************
 *The contents of this file are subject to the SugarCRM Professional End User License Agreement
 *("License") which can be viewed at http://www.sugarcrm.com/EULA.
 *By installing or using this file, You have unconditionally agreed to the terms and conditions of the License, and You may
 *not use this file except in compliance with the License. Under the terms of the license, You
 *shall not, among other things: 1) sublicense, resell, rent, lease, redistribute, assign or
 *otherwise transfer Your rights to the Software, and 2) use the Software for timesharing or
 *service bureau purposes such as hosting the Software for commercial gain and/or for the benefit
 *of a third party.  Use of the Software may be subject to applicable fees and any use of the
 *Software without first paying applicable fees is strictly prohibited.  You do not have the
 *right to remove SugarCRM copyrights from the source code or user interface.
 * All copies of the Covered Code must include on each user interface screen:
 * (i) the "Powered by SugarCRM" logo and
 * (ii) the SugarCRM copyright notice
 * in the same form as they appear in the distribution.  See full license for requirements.
 *Your Warranty, Limitations of liability and Indemnity are expressly stated in the License.  Please refer
 *to the License for the specific language governing these rights and limitations under the License.
 *Portions created by SugarCRM are Copyright (C) 2006 SugarCRM, Inc.; All Rights
 *Reserved.
 ********************************************************************************/

require_once 'include/SugarQueue/SugarJobQueue.php';
require_once 'include/Link2Tag.php';

/**
 * Queue class for activity stream events.
 * @api
 */
class ActivityQueueManager
{
    public static $linkBlacklist = array('user_sync', 'activities');
    public static $linkModuleBlacklist = array('ActivityStream/Activities');
    public static $linkDupeCheck = array();
    public static $moduleBlacklist = array('OAuthTokens', 'SchedulersJobs', 'Activities', 'vCals');
    public static $moduleWhitelist = array('Notes', 'Tasks', 'Meetings', 'Calls', 'Emails');

    /**
     * Logic hook arbiter for activity streams.
     * @param  SugarBean $bean
     * @param  string    $event
     * @param  array     $args
     */
    public function eventDispatcher(SugarBean $bean, $event, $args)
    {
        if ($bean instanceof Activity && ($bean->activity_type == 'post' || $bean->activity_type == 'attach')) {
            // Posts.
            if ($event == 'after_save' && !$args['isUpdate']) {
                $this->processPostSubscription($bean);
                $this->processTags($bean);
            } elseif ($event == 'before_save') {
                $bean->data = json_decode($bean->data, true);

                if (!isset($bean->data['object']) && !empty($bean->parent_type)) {
                    $parent = BeanFactory::retrieveBean($bean->parent_type, $bean->parent_id);
                    if ($parent && !is_null($parent->id)) {
                        $bean->data['object'] = self::getBeanAttributes($parent);
                    } else {
                        $bean->data['object_type'] = $bean->parent_type;
                    }
                }

                $this->processEmbed($bean);

                $bean->data = json_encode($bean->data);
            }
        } elseif (Activity::isEnabled()) {
            $activity = BeanFactory::getBean('Activities');
            $eventTriggered = true;
            if ($event == 'after_save' && self::isAuditable($bean)) {
                $this->createOrUpdate($bean, $args, $activity);
            } elseif ($event == 'after_delete' && self::isAuditable($bean)) {
                $this->delete($bean, $activity);
            } elseif ($event == 'after_restore' && self::isAuditable($bean)) {
                $this->undelete($bean, $args, $activity);
            } elseif ($event == 'after_relationship_add' && $this->isValidLink($args)) {
                $this->link($args, $activity);
            } elseif ($event == 'after_relationship_delete' && $this->isValidLink($args)) {
                $this->unlink($args, $activity);
            } else {
                $eventTriggered = false;
            }

            // Add the job queue process to add rows to the activities_users
            // join table. This has been moved to the job queue as it's a
            // potentially slow operation.
            if ($eventTriggered) {
                $this->processSubscriptions($bean, $activity, $args);
            }
        }
    }

    protected static function isAuditable(SugarBean $bean)
    {
        if (in_array($bean->module_name, self::$moduleBlacklist)) {
            return false;
        }
        if (in_array($bean->module_name, self::$moduleWhitelist)) {
            return true;
        }
        return $bean->is_AuditEnabled();
    }

    /**
     * Helper to determine whether an activity can be created for a link.
     * @param array $args
     */
    protected function isValidLink(array $args)
    {
        $blacklist = in_array($args['link'], self::$linkBlacklist);
        $lhs_module = in_array($args['module'], self::$linkModuleBlacklist);
        $rhs_module = in_array($args['related_module'], self::$linkModuleBlacklist);
        if ($blacklist || $lhs_module || $rhs_module || !empty($GLOBALS['resavingRelatedBeans'])) {
            return false;
        } else {
            foreach (self::$linkDupeCheck as $dupe_args) {
                if ($dupe_args['relationship'] == $args['relationship']) {
                    if (self::isLinkDupe($args, $dupe_args)) {
                        return false;
                    }
                }
            }
        }
        return true;
    }

    /**
     * Helper to check if a link or unlink activity is a duplicate.
     * @param  array $args1
     * @param  array $args2
     * @return bool
     */
    protected static function isLinkDupe($args1, $args2)
    {
        if ($args1['module'] == $args2['related_module'] && $args1['id'] == $args2['related_id']) {
            return true;
        }
        if ($args1['module'] == $args2['module'] && $args1['id'] == $args2['id']) {
            return true;
        }
        return false;
    }

    /**
     * Handler for create and update actions on a bean.
     * @param SugarBean $bean
     * @param array     $args
     * @param Activity  $act
     */
    protected function createOrUpdate(SugarBean $bean, array $args, Activity $act)
    {
        // Subscribe the user that created the record, and the user to whom the
        // record is assigned.
        $subs = BeanFactory::getBeanName('Subscriptions');
        if (isset($bean->assigned_user_id)) {
            $assigned_user = BeanFactory::getBean('Users', $bean->assigned_user_id);
            $subs::subscribeUserToRecord($assigned_user, $bean);
        }

        $data = array(
            'object' => self::getBeanAttributes($bean),
        );
        if ($args['isUpdate']) {
            $act->activity_type = 'update';
            $data['changes'] = $args['dataChanges'];
        } else {
            // Subscribe the user that created the record.
            if (isset($bean->created_by)) {
                $created_user = BeanFactory::getBean('Users', $bean->created_by);
                $subs::subscribeUserToRecord($created_user, $bean);
            }
            $act->activity_type = 'create';
        }
        $act->parent_id = $bean->id;
        $act->parent_type = $bean->module_name;
        $act->data = $data;
        $act->save();
        $this->processRecord($bean, $act);
        $this->processParentAttributes($bean);
    }

    /**
     * Handler for delete actions on a bean.
     * @param  SugarBean $bean
     * @param  Activity  $act
     */
    protected function delete(SugarBean $bean, Activity $act)
    {
        $data = array(
            'object' => self::getBeanAttributes($bean),
        );
        $act->activity_type = 'delete';
        $act->parent_id = $bean->id;
        $act->parent_type = $bean->module_name;
        $act->data = $data;
        $act->save();

        $this->processRecord($bean, $act);
    }

    /**
     * Handler for undelete actions on a bean.
     * @param  SugarBean $bean
     * @param  Activity  $act
     */
    protected function undelete(SugarBean $bean, Activity $act)
    {
        $data = array(
            'object' => self::getBeanAttributes($bean),
        );
        $act->activity_type = 'undelete';
        $act->parent_id = $bean->id;
        $act->parent_type = $bean->module_name;
        $act->data = $data;
        $act->save();
        $this->processRecord($bean, $act);
    }

    /**
     * Handler for link actions on two beans.
     * @param  array    $args
     * @param  Activity $act
     */
    protected function link(array $args, Activity $act)
    {
        if (!$args['id'] || !$args['related_id']) {
            return;
        }
        $lhs = BeanFactory::getBean($args['module'], $args['id']);
        $rhs = BeanFactory::getBean($args['related_module'], $args['related_id']);
        $data = array(
            'object' => self::getBeanAttributes($lhs),
            'subject' => self::getBeanAttributes($rhs),
            'link' => $args['link'],
            'relationship' => $args['relationship'],
        );
        $act->activity_type = 'link';
        $act->parent_id = $lhs->id;
        $act->parent_type = $lhs->module_name;
        $act->data = $data;
        $act->save();
        self::$linkDupeCheck[] = $args;
        $this->processRecord($lhs, $act);
        $this->processRecord($rhs, $act);
    }

    /**
     * Handler for unlink actions on two beans.
     * @param  array    $args [description]
     * @param  Activity $act  [description]
     */
    protected function unlink(array $args, Activity $act)
    {
        if (!$args['id'] || !$args['related_id']) {
            return;
        }
        $lhs = BeanFactory::getBean($args['module'], $args['id']);
        $rhs = BeanFactory::getBean($args['related_module'], $args['related_id']);
        $data = array(
            'object' => self::getBeanAttributes($lhs),
            'subject' => self::getBeanAttributes($rhs),
            'link' => $args['link'],
            'relationship' => $args['relationship'],
        );
        $act->activity_type = 'unlink';
        $act->parent_id = $lhs->id;
        $act->parent_type = $lhs->module_name;
        $act->data = $data;
        $act->save();
        self::$linkDupeCheck[] = $args;
        $this->processRecord($lhs, $act);
        $this->processRecord($rhs, $act);
    }

    /**
     * Helper to denormalize critical bean attributes.
     * @param  SugarBean $bean
     * @return array     Contains name, type, module and ID of the bean.
     */
    protected static function getBeanAttributes(SugarBean $bean)
    {
        return array(
            'name' => $bean->get_summary_text(),
            'type' => $bean->object_name,
            'module' => $bean->module_name,
            'id' => $bean->id,
        );
    }

    /**
     * Helper for processing records which aren't explicitly linked.
     *
     * THIS IS A BIG, GIANT HACK. IF YOU ARE READING THIS, PLEASE PLEASE MAKE
     * SURE THAT WE'RE WORKING ON A WAY TO GET RID OF THIS. To get away from
     * this hack, we need to ensure that Meetings/Calls/Tasks/etc save their
     * relationships correctly when parent_type/parent_id are set.
     *
     * @ticket ABE-430
     * @param  SugarBean $bean
     */
    protected function processParentAttributes(SugarBean $bean)
    {
        if (!(isset($bean->parent_id) && isset($bean->parent_type))) {
            // If we don't have a parent type or parent ID on this bean, stop.
            return;
        }

        $old_parent_id = '';
        if (is_array($bean->fetched_row) && isset($bean->fetched_row['parent_id'])) {
            $old_parent_id = $bean->fetched_row['parent_id'];
        }

        if ($bean->parent_id !== $old_parent_id) {
            if (!empty($old_parent_id)) {
                // Create a fake unlink.
                $args = array(
                    'id' => $old_parent_id,
                    'module' => $bean->fetched_row['parent_type'],
                    'related_id' => $bean->id,
                    'related_module' => $bean->module_name,
                    'link' => 'fake_link_' . $bean->module_name,
                    'relationship' => 'fake_rel_' . $bean->module_name,
                );
                $unlink_activity = BeanFactory::getBean('Activities');
                $this->unlink($args, $unlink_activity);
                $this->processSubscriptions($bean, $unlink_activity, $args);
            }

            // We create a fake link here.
            $args = array(
                'id' => $bean->parent_id,
                'module' => $bean->parent_type,
                'related_id' => $bean->id,
                'related_module' => $bean->module_name,
                'link' => 'fake_link_' . $bean->module_name,
                'relationship' => 'fake_rel_' . $bean->module_name,
            );
            $link_activity = BeanFactory::getBean('Activities');
            $this->link($args, $link_activity);
            $this->processSubscriptions($bean, $link_activity, $args);
        }
    }

    /**
     * Helper for processing record activities.
     */
    protected function processRecord(SugarBean $bean, Activity $act)
    {
        if ($bean->load_relationship('activities')) {
            $bean->activities->add($act);
        }
    }

    protected function processTags(Activity $act)
    {
        $data = json_decode($act->data, true);
        if (!empty($data['tags']) && is_array($data['tags'])) {
            foreach ($data['tags'] as $tag) {
                $bean = BeanFactory::retrieveBean($tag['module'], $tag['id']);
                $this->processRecord($bean, $act);
            }
        }
    }

    protected function processEmbed(Activity $act)
    {
        if (!empty($act->data['value'])) {
            $val = Link2Tag::convert($act->data['value']);
            if (!empty($val)) {
                $act->data = array_merge($act->data, $val);
            }
        }
    }

    /**
     * Helper for processing subscriptions on a post activity.
     * @param  Activity $act
     */
    protected function processPostSubscription(Activity $act)
    {
        if (isset($act->parent_type) && isset($act->parent_id)) {
            $bean = BeanFactory::getBean($act->parent_type, $act->parent_id);
            $this->processRecord($bean, $act);
            $this->processSubscriptions($bean, $act, array());
        } else {
            $db = DBManagerFactory::getInstance();
            $sql = 'INSERT INTO activities_users VALUES (';
            $values = array(
                '"' . create_guid() . '"',
                '"' . $act->id . '"',
                '"Teams"',
                '"1"',
                '"[]"',
                '"' . $act->date_modified . '"',
                '0',
            );
            $sql .= implode(', ', $values) . ')';
            $db->query($sql);
        }
    }

    /**
     * Helper for processing subscriptions on a bean-related activity.
     * @param  SugarBean $bean
     * @param  Activity  $act
     * @param  array     $args
     */
    protected function processSubscriptions(SugarBean $bean, Activity $act, array $args)
    {
        $subs = BeanFactory::getBeanName('Subscriptions');
        $user_partials = $subs::getSubscribedUsers($bean);
        $data = array(
            'act_id' => $act->id,
            'bean_module' => $bean->module_name,
            'bean_id' => $bean->id,
            'args' => $args,
            'user_partials' => $user_partials,
        );

        $job = BeanFactory::getBean('SchedulersJobs');
        $job->requeue = 1;
        $job->name = "ActivityStream add";
        $job->data = serialize($data);
        $job->target = "class::SugarJobAddActivitySubscriptions";

        if (count($user_partials) < 5) {
            $job->execute_time = TimeDate::getInstance()->nowDb();
            $job->runJob();
        } else {
            $queue = new SugarJobQueue();
            $queue->submitJob($job);
        }
    }
}
