<?php
if(!defined('sugarEntry') || !sugarEntry) die('Not A Valid Entry Point');

require_once('include/generic/SugarWidgets/SugarWidgetField.php');

class SugarWidgetSubPanelRelatedContentFavorite extends SugarWidgetField {
	
	function displayHeaderCell(&$layout_def) {
		return "&nbsp;";
	}
	
	function displayList(&$layout_def) {

		// we combine oppty id and internal_id (INFO/SYN_KEY, stored in doc name field) + vardef of favo id is extended to 144 chars
		$id = $layout_def['fields']['NAME'].'-'.$_REQUEST['record']; 
		$return = SugarFavorites::generateStar(SugarFavorites::isUserFavorite('ibm_RelatedContent', $id), 'ibm_RelatedContent', $id);
		
		return $return;
	}
	

}

?>
