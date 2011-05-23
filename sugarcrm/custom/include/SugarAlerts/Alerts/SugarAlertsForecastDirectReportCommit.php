<?php

require_once('custom/include/SugarAlerts/SugarAlerts.php');

class SugarAlertsForecastDirectReportCommit extends SugarAlerts {

	private $quarter = null;

	public static function getAlertTitle() {
		return 'Direct Report Committed Forecast';
	}
	
	private function getQuarter(){
		if(!is_null($this->quarter)){
			return $this->quarter;
		}
		
		$q_q = "SELECT name FROM timeperiods WHERE id = '{$this->bean->timeperiod_id}' AND timeperiods.deleted = 0";
		$res = $GLOBALS['db']->query($q_q);
		$row = $GLOBALS['db']->fetchByAssoc($res);
		$quarter = '';
		if(!empty($row['name'])){
			$quarter = $row['name'];
		}
		
		$this->quarter = $quarter;		
		return $quarter;
	}
	
	protected function generateDashletContent($links = true) {
		$quarter = $this->getQuarter();
		if($links){
			$quarter = "<a href=index.php?module=Forecasts&action=DetailView&timeperiod_id={$this->bean->timeperiod_id}&user_id={$GLOBALS['current_user']->id}>{$quarter}</a>";
		}
		
		$user = "{$GLOBALS['current_user']->first_name} {$GLOBALS['current_user']->last_name}";
		if($links){
			$user = "<a href=index.php?module=Employees&action=DetailView&record={$GLOBALS['current_user']->id}>{$user}</a>";
		}
		
		$ret = "{$user} has updated their {$quarter} Forecast";
		return $ret;
	}
	
	protected function generateCubeContent() {
		return $this->generateDashletContent();
	}

	protected function generateEmailContent() {
		$subject = $this->generateDashletContent(false);
		$quarter = $this->getQuarter();
		
		$body_lines[] = "Committer: {$GLOBALS['current_user']->first_name} {$GLOBALS['current_user']->last_name}";
		$body_lines[] = "Forecast Year/Qtr: {$quarter}";
		$body_lines[] = "";
		$body_lines[] = "{$GLOBALS['sugar_config']['site_url']}/index.php?module=Forecasts&action=index&user={$GLOBALS['current_user']->id}";
		
		$body = implode("\n", $body_lines);
		
		return array('subject' => $subject, 'body' => $body);
	}
	
	// supported by the parent SugarAlerts class. If defined, it returns an array with a list of user ids to filter
	protected function customUserFilter(){
		$manager_query = "SELECT reports_to_id FROM users WHERE id = '{$GLOBALS['current_user']->id}'";
		$res = $GLOBALS['db']->query($manager_query);
		$row = $GLOBALS['db']->fetchByAssoc($res);
		if(!empty($row['reports_to_id'])){
			return array($row['reports_to_id']);
		}
		else{
			return array();
		}
	}
}
