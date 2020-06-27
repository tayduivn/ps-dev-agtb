<?php
/*
 * Your installation or use of this SugarCRM file is subject to the applicable
 * terms available at
 * http://support.sugarcrm.com/Resources/Master_Subscription_Agreements/.
 * If you do not agree to all of the applicable terms or do not have the
 * authority to bind the entity as an authorized representative, then do not
 * install or use this SugarCRM file.
 *
 * Copyright (C) SugarCRM Inc. All rights reserved.
 */

/**
 * Update date_time_end in schedulers table.
 */
class SugarUpgradeUpdateSchedulersDateTimeEnd extends UpgradeDBScript
{
    public $order = 9999;

    /**
     * Execute upgrade tasks
     * This script updates date_time_end in schedulers table
     * @see UpgradeScript::run()
     */
    public function run()
    {
        if (version_compare($this->from_version, '10.2.0', '<')) {
            $this->log('Updating date_time_end in schedulers table');
            $dates = ["2037-12-31 23:59:59", "2030-12-31 23:59:59", "2020-12-31 23:59:59"];
            $sql = "UPDATE schedulers SET date_time_end = null WHERE date_time_end = ? or date_time_end = ? or date_time_end = ?";
            $this->executeUpdate($sql, $dates);
        }
    }
}
