<?php
/*
 * By installing or using this file, you are confirming on behalf of the entity
 * subscribed to the SugarCRM Inc. product ("Company") that Company is bound by
 * the SugarCRM Inc. Master Subscription Agreement (“MSA”), which is viewable at:
 * http://www.sugarcrm.com/master-subscription-agreement
 *
 * If Company is not bound by the MSA, then by installing or using this file
 * you are agreeing unconditionally that Company will be bound by the MSA and
 * certifying that you have authority to bind Company accordingly.
 *
 * Copyright  2004-2013 SugarCRM Inc.  All rights reserved.
 */

require_once('include/api/SugarApi.php');

class ForecastManagerWorksheetsApi extends SugarApi
{
    public function registerApiRest()
    {
        //Extend with test method
        return array(
            'forecastManagerWorksheetSave' => array(
                'reqType' => 'PUT',
                'path' => array('ForecastManagerWorksheets', '?'),
                'pathVars' => array('module', 'record'),
                'method' => 'forecastManagerWorksheetSave',
                'shortHelp' => 'Update a ForecastManagerWorksheet model',
                'longHelp' => 'modules/Forecasts/clients/base/api/help/ForecastWorksheetManagerPut.html',
            )
        );
    }

    /**
     * This method handles saving data for the /ForecastManagerWorksheet REST endpoint
     *
     * @param $api ServiceBase The API class of the request, used in cases where the API changes how the fields are pulled from the args array.
     * @param $args array The arguments array passed in from the API
     * @return Array of worksheet data entries
     * @throws SugarApiExceptionNotAuthorized
     */
    public function forecastManagerWorksheetSave($api, $args)
    {
        if (!SugarACL::checkAccess('Forecasts', 'edit')) {
            throw new SugarApiExceptionNotAuthorized('No access to view records for module: Forecasts');
        }
        $obj = $this->getClass($args);
        $obj->save();

        return array();
    }

    /**
     * @param $args
     * @return SugarForecasting_Manager
     */
    protected function getClass($args)
    {
        // base file and class name
        $file = 'include/SugarForecasting/Manager.php';
        $klass = 'SugarForecasting_Manager';

        // check for a custom file exists
        SugarAutoLoader::requireWithCustom($file);
        $klass = SugarAutoLoader::customClass($klass);
        // create the class

        /* @var $obj SugarForecasting_AbstractForecast */
        $obj = new $klass($args);
        return $obj;
    }

}
