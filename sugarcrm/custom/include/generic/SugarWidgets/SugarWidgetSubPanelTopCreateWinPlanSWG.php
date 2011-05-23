<?php
if(!defined('sugarEntry') || !sugarEntry) die('Not A Valid Entry Point');
require_once('include/generic/SugarWidgets/SugarWidgetSubPanelTopButtonQuickCreate.php');

class SugarWidgetSubPanelTopCreateWinPlanSWG extends SugarWidgetSubPanelTopButton
{
	function display($defines, $additionalFormFields = null)
	{
		global $app_strings;
		global $mod_strings;
		global $currentModule;

		$this->module="ibm_WinPlanSWG";
		$this->subpanelDiv = "ibm_winplans";
		
		$button = '<form action="index.php" method="post" name="subform_WinPlanSWG" id="subform_WinPlanSWG">
		
		<input type="submit" value="Create Software WinPlan" title="Create Software WinPlan" class="button" name="subform_WinPlanSWG_createButton" id="subform_WinPlanSWG_createButton" />
		<input type="hidden" name="action" value="EditView" />
		<input type="hidden" name="module" value="ibm_WinPlanSWG" />
		<input type="hidden" name="return_module" value="'.$currentModule.'" />
		<input type="hidden" name="return_action" value="'.$defines['action'].'" />
		<input type="hidden" name="return_id" value="'.$defines['focus']->id .'" />
		
		<input type="hidden" name="opportunities_ibm_winplanswg_name" value="'.$defines['focus']->name.'" />
		<input type="hidden" name="opportunitb5cfunities_ida" value="'.$defines['focus']->id.'" />
		
		</form>';
		
		return $button;
	}

}
?>
