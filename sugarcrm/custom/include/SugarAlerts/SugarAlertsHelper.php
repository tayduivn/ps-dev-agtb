<?php

require_once('include/Sugar_Smarty.php');

class SugarAlertsHelper {
	
	public static function getAlertsTable($user_or_id, $type, $limit = 20){
		$user = $user_or_id;
		if(is_string($user_or_id)){
			$user = new User();
			$user->retrieve($user_or_id);
		}
		
		$ss = new Sugar_Smarty();
		
		$timedatenew = TimeDateNew::getInstance();
		
		require_once('custom/include/SugarAlerts/SugarAlerts.php');
		$sa = new SugarAlerts();
		$data = $sa->getUserAlerts($user, $type, $limit, true);
		
		require_once('modules/SugarFeed/SugarFeed.php');
		
		foreach($data as $k => $row){
			if(!empty($row['date_entered'])){
				$dt = $timedatenew->fromDb($row['date_entered']);
				$value = $timedatenew->asUser($dt);
				$data[$k]['date_entered'] = SugarFeed::getTimeLapse($value);
			}
		}
		
		$ss->assign('alert_type', $type);
		$ss->assign('data', $data);
		$ss->assign('current_user_id', $user->id);

		$str = $ss->fetch('custom/include/SugarAlerts/tpls/SugarAlertsTable.tpl');
		return $str;
	}
	
	public static function getAlertsJS(){
		$str = "<script type='text/javascript' src='".getJSPath('custom/include/SugarAlerts/js/SugarAlerts.js')."'></script>";
		return $str;
	}
}
