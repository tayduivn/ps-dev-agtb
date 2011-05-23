<?php

/*
 * Custom widget for Opportunities Additional Team Roles
 */

require_once('include/SugarFields/Fields/Base/SugarFieldBase.php');

class SugarFieldOppTeamRoles extends SugarFieldBase {

	function getDetailViewSmarty($parentFieldArray, $vardef, $displayParams, $tabindex) {

		// get dropdown values for user roles
		global $app_list_strings;
		if(isset($app_list_strings[$vardef['dropdown_list']]) && is_array($app_list_strings[$vardef['dropdown_list']])) {
			$this->ss->assign('opportunity_team_roles', $app_list_strings[$vardef['dropdown_list']]);
		} else {
			$this->ss->assign('opportunity_team_roles', array());
		}
		
		return "<span id='{$vardef['name']}'>" .  $this->getSmartyView($parentFieldArray, $vardef, $displayParams, $tabindex, 'DetailView') . '</span>';
    }

	function getEditViewSmarty($parentFieldArray, $vardef, $displayParams, $tabindex) {

		// pass dropdown to smarty
		global $app_list_strings;
		if(isset($app_list_strings[$vardef['dropdown_list']]) && is_array($app_list_strings[$vardef['dropdown_list']])) {
			$this->ss->assign('opportunity_team_roles', $app_list_strings[$vardef['dropdown_list']]);
		} else {
			$this->ss->assign('opportunity_team_roles', array());
		}

		return $this->getSmartyView($parentFieldArray, $vardef, $displayParams, $tabindex, 'EditView');
    }  
    
}
?>