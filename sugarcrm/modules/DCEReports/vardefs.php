<?php
/*********************************************************************************
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
$dictionary['DCEReport'] = array(
    'table'=>'dcereports',
    'audited'=>true,
    'fields'=>array (
        'num_of_logins' => 
        array (
            'required' => false,
            'name' => 'num_of_logins',
            'vname' => 'LBL_NUM_OF_LOGINS',
            'type' => 'int',
            'comments' => 'sum of number of logins in given time range',
            'reportable' => 1,
        ),
        'num_of_users' => 
        array (
            'required' => false,
            'name' => 'num_of_users',
            'vname' => 'LBL_NUM_OF_USERS',
            'type' => 'int',
            'comments' => 'sum of number of users in given time range',
            'reportable' => 1,
        ),
        'max_num_sessions' => 
        array (
            'required' => false,
            'name' => 'max_num_sessions',
            'vname' => 'LBL_MAX_NUM_SESSIONS',
            'type' => 'int',
            'comments' => 'max number of sessions in given time range',
            'reportable' => 1,
        ),
        'num_of_requests' => 
        array (
            'required' => false,
            'name' => 'num_of_requests',
            'vname' => 'LBL_NUM_OF_REQUESTS',
            'type' => 'int',
            'comments' => 'sum of number of requests in given time range',
            'reportable' => 1,
        ),
        'memory' => 
        array (
            'required' => false,
            'name' => 'memory',
            'vname' => 'LBL_MEMORY',
            'type' => 'int',
            'comments' => 'sum of memory usage in given time range',
            'reportable' => 1,
        ),
        'num_of_files' => 
        array (
            'required' => false,
            'name' => 'num_of_files',
            'vname' => 'LBL_NUM_OF_FILES',
            'type' => 'int',
            'comments' => 'sum of number of files in given time range',
            'reportable' => 1,
        ),
        'num_of_queries' => 
        array (
            'required' => false,
            'name' => 'num_of_queries',
            'vname' => 'LBL_NUM_OF_QUERIES',
            'type' => 'int',
            'comments' => 'sum of number of queries in given time range',
            'reportable' => 1,
        ),
        'last_login_time' =>
        array (
            'name' => 'last_login_time',
            'vname' => 'LBL_LAST_LOGIN_TIME',
            'type' => 'datetime',
            'comments' => 'last recorded login in given time range',
            'reportable' => 1,
        ),
        'slow_logged_queries' =>
        array (
            'name' => 'slow_logged_queries',
            'vname' => 'LBL_SLOW_LOGGED_QUERIES',
            'type' => 'text',
            'comments' => 'queries that were logged as being slow',
            'reportable' => 1,
        ),
        'instance_name' =>
        array (
            'name' => 'instance_name',
            'vname' => 'LBL_INSTANCE_NAME',
            'type' => 'varchar',
            'len' => '255',
            'comments' => 'name of instance being reported',
            'reportable' => 1,
        ),
        'instance_id' =>
        array (
            'name' => 'instance_id',
            'vname' => 'LBL_INSTANCE_ID',
            'type' => 'id',
            'comments' => 'id of instance being reported on',
            'reportable' => 1,
        ),
        'time_start' =>
        array (
            'name' => 'time_start',
            'vname' => 'LBL_TIME_START',
            'type' => 'datetime',
            'comments' => 'starting limit of time being reported on',
            'reportable' => 1,
        ),
        'time_end' =>
        array (
            'name' => 'time_end',
            'vname' => 'LBL_TIME_END',
            'type' => 'datetime',
            'comments' => 'ending limit of time being reported on',
            'reportable' => 1,
        ),
////////No DB field For Licensing Report
        'account_name' =>
        array (
            'name' => 'account_name',
            'vname' => 'LBL_ACCOUNT_NAME',
            'type' => 'varchar',
            'source'=>'non-db',
        ),
        'first_name' =>
        array (
            'name' => 'first_name',
            'vname' => 'LBL_FIRST_NAME',
            'type' => 'varchar',
            'source'=>'non-db',
        ),
        'last_name' =>
        array (
            'name' => 'last_name',
            'vname' => 'LBL_LAST_NAME',
            'type' => 'varchar',
            'source'=>'non-db',
        ),
        'contact_role' =>
        array (
            'name' => 'contact_role',
            'vname' => 'LBL_CONTACT_ROLE',
            'type' => 'varchar',
            'source'=>'non-db',
        ),
    ),
    'indices' => array (
        'id'=>array('name' =>'dcereportsspk', 'type' =>'primary', 'fields'=>array('id'))
    ),
);

VardefManager::createVardef('DCEReports','DCEReport', array('basic','team_security'));
?>
