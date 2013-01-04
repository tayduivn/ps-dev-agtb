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

$dictionary['ForecastOpportunities'] = array( 'table'=>'does_not_exist',
'acl_fields' =>false,
'fields' => array (
  'id' =>
  array (
    'name' => 'id',
    'vname' => 'LB_FS_KEY',
    'type' => 'id',
	'source'=>'non-db',
    'reportable'=>true,
  ),
  'name' =>
  array (
    'name' => 'name',
    'vname' => 'LB_FS_KEY',
    'type' => 'id',
	'source'=>'non-db',
  ),
  'revenue' =>
  array (
    'name' => 'revenue',
    'vname' => 'LB_FS_KEY',
    'type' => 'id',
	'source'=>'non-db',
  ),
  'date_entered' =>
  array (
    'name' => 'date_entered',
    'vname' => 'LB_FS_KEY',
    'type' => 'id',
	'source'=>'non-db',
  ),
  'weighted_value' =>
  array (
    'name' => 'weighted_value',
    'vname' => 'LB_FS_KEY',
    'type' => 'id',
	'source'=>'non-db',
  ),

  'account_name' =>
  array (
    'name' => 'account_name',
    'vname' => 'LB_FS_KEY',
    'type' => 'id',
	'source'=>'non-db',
  ),
  'probability' =>
  array (
    'name' => 'probability',
    'vname' => 'LB_FS_KEY',
    'type' => 'id',
	'source'=>'non-db',
  ),
  'worksheet_id' =>
  array (
    'name' => 'worksheet_id',
    'vname' => 'LB_FS_KEY',
    'type' => 'id',
	'source'=>'non-db',
  ),
  //used to store worksheet values.
 'wk_likely_case' =>
  array (
    'name' => 'wk_likely_case',
    'vname' => 'LB_FS_LIKELY_CASE',
    'type' => 'currency',
	'source'=>'non-db',
  ),
  //used to store worksheet values.
  'wk_worst_case' =>
  array (
    'name' => 'wk_worst_case',
    'vname' => 'LB_FS_WORST_CASE',
    'type' => 'currency',
	'source'=>'non-db',
    ) ,
   //used to store worksheet values.
  'wk_best_case' =>
  array (
    'name' => 'wk_best_case',
    'vname' => 'LB_FS_BEST_CASE',
    'type' => 'currency',
	'source'=>'non-db',
    ),
  'next_step' =>
  array (
    'name' => 'next_step',
    'vname' => 'LB_FS_KEY',
    'type' => 'varchar',
    'source'=>'non-db',
  ),
  'opportunity_type' =>
  array (
    'name' => 'opportunity_type',
    'vname' => 'LB_FS_KEY',
    'type' => 'varchar',
    'source'=>'non-db',
  ),
  'description' =>
  array (
    'name' => 'descrfiption',
    'vname' => 'LB_FS_KEY',
    'type' => 'varchar',
    'source'=>'non-db',
  ),
  //represent's a value committed by forecast user.
  'best_case' =>
  array (
    'name' => 'best_case',
    'vname' => 'LB_BEST_CASE_VALUE',
    'type' => 'currency',
	'source'=>'non-db',
  ),
  //represent's a value committed by forecast user.
  'likely_case' =>
  array (
    'name' => 'likely_case',
    'vname' => 'LB_LIKELY_VALUE',
    'type' => 'currency',
    'source'=>'non-db',
  ),
  //represent's a value committed by forecast user.
  'worst_case' =>
  array (
    'name' => 'worst_case',
    'vname' => 'LB_WORST_CASE_VALUE',
    'type' => 'currency',
    'source'=>'non-db',
  ),

  ),
);

$dictionary['ForecastDirectReports'] = array( 'table'=>'does_not_exist',
'acl_fields' =>false,
'fields' => array (
  'id' =>
  array (
    'name' => 'id',
    'vname' => 'LB_FS_KEY',
    'type' => 'id',
	'source'=>'non-db',
  ),
  'user_id' =>
  array (
    'name' => 'user_id',
    'vname' => 'LB_FS_KEY',
    'type' => 'id',
	'source'=>'non-db',
  ),
  'user_name' =>
  array (
    'name' => 'user_name',
    'vname' => 'LB_FS_KEY',
    'type' => 'id',
	'source'=>'non-db',
  ),
  'first_name' =>
  array (
    'name' => 'first_name',
    'vname' => 'LB_FS_KEY',
    'type' => 'id',
	'source'=>'non-db',
  ),
  'last_name' =>
  array (
    'name' => 'last_name',
    'vname' => 'LB_FS_KEY',
    'type' => 'id',
	'source'=>'non-db',
  ),

  'opp_count' =>
  array (
    'name' => 'opp_count',
    'vname' => 'LB_FS_KEY',
    'type' => 'id',
	'source'=>'non-db',
  ),
  'opp_weigh_value' =>
  array (
    'name' => 'opp_weigh_value',
    'vname' => 'LB_FS_KEY',
    'type' => 'id',
	'source'=>'non-db',
  ),
  //represent's a value committed by forecast user.
  'best_case' =>
  array (
    'name' => 'best_case',
    'vname' => 'LB_BEST_CASE_VALUE',
    'type' => 'currency',
	'source'=>'non-db',
  ),
  //represent's a value committed by forecast user.
  'likely_case' =>
  array (
    'name' => 'likely_case',
    'vname' => 'LB_LIKELY_VALUE',
    'type' => 'currency',
    'source'=>'non-db',
  ),
  //represent's a value committed by forecast user.
  'worst_case' =>
  array (
    'name' => 'worst_case',
    'vname' => 'LB_WORST_CASE_VALUE',
    'type' => 'currency',
    'source'=>'non-db',
  ),
  //used to store worksheet values.
 'wk_likely_case' =>
  array (
    'name' => 'wk_likely_case',
    'vname' => 'LB_FS_LIKELY_CASE',
    'type' => 'currency',
	'source'=>'non-db',
  ),
  //used to store worksheet values.
  'wk_worst_case' =>
  array (
    'name' => 'wk_worst_case',
    'vname' => 'LB_FS_WORST_CASE',
    'type' => 'currency',
	'source'=>'non-db',
    ) ,
   //used to store worksheet values.
  'wk_best_case' =>
  array (
    'name' => 'wk_best_case',
    'vname' => 'LB_FS_BEST_CASE',
    'type' => 'currency',
	'source'=>'non-db',
    ) ,
  'ref_timeperiod_id' =>
  array (
    'name' => 'ref_timeperiod_id',
    'vname' => 'LB_FS_KEY',
    'type' => 'id',
	'source'=>'non-db',
  ),
   'ref_user_id' =>
  array (
    'name' => 'ref_user_id',
    'vname' => 'LB_FS_KEY',
    'type' => 'id',
	'source'=>'non-db',
  ),
   'forecast_type' =>
  array (
    'name' => 'forecast_type',
    'vname' => 'LB_FS_KEY',
    'type' => 'id',
	'source'=>'non-db',
  ),
   'date_entered' =>
  array (
    'name' => 'date_entered',
    'vname' => 'LBL_FDR_DATE_COMMIT',
    'type' => 'datetime',
    'source'=>'non-db',
  ),
   'date_comitted' =>
  array (
    'name' => 'date_comitted',
    'vname' => 'LBL_FDR_DATE_COMMIT',
    'type' => 'date',
    'source'=>'non-db',
  ),

  ),
);
$dictionary['Forecast'] = array('table' => 'forecasts'
,'acl_fields' =>false,
   'fields' => array (
  'id' =>
  array (
    'name' => 'id',
    'vname' => 'LBL_FORECAST_ID',
    'type' => 'id',
    'required'=>true,
    'reportable'=>false,
    'comment' => 'Unique identifier',
  ),

 'timeperiod_id' =>
  array (
    'name' => 'timeperiod_id',
    'vname' => 'LBL_FORECAST_TIME_ID',
    'type' => 'enum',
    'dbType' => 'id',
    'reportable'=>true,
    'function' => 'getTimePeriodsDropDownForForecasts',
	'comment' => 'ID of the associated time period for this forecast',
   ),

  'forecast_type' =>
  array (
    'name' => 'forecast_type',
    'vname' => 'LBL_FORECAST_TYPE',
    'type' => 'enum',
    'len' => 100,
    'massupdate' => false,
    'options' => 'forecast_type_dom',
	'comment' => 'Indicator of whether forecast is direct or rollup',
  ),
  'opp_count' =>
  array (
    'name' => 'opp_count',
    'vname' => 'LBL_FORECAST_OPP_COUNT',
    'type' => 'int',
    'len' => '5',
    'comment' => 'Number of opportunities represented by this forecast',
  ),
  'opp_weigh_value' =>
  array (
    'name' => 'opp_weigh_value',
    'vname' => 'LBL_FORECAST_OPP_WEIGH',
    'type' => 'int',
    'comment' => 'Weighted amount of all opportunities represented by this forecast',
  ),
   'currency_id' =>
   array (
       'name' => 'currency_id',
       'vname' => 'LBL_CURRENCY',
       'type' => 'id',
       'required' => true,
   ),
   'base_rate' =>
   array (
       'name' => 'base_rate',
       'vname' => 'LBL_BASE_RATE',
       'type' => 'double',
       'required' => true,
   ),
   'best_case' =>
  array (
    'name' => 'best_case',
    'vname' => 'LBL_FORECAST_OPP_BEST_CASE',
    'type' => 'currency',
    'comment' => 'Best case forecast amount',
  ),
  //renamed commit_value to likely_case
  'likely_case' =>
  array (
    'name' => 'likely_case',
    'vname' => 'LBL_FORECAST_OPP_COMMIT',
    'type' => 'currency',
    'comment' => 'Likely case forecast amount',
  ),
  'worst_case' =>
  array (
    'name' => 'worst_case',
    'vname' => 'LBL_FORECAST_OPP_WORST',
    'type' => 'currency',
    'comment' => 'Worst case likely amount',
  ),
'user_id' =>
  array (
    'name' => 'user_id',
    'vname' => 'LBL_FORECAST_USER',
    'type' => 'id',
    'comment' => 'User to which this forecast pertains',
  ),
'date_entered' =>
  array (
    'name' => 'date_entered',
    'vname' => 'LBL_DATE_ENTERED',
    'type' => 'datetime',
    'required'=>true,
    'comment' => 'Date record created',
  ),
'date_modified' =>
  array (
    'name' => 'date_modified',
    'vname' => 'LBL_DATE_MODIFIED',
    'type' => 'datetime',
    'required'=>true,
    'comment' => 'Date record modified',
  ),
 'deleted' =>
  array (
    'name' => 'deleted',
    'vname' => 'LBL_DELETED',
    'type' => 'bool',
    'required' => false,
    'reportable'=>false,
    'comment' => 'Record deletion indicator',
  ),
 /*'timeperiod_name'=>
   array(
		'name'=>'timeperiod_name',
		'rname'=>'name',
		'id_name'=>'timeperiod_id',
		'vname'=>'LBL_TIMEPERIOD_NAME',
		'type'=>'relate',
		'table'=>'timeperiods',
		'isnull'=>'true',
		'module'=>'TimePeriods',
		'massupdate'=>false,
		'source'=>'non-db'
		),*/
 'user_name'=>
   array(
		'name'=>'user_name',
		'rname'=>'user_name',
		'id_name'=>'user_id',
		'vname'=>'LBL_USER_NAME',
		'type'=>'relate',
		'table'=>'users',
		'isnull'=>'true',
		'module'=>'Users',
		'massupdate'=>false,
		'source'=>'non-db'
		),
 'reports_to_user_name'=>
   array(
		'name'=>'reports_to_user_name',
		'rname'=>'user_name',
		'id_name'=>'reports_to_user_name',
		'vname'=>'LBL_REPORTS_TO_USER_NAME',
		'type'=>'relate',
		'table'=>'reports_to',
		'isnull'=>'true',
		'module'=>'Users',
		'massupdate'=>false,
		'source'=>'non-db'
		),
//timeperiod's start date
 'start_date' =>
	array (
		'name' => 'start_date',
    	'type' => 'date',
		'source'=>'non-db',
		'table' => 'timeperiods',
  	),
//timeperiod's end date
 'end_date' =>
	array (
		'name' => 'end_date',
    	'type' => 'date',
		'source'=>'non-db',
		'table' => 'timeperiods',
  	),
//timeperiod's name
 'name' =>
	array (
		'name' => 'name',
    	'type' => 'varchar',
		'source'=>'non-db'
  	),
  'created_by_link' =>
  array (
    'name' => 'created_by_link',
    'type' => 'link',
    'relationship' => 'forecasts_created_by',
    'vname' => 'LBL_CREATED_BY_USER',
    'link_type' => 'one',
    'module'=>'Users',
    'bean_name'=>'User',
    'source'=>'non-db',
  ),

  ),

 'relationships' => array (

   'forecasts_created_by' =>
   array('lhs_module'=> 'Users', 'lhs_table'=> 'users', 'lhs_key' => 'id',
   'rhs_module'=> 'Forecasts', 'rhs_table'=> 'forecasts', 'rhs_key' => 'user_id',
   'relationship_type'=>'one-to-many')

),
 'indices' => array (
       array('name' =>'forecastspk', 'type' =>'primary', 'fields'=>array('id')),
       array('name' =>'idx_forecast_user_tp', 'type' =>'index', 'fields'=>array('user_id', 'timeperiod_id', 'date_modified')),
       ),

    'acls' => array (
        'SugarACLStatic' => true,
    ),
);

$dictionary['Worksheet'] =  array('table' => 'worksheet', 'fields' => array (
  'id' =>
  array (
    'name' => 'id',
    'vname' => 'LBL_WK_ID',
    'type' => 'id',
    'reportable'=>false,
    'comment' => 'Unique identifier',
  ),
//worsheet owner/creator's id
  'user_id' =>
  array (
    'name' => 'user_id',
    'vname' => 'LBL_WK_USER_ID',
    'type' => 'id',
    'comment' => 'User to which this worksheet pertains',
  ),
  //worsheet is for this timeperiod.
  'timeperiod_id' =>
  array (
    'name' => 'timeperiod_id',
    'vname' => 'LBL_WK_TIMEPERIOD_ID',
    'type' => 'enum',
    'dbType' => 'id',
    'reportable'=> true,
    'function' => 'getTimePeriodsDropDownForForecasts',
    'comment' => 'ID of the associated time period for this worksheet',
  ),
  //worksheet is for this forecast type.
  //valid values are Direct/Rollup.
   'forecast_type'=>
  array(
  	'name'=>'forecast_type',
  	'vname'=>'LBL_WK_FORECAST_TYPE',
  	'type'=>'varchar',
  	'len' => 100,
  	'comment' => 'Indicator of whether worksheet is direct or rollup',
  ),
  //Worsheet entry
  //can be userid or opportunity it
  'related_id' =>
  array (
    'name' => 'related_id',
    'vname' => 'LBL_WK_RELATED_ID',
    'type' => 'id',
    'comment' => 'User ID or Opportunity ID for this worksheet',
  ),
  //forecast type for related_id, null if related_id represents an opportunity.
   'related_forecast_type'=>
  array(
  	'name'=>'related_forecast_type',
  	'vname'=>'LBL_WK_FORECAST_TYPE',
  	'type'=>'varchar',
  	'len' => 100,
    'reportable' => false,
  	'comment' => 'Direct or rollup, or null if related_id is an Opportunity',
  ),
    'currency_id' =>
    array (
        'name' => 'currency_id',
        'vname' => 'LBL_CURRENCY',
        'type' => 'id',
        'required' => true,
    ),
    'base_rate' =>
    array (
        'name' => 'base_rate',
        'vname' => 'LBL_BASE_RATE',
        'type' => 'double',
        'required' => true,
    ),
    'best_case' =>
  array (
    'name' => 'best_case',
    'vname' => 'LBL_BEST_CASE_VALUE',
    'type' => 'currency',
    'comment' => 'Best case worksheet amount',
  ),
  'likely_case' =>
  array (
    'name' => 'likely_case',
    'vname' => 'LBL_LIKELY_CASE_VALUE',
    'type' => 'currency',
    'comment' => 'Likely case worksheet amount',
  ),
  'worst_case' =>
  array (
    'name' => 'worst_case',
    'vname' => 'LBL_WORST_CASE_VALUE',
    'type' => 'currency',
    'comment' => 'Worst case worksheet amount',
  ),
  'date_modified' =>
  array (
    'name' => 'date_modified',
    'vname' => 'LBL_DATE_MODIFIED',
    'type' => 'datetime',
    'comment' => 'Date record modified',
  ),
  'modified_user_id' =>
  array (
    'name' => 'modified_user_id',
    'vname' => 'LBL_MODIFIED_USER_ID',
    'type' => 'id',
    'len' => 36,
    'comment' => 'User ID that last modified record',
  ),
  'deleted' =>
  array (
    'name' => 'deleted',
    'vname' => 'LBL_DELETED',
    'type' => 'bool',
    'default' => '0',
    'reportable'=>false,
    'comment' => 'Record deletion indicator'
  ),
  'commit_stage' =>
  array (
    'name' => 'commit_stage',
    'vname' => 'LBL_COMMIT_STAGE',
    'type' => 'enum',
    'options' => 'commit_stage_dom',
    'len' => '20',
    'comment' => 'The bucket stage that the opportunity belongs to',
  ),
  'op_probability' =>
  array (
    'name' => 'op_probability',
    'vname' => 'LBL_OW_PROBABILITY',
    'type' => 'int',
    'dbType' => 'double',
    'comment' => 'Worksheet Placeholder for the probability of closure',
    'validation' => array('type' => 'range', 'min' => 0, 'max' => 100),
    'default' => '0'
  ),
  'quota' =>
	array (
	  'name' => 'quota',
	  'vname' => 'LBL_AMOUNT',
	  'type' => 'currency',
	  'reportable' => true,
	  'importable' => 'required',
	  'comment' => 'Worksheet placeholder for quota amount'
	),
  'version' =>
	array (
	  'name' => 'version',
	  'vname' => 'LBL_WK_VERSION',
	  'type'=>'int',
	  'default' => 1,
      'studio' => false,
	  'comment' => 'Worksheet version - draft = 0'
	),
    'revision' =>
    array (
      'name' => 'revision',
      'vname' => 'LBL_WK_REVISION',
      'type'=>'double',
      'default' => 0,
      'studio' => false,
      'comment' => 'Revision # - microtime of save.'
    ),
 ),
 'indices' => array (
       array('name' =>'worksheetpk', 'type' =>'primary', 'fields'=>array('id')),
       array('name' =>'idx_worksheet_user_id', 'type' =>'index', 'fields'=>array('user_id')),
       array('name' =>'idx_worksheet_rel_id_del', 'type' =>'index', 'fields'=>array('related_id', 'user_id', 'deleted', 'version', 'revision')),
 )

);

$dictionary['ForecastWorksheet'] = array('table'=>'does_not_exist',
'acl_fields' =>false,
'fields' => array (
    'id' =>
    array (
        'name' => 'id',
        'vname' => 'LBL_ID',
        'type' => 'id',
    ),
    'product_id' =>
    array(
        'name' => 'product_id',
        'vname' => 'LBL_PRODUCT_ID',
        'type' => 'id'
    ),
    'assigned_user_id' =>
    array (
        'name' => 'assigned_user_id',
        'vname' => 'LBL_ASSIGNED_USER_ID',
        'type' => 'id',
    ),
    'commit_stage' =>
    array(
        'name' => 'commit_stage',
        'type' => 'enum',
        'options' => 'commit_stage_dom',
        'vname' => 'LBL_COMMIT_STAGE',
    ),
    'name' =>
    array(
        'name' => 'name',
        'vname' => 'LBL_NAME',
        'type' => 'varchar',
    ),
    'date_closed' =>
    array(
        'name' => 'date_closed',
        'vname' => 'LBL_DATE_CLOSED',
        'type' => 'date',
    ),
    'sales_stage' =>
    array(
        'name' => 'sales_stage',
        'vname' => 'LBL_SALES_STAGE',
        'type' => 'varchar',
    ),
    'probability' =>
    array(
        'name' => 'probability',
        'vname' => 'LBL_OW_PROBABILITY',
        'type' => 'int',
    ),
    'amount' =>
    array(
        'name' => 'amount',
        'vname' => 'LBL_AMOUNT',
        'type' => 'currency',
    ),
    'best_case' =>
    array(
        'name' => 'best_case',
        'vname' => 'LBL_BEST_CASE',
        'type' => 'currency',
    ),
    'likely_case' =>
    array(
        'name' => 'likely_case',
        'vname' => 'LBL_LIKELY_CASE',
        'type' => 'currency',
    ),
    'worst_case' =>
    array(
        'name' => 'worst_case',
        'vname' => 'LBL_WORST_CASE',
        'type' => 'currency',
    ),
    'currency_id' =>
    array(
        'name' => 'currency_id',
        'vname' => 'LBL_CURRENCY_ID',
        'type' => 'id',
    ),
    'base_rate' =>
    array(
        'name' => 'base_rate',
        'vname' => 'LBL_BASE_RATE',
        'type' => 'double',
    ),
    'version' =>
    array(
        'name' => 'version',
        'vname' => 'LBL_VERSION',
        'type' => 'int'
    )
),
);


$dictionary['ForecastManagerWorksheet'] = array('table'=>'does_not_exist',
'acl_fields' =>false,
'fields' => array (
    'id' =>
    array (
        'name' => 'id',
        'vname' => 'LBL_ID',
        'type' => 'id',
    ),
    'quota_id' =>
    array (
        'name' => 'quota_id',
        'vname' => 'LBL_QUOTA_ID',
        'type' => 'id',
    ),
    'forecast_id' =>
    array (
        'name' => 'forecast_id',
        'vname' => 'LBL_FORECAST_ID',
        'type' => 'id',
    ),
    'worksheet_id' =>
    array (
        'name' => 'worksheet_id',
        'vname' => 'LBL_WORKSHEET_ID',
        'type' => 'id',
    ),
    'name' =>
    array(
        'name' => 'name',
        'vname' => 'LBL_NAME',
        'type' => 'varchar',
    ),
    'amount' =>
    array(
        'name' => 'amount',
        'vname' => 'LBL_AMOUNT',
        'type' => 'currency',
    ),
    'quota' =>
    array(
        'name' => 'quota',
        'vname' => 'LBL_QUOTA',
        'type' => 'currency',
    ),
    'best_case' =>
    array(
        'name' => 'best_case',
        'vname' => 'LBL_BEST_CASE',
        'type' => 'currency',
    ),
    'best_case_adjusted' =>
    array(
        'name' => 'best_case_adjusted',
        'vname' => 'LBL_BEST_CASE_VALUE',
        'type' => 'currency',
    ),
    'likely_case' =>
    array(
        'name' => 'likely_case',
        'vname' => 'LBL_LIKELY_CASE',
        'type' => 'currency',
    ),
    'likely_case_adjusted' =>
    array(
        'name' => 'likely_case_adjusted',
        'vname' => 'LBL_LIKELY_CASE_VALUE',
        'type' => 'currency',
    ),
    'worst_case' =>
    array(
        'name' => 'worst_case',
        'vname' => 'LBL_WORST_CASE',
        'type' => 'currency',
    ),
    'worst_case_adjusted' =>
    array(
        'name' => 'worst_case_adjusted',
        'vname' => 'LBL_WORST_CASE_VALUE',
        'type' => 'currency',
    ),
    'currency_id' =>
    array(
        'name' => 'currency_id',
        'vname' => 'LBL_CURRENCY_ID',
        'type' => 'id',
    ),
    'timeperiod_id' =>
    array(
        'name' => 'timeperiod_id',
        'vname' => 'LBL_FORECAST_TIME_ID',
        'type' => 'id',
    ),
    'base_rate' =>
    array(
        'name' => 'base_rate',
        'vname' => 'LBL_BASE_RATE',
        'type' => 'double',
    ),
    'version' =>
    array(
        'name' => 'version',
        'vname' => 'LBL_VERSION',
        'type' => 'int',
    ),
    'user_id' =>
    array(
        'name' => 'user_id',
        'vname' => 'LBL_FS_USER_ID',
        'type' => 'id',
    ),
    'commit_stage' =>
    array(
        'name' => 'commit_stage',
        'vname' => 'LBL_COMMIT_STAGE',
        'type' => 'enum',
    ),
    'label' =>
    array(
        'name' => 'label',
        'vname' => 'LBL_LABEL',
        'type' => 'varchar',
    ),
    'date_modified' =>
    array(
        'name' => 'date_modified',
        'vname' => 'LBL_DATE_MODIFIED',
        'type' => 'datetime',
    )
),
);
