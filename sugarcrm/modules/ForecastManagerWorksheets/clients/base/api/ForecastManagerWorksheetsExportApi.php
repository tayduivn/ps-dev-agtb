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

require_once('clients/base/api/ExportApi.php');
class ForecastManagerWorksheetsExportApi extends ExportApi
{
    /**
     * This function registers the Rest api
     */
    public function registerApiRest()
    {
        return array(
            'exportGet' => array(
                'reqType' => 'GET',
                'path' => array('ForecastManagerWorksheets', 'export'),
                'pathVars' => array('module', ''),
                'method' => 'export',
                'rawReply' => true,
                'allowDownloadCookie' => true,
                'shortHelp' => 'Returns a record set in CSV format along with HTTP headers to indicate content type.',
                'longHelp' => 'include/api/help/module_export_get_help.html',
            ),
        );
    }

    public function export(ServiceBase $api, $args = array())
    {
        ob_start();
        // Load up a seed bean
        $seed = BeanFactory::getBean('ForecastManagerWorksheets');

        if (!$seed->ACLAccess('list')) {
            throw new SugarApiExceptionNotAuthorized('No access to view records for module: ' . $seed->object_name);
        }

        $args['timeperiod_id'] = isset($args['timeperiod_id']) ? $args['timeperiod_id'] : TimePeriod::getCurrentId();
        $args['user_id'] = isset($args['user_id']) ? $args['user_id'] : $api->user->id;
        // don't allow encoding to html for data used in export
        $args['encode_to_html'] = false;

        // base file and class name
        $file = 'include/SugarForecasting/Export/Manager.php';
        $klass = 'SugarForecasting_Export_Manager';

        // check for a custom file exists
        SugarAutoLoader::requireWithCustom($file);
        $klass = SugarAutoLoader::customClass($klass);
        // create the class
        /* @var $obj SugarForecasting_Export_AbstractExport */
        $obj = new $klass($args);

        $content = $obj->process($api);
        ob_end_clean();

        return $this->doExport($api, $obj->getFilename(), $content);
    }
}
