<?php
/**
 * Create Forecasting settings
 */
class SugarUpgradeSetupForecast extends UpgradeScript
{
    public $order = 7000;
    public $type = self::UPGRADE_DB;

    public function run()
    {
        if(!$this->toFlavor('pro')) return;
        if(!version_compare($this->from_version, '6.7.0', "<")) return;

        require_once('modules/Forecasts/ForecastsDefaults.php');
        ForecastsDefaults::setupForecastSettings(true, $this->from_version, $this->to_version);
        ForecastsDefaults::upgradeColumns();
    }
}
