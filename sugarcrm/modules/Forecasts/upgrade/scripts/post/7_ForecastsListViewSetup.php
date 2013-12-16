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
        // if we are going to the same flavor, we can ignore this for now
        if ($this->from_flavor == $this->to_flavor) {
            return;
        }

        /* @var $admin Administration */
        $admin = BeanFactory::getBean('Administration');
        $config = $admin->getConfigForModule('Forecasts');

        // figure out the columns that we need to store
        $columns = $this->setupForecastListViewMetaData($config);

        // save the updated worksheet_columns to the forecast config
        $admin->saveSetting('Forecasts', 'worksheet_columns', json_encode($columns), 'base');
    }

    /**
     * @param array $forecast_config        The Current Forecast Config
     * @return array                        The new columns that were set
     */
    protected function setupForecastListViewMetaData($forecast_config)
    {
        // setup the forecast columns based on the config
        require_once('include/api/RestService.php');
        require_once('modules/Forecasts/clients/base/api/ForecastsConfigApi.php');
        require_once('modules/Forecasts/ForecastsDefaults.php');
        $api = new RestService();
        $api->user = $this->context['admin'];
        $api->platform = 'base';
        $client = new ForecastsConfigApi();

        // get the to_flavor default columns
        $newFlavorColumns = ForecastsDefaults::getWorksheetColumns($this->to_flavor);
        // get the from_flavor default columns
        $prevFlavorColumns = ForecastsDefaults::getWorksheetColumns($this->from_flavor);
        // get the current columns from the forecast_config
        $currentColumns = $forecast_config['worksheet_columns'];
        // find any additional columns that may have been added columns from previous defaults
        $additional_columns = array_diff($currentColumns, $prevFlavorColumns);
        // find any of the default columns that have been removed from the previous defaults
        $remove_columns = array_diff($prevFlavorColumns, $currentColumns);

        // merge the new columns with any additional columns that may have been added, and then remove any of the
        // default columns that may have been removed
        $columns = array_diff(array_merge($newFlavorColumns, $additional_columns), $remove_columns);

        // save the columns to the worksheet list viewdefs
        $client->setWorksheetColumns($api, $columns, $forecast_config['forecast_by']);

        unset($api, $client);

        return $columns;
    }
}
