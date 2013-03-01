<?php

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
        if (!is_string($this->data)) {
            $this->data = json_encode($this->data);
        }
        $this->last_comment = $this->last_comment_bean->toJson();
        $return = parent::save($check_notify);
        return $return;
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
