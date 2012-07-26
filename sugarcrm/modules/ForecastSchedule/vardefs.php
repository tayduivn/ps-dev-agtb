<?php
if(!defined('sugarEntry') || !sugarEntry) die('Not A Valid Entry Point');
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
$dictionary['ForecastSchedule'] = array('table' => 'forecast_schedule',
'acl_fields'=>false
                               ,'fields' => array (
  'id' => 
  array (
    'name' => 'id',
    'vname' => 'LB_FS_KEY',
    'type' => 'id',
    'required'=>true,
    'reportable'=>false,
    'comment' => 'Unique identifier',
  ),
  
  'timeperiod_id' => 
  array (
    'name' => 'timeperiod_id',
    'vname' => 'LBL_FS_TIMEPERIOD_ID',
    'type' => 'id',
    'reportable'=>false,
    'comment' => 'ID of the associated time period for this forecast schedule',
  ),
 
  'user_id' => 
  array (
    'name' => 'user_id',
    'vname' => 'LBL_FS_USER_ID',
    'type' => 'id',
    'reportable'=>false,
    'comment' => 'User to which this forecast schedule pertains',
  ),

  'cascade_hierarchy' => 
  array (
    'name' => 'cascade_hierarchy',
    'vname' => 'LBL_FS_CASCADE',
    'type' => 'bool',
    'comment' => 'Flag indicating if a forecast for a manager is propagated to his reports',
  ),

  'forecast_start_date' => 
  array (
    'name' => 'forecast_start_date',
    'vname' => 'LBL_FS_FORECAST_START_DATE',
    'type' => 'date',
    'comment' => 'Starting date for this forecast',
  ),
  
 'status' => 
  array (
    'name' => 'status',
    'vname' => 'LBL_FS_STATUS',
    'type' => 'enum',
    'len' => 100,
    'options' => 'forecast_schedule_status_dom',
	'comment' => 'Status of this forecast',        
  ),

 'created_by' => 
  array (
    'name' => 'created_by',
    'vname' => 'LBL_FS_CREATED_BY',
    'type' => 'varchar',
    'len' => '36',
    'comment' => 'User name who created record',
  ),
  
  'date_entered' => 
  array (
    'name' => 'date_entered',
    'vname' => 'LBL_FS_DATE_ENTERED',
    'type' => 'datetime',
    'comment' => 'Date record created',
  ),
  
  'date_modified' => 
  array (
    'name' => 'date_modified',
    'vname' => 'LBL_FS_DATE_MODIFIED',
    'type' => 'datetime',
    'comment' => 'Date record modified',
  ),
  
  'deleted' => 
  array (
    'name' => 'deleted',
    'vname' => 'LBL_FS_DELETED',
    'type' => 'bool',
    'reportable'=>false,
    'comment' => 'Record deletion indicator',
  ),

  /*
   * expected_base_case is used to store the value of the user's expected best case
   */
  'expected_best_case' =>
   array (
       'name' => 'expected_best_case',
       'vname' => 'LBL_EXPECTED_BEST_CASE',
       'dbType' => 'decimal',
       'type' => 'currency',
       'len' => '26,6',
   ),

  /*
   * expected_likely_case is used to store the value of the user's expected likely case
   */
   'expected_likely_case' =>
   array(
       'name' => 'expected_likely_case',
       'vname' => 'LBL_EXPECTED_LIKELY_CASE',
       'dbType' => 'decimal',
       'type' => 'currency',
       'len' => '26,6',
   ),

  /*
   * expected_worst_case is used to store the value of the user's expected worst case
   */
   'expected_worst_case' =>
   array(
       'name' => 'expected_worst_case',
       'vname' => 'LBL_EXPECTED_WORST_CASE',
       'dbType' => 'decimal',
       'type' => 'currency',
       'len' => '26,6',
   ),

  /*
   * expected_amount is used to store the value of the user's expected amount
   */
   'expected_amount' =>
   array(
       'name' => 'expected_amount',
       'vname' => 'LBL_EXPECTED_AMOUNT',
       'dbType' => 'decimal',
       'type' => 'currency',
       'len' => '26,6',
   ),

  /*
   * include_expected is used to determine whether or not the expected values should be included in the forecast
   */
   'include_expected' =>
   array(
       'name' => 'include_expected',
       'vname' => 'LBL_INCLUDE_EXPECTED',
       'type' => 'bool',
       'default' => '0',
   ),

 )
, 'indices' => array (
       array('name' =>'forecastschedulepk', 'type' =>'primary', 'fields'=>array('id'))
       )
);
?>
