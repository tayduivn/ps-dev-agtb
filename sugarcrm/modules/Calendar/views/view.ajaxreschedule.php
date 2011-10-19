<?php
require_once('include/MVC/View/SugarView.php');

class CalendarViewAjaxReschedule extends SugarView {

	function CalendarViewAjaxReschedule(){
 		parent::SugarView();
	}
	
	function process(){
		$this->display();
	}
	
	function display(){
		require_once("modules/Calls/Call.php");
		require_once("modules/Meetings/Meeting.php");

		global $beanFiles,$beanList;
		$module = $_REQUEST['current_module'];
		require_once($beanFiles[$beanList[$module]]);
		$bean = new $beanList[$module]();	
	
		$bean->retrieve($_REQUEST['record']);

		if(!$bean->ACLAccess('Save')){
			die;	
		}
		
		$field = "date_start";
		if($module == "Tasks")
			$field = "date_due";	
			
		$_POST[$field] = $_REQUEST['datetime'];
			
		require_once('include/formbase.php');		
		$bean = populateFromPost("",$bean);
		
		$bean->save();
	}	

}

?>
