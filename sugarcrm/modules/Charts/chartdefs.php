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
$chartsStrings = return_module_language($GLOBALS['current_language'], 'Charts');

$chartDefs = array(
	//BEGIN SUGARCRM flav=pro ONLY
	'pipeline_by_sales_stage_funnel'=>
		array(	'type' => 'code',
				'id' => 'Chart_pipeline_by_sales_stage',
				'label' => $chartsStrings['LBL_CHART_PIPELINE_BY_SALES_STAGE_FUNNEL'],
				'chartUnits' => $chartsStrings['LBL_OPP_SIZE'] . ' $1' . $chartsStrings['LBL_OPP_THOUSANDS'],
				'chartType' => 'funnel chart 3D',
				'groupBy' => array( 'sales_stage', 'user_name', ),
				'base_url'=>
					array( 	'module' => 'Opportunities',
							'action' => 'index',
							'query' => 'true',
							'searchFormTab' => 'advanced_search',
						 ),
				'url_params' => array( 'assigned_user_id', 'sales_stage', 'date_start', 'date_closed' ),
			 ),
	//END SUGARCRM flav=pro ONLY
	'pipeline_by_sales_stage'=>
		array( 	'type' => 'code',
				'id' => 'Chart_pipeline_by_sales_stage',
				'label' => $chartsStrings['LBL_CHART_PIPELINE_BY_SALES_STAGE'],
				'chartUnits' => $chartsStrings['LBL_OPP_SIZE'] . ' $1' . $chartsStrings['LBL_OPP_THOUSANDS'],
				'chartType' => 'horizontal group by chart',
				'groupBy' => array( 'sales_stage', 'user_name' ),
				'base_url'=>
					array( 	'module' => 'Opportunities',
							'action' => 'index',
							'query' => 'true',
							'searchFormTab' => 'advanced_search',
						 ),
				'url_params' => array( 'assigned_user_id', 'sales_stage', 'date_start', 'date_closed' ),
			),
	'lead_source_by_outcome'=>
		array(	'type' => 'code',
				'id' => 'Chart_lead_source_by_outcome',
				'label' => $chartsStrings['LBL_CHART_LEAD_SOURCE_BY_OUTCOME'],
				'chartUnits' => '',
				'chartType' => 'horizontal group by chart',
				'groupBy' => array( 'lead_source', 'sales_stage' ),
				'base_url'=>
					array( 	'module' => 'Opportunities',
							'action' => 'index',
							'query' => 'true',
							'searchFormTab' => 'advanced_search',
						 ),
				'url_params' => array( 'lead_source', 'sales_stage', 'date_start', 'date_closed' ),
			 ),
	'outcome_by_month'=>
		array(	'type' => 'code',
				'id' => 'Chart_outcome_by_month',
				'label' => $chartsStrings['LBL_CHART_OUTCOME_BY_MONTH'],
				'chartUnits' => $chartsStrings['LBL_OPP_SIZE'] . ' $1' . $chartsStrings['LBL_OPP_THOUSANDS'],
				'chartType' => 'stacked group by chart',
				'groupBy' => array( 'm', 'sales_stage', ),
				'base_url'=>
					array( 	'module' => 'Opportunities',
							'action' => 'index',
							'query' => 'true',
							'searchFormTab' => 'advanced_search',
						 ),
				'url_params' => array( 'sales_stage', 'date_closed' ),
			 ),
	'pipeline_by_lead_source'=>
		array(	'type' => 'code',
				'id' => 'Chart_pipeline_by_lead_source',
				'label' => $chartsStrings['LBL_CHART_PIPELINE_BY_LEAD_SOURCE'],
				'chartUnits' => $chartsStrings['LBL_OPP_SIZE'] . ' $1' . $chartsStrings['LBL_OPP_THOUSANDS'],
				'chartType' => 'pie chart',
				'groupBy' => array( 'lead_source', ),
				'base_url'=>
					array( 	'module' => 'Opportunities',
							'action' => 'index',
							'query' => 'true',
							'searchFormTab' => 'advanced_search',
						 ),
				'url_params' => array( 'lead_source', ),
			 ),
	//BEGIN SUGARCRM flav=pro ONLY
	'opportunities_this_quarter' =>
		array( 	'type' => 'code',
				'id' => 'opportunities_this_quarter',
				'label' => $chartsStrings['LBL_CHART_OPPORTUNITIES_THIS_QUARTER'],
				'chartType' => 'gauge chart',
				'chartUnits' => 'Number of Opportunities',
				'groupBy' => array( ),
				'gaugeTarget' => 200,
				'base_url'=>
					array( 	'module' => 'Opportunities',
							'action' => 'index',
							'query' => 'true',
							'searchFormTab' => 'advanced_search',
						 ),
		),
	//END SUGARCRM flav=pro ONLY

	'my_modules_used_last_30_days' =>
		array( 	'type' => 'code',
				'id' => 'my_modules_used_last_30_days',
				'label' => $chartsStrings['LBL_CHART_MY_MODULES_USED_30_DAYS'],
				'chartType' => 'horizontal bar chart',
				'chartUnits' => $chartsStrings['LBL_MY_MODULES_USED_SIZE'],
				'groupBy' => array( 'module_name'),
				'base_url'=>
					array( 	'module' => 'Trackers',
							'action' => 'index',
							'query' => 'true',
							'searchFormTab' => 'advanced_search',
						 ),

		),

    //BEGIN SUGARCRM flav=pro ONLY
	'my_team_modules_used_last_30_days' =>
		array( 	'type' => 'code',
				'id' => 'my_team_modules_used_last_30_days',
				'label' => $chartsStrings['LBL_CHART_MODULES_USED_DIRECT_REPORTS_30_DAYS'],
				'chartType' => 'horizontal group by chart',
				'chartUnits' => $chartsStrings['LBL_MY_MODULES_USED_SIZE'],
				'groupBy' => array('user_name', 'module_name'),
				'base_url'=>
					array( 	'module' => 'Trackers',
							'action' => 'index',
							'query' => 'true',
							'searchFormTab' => 'advanced_search',
						 ),
		),
	//END SUGARCRM flav=pro ONLY
);

if(SugarAutoLoader::existing('custom/Charts/chartDefs.ext.php')) {
	include_once('custom/Charts/chartDefs.ext.php');
}
