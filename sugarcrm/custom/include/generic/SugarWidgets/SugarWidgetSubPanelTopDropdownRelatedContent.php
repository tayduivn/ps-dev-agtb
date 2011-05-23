<?php
if(!defined('sugarEntry') || !sugarEntry) die('Not A Valid Entry Point');
require_once('include/generic/SugarWidgets/SugarWidgetSubPanelTopButtonQuickCreate.php');

class SugarWidgetSubPanelTopDropdownRelatedContent extends SugarWidgetSubPanelTopButton
{
	function display($defines, $additionalFormFields = null)
	{
		global $app_strings;
		global $currentModule;

		$mod_strings = return_module_language($GLOBALS['current_language'], 'ibm_RelatedContent');
		
		$this->module="ibm_WinPlanGeneric";
		$this->subpanelDiv = "ibm_winplans";

		$filter = '';
		
		// get level20 product id's and names from related revenueLineItems
		$all_id = '';
		if(is_object($defines['focus']) && $defines['focus']->id) {
			$dd = IBMHelper::oppty_get_level20($defines['focus']->id);
			
			// comma seperate all l20 ids to be used for "all products"
			foreach($dd as $id=>$name) {
				$all_id .= $id.',';
			}
		}

		// construct inline url for subpanel reload
		$inline_url = "index.php?sugar_body_only=1&module=Opportunities&subpanel=opportunities_ibm_relatedcontent&action=SubPanelViewer&inline=1&record={$defines['focus']->id}&layout_def_key=Opportunities&related_content_filter=";
		
		$filter = $mod_strings['LBL_RELATED_CONTENT_FILTER'].': ';
		$filter .= '<select id="related_content_filter" name="related_content_filter" onchange="document.getElementById(\'related_content_filter_loader\').style.display=\'inline\'; sel = document.getElementById(\'related_content_filter\').value; filter = \''.$inline_url.'\' + sel; current_child_field = \'opportunities_ibm_relatedcontent\';showSubPanel(\'opportunities_ibm_relatedcontent\',filter,true,\'Opportunities\');document.getElementById(\'show_link_opportunities_ibm_relatedcontent\').style.display=\'none\';document.getElementById(\'hide_link_opportunities_ibm_relatedcontent\').style.display=\'\'; document.getElementById(\'related_content_filter\').value = sel; document.getElementById(\'related_content_filter_loader\').style.display=\'none\';">';
		foreach($dd as $v => $l) {
			$filter .= '<option value="'.$v.'">'.str_replace("\n",' ',$l).'</option>';
		}

		// add "all products" and select it by default
		if(count($dd)) {
			$filter .= '<option value="'.rtrim($all_id,',').'" selected="selected">'.$mod_strings['LBL_RELATED_CONTENT_FILTER_ALL'].'</option>';
		
		// if no revenue line items exist yet
		} else {
			$filter .= '<option value="'.rtrim($all_id,',').'" selected="selected">'.$mod_strings['LBL_RELATED_CONTENT_NO_RLI'].'</option>';
		}
		$filter .= '</select>';
			
		// UI feedback
		$filter .= '<span id="related_content_filter_loader" name="related_content_filter_loader" style="display: none;">
					<img src="themes/default/images/revitems-loader.gif" /></span>';
		
		return $filter;
	}

}
?>