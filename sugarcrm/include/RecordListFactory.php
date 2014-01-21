<?php
/*
 * By installing or using this file, you are confirming on behalf of the entity
 * subscribed to the SugarCRM Inc. product ("Company") that Company is bound by
 * the SugarCRM Inc. Master Subscription Agreement ("MSA"), which is viewable at:
 * http://www.sugarcrm.com/master-subscription-agreement
 *
 * If Company is not bound by the MSA, then by installing or using this file
 * you are agreeing unconditionally that Company will be bound by the MSA and
 * certifying that you have authority to bind Company accordingly.
 *
 * Copyright  2004-2013 SugarCRM Inc.  All rights reserved.
 */

/**
 * Factory to create Record Lists.
 */
class RecordListFactory
{
    /**
     * Retrieves the data for a record list
     * @param string $id
     * @param $user
     *
     * @return array id, module, records
     */
    public static function getRecordList($id, $user = null)
    {
        $data = null;
        $db = DBManagerFactory::getInstance();

        if ($user == null) {
            $user = $GLOBALS['current_user'];
        }

        $ret = $db->query("SELECT * FROM record_list WHERE id = '".$db->quote($id)."' AND assigned_user_id = '".$db->quote($user->id)."'",true);

        $row = $db->fetchByAssoc($ret, false);

        if (!empty($row['records'])) {
            $data = $row;
            $data['records'] = json_decode($data['records']);
        }

        return $data;
    }

    /**
     * Saves a record list object and returns the id
     * @param      $recordList
     * @param      $module
     * @param      $id
     * @param      $user
     *
     * @return string
     */
    public static function saveRecordList($recordList, $module, $id = null, $user = null)
    {
        $db = DBManagerFactory::getInstance();

        if ($user == null) {
            $user = $GLOBALS['current_user'];
        }

        $currentTime = $GLOBALS['timedate']->nowDb();

        if (empty($id)) {
            $id = create_guid();
            $query = "INSERT INTO record_list (id, assigned_user_id, module_name, records, date_modified) VALUES ('".$db->quote($id)."','".$db->quote($user->id)."', '".$db->quote($module)."','".$db->quote(json_encode($recordList))."', '".$currentTime."')";
        } else {
            $query = "UPDATE record_list SET records = '".$db->quote(json_encode($recordList))."', date_modified = '".$currentTime."'";
        }

        $db->query($query,true);

        return $id;
    }

    /**
     * Deletes a record list based on record list id
     * @param $recordListId
     *
     * @return mixed
     */
    public static function deleteRecordList($recordListId)
    {
        $db = DBManagerFactory::getInstance();
        $ret = $db->query("DELETE FROM record_list WHERE id = '".$db->quote($recordListId)."'",true);

        return $ret;
    }
}
