<?php
/**
 * Remove SugarParsers and ReportBuilder as they are no longer used by Forecasting
 */
class SugarUpgradeSugarParsersDelete extends UpgradeScript
{
    public $order = 7000;
    public $version = '7.0.0';
    public $type = self::UPGRADE_CORE;

    public function run()
    {
        $files = array('include/SugarParsers',
            'include/SugarCharts/ReportBuilder.php');
        $this->fileToDelete($files);
    }
}
