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
 * Handles changing empty string duration_minutes values to null prior to the
 * rebuild call so that the meetings table can be altered appropriately.
 */
class SugarUpgradeNullifyEmptyDurationMinutes extends UpgradeScript
{
    /**
     * Order is very important here. This must run before the Rebuild upgrader,
     * which runs at 2100.
     * @var integer
     */
    public $order = 2090;

    /**
     * Marked as all since this needs to support DB and Shadow upgrades
     * @var integer
     */
    public $type = self::UPGRADE_ALL;

    public function run()
    {
        // Only run this if we are coming from a version lower than 7.7.1
        if (version_compare($this->from_version, '7.7.1.0', '<')) {
            $this->setEmptyValuesToNull();
        }
    }

    /**
     * Sets all empty string duration_minutes columns to null to allow the alter
     * routine in the next step to alter the table correctly. The net affect of
     * this change is minimal since an empty string and a null value are treated
     * the same across the application.
     */
    protected function setEmptyValuesToNull()
    {
        // Get our emptry string for comparison
        $empty = $this->db->quoted('');

        // Build the sql that will handle the reset
        $sql = "UPDATE meetings
                SET duration_minutes = NULL
                WHERE duration_minutes = $empty";

        // Now capture the result of what just happened. Success means there might
        // be affected rows, failure means there was an error.
        if ($result = $this->db->query($sql)) {
            $rows = $this->db->getAffectedRowCount($result);
            $msg = "Meetings duration_minutes column reset from empty string to null. Affected records: $rows";
        } else {
            $err = $this->db->lastError();
            $msg = "Meetings duration_minutes column reset failed: $err";
        }

        // Log it an carry on
        $this->log($msg);
    }
}
