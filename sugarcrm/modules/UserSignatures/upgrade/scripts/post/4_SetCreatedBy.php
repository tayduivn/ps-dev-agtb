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
 * Copyright (C) 2004-2014 SugarCRM Inc.  All rights reserved.
 */
class SugarUpgradeSetCreatedBy extends UpgradeScript
{
    public $order = 4000;
    public $type = self::UPGRADE_DB;

    /**
     * Copy the value of the user_id field to the created_by field for all UserSignatures records.
     */
    public function run()
    {
        if (!version_compare($this->from_version, '7.2.1', '<')) {
            return;
        }
        $seed = BeanFactory::newBean('UserSignatures');
        $sql = "UPDATE "
            . $seed->getTableName()
            . " SET created_by=user_id, modified_user_id="
            . $GLOBALS['db']->quoted($GLOBALS['current_user']->id)
            . ", date_modified="
            . $GLOBALS['db']->quoted($GLOBALS['timedate']->nowDb())
            . " WHERE " . $this->db->convert('created_by', 'ifnull') . "<>user_id";
        $GLOBALS['db']->query($sql);
    }
}
