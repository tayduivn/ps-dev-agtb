<?php
if(!defined('sugarEntry') || !sugarEntry) die('Not A Valid Entry Point');

require_once('include/generic/SugarWidgets/SugarWidgetField.php');

class SugarWidgetSubPanelRelatedContentInfoButton extends SugarWidgetField {
	
	function displayHeaderCell(&$layout_def) {
		return "&nbsp;";
	}
	
	function displayList(&$layout_def) {

		$mod_strings = return_module_language($GLOBALS['current_language'], 'ibm_RelatedContent');
		
		$tt_text = "<b>{$mod_strings['LBL_INFO_AUTHOR_NAME']}:</b> {$layout_def['fields']['INFO_AUTHOR_FIRST']} {$layout_def['fields']['INFO_AUTHOR_LAST']}<BR>".
				"<b>{$mod_strings['LBL_INFO_AUTHOR_EMAIL']}:</b> {$layout_def['fields']['INFO_AUTHOR_EMAIL']}<BR>".
				"<b>{$mod_strings['LBL_INFO_ABSTRACT']}:</b> {$layout_def['fields']['INFO_ABSTRACT']}<BR>"
		;
		
		$return = "<img id='{$layout_def['fields']['ID']}_tt' align='absmiddle' width='18' height='18' class='info' border='0' src='custom/themes/default/images/info_inline.png'><script type='text/javascript'>new YAHOO.widget.Tooltip('comp_{$layout_def['fields']['ID']}_tooltip', { context:'{$layout_def['fields']['ID']}_tt', width:'400px', text:'{$tt_text}' });</script>";

		return $return;
		
	}
	

}

?>