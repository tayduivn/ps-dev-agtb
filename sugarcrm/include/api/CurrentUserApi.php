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
require_once('include/SugarFields/SugarFieldHandler.php');

class CurrentUserApi extends SugarApi {
    public function registerApiRest() {
        return array(
            'retrieve' => array(
                'reqType' => 'GET',
                'path' => array('me',),
                'pathVars' => array(),
                'method' => 'retrieveCurrentUser',
                'shortHelp' => 'Returns current user',
                'longHelp' => 'include/api/help/me.html',
            ),
            'update' => array(
                'reqType' => 'PUT',
                'path' => array('me',),
                'pathVars' => array(),
                'method' => 'updateCurrentUser',
                'shortHelp' => 'Updates current user',
                'longHelp' => 'include/api/help/me.html',
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
        $user_data = array(
            'timezone' => $current_user->getPreference('timezone'),
            'datepref' => $current_user->getPreference('datef'),
            'timepref' => $current_user->getPreference('timef'),
        );



        if ( isset($_SESSION['type']) && $_SESSION['type'] == 'support_portal' ) {
            $contact = BeanFactory::getBean('Contacts',$_SESSION['contact_id']);
            $user_data['type'] = 'support_portal';
            $user_data['user_id'] = $current_user->id;
            $user_data['user_name'] = $current_user->user_name;
            $user_data['id'] = $_SESSION['contact_id'];
            $user_data['account_ids'] = $_SESSION['account_ids'];
            $user_data['full_name'] = $contact->full_name;
            $user_data['portal_name'] = $contact->portal_name;
        } else {
            $user_data['type'] = 'user';
            $user_data['id'] = $current_user->id;
            $user_data['full_name'] = $current_user->full_name;
            $user_data['user_name'] = $current_user->user_name;
        }
        return $data = array('current_user'=>$user_data);

    }
    /**
     * Updates current user info
     *
     * @param $api
     * @param $args
     * @return array
     */
    public function updateCurrentUser($api, $args) {
        global $current_user;

        if ( isset($_SESSION['type']) && $_SESSION['type'] == 'support_portal' ) {
            $bean = BeanFactory::getBean('Contacts',$_SESSION['contact_id']);
        } else {
            $bean = $current_user;
        }

        // setting these for the loadBean
        $args['module'] = $bean->module_name;
        $args['record'] = $bean->id;

        $id = $this->updateBean($bean, $api, $args);

        return $this->retrieveCurrentUser($api, $args);
    }
}
