<?php
if(!defined('sugarEntry') || !sugarEntry) die('Not A Valid Entry Point');

require_once('include/MVC/View/views/view.ajax.php');

class AccountsViewAjaxautocomplete extends ViewAjax {
		function __construct(){
			parent::ViewAjax();
		}
		function display(){
			global $app_list_strings;
			
			if(empty($_REQUEST['q'])){
				echo "{$_REQUEST['callback']}(" . json_encode(array()) . ");";
				return;
			}
			
			if(empty($_REQUEST['options'])){
				echo "{$_REQUEST['callback']}(" . json_encode(array()) . ");";
				return;
			}
			
			if(empty($app_list_strings[$_REQUEST['options']])){
				echo "{$_REQUEST['callback']}(" . json_encode(array()) . ");";
				return;
			}
			
			$final_list = array();
			
			$query = str_replace("/", "\/", $_REQUEST['q']);
			$filtered = preg_grep("/{$query}/i", $app_list_strings[$_REQUEST['options']]);
			foreach($filtered as $value){
				$key = array_search($value, $app_list_strings[$_REQUEST['options']]);
				$final_list['option_items'][] = array('key' => $key, 'text' => $value);
			}
			
			echo "{$_REQUEST['callback']}(" . json_encode($final_list) . ");";
		}
}
