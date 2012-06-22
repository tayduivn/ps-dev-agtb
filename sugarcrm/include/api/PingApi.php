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

// A simple example class
class PingApi extends SugarApi {
    public function registerApiRest() {
        return array(
            'ping' => array(
                'reqType' => 'GET',
                'path' => array('ping'),
                'pathVars' => array(''),
                'method' => 'ping',
                'shortHelp' => 'An example API only responds with pong',
                'longHelp' => 'include/api/html/ping_base_help.html',
            ),
            'pingWithTime' => array(
                'reqType' => 'GET',
                'path' => array('ping', 'whattimeisit'),
                'pathVars' => array('', 'subMethod'),
                'method' => 'ping',
                'shortHelp' => 'An example API only responds with the current time in server format.',
                'longHelp' => 'include/api/html/ping_whattimeisit_help.html',
            ),
        );
    }

    public function registerApiSoap() {
        return array(
            'functions' => array(
                'ping' => array(
                    'methodName' => 'ping',
                    'requestVars' => array(
                    ),
                    'returnVars' => array(
                        'xsd:string',
                    ),
                    'method' => 'ping',
                    'shortHelp' => 'Sample/test API that only responds with pong',
                ),
                'pingWithTime' => array(
                    'methodName' => 'pingTime',
                    'requestVars' => array(
                    ),
                    'extraVars' => array(
                        'subMethod' => 'whattimeisit',
                    ),
                    'returnVars' => array(
                        'xsd:string',
                    ),
                    'method' => 'ping',
                    'shortHelp' => 'Sample/test API that responds with the curernt date/time',
                ),
            ),
            'types' => array(),
        );
    }

    public function ping($api, $args) {
        if ( isset($args['subMethod']) && $args['subMethod'] == 'whattimeisit' ) {
            require_once('include/SugarDateTime.php');
            $dt = new SugarDateTime('now');
            return $dt->asDb();
        }

        // Just a normal ping request
        return 'pong';
    }

}
