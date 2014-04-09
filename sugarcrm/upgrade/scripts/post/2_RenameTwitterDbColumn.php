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

/**
 * Change db column name from 'twitter_id' to 'twitter' when upgrading from 6.7.5+ to 7
 */
class SugarUpgradeRenameTwitterDbColumn extends UpgradeScript
{
    public $order = 2000;
    public $type = self::UPGRADE_DB;

    public function run()
    {
        // must be upgrading from 6.7.5+ to 7. mysql is the only db available to 6.7
        if (!version_compare($this->from_version, '6.7.4', '>') || !version_compare($this->from_version, '7.0.0', '<') || $this->db->getScriptName() != 'mysql') {
            return;
        }

        global $moduleList;

        foreach ($moduleList as $module) {
            $focus = BeanFactory::getBean($module);
                
            if (!empty($focus) && (($focus instanceOf Company) || ($focus instanceOf Person)) && !empty($focus->table_name)
                    && $focus->table_name != 'users' && $focus->table_name != 'styleguide') { // there are exceptions, eg, Employees, Styleguide
                if ($this->db->query("alter table `{$focus->table_name}` change `twitter_id` `twitter` varchar(100) NULL")) {
                    $this->log("Changed column name 'twitter_id' to 'twitter' for table: {$focus->table_name}");
                }
                else {
                    $this->log("Failed to change column name 'twitter_id' to 'twitter' for table: {$focus->table_name}");
                }
            }
        }
    }
}
