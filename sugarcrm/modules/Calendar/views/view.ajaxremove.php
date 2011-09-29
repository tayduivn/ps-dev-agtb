<?php
require_once('include/MVC/View/SugarView.php');

class CalendarViewAjaxRemove extends SugarView {

	function CalendarViewAjaxRemove(){
 		parent::SugarView();
	}
	
	function process(){
		$this->display();
	}
	
	function display(){
		require_once("modules/Calls/Call.php");
		require_once("modules/Meetings/Meeting.php");
		require_once("modules/Calendar/utils.php");

		global $beanFiles,$beanList;
		$module = $_REQUEST['current_module'];
		require_once($beanFiles[$beanList[$module]]);
		$bean = new $beanList[$module]();
		//$type = strtolower($beanList[$module]);
		//$table_name = $bean->table_name;
		//$jn = $type."_id_c";

		$bean->retrieve($_REQUEST['record']);

		if(!$bean->ACLAccess('delete')){
			die;	
		}

		$bean->mark_deleted($_REQUEST['record']);

		/*if($_REQUEST['delete_recurring']){
			remove_recurrence($bean,$table_name,$jn,$_REQUEST['record']);
		}*/

		$json_arr = array(
			'success' => 'yes',
		);

		ob_clean();
		echo json_encode($json_arr);
	}	

}

?>