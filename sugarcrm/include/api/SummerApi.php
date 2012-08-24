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
/**
 * Summer invite API
 */
class SummerApi extends SugarApi {

    public function __construct()
    {
        $this->box = BoxOfficeClient::getInstance();
    }

    public function registerApiRest() {
        return array(
            'office' => array(
                'reqType' => 'GET',
                'path' => array('summer','office'),
                'pathVars' => array('',''),
                'method' => 'office',
                'shortHelp' => 'Office Surroundings',
            ),
            'invite' => array(
                'reqType' => 'POST',
                'path' => array('summer','invite'),
                'pathVars' => array('',''),
                'method' => 'invite',
                'shortHelp' => 'Invite People',
            ),
            'logout' => array(
                'reqType' => 'POST',
                'path' => array('summer','logout'),
                'pathVars' => array('',''),
                'method' => 'logout',
                'shortHelp' => 'Log out of the instance',
            )
        );
    }

    public function office($api, $args)
    {
        return $this->box->getUsersInstances();
    }

    public function invite($api, $args)
    {
        if(!isset($args['email'])) {
            throw new SugarApiExceptionMissingParameter('Email is missing.');
        }
        return $this->box->invite($args['email']);
    }

    public function logout($api, $args)
    {
        $this->box->deleteSession();
        unset($_SESSION['authenticated_user_id']);
        return true;
    }
}
