<?php
if(!defined('sugarEntry') || !sugarEntry) die('Not A Valid Entry Point');
/*********************************************************************************
 *The contents of this file are subject to the SugarCRM Professional End User License Agreement
 *("License") which can be viewed at http://www.sugarcrm.com/EULA.
 *By installing or using this file, You have unconditionally agreed to the terms and conditions of the License, and You may
 *not use this file except in compliance with the License. Under the terms of the license, You
 *shall not, among other things: 1) sublicense, resell, rent, lease, redistribute, assign or
 *otherwise transfer Your rights to the Software, and 2) use the Software for timesharing or
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

/**
 * Provides a factory to list, discover and create external API calls
 *
 * Main features are to list available external API's by supported features, modules and which ones have access for the user.
 **/
class ExternalAPIFactory{
    
    /** 
     * This will hand back an initialized class for the requested external API, it will also load in the external API password information into the bean.
     * @param string $apiName The name of the requested API ( known API's can be listed by the listAPI() call )
     * @param bool $apiName Ignore authentication requirements (optional)
     * @return API class
     */
    public static function loadAPI($apiName,$ignoreAuth=false) {
        $apiFile = basename($apiName);
        $apiFile = 'include/ExternalAPI/'.$apiFile.'/'.$apiFile.'.php';
        require_once($apiFile);

        $apiClassName = $apiName;
        if ( file_exists('custom/'.$apiFile) ) {
            require_once('custom/'.$apiFile);
            if ( class_exists($apiName.'_cstm') ) {
                $apiClassName = $apiName.'_cstm';
            }
        } else {
            if ( ! file_exists($apiFile) ) {
                // Cannot find the request API
                return false;
            }
        }

        $apiClass = new $apiClassName();

        if ( !$ignoreAuth && $apiClass->useAuth ) {
            $eapmAuth = EAPM::getLoginInfo($apiName);
            
            if ( isset($eapmAuth['application']) ) {
                // They have auth information and the class supports it.
                // Loas it in
                $apiClass->loadEAPM($eapmAuth);
            } else if ( $apiClass->requireAuth ) {
                // They don't have auth information, and the class requires it.
                return false;
            }
        }

        return $apiClass;
    }

    /** 
     * Lists the available API's for a module or all modules, and possibly ignoring if the user has auth information for that API even if it is required
     * @param string $module Which module name you are searching for, leave blank to find all API's
     * @param bool $ignoreAuth Ignore API's demands for authentication (used to get a complete list of modules
     * @return API class
     */
    public static function listAPI($module = '', $ignoreAuth = false) {
        // FIXME: Add caching

        $apiFullList = array();

        $baseDirList = array('include/externalAPI/','custom/include/externalAPI');
        foreach ( $baseDirList as $baseDir ) {
            $dirList = glob($baseDir.'*',GLOB_NOSORT|GLOB_ONLYDIR);
            foreach($dirList as $dir) {
                if ( $dir == $baseDir.'.' || $dir == $baseDir.'..' || $dir == $baseDir.'Base' ) {
                    continue;
                }
                
                $apiName = str_replace($baseDir,'',$dir);
                
                $apiFullList[$apiName] = $dir;
            }
        }
        
        $apiFinalList = array();

        foreach ( $apiFullList as $apiName => $ignore ) {
            $currAPI = self::loadApi($apiName,$ignoreAuth);
            if ( $currAPI === false ) {
                // This API did not load, probably missing a password
                continue;
            }
            
            if ( $module == '' || in_array($module,$currAPI->supportedModules) ) {
                $apiFinalList[$apiName] = $currAPI;
            }
        }

        return $apiFinalList;
    }

    public static function getModuleDropDown($moduleName, $ignoreAuth = false) {
        global $app_strings;

        $apiList = self::listAPI($moduleName,$ignoreAuth);

        $apiDropdown = array();
        
        foreach ( $apiList as $apiName => $ignore ) {
            $translateKey = 'LBL_EXTAPI_'.strtoupper($apiName);
            if ( !empty($app_strings[$translateKey]) ) {
                $apiDropdown[$apiName] = $app_strings[$translateKey];
            } else {
                $apiDropdown[$apiName] = $apiName;
            }
        }

        return $apiDropdown;
        
    }
}