<?php
require_once('clients/base/api/ModuleApi.php');

class ForecastsModuleApi extends ModuleApi
{
    public function registerApiRest()
    {
        return array(
            'create' => array(
                'reqType' => 'POST',
                'path' => array('Forecasts'),
                'pathVars' => array('module'),
                'method' => 'createRecord',
                'shortHelp' => 'This method creates a new record of the specified type',
                'longHelp' => 'include/api/help/module_new_help.html',
            ),
        );
    }

    public function createRecord(ServiceBase $api, array $args)
    {
        if (!SugarACL::checkAccess('Forecasts', 'edit')) {
            throw new SugarApiExceptionNotAuthorized('No access to edit records for module: Forecasts');
        }

        $obj = $this->getClass($args);
        $obj->save();

        return array();
    }

    /**
     * Get the Committed Class
     *
     * @param array $args
     * @return SugarForecasting_Committed
     */
    protected function getClass($args)
    {
        // base file and class name
        $file = 'include/SugarForecasting/Committed.php';
        $klass = 'SugarForecasting_Committed';

        // check for a custom file exists
        SugarAutoLoader::requireWithCustom($file);
        $klass = SugarAutoLoader::customClass($klass);
        // create the class

        /* @var $obj SugarForecasting_AbstractForecast */
        $obj = new $klass($args);

        return $obj;
    }
}