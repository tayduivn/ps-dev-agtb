<?php

class Comment extends Basic
{
    public $table_name = 'comments';
    public $object_name = 'Comment';
    public $module_name = 'Comments';
    public $module_dir = 'ActivityStream/Comments';

    public $id;
    public $name = '';
    public $date_entered;
    public $date_modified;
    public $parent_id;
    public $data = '{}';
    public $created_by;
    public $created_by_name;

    /**
     * Method that returns a JSON representation of the bean.
     * @return string
     */
    public function toJson()
    {
        $this->retrieve();
        $sfh = new SugarFieldHandler();
        $data = array();
        foreach ($this->field_defs as $fieldName => $properties) {
            $type = !empty($properties['custom_type']) ? $properties['custom_type'] : $properties['type'];

            $field = $sfh->getSugarField($type);
            if ($field != null && isset($this->$fieldName)) {
                $field->apiFormatField($data, $this, array(), $fieldName, $properties);
            }
        }
        return json_encode($data);
    }

    /**
     * Saves the current comment.
     * @param  boolean $check_notify
     * @return string|bool           GUID of saved comment or false.
     */
    public function save($check_notify = false)
    {
        if (!is_string($this->data)) {
            $this->data = json_encode($this->data);
        }
        $post = BeanFactory::getBean('Activities', $this->parent_id);
        if (!empty($post) && $post->id) {
            $isNew = true;
            if ($this->id) {
                $isNew = false;
            }
            if (parent::save($check_notify)) {
                if ($isNew) {
                    $post->addComment($this);
                }
                return $this->id;
            }
        }
        return false;
    }

    /**
     * Retrieves the Comment specified.
     *
     * SugarBean's signature states that encode is true by default. However, as
     * we store JSON data, we want to modify that behaviour to be false so that
     * the JSON data does not have characters replaced by HTML entities.
     * @param  string  $id      GUID of the Comment record
     * @param  boolean $encode  Encode quotes and other special characters
     * @param  boolean $deleted Flag to allow retrieval of deleted records
     * @return Comment
     */
    public function retrieve($id = '-1', $encode = false, $deleted = true)
    {
        parent::retrieve($id, $encode, $deleted);
        return $this;
    }
}
