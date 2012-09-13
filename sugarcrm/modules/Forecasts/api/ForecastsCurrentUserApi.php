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

require_once('modules/Users/User.php');
require_once('include/api/CurrentUserApi.php');

class ForecastsCurrentUserApi extends CurrentUserApi {
    public function registerApiRest() {
        return array(
            'retrieve' => array(
                'reqType' => 'GET',
                'path' => array('Forecasts','me'),
                'pathVars' => array(),
                'method' => 'retrieveCurrentUser',
                'shortHelp' => 'Returns current user',
                'longHelp' => 'include/api/html/me.html',
            ),
            'selecteUserObject' => array(
                'reqType' => 'GET',
                'path' => array('Forecasts', 'user', '?'),
                'pathVars' => array('', '', 'userId'),
                'method' => 'retrieveSelectedUser',
                'shortHelp' => 'Returns selectedUser object for given user',
                'longHelp' => 'include/api/html/user.html',
            ),
        );
    }

    /**
     * Retrieves the current user info
     *
     * @param $api
     * @param $args
     * @return array
     */
    public function retrieveCurrentUser($api, $args) {
        global $current_user;

        $data = parent::retrieveCurrentUser($api, $args);

        // Add Forecasts-specific items to returned data
        $data['current_user']['isManager'] = User::isManager($current_user->id);
        $data['current_user']['showOpps'] = false;
        $data['current_user']['first_name'] = $current_user->first_name;
        $data['current_user']['last_name'] = $current_user->last_name;

        return $data;
    }

    /**
     * Retrieves a "selecteUser" object for a given user id
     *
     * @param $api
     * @param $args
     * @return array
     */
    public function retrieveSelectedUser($api, $args) {
        global $locale;
        $uid = $args['userId'];
        $user = BeanFactory::getBean('Users', $uid);
        $data = array();
        $data['id'] = $user->id;
        $data['full_name'] = $locale->getLocaleFormattedName($user->first_name,$user->last_name);
        $data['first_name'] = $user->first_name;
        $data['last_name'] = $user->last_name;
        $data['isManager'] = User::isManager($user->id);
        return $data;
    }
}
