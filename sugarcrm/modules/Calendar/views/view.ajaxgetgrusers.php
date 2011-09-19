<?php
require_once('include/MVC/View/SugarView.php');

class CalendarViewAjaxGetGRUsers extends SugarView {

	function CalendarViewAjaxGetGRUsers(){
 		parent::SugarView();
	}
	
	function process(){
		$this->display();
	}
	
	function display(){
		$users_arr = array();
		require_once("modules/Users/User.php");	
	
		$user_ids = explode(",", trim($_REQUEST['users'],','));	
		$user_ids = array_unique($user_ids);	

		require_once('include/json_config.php');
		global $json;
		$json = getJSONobj();
		$json_config = new json_config();        
	       
		foreach($user_ids as $u_id){
			if(empty($u_id))
				continue;
			$bean = new User();
			$bean->retrieve($u_id);
			array_push($users_arr, $json_config->populateBean($bean));        	
		}
		
		$GRjavascript = "\n" . $json_config->global_registry_var_name."['focus'].users_arr = " . $json->encode($users_arr) . ";\n";       	
		ob_clean();
		echo $GRjavascript;
	}	

}

?>
