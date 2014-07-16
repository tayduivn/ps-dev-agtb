<?php
 if(!defined('sugarEntry') || !sugarEntry) die('Not A Valid Entry Point');
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
 * 'Clearing the fts_queue so we can add a primary key
 */
class SugarUpgradeTruncateFTSTable extends UpgradeScript
{
    public $order = 2010;
    public $type = self::UPGRADE_DB;

    public function run()
    {
        global $dictionary;
        if(empty($dictionary['fts_queue']) ||  version_compare($this->from_version, '7.2.2', '>=')) {
            return;
        }
        $this->log('Clearing the fts_queue so we can add a primary key');

        $this->db->query($this->db->truncateTableSQL("fts_queue"));
    }
}
