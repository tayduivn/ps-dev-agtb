<?php
if(!defined('sugarEntry') || !sugarEntry) die('Not A Valid Entry Point');
require_once('include/generic/SugarWidgets/SugarWidgetSubPanelTopButtonQuickCreate.php');

class SugarWidgetSubPanelTopCreateWinPlanGeneric extends SugarWidgetSubPanelTopButton
{
	function display($defines, $additionalFormFields = null)
	{
		global $app_strings;
		global $mod_strings;
		global $currentModule;

		$this->module="ibm_WinPlanGeneric";
		$this->subpanelDiv = "ibm_winplans";
		
		$button = '<form action="index.php" method="post" name="subform_WinPlanGeneric" id="subform_WinPlanGeneric">
		
		<input type="submit" value="Create Generic WinPlan" title="Create Generic WinPlan" class="button" name="subform_WinPlanGeneric_createButton" id="subform_WinPlanGeneric_createButton" />
		<input type="hidden" name="action" value="EditView" />
		<input type="hidden" name="module" value="ibm_WinPlanGeneric" />
		<input type="hidden" name="return_module" value="'.$currentModule.'" />
		<input type="hidden" name="return_action" value="'.$defines['action'].'" />
		<input type="hidden" name="return_id" value="'.$defines['focus']->id .'" />
		
		<input type="hidden" name="opportunities_ibm_winplangeneric_name" value="'.$defines['focus']->name.'" />
		<input type="hidden" name="opportuniteefdunities_ida" value="'.$defines['focus']->id.'" />
		
		</form>';
		
		return $button;
	}

}
?>
