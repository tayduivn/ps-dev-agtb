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

require_once("service/core/SoapHelperWebService.php");
require_once("service/core/SugarWebServiceImpl.php");
require_once("soap/SoapError.php");
require_once("include/api/SugarApi/ServiceCoreHelper.php");

class listApi extends SugarApi {
    public function registerApiRest() {
        return array(
            'listModules' => array(
                'reqType' => 'GET',
                'path' => array('<module>'),
                'pathVars' => array('module'),
                'method' => 'listModule',
                'shortHelp' => 'List records in this module',
                'longHelp' => 'include/api/html/module_list_help.html',
            ),
            'searchModules' => array(
                'reqType' => 'GET',
                'path' => array('<module>','search','?'),
                'pathVars' => array('module','','query'),
                'method' => 'listModule',
                'shortHelp' => 'Searches records in this module',
                'longHelp' => 'include/api/html/module_list_search_help.html',
            ),
        );
    }

    public function __construct() {
        // Until we get rid of the service/core/*.php, we need to keep this around
    }

    public function listModule($api, $args) {
        global $current_user;

        $deleted = false;
        if ( isset($args['deleted']) && ( strtolower($args['deleted']) == 'true' || $args['deleted'] == '1' ) ) {
            $deleted = true;
        }
        $maxResult = 0;
        if ( isset($args['maxResult']) ) {
            $maxResult = (int)$args['maxResult'];
        }
        $offset = 0;
        if ( isset($args['offset']) ) {
            $offset = (int)$args['offset'];
        }
        
        $userFields = array();
        if (array_key_exists("fields", $args)) {
            $tmpfields = explode(",", $args["fields"]);
            foreach ($tmpfields as $f) {
                array_push($userFields, $f);
            }
        }

        $helper = new SoapHelperWebServices();

        $userModList = $helper->get_user_module_list($current_user);
        $tmp = $helper->get_user_module_list($current_user);
        $UserModulList = array_keys($tmp);
        $tmp = null;

        if (!in_array($args['module'], $UserModulList)) {
            throw new SugarApiExceptionNotAuthorized("Current user does not have access to this resource!");
        }

        $obj = new SugarWebServiceImpl();
        $fields = $obj->get_module_fields($api->sessionId, $args['module'], array());
        $modFieldNames = array_keys($fields["module_fields"]);

        // check to make sure all requested fields by the user are valid for this object //
        foreach ($userFields as $ufield) {
            if (!in_array($ufield, $modFieldNames)) {
                throw new SugarApiExceptionInvalidParameter("Request field: '{$ufield}' is not a valid field name for the '{$args['module']}' module!");
            }
        }

        $entryList = $obj->get_entry_list($api->sessionId, $args['module'], (isset($args['query'])?$args['query']:''), (isset($args['offset'])?$args['offset']:''), $offset, $userFields, array(), $maxResult, $deleted);

        if (isset($entryList['error']) && $entryList["error"] != 0) {
            throw new SugarApiExceptionError($entryList["err_msg"]);
        }

        $ids = array();

        foreach ($entryList["entry_list"] as $dhash) {
            if (array_key_exists("name_value_list", $dhash)) {
                $fieldsData = array();
                foreach (array_keys($dhash["name_value_list"]) as $dkey) {
                    $fieldsData[$dkey] = $dhash["name_value_list"][$dkey]["value"];
                }

                $fieldsData["id"] = $dhash["id"];
                $ids[] = $fieldsData;
            } else {
                $ids[] = $dhash["id"];
            }
        }


        $response = array();
        $response["next_offset"] = $entryList["next_offset"];
        $response["result_count"] = $entryList["result_count"];
        $response["records"] = $ids;

        return $response;
    }

}