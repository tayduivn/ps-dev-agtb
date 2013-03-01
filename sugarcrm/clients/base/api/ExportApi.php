<?php
if(!defined('sugarEntry') || !sugarEntry) die('Not A Valid Entry Point');
/*********************************************************************************
 * The contents of this file are subject to the SugarCRM Master Subscription
 * Agreement ("License") which can be viewed at
 * http://www.sugarcrm.com/crm/master-subscription-agreement
 * By installing or using this file, You have unconditionally agreed to the
 * terms and conditions of the License, and You may not use this file except in
 * compliance with the License.  Under the terms of the license, You shall not,
 * among other things: 1) sublicense, resell, rent, lease, redistribute, assign
 * or otherwise transfer Your rights to the Software, and 2) use the Software
 * for timesharing or service bureau purposes such as hosting the Software for
 * commercial gain and/or for the benefit of a third party.  Use of the Software
 * may be subject to applicable fees and any use of the Software without first
 * paying applicable fees is strictly prohibited.  You do not have the right to
 * remove SugarCRM copyrights from the source code or user interface.
 *
 * All copies of the Covered Code must include on each user interface screen:
 *  (i) the "Powered by SugarCRM" logo and
 *  (ii) the SugarCRM copyright notice
 * in the same form as they appear in the distribution.  See full license for
 * requirements.
 *
 * Your Warranty, Limitations of liability and Indemnity are expressly stated
 * in the License.  Please refer to the License for the specific language
 * governing these rights and limitations under the License.  Portions created
 * by SugarCRM are Copyright (C) 2004-2012 SugarCRM, Inc.; All Rights Reserved.
 ********************************************************************************/



require_once('include/export_utils.php');

/*
 * Export API implementation
 */
class ExportApi extends SugarApi {

    /**
     * This function registers the Rest api
     */
    public function registerApiRest() {
        return array(
            'exportPost' => array(
                'reqType' => 'POST',
                'path' => array('<module>','export'),
                'pathVars' => array('module',''),
                'method' => 'export',
                'jsonParams' => array('filter'),
                'rawReply' => true,
                'shortHelp' => 'Returns a record set in CSV format along with HTTP headers to indicate content type.',
                'longHelp' => 'include/api/help/module_export_post_help.html',
            ),
            'exportGet' => array(
                'reqType' => 'GET',
                'path' => array('<module>','export'),
                'pathVars' => array('module',''),
                'method' => 'export',
                'jsonParams' => array('filter'),
                'rawReply' => true,
                'shortHelp' => 'Returns a record set in CSV format along with HTTP headers to indicate content type.',
                'longHelp' => 'include/api/help/module_export_get_help.html',
            ),
        );
    }

    /**
     * Export API
     * @param $api ServiceBase The API class of the request, used in cases where the API changes how the fields are pulled from the args array.
     * @param $args array The arguments array passed in from the API
     * @return String
     */
    public function export(ServiceBase $api, array $args)
    {
        $seed = BeanFactory::newBean($args['module']);

        if (!$seed->ACLAccess('export')) {
            throw new SugarApiExceptionNotAuthorized($GLOBALS['app_strings']['ERR_EXPORT_DISABLED']);
        }

        //Bug 30094, If zlib is enabled, it can break the calls to header() due to output buffering. This will only work php5.2+
        ini_set('zlib.output_compression', 'Off');

        ob_start();
        global $sugar_config;
        global $current_user;
        global $app_list_strings;

        $theModule = clean_string($args['module']);

        if ($sugar_config['disable_export'] || (!empty($sugar_config['admin_export_only']) && !(is_admin($current_user) || (ACLController::moduleSupportsACL($the_module)  && ACLAction::getUserAccessLevel($current_user->id,$the_module, 'access') == ACL_ALLOW_ENABLED &&
            (ACLAction::getUserAccessLevel($current_user->id, $theModule, 'admin') == ACL_ALLOW_ADMIN ||
             ACLAction::getUserAccessLevel($current_user->id, $theModule, 'admin') == ACL_ALLOW_ADMIN_DEV))))) {
            throw new SugarApiExceptionNotAuthorized($GLOBALS['app_strings']['ERR_EXPORT_DISABLED']);
        }

        //check to see if this is a request for a sample or for a regular export
        if(!empty($args['sample'])) {
            //call special method that will create dummy data for bean as well as insert standard help message.
            $content = exportSampleFromApi($args);

        } else {
            $content = exportFromApi($args);
        }

        $filename = $args['module'];
        //use label if one is defined
        if (!empty($app_list_strings['moduleList'][$args['module']])) {
            $filename = $app_list_strings['moduleList'][$args['module']];
        }

        //strip away any blank spaces
        $filename = str_replace(' ', '', $filename);


        if(isset($args['members']) && $args['members'] == true)
        {
                $filename .= '_'.'members';
        }
        ///////////////////////////////////////////////////////////////////////////////
        ////	BUILD THE EXPORT FILE
        ob_end_clean();
        header("Pragma: cache");
        header("Content-type: application/octet-stream; charset=".$GLOBALS['locale']->getExportCharset());
        header("Content-Disposition: attachment; filename={$filename}.csv");
        header("Content-transfer-encoding: binary");
        header("Expires: Mon, 26 Jul 1997 05:00:00 GMT" );
        header("Last-Modified: " . TimeDate::httpTime() );
        header("Cache-Control: post-check=0, pre-check=0", false );
        header("Content-Length: ".mb_strlen($GLOBALS['locale']->translateCharset($content, 'UTF-8', $GLOBALS['locale']->getExportCharset())));

        print $GLOBALS['locale']->translateCharset($content, 'UTF-8', $GLOBALS['locale']->getExportCharset());

        sugar_cleanup(true);
        return 0;
    }
}
