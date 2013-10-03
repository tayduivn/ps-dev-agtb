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

require_once 'clients/base/api/MetadataApi.php';
require_once 'clients/mobile/api/CurrentUserMobileApi.php';

// An API to let the user in to the metadata
class MetadataMobileApi extends MetadataApi {
    protected static $blackListModuleDataKeys = array(
        'menu'
    );
    protected static $allowedModuleViews = array(
        'list',
        'edit',
        'detail',
    );
    protected static $allowedModuleLayouts = array(
        'list',
        'edit',
        'detail',
        'subpanels',
    );


    protected function getModules() {
        // The current user API gets the proper list of modules, we'll re-use it here
        $currentUserApi = new CurrentUserMobileApi();
        $modules = $currentUserApi->getModuleList();
        // add in Users [Bug59548]
        if(!array_search('Users', $modules)) {
        	$modules[] = 'Users';
        }
        return $modules;
    }


    /**
     * The same as MetadataApi::loadMetadata except that the result is filtered to remove
     * unnecesary elements for nomad/mobile
     *
     * @return array|void
     */
    protected function loadMetadata(array $args) {
        $data = parent::loadMetadata($args);

        if (!empty($data['modules'])) {
            foreach($data['modules'] as $module=> $mData) {
                //blacklist certain data types alltogether
                foreach(self::$blackListModuleDataKeys as $key) {
                    unset($data['modules'][$module][$key]);
                }
                //views and layouts should be white-list filtered
                if (!empty($mData['views'])) {
                    foreach($mData['views'] as $key => $def) {
                        if (!in_array($key, self::$allowedModuleViews)) {
                            unset($data['modules'][$module]['views'][$key]);
                        }
                    }
                }
                if (!empty($mData['layouts'])) {
                    foreach($mData['layouts'] as $key => $def) {
                        if (!in_array($key, self::$allowedModuleLayouts)) {
                            unset($data['modules'][$module]['layouts'][$key]);
                        }
                    }
                }
            }
        }

        return $data;
    }
}