<?php
/**
 * Create default reports for CE->PRO
 */
class SugarUpgradeCreateReports extends UpgradeScript
{
    public $order = 4000;
    public $type = self::UPGRADE_DB;

    public function run()
    {
        if(!($this->from_flavor == 'ce' && $this->toFlavor('pro'))) return;
    	require_once('modules/Reports/SavedReport.php');
    	require_once('modules/Reports/SeedReports.php');
        create_default_reports();
    }
}
