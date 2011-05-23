<?php
if(!defined('sugarEntry') || !sugarEntry) die('Not A Valid Entry Point');

require_once('include/MVC/View/views/view.ajax.php');

class AccountsViewSugar_alert_action extends ViewAjax {
		function __construct(){
			parent::ViewAjax();
		}
		function display(){
			if(!isset($_REQUEST['alert_action'])){
				echo json_encode(array("error" => "alert_action not defined"));
				return;
			}
			
			require_once('custom/include/SugarAlerts/SugarAlerts.php');
			$sa = new SugarAlerts();
			
			if($_REQUEST['alert_action'] == 'delete'){
				if(empty($_REQUEST['record'])){
					echo json_encode(array("error" => "record not defined"));
					return;
				}
				
				if(empty($_REQUEST['type'])){
					echo json_encode(array("error" => "type not defined"));
					return;
				}
				
				$success = $sa->deleteAlert($_REQUEST['record']);
				if($success){
					echo json_encode(array("success" => "true"));
					return;
				}
				else{
					echo json_encode(array("error" => "could not delete {$_REQUEST['record']}"));
					return;
				}
			}
			else if($_REQUEST['alert_action'] == 'getUnreadCount'){
				if(empty($_REQUEST['type'])){
					echo json_encode(array("error" => "type not defined"));
					return;
				}
				
				$user_id = isset($_REQUEST['user_id']) ? $_REQUEST['user_id'] : $GLOBALS['current_user']->id;
				$alert_count = $sa->getUserUnreadAlertCount($user_id, $_REQUEST['type']);
				echo json_encode(array("count" => $alert_count, "success" => true));
				return;
			}
			else{
				echo json_encode(array("error" => "not a valid action: {$_REQUEST['alert_action']}"));
				return;
			}
		}
}
