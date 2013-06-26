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

$chartsStrings = return_module_language($current_language, 'Charts');

$predefined_charts = array(
	'Chart_pipeline_by_sales_stage'=>
	array('type'=>'code','id'=>'Chart_pipeline_by_sales_stage','label'=>$chartsStrings['LBL_CHART_PIPELINE_BY_SALES_STAGE'],'chartType'=>'horizontal group by chart',),
	'Chart_lead_source_by_outcome'=>
	array('type'=>'code','id'=>'Chart_lead_source_by_outcome','label'=>$chartsStrings['LBL_CHART_LEAD_SOURCE_BY_OUTCOME'],'chartType'=>'horizontal group by chart',),
	'Chart_outcome_by_month'=>
	array('type'=>'code','id'=>'Chart_outcome_by_month','label'=>$chartsStrings['LBL_CHART_OUTCOME_BY_MONTH'],'chartType'=>'stacked group by chart',),
	'Chart_pipeline_by_lead_source'=>
	array('type'=>'code','id'=>'Chart_pipeline_by_lead_source','label'=>$chartsStrings['LBL_CHART_PIPELINE_BY_LEAD_SOURCE'],'chartType'=>'pie chart',),
	'Chart_my_pipeline_by_sales_stage'=>
	array('type'=>'code','id'=>'Chart_pipeline_by_sales_stage','label'=>$chartsStrings['LBL_CHART_MY_PIPELINE_BY_SALES_STAGE'],'chartType'=>'funnel chart',),
);