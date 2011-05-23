<?php
if(!defined('sugarEntry') || !sugarEntry) die('Not A Valid Entry Point');

/**
 * START jvink - This chart only works for users who have reports to defined
 */

require_once('include/charts/Charts.php');
require_once('modules/Forecasts/Common.php');
        
class SalesActivityTrackerChart {
	
	// holds chart dataset
	var $chart_data = array();
	
	// hierarchy helper
	var $hh;
	
	// dashlet settings
	var $dashletId, $dashletStrings;
	
	function __construct($dashletId, $dashletStrings) {
		global $current_user;
		
		// setup hierarchy helper
		$this->hh = new Common();
   		$this->hh->set_current_user($current_user->id);
		$this->hh->setup();

		// if there is no downline, we are not a manager
		// add current user to it so he can actually see his own records
		if(! count($this->hh->my_direct_reports)) {
    		$this->hh->my_direct_reports[$current_user->id] = $current_user->user_name;
    	}
		
		// dashlet stuff
		$this->dashletId = $dashletId;
		$this->dashletStrings = $dashletStrings;
	}
	
    function get_chart($start_date, $end_date) {

    	$label_picker = array (
    		'calls' => $this->dashletStrings['LBL_CHART_CALLS'],
    		'meetings' => $this->dashletStrings['LBL_CHART_MEETINGS'],
    		'tasks' => $this->dashletStrings['LBL_CHART_TASKS'],
    	);
    	
		// loop over users and setup a stacked bar for each of them
    	foreach($this->hh->my_direct_reports as $user_id => $user_name) {

    		// calls & meetings are matched against linked users,
    		// tasks only based upon assigned_user_id
    		$module_list = array(
    			'calls' => array('join' => 'calls_users', 'rel_field' => 'call_id'),
    			'meetings' => array('join' => 'meetings_users', 'rel_field' => 'meeting_id'),
    			'tasks' => false,
   			);
   			
   			foreach($module_list as $table => $related) {

   				// match on module_users table
   				if($related) {
   					$sql = "SELECT COUNT(main.id) AS total
   							FROM {$table} main
   							INNER JOIN {$related['join']} rel
   								ON rel.{$related['rel_field']} = main.id
   								AND rel.user_id = '{$user_id}'
   								AND rel.deleted = 0 
   							WHERE main.deleted = 0
   								AND main.date_entered >= '{$start_date} 00:00:00'
   								AND main.date_entered <= '{$end_date} 23:59:59'";
   				
   				// match on assigned user id
   				} else {
   					$sql = "SELECT COUNT(id) AS total
   							FROM {$table}
   							WHERE assigned_user_id = '{$user_id}' 
   								AND deleted = 0
   								AND date_entered >= '{$start_date} 00:00:00'
   								AND date_entered <= '{$end_date} 23:59:59'";
   				}
   				
   				$q = $GLOBALS['db']->query($sql);
   				if($res = $GLOBALS['db']->fetchByAssoc($q)) {
   					$this->chartAddNode($user_name, $label_picker[$table], $res['total']);
   				}
   			}
    	}
        
		require_once('include/SugarCharts/SugarChartReports.php');
		
		$return = '<script type="text/javascript" src="include/javascript/swfobject.js"></script>';
		$return .= '<div id="sales_activity_tracker_container" style="width: 100%;">';

		// chart title
		global $timedate;
		$chart_title = $this->dashletStrings['LBL_TITLE_CHART'].' '.
						$timedate->to_display_date($start_date, false).' '.
						$this->dashletStrings['LBL_TITLE_CHART_UNTIL'].' ';
						//$timedate->to_display_date($end_date, false);
		
		$sugarChart = new SugarChartReports();
		$sugarChart->is_currency = false;
		$sugarChart->setData($this->chart_data);
		$sugarChart->setDisplayProperty('title', $chart_title);
		$sugarChart->setDisplayProperty('subtitle', '');
		$sugarChart->setDisplayProperty('type', 'horizontal group by chart');
		$sugarChart->setDisplayProperty('legend', 'off');
		$sugarChart->setDisplayProperty('print', 'off');

		$xmlFile = $sugarChart->getXMLFileName($this->dashletId);
		$sugarChart->saveXMLFile($xmlFile, $sugarChart->generateXML());
		$return .= $sugarChart->display($this->dashletId, $xmlFile, '100%', '480','',false);
		$return .= '</div>';

        return $return;        
        
    }
    
    function chartAddNode($base, $name, $value) {
    	
    	// bar
    	if(! isset($this->chart_data[$base])) {
    		$this->chart_data[$base] = array();
    	}

    	// element
    	$this->chart_data[$base][$name] = array (
    		'numerical_value' => $value,
			'group_base_text' => $name,
        );
    }
}
?>
