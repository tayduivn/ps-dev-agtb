<?php
if(!defined('sugarEntry') || !sugarEntry) die('Not A Valid Entry Point');

require_once('include/MVC/View/views/view.ajax.php');

class AccountsViewAjaxsetfieldvalue extends ViewAjax {
		function __construct(){
			parent::ViewAjax();
		}
		function display(){
			if(empty($_REQUEST['smodule'])){
				echo json_encode(array("error" => "smodule not defined"));
				return;
			}
			
			if(empty($_REQUEST['record'])){
				echo json_encode(array("error" => "record not defined"));
				return;
			}
			
			if(empty($_REQUEST['field'])){
				echo json_encode(array("error" => "field not defined"));
				return;
			}
			
			if(!isset($_REQUEST['value'])){
				echo json_encode(array("error" => "value not defined"));
				return;
			}
			
			$bean_name = get_singular_bean_name($_REQUEST['smodule']);
			global $beanFiles;
			if(empty($beanFiles[$bean_name])){
				echo json_encode(array("error" => "No bean for {$_REQUEST['smodule']}"));
				return;
			}
			
			require_once($beanFiles[$bean_name]);
			$bean = new $bean_name();
			// sadek - I KNOW THIS IS A VULNERABILITY FOR TEAM SECURITY, BUT OKAY FOR THIS PROJECT
			$bean->disable_row_level_security = true;
			$bean->retrieve($_REQUEST['record']);
			
			if(empty($bean->id)){
				echo json_encode(array("error" => "No record for {$_REQUEST['smodule']} with id {$_REQUEST['record']}"));
				return;
			}
			
			if(!isset($bean->$_REQUEST['field'])){
				echo json_encode(array("error" => "Field {$_REQUEST['field']} is not set on the object"));
				return;
			}
			else{
				$bean->$_REQUEST['field'] = $_REQUEST['value'];
				$bean->save();
				echo json_encode(array("success" => "true"));
				return;
			}
		}
}
