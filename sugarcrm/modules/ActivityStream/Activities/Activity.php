<?php
/*
 * By installing or using this file, you are confirming on behalf of the entity
 * subscribed to the SugarCRM Inc. product ("Company") that Company is bound by
 * the SugarCRM Inc. Master Subscription Agreement (“MSA”), which is viewable at:
 * http://www.sugarcrm.com/master-subscription-agreement
 *
 * If Company is not bound by the MSA, then by installing or using this file
 * you are agreeing unconditionally that Company will be bound by the MSA and
 * certifying that you have authority to bind Company accordingly.
 *
 * Copyright  2004-2013 SugarCRM Inc.  All rights reserved.
 */

require_once 'modules/ActivityStream/Activities/ActivityQueueManager.php';

class Activity extends Basic
{
    public $table_name = 'activities';
    public $object_name = 'Activity';
    public $module_name = 'Activities';
    public $module_dir = 'ActivityStream/Activities';

    public $id;
    public $name = '';
    public $date_entered;
    public $date_modified;
    public $parent_id;
    public $parent_type;
    public $activity_type = 'post';
    public $data = '{}';
    public $last_comment = '{}';
    public $last_comment_bean;
    public $comment_count;
    public $created_by;
    public $created_by_name;

    /**
     * Disable Custom Field lookup since Activity Streams don't support them
     *
     * @var bool
     */
    public $disable_custom_fields = true;

    public static $enabled = true;

    /**
     * Constructor for the Activity bean.
     *
     * Override SugarBean's constructor so that we can create a comment bean as
     * a property of this object.
     */
    public function __construct()
    {
        parent::__construct();
        $this->last_comment_bean = BeanFactory::getBean('Comments');
    }

    /**
     * Retrieves the Activity specified.
     *
     * SugarBean's signature states that encode is true by default. However, as
     * we store JSON data, we want to modify that behaviour to be false so that
     * the JSON data does not have characters replaced by HTML entities.
     * @param  string  $id      GUID of the Activity record
     * @param  boolean $encode  Encode quotes and other special characters
     * @param  boolean $deleted Flag to allow retrieval of deleted records
     * @return Activity
     */
    public function retrieve($id, $encode = false, $deleted = true)
    {
        // TODO: Fix this after ENGRD-17 is resolved.
        $encode = false;
        parent::retrieve($id, $encode, $deleted);
        $this->last_comment_bean->populateFromRow(json_decode($this->last_comment, true));
        return $this;
    }

    /**
     * Adds a comment to the activity, handling the denormalized columns.
     * @param Comment $comment
     */
    public function addComment(Comment $comment)
    {
        if ($this->id && $comment->id && $comment->parent_id == $this->id) {
            $this->comment_count++;
            $this->last_comment_bean = $comment;
            $this->save();
            return true;
        }
        return false;
    }

    /**
     * Removes a comment from the activity, handling the denormalized columns.
     * @param  string $comment_id ID of the comment being deleted.
     * @return void
     */
    public function deleteComment($comment_id)
    {
        if ($comment_id && $this->id) {
            $comment = BeanFactory::getBean("Comments", $comment_id);
            if ($comment->parent_id == $this->id) {
                $comment->mark_deleted($comment_id);
                $this->comment_count--;
                $this->load_relationship('comments');
                $params = array('limit' => 1, 'orderby' => 'date_entered DESC');
                $linkResult = $this->comments->query($params);
                $last_comment_id = null;
                $linkResult = array_keys($linkResult['rows']);
                if (count($linkResult)) {
                    $last_comment_id = $linkResult[0];
                }
                $this->last_comment_bean = BeanFactory::getBean('Comments', $last_comment_id);
                $this->save();
            }
        }
    }

    /**
     * Saves the current activity.
     * @param  boolean $check_notify
     * @return string|bool ID of the new post or false
     */
    public function save($check_notify = false)
    {
        $isUpdate = !(empty($this->id) || $this->new_with_id);

        if (is_string($this->data)) {
            $this->data = json_decode($this->data, true);
        }

        $this->data = $this->processDataWithHtmlPurifier($this->activity_type, $this->data);

        if ($this->activity_type == 'post' || $this->activity_type == 'attach') {
            if (!isset($this->data['object']) && !empty($this->parent_type)) {
                $parent = BeanFactory::retrieveBean($this->parent_type, $this->parent_id);
                if ($parent && !is_null($parent->id)) {
                    $this->data['object'] = ActivityQueueManager::getBeanAttributes($parent);
                } else {
                    $this->data['object_type'] = $this->parent_type;
                }
            }

            if (!$isUpdate) {
                $this->processEmbed();
            }
        }

        if (!is_string($this->data)) {
            $this->data = json_encode($this->data);
        }
        $this->last_comment = $this->last_comment_bean->toJson();

        $return = parent::save($check_notify);

        if (($this->activity_type === 'post' || $this->activity_type === 'attach') && !$isUpdate) {
            $this->processPostSubscription();
            $this->processTags();
        }

        return $return;
    }

    protected function processEmbed()
    {
        if (!empty($this->data['value'])) {
            $val = EmbedLinkService::get($this->data['value']);
            if (!empty($val)) {
                $this->data = array_merge($this->data, $val);
            }
        }
    }

    protected function processTags()
    {
        $data = json_decode($this->data, true);
        if (!empty($data['tags']) && is_array($data['tags'])) {
            foreach ($data['tags'] as $tag) {
                $bean = BeanFactory::retrieveBean($tag['module'], $tag['id']);
                $this->processRecord($bean);
            }
        }
    }

    /**
     * Helper for processing record activities.
     * @param  SugarBean $bean
     */
    public function processRecord(SugarBean $bean)
    {
        if ($bean->load_relationship('activities')) {
            $bean->activities->add($this);
        }
    }

    /**
     * Helper for processing subscriptions on a post activity.
     */
    protected function processPostSubscription()
    {
        if (isset($this->parent_type) && isset($this->parent_id)) {
            $bean = BeanFactory::getBean($this->parent_type, $this->parent_id);
            $subscriptionsBeanName = BeanFactory::getBeanName('Subscriptions');
            $this->processRecord($bean);
            $subscriptionsBeanName::processSubscriptions($bean, $this, array());
        } else {
            $globalTeam = BeanFactory::getBean('Teams', '1');
            if ($this->load_relationship('activities_teams')) {
                $this->activities_teams->add($globalTeam, array('fields' => '[]'));
            }
        }
    }

    /**
     * Removes harmful html tags from data using html purifier
     * @param $data array
     * @return array data
     */
    public function processDataWithHtmlPurifier($activityType, $data = array())
    {
        if ($activityType === 'post' && !empty($data['value'])) {
            $data['value'] = SugarCleaner::cleanHtml($data['value']);
        }

        return $data;
    }

    /**
     * Overwrite the notifications handler.
     */
    public function _sendNotifications()
    {
        return false;
    }

    public function get_notification_recipients()
    {
        return array();
    }

    public static function enable()
    {
        self::$enabled = true;
    }

    public static function disable()
    {
        self::$enabled = false;
    }

    public static function isEnabled()
    {
        return self::$enabled;
    }
}
