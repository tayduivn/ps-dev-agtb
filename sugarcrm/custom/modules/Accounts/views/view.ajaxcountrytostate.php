<?php
if(!defined('sugarEntry') || !sugarEntry) die('Not A Valid Entry Point');

require_once('include/MVC/View/views/view.ajax.php');

class AccountsViewAjaxcountrytostate extends ViewAjax {
		function __construct(){
			parent::ViewAjax();
		}
		function display(){
			if(empty($_REQUEST['country_code'])){
				return json_encode(array());
			}
			
			if(!array_key_exists($_REQUEST['country_code'], $GLOBALS['app_list_strings']['country_options'])){
				return json_encode(array());
			}
			
			$final_list = array('' => '');
			$state_keys = array_keys($GLOBALS['app_list_strings']['state_options']);
			$filtered = preg_grep("/{$_REQUEST['country_code']}_/", $state_keys);
			foreach($filtered as $state_key){
				$final_list[$state_key] = $GLOBALS['app_list_strings']['state_options'][$state_key];
			}
			
			echo json_encode($final_list);
		}
}
