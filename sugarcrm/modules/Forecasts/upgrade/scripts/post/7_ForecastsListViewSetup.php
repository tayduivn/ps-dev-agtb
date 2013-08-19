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
 * Create Forecasting settings
 */
class SugarUpgradeForecastsListViewSetup extends UpgradeScript
{
    public $order = 7001;
    public $type;
    
    public function __construct($upgrader) 
    {
        parent::__construct($upgrader);
        $this->type = self::UPGRADE_CORE | self::UPGRADE_CUSTOM;
    }

    public function run()
    {

        if (!$this->toFlavor('pro')) {
            return;
        }

        if (!version_compare($this->from_version, '7.0', "<")) {
            return;
        }

        /* @var $admin Administration */
        $admin = BeanFactory::getBean('Administration');
        $config = $admin->getConfigForModule('Forecasts');

        $this->setupForecastListViewMetaData($config);
    }

    protected function setupForecastListViewMetaData($forecast_config)
    {
        // setup the forecast columns based on the config
        require_once('include/api/RestService.php');
        require_once('modules/Forecasts/clients/base/api/ForecastsConfigApi.php');
        $api = new RestService();
        $api->user = $this->context['admin'];
        $api->platform = 'base';
        $client = new ForecastsConfigApi();
        $client->setWorksheetColumns($api, $forecast_config['worksheet_columns'], $forecast_config['forecast_by']);

        unset($api, $client);
    }
}
