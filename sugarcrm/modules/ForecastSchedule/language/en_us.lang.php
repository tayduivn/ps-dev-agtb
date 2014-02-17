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
/*********************************************************************************
 * $Id: en_us.lang.php 54394 2010-02-09 20:38:34Z roger $
 * Description:  Defines the English language pack for the base application.
 * Portions created by SugarCRM are Copyright (C) SugarCRM, Inc.
 * All Rights Reserved.
 * Contributor(s): ______________________________________..
 ********************************************************************************/

$mod_strings = array (

  //module strings.
  'LBL_MODULE_NAME' => 'Forecasts',
  'LBL_MODULE_NAME_SINGULAR' => 'Forecast',
  'LNK_NEW_OPPORTUNITY' => 'Create Opportunity',
  'LBL_MODULE_TITLE' => 'Forecasts',
  'LBL_LIST_FORM_TITLE' => 'Committed Forecasts',
  'LNK_UPD_FORECAST' => 'Forecast worksheet',
  'LNK_QUOTA' => 'Quotas',
  'LNK_FORECAST_LIST' => 'View forecast history',
  'LBL_FORECAST_HISTORY' => 'Forecasts: History',
  'LBL_FORECAST_HISTORY_TITLE' => 'Forecasts: History',
  
  //var defs
  'LBL_TIMEPERIOD_NAME' => 'Time period',
  'LBL_USER_NAME' => 'User name',
  'LBL_REPORTS_TO_USER_NAME' => 'Reports to',
  
  //forecast table
  'LBL_FORECAST_ID' => 'ID',
  'LBL_FORECAST_TIME_ID' => 'Time period ID',
  'LBL_FORECAST_TYPE' => 'Forecast type',
  'LBL_FORECAST_OPP_COUNT' => 'Opportunities',
  'LBL_FORECAST_OPP_WEIGH'=> 'Weighted amount',
  'LBL_FORECAST_OPP_COMMIT' => 'Likely case',
  'LBL_FORECAST_USER' => 'User',
  'LBL_DATE_COMMITTED'=> 'Date committed',
  'LBL_DATE_ENTERED' => 'Date entered',
  'LBL_DATE_MODIFIED' => 'Date modified',
  'LBL_CREATED_BY' => 'Created by',
  'LBL_DELETED' => 'Deleted',
  
   //Quick Commit labels.
  'LBL_QC_TIME_PERIOD' => 'Time period:',
  'LBL_QC_OPPORTUNITY_COUNT' => 'Opportunity count:',
  'LBL_QC_WEIGHT_VALUE' => 'Weighted amount:',
  'LBL_QC_COMMIT_VALUE' => 'Commit amount:',
  'LBL_QC_COMMIT_BUTTON' => 'Commit',
  'LBL_QC_WORKSHEET_BUTTON' => 'Worksheet',
  'LBL_QC_ROLL_COMMIT_VALUE' => 'Rollup commit amount:',
  'LBL_QC_DIRECT_FORECAST' => 'My direct forecast:',
  'LBL_QC_ROLLUP_FORECAST' => 'Team Forecast:',
  'LBL_QC_UPCOMING_FORECASTS' => 'My Forecasts',
  'LBL_QC_LAST_DATE_COMMITTED' => 'Last commit date:',
  'LBL_QC_LAST_COMMIT_VALUE' => 'Last commit amount:',
  'LBL_QC_HEADER_DELIM'=> 'To',
  'LBL_CURRENCY' => 'Currency',
  'LBL_CURRENCY_RATE' => 'Currency rate',
    //opportunity worksheet list view labels
  'LBL_OW_OPPORTUNITIES' => "Opportunity",
  'LBL_OW_ACCOUNTNAME' => "Account",
  'LBL_OW_REVENUE' => "Amount",
  'LBL_OW_WEIGHTED' => "Weighted amount",
  'LBL_OW_MODULE_TITLE'=> 'Opportunity Worksheet',
  'LBL_OW_PROBABILITY'=>'Probability',
  'LBL_OW_NEXT_STEP'=>'Next step',
  'LBL_OW_DESCRIPTION'=>'Description',
  'LBL_OW_TYPE'=>'Type',

  //forecast schedule shortcuts
  'LNK_NEW_TIMEPERIOD' => 'Create time period',
  'LNK_TIMEPERIOD_LIST' => 'View time periods',
  
  //Forecast schedule sub panel list view.
  'LBL_SVFS_FORECASTDATE' => 'Schedule start date',
  'LBL_SVFS_STATUS' => 'Status',
  'LBL_SVFS_USER' => 'For',
  'LBL_SVFS_CASCADE' => 'Cascade to reports?',
  'LBL_SVFS_HEADER' => 'Forecast Schedule:',
  
  //Forecast Schedule detail; view.....
   'LB_FS_KEY' => 'ID',
   'LBL_FS_TIMEPERIOD_ID' => 'Time period ID',
   'LBL_FS_USER_ID' => 'User ID',
   'LBL_FS_TIMEPERIOD' => 'Time period',
   'LBL_FS_START_DATE' => 'Start date',
   'LBL_FS_END_DATE' => 'End date',
   'LBL_FS_FORECAST_START_DATE' => "Forecast start date",
   'LBL_FS_STATUS' => 'Status',
   'LBL_FS_FORECAST_FOR' => 'Schedule for:',
   'LBL_FS_CASCADE' =>'Cascade?',  
   'LBL_FS_MODULE_NAME' => 'Forecast schedule',
   'LBL_FS_CREATED_BY' =>'Created by',
   'LBL_FS_DATE_ENTERED' => 'Date entered',
   'LBL_FS_DATE_MODIFIED' => 'Date modified',
   'LBL_FS_DELETED' => 'Deleted',
    
  //forecast worksheet direct reports forecast
  'LBL_FDR_USER_NAME'=>'Direct report',
  'LBL_FDR_OPPORTUNITIES'=>'Opportunities in forecast:',
  'LBL_FDR_WEIGH'=>'Weighted amount of opportunities:',
  'LBL_FDR_COMMIT'=>'Committed amount',
  'LBL_FDR_DATE_COMMIT'=>'Commit date',
  
  //detail view.
  'LBL_DV_HEADER' => 'Forecasts: Worksheet',
  'LBL_DV_MY_FORECASTS' => 'My forecasts',
  'LBL_DV_MY_TEAM' => "Team Forecasts" ,
  'LBL_DV_TIMEPERIODS' => 'Time periods:',
  'LBL_DV_FORECAST_PERIOD' => 'Forecast time period',
  'LBL_DV_FORECAST_OPPORTUNITY' => 'Forecast opportunities',
  'LBL_SEARCH' => 'Select',
  'LBL_SEARCH_LABEL' => 'Select',
  'LBL_COMMIT_HEADER' => 'Forecast commit',
  'LBL_DV_LAST_COMMIT_DATE' =>'Last commit date:',
  'LBL_DV_LAST_COMMIT_AMOUNT' =>'Last commit amounts:',
  'LBL_DV_FORECAST_ROLLUP' => 'Forecast rollup',
  'LBL_DV_TIMEPERIOD' => 'Time period:',
  'LBL_DV_TIMPERIOD_DATES' => 'Date range:',
  
  //list view
  'LBL_LV_TIMPERIOD'=> 'Time period',
  'LBL_LV_TIMPERIOD_START_DATE'=> 'Start date',
  'LBL_LV_TIMPERIOD_END_DATE'=> 'End date',
  'LBL_LV_TYPE'=> 'Forecast type',
  'LBL_LV_COMMIT_DATE'=> 'Date committed',
  'LBL_LV_OPPORTUNITIES'=> 'Opportunities',
  'LBL_LV_WEIGH'=> 'Weighted amount',
  'LBL_LV_COMMIT'=> 'Committed amount',
  
  'LBL_COMMIT_NOTE'=> 'Enter amounts that you would like to commit for the selected time period:',
  
  'LBL_COMMIT_MESSAGE'=> 'Do you want to commit these amounts?',
  'ERR_FORECAST_AMOUNT' => 'Commit amount is required and must be a number.',

  // js error strings
  'LBL_FC_START_DATE' => 'Start date',
  'LBL_FC_USER' => 'Schedule for',
  
  'LBL_NO_ACTIVE_TIMEPERIOD'=>'No active time periods for the forecast module.',
  'LBL_FDR_ADJ_AMOUNT'=>'Adjusted amount',
  'LBL_SAVE_WOKSHEET'=>'Save worksheet',
  'LBL_RESET_WOKSHEET'=>'Reset worksheet',
  'LBL_RESET_CHECK'=>'All worksheet data for the selected time period and logged in user will be removed. Continue?',
  
  'LBL_EDIT_LAYOUT' => 'Edit layout', /*for 508 compliance fix*/
  'LBL_EXPECTED_OPPORTUNITIES' => 'Expected opportunities',
  'LBL_EXPECTED_BEST_CASE' => 'Expected best case',
  'LBL_EXPECTED_LIKELY_CASE' => 'Expected likely case',
  'LBL_EXPECTED_WORST_CASE' => 'Expected worst case',
  'LBL_EXPECTED_AMOUNT' => 'Expected amount',
  'LBL_INCLUDE_EXPECTED' => 'Include expected'
);
