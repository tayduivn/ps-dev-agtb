<?php
if(!defined('sugarEntry') || !sugarEntry) die('Not A Valid Entry Point');
require_once('include/generic/SugarWidgets/SugarWidgetSubPanelTopButtonQuickCreate.php');

class SugarWidgetSubPanelTopCreateWinPlanSTG extends SugarWidgetSubPanelTopButton
{
	function display($defines, $additionalFormFields = null)
	{
		global $app_strings;
		global $mod_strings;
		global $currentModule;

		$this->module="ibm_WinPlanSTG";
		$this->subpanelDiv = "ibm_winplans";
		
		$button = '<form action="index.php" method="post" name="subform_WinPlanSTG" id="subform_WinPlanSTG">
		
		<input type="submit" value="Create Systems WinPlan" title="Create Systems WinPlan" class="button" name="subform_WinPlanSTG_createButton" id="subform_WinPlanSTG_createButton" />
		<input type="hidden" name="action" value="EditView" />
		<input type="hidden" name="module" value="ibm_WinPlanSTG" />
		<input type="hidden" name="return_module" value="'.$currentModule.'" />
		<input type="hidden" name="return_action" value="'.$defines['action'].'" />
		<input type="hidden" name="return_id" value="'.$defines['focus']->id .'" />
		
		<input type="hidden" name="opportunities_ibm_winplanstg_name" value="'.$defines['focus']->name.'" />
		<input type="hidden" name="opportunit8b0bunities_ida" value="'.$defines['focus']->id.'" />
		
		</form>';
		
		return $button;
	}

}
?>
