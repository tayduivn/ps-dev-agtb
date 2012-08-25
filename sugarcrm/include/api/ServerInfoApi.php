<?php
if(!defined('sugarEntry') || !sugarEntry) die('Not A Valid Entry Point');
/********************************************************************************
 *The contents of this file are subject to the SugarCRM Professional End User License Agreement
 *("License") which can be viewed at http://www.sugarcrm.com/EULA.
 *By installing or using this file, You have unconditionally agreed to the terms and conditions of the License, and You may
 *not use this file except in compliance with the License. Under the terms of the license, You
 *shall not, among other things: 1) sublicense, resell, rent, lease, redistribute, assign or
 *otherwise transfer Your rights to the Software, and 2) use the Software for timesharing or
 *service bureau purposes such as hosting the Software for commercial gain and/or for the benefit
 *of a third party.  Use of the Software may be subject to applicable fees and any use of the
 *Software without first paying applicable fees is strictly prohibited.  You do not have the
 *right to remove SugarCRM copyrights from the source code or user interface.
 * All copies of the Covered Code must include on each user interface screen:
 * (i) the "Powered by SugarCRM" logo and
 * (ii) the SugarCRM copyright notice
 * in the same form as they appear in the distribution.  See full license for requirements.
 *Your Warranty, Limitations of liability and Indemnity are expressly stated in the License.  Please refer
 *to the License for the specific language governing these rights and limitations under the License.
 *Portions created by SugarCRM are Copyright (C) 2004 SugarCRM, Inc.; All Rights Reserved.
 ********************************************************************************/

require_once('data/BeanFactory.php');
require_once('include/SugarSearchEngine/SugarSearchEngineFactory.php');
require_once('include/SugarSearchEngine/SugarSearchEngineMetadataHelper.php');
/*
 * @api
 */
class ServerInfoApi extends SugarApi {
    public function registerApiRest() {
        return array(
            'serverinfo' => array(
                'reqType' => 'GET',
                'path' => array('ServerInfo'),
                'pathVars' => array(''),
                'method' => 'getServerInfo',
                'shortHelp' => 'This method gets ServerInfo',
                'longHelp' => '',
            ),
        );
    }

    /**
     * Get Server Info
     * @param $api ServiceBase The API class of the request, used in cases where the API changes how security is applied
     * @param $args array The arguments array passed in from the API
     * @return array of ServerInfo
     */
    public function getServerInfo($api, $args)
    {
        global $sugar_flavor;

        $data['flavor'] = $sugar_flavor;

        $admin  = new Administration();
        $admin->retrieveSettings('info');
        if(isset($admin->settings['info_sugar_version'])){
            $data['version'] = $admin->settings['info_sugar_version'];
        }else{
            $data['version'] = '1.0';
        }

        if(!isSearchEngineDown())
        {
            $data['fts'] = array(
                                    'enabled'       =>  'TRUE',
                                    'modules'       =>  SugarSearchEngineMetadataHelper::getSystemEnabledFTSModules(),
                                    'type'          =>  SugarSearchEngineFactory::getFTSEngineNameFromConfig(),
                                );
        }
        else
        {
            $data['fts'] = array(
                                    'enabled'   =>  'FALSE',
                                );
        }

        $data['gmt_time'] = TimeDate::getInstance()->nowDb();

        $data['server_time'] = date('Y-m-d H:i:s');

        return $data;
    }


}
