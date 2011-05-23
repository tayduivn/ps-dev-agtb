<?php
if(!defined('sugarEntry') || !sugarEntry) die('Not A Valid Entry Point');

require_once('include/MVC/View/views/view.ajax.php');

class AccountsViewAjaxtaginline extends ViewAjax {
		function __construct(){
			parent::ViewAjax();
		}
		function display(){
			$current_value = $_REQUEST['current_value'] . (empty($_REQUEST['current_value']) ? "" : ", ") ;
			$sing_mod = get_singular_bean_name($_REQUEST['tag_module']);
			require_once('include/SugarObjects/VardefManager.php');
			VardefManager::refreshVardefs($_REQUEST['tag_module'], $sing_mod);
			// echo json_encode(array("html" => var_export($sing_mod, true))); die();
			$parentFieldArray = 'fields';
			$vardef = $GLOBALS['dictionary'][$sing_mod]['fields'][$_REQUEST['field']];
			$displayType = 'EditView';
			$tabindex = 1;
			$displayParams = array();
			require_once('include/SugarFields/SugarFieldHandler.php');
			$field = SugarFieldHandler::getSugarField($vardef['type']);
			$field->setup($parentFieldArray, $vardef, $displayParams, $tabindex, false);
			$field->ss->assign('current_tag_val', $current_value);
			$field->ss->assign('idname', $_REQUEST['field']);
			$field->ss->assign('displayParams', $displayParams);
			$field->ss->assign('vardef', $vardef);

			$all_tags = IBMHelper::getModuleTags($vardef['tags_module']);
			$all_tags_str = '';
			foreach($all_tags as $tag) {
				$all_tags_str .= "'" . str_replace("'", "\'", $tag) . "',";
			}
			if(!empty($all_tags_str)){
				$all_tags_str = substr($all_tags_str, 0, -1);
			}
			$field->ss->assign('all_tags_str', $all_tags_str);
			
			$html_content = $field->ss->fetch('custom/include/SugarFields/Fields/Tag/InlineEditView.tpl');
			$js_content = $field->ss->fetch('custom/include/SugarFields/Fields/Tag/InlineEditViewJS.tpl');
			
			echo json_encode(array("html" => $html_content, 'javascript' => $js_content));
			//	echo json_encode(array("html" => var_export($_REQUEST, true)));
		}
}
