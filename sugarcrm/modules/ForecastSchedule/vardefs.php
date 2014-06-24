<?php
if(!defined('sugarEntry') || !sugarEntry) die('Not A Valid Entry Point');
/*
 * Your installation or use of this SugarCRM file is subject to the applicable
 * terms available at
 * http://support.sugarcrm.com/06_Customer_Center/10_Master_Subscription_Agreements/.
 * If you do not agree to all of the applicable terms or do not have the
 * authority to bind the entity as an authorized representative, then do not
 * install or use this SugarCRM file.
 *
 * Copyright (C) SugarCRM Inc. All rights reserved.
 */
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
    'type' => 'id',
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
//BEGIN SUGARCRM flav=int ONLY
    'currency_id' =>
    array (
        'name' => 'currency_id',
        'vname' => 'LBL_CURRENCY',
        'type' => 'currency_id',
        'dbType' => 'id',
        'default'=>'-99',
        'required' => true,
    ),
    'base_rate' =>
    array (
        'name' => 'base_rate',
        'vname' => 'LBL_BASE_RATE',
        'type' => 'decimal',
        'len' => '26,6',
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
    * expected_commit_stage is used to specify forecast commit ranges (Include, Likely, Omit etc.)
    */
    'expected_commit_stage' =>
    array (
        'name' => 'expected_commit_stage',
        'vname' => 'LBL_COMMIT_STAGE',
        'type' => 'enum',
        'options' => 'commit_stage_dom',
        'len' => '20',
    ),
//END SUGARCRM flav=int ONLY
 )
, 'indices' => array (
       array('name' =>'forecastschedulepk', 'type' =>'primary', 'fields'=>array('id'))
       )
);
?>
