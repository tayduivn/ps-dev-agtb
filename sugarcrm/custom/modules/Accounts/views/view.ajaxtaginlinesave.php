<?php
if(!defined('sugarEntry') || !sugarEntry) die('Not A Valid Entry Point');

require_once('include/MVC/View/views/view.ajax.php');

class AccountsViewAjaxtaginlinesave extends ViewAjax {
		function __construct(){
			parent::ViewAjax();
		}
		function display(){
			$success = IBMHelper::saveTags($_REQUEST['tag_module'], $_REQUEST['record'], $_REQUEST['save_value']);
			
			$bean_name = get_singular_bean_name($_REQUEST['tag_module']);
			$bean = new $bean_name();
			$bean->disable_row_level_securitiy = true;
			$bean->retrieve($_REQUEST['record']);
			$tags = IBMHelper::getRecordTags($bean);
			echo json_encode(array("tags" => $tags));
		}
}
