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

require_once('include/api/listApi.php');

class relateApi extends listApi {
    public function registerApiRest() {
        return array(
            'listRelatedRecords' => array(
                'reqType' => 'GET',
                'path' => array('<module>','?','?'),
                'pathVars' => array('module','record','relationship'),
                'method' => 'listRelated',
                'shortHelp' => 'List related records to this module',
                'longHelp' => 'include/api/html/module_relate_help.html',
            ),
        );
    }
    
    public function listRelated($api, $args) {
        global $current_user;
        $deleted = 0;
        $relatedFields = array();
        $fields = array();
        $where = "";

        if (isset($args['fields'])) {
            $tmp = explode(",", $args["fields"]);
            foreach ($tmp as $f) {
                if (!empty($f)) {
                    array_push($fields, $f);
                }
            }
        }

        if (isset($args['where'])) {
            $where = $args["where"];
        }

        $obj = new SugarWebServiceImpl();
        $relateData = $obj->get_relationships($api->sessionId,
            $args['module'],
            $args['record'],
            $args['relationship'],
            $where,
            $fields,
            array(),
            $deleted);

        if (!array_key_exists("entry_list", $relateData)) {
            throw new SugarApiExceptionError("No returned data");
        }

        $retData = array();
        foreach ($relateData["entry_list"] as $entry) {
            $keys = array_keys($entry);
            $tmpData = array();

            // this inner loop is needed to remove the unneeded nesting of hashes where name="name" &
            // value="value" so the data is a proper hash //
            foreach ($keys as $key) {
                if ($key != "name_value_list") {
                    $tmpData[$key] = $entry[$key];
                } else {
                    $value = $entry[$key];
                    foreach ($value as $listData) {
                        $tmpData[$listData["name"]] = $listData["value"];
                    }
                }
            }
            array_push($retData, $tmpData);
        }

        return $retData;
    }
}
