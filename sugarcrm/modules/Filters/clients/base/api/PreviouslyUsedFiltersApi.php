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

/**
 * @api
 */
class PreviouslyUsedFiltersApi extends SugarApi {
    public function registerApiRest() {
        return array(
            'setUsed' => array(
                'reqType' => 'PUT',
                'path' => array('Filters', '?', 'used', '?'),
                'pathVars' => array('module', 'module_name', 'used', 'record'),
                'method' => 'setUsed',
                'shortHelp' => 'This method sets the filter as used for the current user',
                'longHelp' => '',
            ),
            'getUsed' => array(
                'reqType' => 'GET',
                'path' => array('Filters', '?', 'used'),
                'pathVars' => array('module', 'module_name', 'used',),
                'method' => 'getUsed',
                'shortHelp' => 'This method gets the filter as used for the current user',
                'longHelp' => '',
            ),
            'deleteUsed' => array(
                'reqType' => 'DELETE',
                'path' => array('Filters', '?', 'used', '?'),
                'pathVars' => array('module', 'module_name', 'used', 'record'),
                'method' => 'deleteUsed',
                'shortHelp' => 'This method sets the filter as used for the current user',
                'longHelp' => '',
            ),   
        );
    }

    public function setUsed($api, $args) {
        $user_preference = new UserPreference($GLOBALS['current_user']);
        $used_filters = $user_preference->getPreference($args['module_name'], 'filters');
        $used_filters[] = $args['record'];
        $user_preference->setPreference($args['module_name'], $used_filters, 'filters');
        $user_preference->savePreferencesToDB(true);
        foreach($used_filters AS $id) {
            $args['module'] = 'Filters';
            $args['record'] = $id;
            $beans[] = $this->loadBean($api, $args, 'view');
        }

        $data = $this->formatBeans($api, $args, $beans);

        return $data;
    }

    public function getUsed($api, $args) {
        $user_preference = new UserPreference($GLOBALS['current_user']);
        $used_filters = $user_preference->getPreference($args['module_name'], 'filters');
        foreach($used_filters AS $id) {
            $args['module'] = 'Filters';
            $args['record'] = $id;
            $beans[] = $this->loadBean($api, $args, 'view');
        }

        $data = $this->formatBeans($api, $args, $beans);

        return $data;        
    }

    public function deleteUsed($api, $args) {
        $user_preference = new UserPreference($GLOBALS['current_user']);
        $used_filters = $user_preference->getPreference($args['module_name'], 'filters');
        if($key = array_search($args['record'], $used_filters)) {
            unset($used_filters[$key]);
        }
        $user_preference->setPreference($args['module_name'], $used_filters, 'filters');
        $user_preference->savePreferencesToDB(true);
        return $used_filters;        
    }
}