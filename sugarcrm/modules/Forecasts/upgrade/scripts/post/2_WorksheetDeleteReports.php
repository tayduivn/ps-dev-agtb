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
 * Since we remove the Worksheet bean in 8_ForecastRemoveFiles, we should mark all the reports
 * that are generated off the worksheet module as deleted
 */
class SugarUpgradeWorksheetDeleteReports extends UpgradeScript
{
    public $order = 2191;
    public $type = self::UPGRADE_DB;

    public function run()
    {

        // we only need to remove these files if the from_version is less than 7.0 but greater or equal than 6.7.0
        if (version_compare($this->from_version, '7.0', '<')) {
            $sql = "UPDATE saved_reports SET deleted = 1 WHERE module = 'Worksheet';";
            $this->db->query($sql);
        }
    }
}
