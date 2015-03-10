<?php
/*
 * Your installation or use of this SugarCRM file is subject to the applicable
 * terms available at
 * http://support.sugarcrm.com/06_Customer_Center/10_Master_Subscription_Agreements/.
 * If you do not agree to all of the applicable terms or do not have the
 * authority to bind the entity as an authorized representative, then do not
 * install or use this SugarCRM file.
 *
 * Copyright (C) SugarCRM Inc. All rights reserved.
 */

/**
 * The Tag class handles operations related to the Tags functionality 
 **/
class Tag extends Basic
{
    public $module_dir = 'Tags';
    public $object_name = 'Tag';
    public $table_name = 'tags';
    public $new_schema = true;
    public $importable = false;
    public function __construct()

    {
        parent::__construct();
    }

    /**
     * @inheritDoc
     */
    function bean_implements($interface)
    {
        switch ($interface) {
            case 'ACL':
                return true;
        }

        return false;
    }

    /**
     * @inheritDoc
     */
    public function save($check_notify = false)
    {
        global $current_user;

        // We need a tag name or really what's the point?
        if (empty($this->name)) {
            return false;
        }

        // Handle setting the assigned user if not already set
        if (empty($this->assigned_user_id)) {
            $this->assigned_user_id = $current_user->id;
        }

        // For searching making sure we lowercase the name to name_lower
        $this->name_lower = strtolower($this->name);
        return parent::save($check_notify);
    }

    /**
     * Gets all the tags for every record id given
     *
     * @param $focus
     * @param $ids array of record ids
     * @return array
     */
    public function getRelatedModuleRecords($focus, $ids)
    {
        // No ids means nothing to do
        if (empty($ids)) {
            return array();
        }

        // We need to make a reasonable assumption here that ids will either be
        // an imploded string of ids or an array of ids. If an array, make it a
        // string of ids.
        if (is_array($ids)) {
            $ids = "'" . implode("','", $ids) . "'";
        }

        $sql = "SELECT tags.id, tags.name, {$focus->table_name}.id as {$focus->table_name}_id";
        $sql .= " FROM {$focus->table_name} INNER JOIN tag_bean_rel ON {$focus->table_name}.id=tag_bean_rel.bean_id";
        $sql .= " INNER JOIN tags ON tags.id=tag_bean_rel.tag_id";
        $sql .= " WHERE {$focus->table_name}.id in ($ids) AND tag_bean_rel.deleted=0";
        $sql .= " ORDER BY tags.name ASC";

        $db = DBManagerFactory::getInstance();
        $result = $db->query($sql);
        $returnArray = array();
        while ($data = $db->fetchByAssoc($result)) {
            $returnArray[$data["{$focus->table_name}_id"]][] = array("id" => $data["id"], "name"=>$data["name"]);
        }
        return $returnArray;
    }
}
