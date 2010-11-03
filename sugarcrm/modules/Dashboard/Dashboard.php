<?php
if(!defined('sugarEntry') || !sugarEntry) die('Not A Valid Entry Point');
/*********************************************************************************
 * The contents of this file are subject to the SugarCRM Enterprise Subscription
 * Agreement ("License") which can be viewed at
 * http://www.sugarcrm.com/crm/products/sugar-enterprise-eula.html
 * By installing or using this file, You have unconditionally agreed to the
 * terms and conditions of the License, and You may not use this file except in
 * compliance with the License.  Under the terms of the license, You shall not,
 * among other things: 1) sublicense, resell, rent, lease, redistribute, assign
 * or otherwise transfer Your rights to the Software, and 2) use the Software
 * for timesharing or service bureau purposes such as hosting the Software for
 * commercial gain and/or for the benefit of a third party.  Use of the Software
 * may be subject to applicable fees and any use of the Software without first
 * paying applicable fees is strictly prohibited.  You do not have the right to
 * remove SugarCRM copyrights from the source code or user interface.
 *
 * All copies of the Covered Code must include on each user interface screen:
 *  (i) the "Powered by SugarCRM" logo and
 *  (ii) the SugarCRM copyright notice
 * in the same form as they appear in the distribution.  See full license for
 * requirements.
 *
 * Your Warranty, Limitations of liability and Indemnity are expressly stated
 * in the License.  Please refer to the License for the specific language
 * governing these rights and limitations under the License.  Portions created
 * by SugarCRM are Copyright (C) 2004-2010 SugarCRM, Inc.; All Rights Reserved.
 ********************************************************************************/
/*********************************************************************************

* Description:  TODO: To be written.
* Portions created by SugarCRM are Copyright (C) SugarCRM, Inc.
* All Rights Reserved.
* Contributor(s): ______________________________________..
********************************************************************************/





//require_once('modules/Charts/code/predefined_charts.php');

class Dashboard extends SugarBean {

	var $db;
	var $field_name_map;

	// Stored fields
	var $id;
	var $date_entered;
	var $date_modified;
	var $modified_user_id;
	var $assigned_user_id;
	var $created_by;
	var $created_by_name;
	var $modified_by_name;
	var $team_id;
	var $name;
	var $description;
	var $content;
	var $user_id;

	var $table_name = "dashboards";
	var $object_name = "Dashboard";

	var $new_schema = true;


	var $additional_column_fields = array();

	var $module_dir = 'Dashboard';
	var $field_defs = array();
	var $field_defs_map = array();

	function Dashboard()
	{
		parent::SugarBean();
		$this->setupCustomFields('Dashboard');
		foreach ($this->field_defs as $field)
		{
			$this->field_name_map[$field['name']] = $field;
		}

		$this->team_id = 1; // make the item globally accessible

	}

	function create_tables ()
	{
		parent::create_tables();
	}

	function get_summary_text()
	{
		return $this->name;
	}

	function getUsersTopDashboard($user_id)
	{
		$where = "dashboards.assigned_user_id='$user_id'";
		$response = $this->get_list("", $where, 0);

		if ( count($response['list']) > 0)
		{
			return $response['list'][0];
		}

		return $this->createUserDashboard($user_id);
	}

	function &createUserDashboard($user_id)
	{
		$test = array();
		$dashboard = new Dashboard();

		$dashboard->assigned_user_id = $user_id;
		$dashboard->created_by = $user_id;
		$dashboard->modified_user_id = $user_id;
		$dashboard->name = "Home";
		$dashboard->content = $this->getDefaultDashboardContents();
		$dashboard->save();
		return $dashboard;
	}

	function getDefaultDashboardContents()
	{
		$contents = array(
		array('type'=>'code','id'=>'Chart_pipeline_by_sales_stage'),
		array('type'=>'code','id'=>'Chart_lead_source_by_outcome'),
		array('type'=>'code','id'=>'Chart_outcome_by_month'),
		array('type'=>'code','id'=>'Chart_pipeline_by_lead_source'),
		);
		return serialize($contents);

	}


	function move ($dir='up',$chart_index)
	{
		$dashboard_def = unserialize(from_html($this->content));
		if ( $dir == 'up' && $chart_index != 0)
		{
			$extracted_array = $dashboard_def[$chart_index];
			array_splice($dashboard_def,$chart_index,1);
			array_splice($dashboard_def,$chart_index-1,0,array($extracted_array));
		}
		else if ( $dir == 'down' && $chart_index != (count($dashboard_def) - 1))
		{
			$extracted_array = $dashboard_def[$chart_index];
			array_splice($dashboard_def,$chart_index,1);
			array_splice($dashboard_def,$chart_index+1,0,array($extracted_array));
		}
		
		$this->content = serialize($dashboard_def);
		$this->save();
	}

	function arrange($chart_order) {
		$dashboard_def = unserialize(from_html($this->content));
		$dashboard_def_new = array();
		foreach($chart_order as $chart_index) {
			array_push($dashboard_def_new, $dashboard_def[$chart_index]);
		}

		$this->content = serialize($dashboard_def_new);
		$this->save();
	}
	
	function delete ($chart_index)
	{
		$dashboard_def = unserialize(from_html($this->content));
		array_splice($dashboard_def,$chart_index,1);
		$this->content = serialize($dashboard_def);
		$this->save();
	}

	function add ($chart_type,$chart_id,$chart_index)
	{
		global $predefined_charts;
		$dashboard_def = unserialize(from_html($this->content));
		if ( $chart_type == 'code')
		{
			if ( isset($predefined_charts[$chart_id]))
			{
				array_splice($dashboard_def,$chart_index,0,array($predefined_charts[$chart_id]));
			}
		} else if ($chart_type=='report')
		{
			$chart_def = array('type'=>'report','id'=>$chart_id);
			array_splice($dashboard_def,$chart_index,0,array($chart_def));

		}
		$this->content = serialize($dashboard_def);
		$this->save();
	}
	
	// return correct dashlet name based on array for 4.5.1 to 5.0 upgrade 
	function getDashletName($id){
		$dashletNames = array(
			'Chart_lead_source_by_outcome' 	=> 'OpportunitiesByLeadSourceByOutcomeDashlet',
			'Chart_pipeline_by_sales_stage' => 'PipelineBySalesStageDashlet',
			'Chart_outcome_by_month' 		=> 'OutcomeByMonthDashlet',
			'Chart_pipeline_by_lead_source' => 'OpportunitiesByLeadSourceDashlet', 
		);
		
		if (isset($dashletNames[$id]))
			return $dashletNames[$id];
		else
			return 'custom_dashlet';
	}
}

?>