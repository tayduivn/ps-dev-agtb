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

/**
 * Queue class for activity stream events.
 * @api
 */
class ActivityQueueManager
{
    public static $linkBlacklist = array('user_sync');

    /**
     * Logic hook arbiter for activity streams.
     * @param  SugarBean $bean
     * @param  string    $event
     * @param  array     $args
     */
    public function eventDispatcher(SugarBean $bean, $event, $args)
    {
        if ($bean instanceof Activity && $bean->activity_type == 'post') {
            // Posts.
            $this->processPostSubscription($bean);
        } else if ($bean->is_AuditEnabled() && Activity::isEnabled()) {
            $activity = BeanFactory::getBean('Activities');
            if ($event == 'after_save') {
                $this->createOrUpdate($bean, $args, $activity);
            } elseif ($event == 'after_delete') {
                $this->delete($bean, $activity);
            } elseif ($event == 'after_restore') {
                $this->undelete($bean, $args, $activity);
            } elseif ($event == 'after_relationship_add' && !in_array($args['link'], self::$linkBlacklist)) {
                $this->link($args, $activity);
            } elseif ($event == 'after_relationship_delete' && !in_array($args['link'], self::$linkBlacklist)) {
                $this->unlink($args, $activity);
            }

            // Add rows to the activities_users join table. We may potentially
            // move this process to the job queue.
            $this->processSubscriptions($bean, $activity, $args);
        }
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
    }

    /**
     * Handler for delete actions on a bean.
     * @param  SugarBean $bean
     * @param  Activity  $act
     */
    protected function delete(SugarBean $bean, Activity $act)
    {
        $act = BeanFactory::getBean('Activities');
        $data = array(
            'object' => self::getBeanAttributes($bean),
        );
        $act->activity_type = 'delete';
        $act->parent_id = $bean->id;
        $act->parent_type = $bean->module_name;
        $act->data = $data;
        $act->save();
    }

    /**
     * Handler for undelete actions on a bean.
     * @param  SugarBean $bean
     * @param  Activity  $act
     */
    protected function undelete(SugarBean $bean, Activity $act)
    {
        $act = BeanFactory::getBean('Activities');
        $data = array(
            'object' => self::getBeanAttributes($bean),
        );
        $act->activity_type = 'undelete';
        $act->parent_id = $bean->id;
        $act->parent_type = $bean->module_name;
        $act->data = $data;
        $act->save();
    }

    /**
     * Handler for link actions on two beans.
     * @param  array    $args
     * @param  Activity $act
     */
    protected function link(array $args, Activity $act)
    {
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
    }

    /**
     * Handler for unlink actions on two beans.
     * @param  array    $args [description]
     * @param  Activity $act  [description]
     */
    protected function unlink(array $args, Activity $act)
    {
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
     * Helper for processing subscriptions on a post activity.
     * @param  Activity $act
     */
    protected function processPostSubscription(Activity $act)
    {
        $db = DBManagerFactory::getInstance();
        $sql = 'INSERT INTO activities_users VALUES (';
        $values = array(
            '"' . create_guid() . '"',
            'NULL',
            '"' . $act->id . '"',
            '"' . $act->parent_type . '"',
            '"' . $act->parent_id . '"',
            '"[]"',
            '"' . $act->date_modified . '"',
            '0',
        );
        $sql .= implode(', ', $values) . ')';
        $db->query($sql);
        // First argument of next block cannot be null.
        // $act->subscribed_users->add(null, array(
        //     'parent_type' => $act->parent_type,
        //     'parent_id' => $act->parent_id,
        //     'fields' => '[]'
        // ));
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
