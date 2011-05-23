<?php

/*
 * Custom widget for Opportunities Additional Team Roles
 */

require_once('include/SugarFields/Fields/Base/SugarFieldBase.php');

class SugarFieldInlineOneToMany extends SugarFieldBase {

	function getDetailViewSmarty($parentFieldArray, $vardef, $displayParams, $tabindex) {

		return "<span id='{$vardef['name']}'>" .  $this->getSmartyView($parentFieldArray, $vardef, $displayParams, $tabindex, 'DetailView') . '</span>';
    }

	function getEditViewSmarty($parentFieldArray, $vardef, $displayParams, $tabindex) {

		return $this->getSmartyView($parentFieldArray, $vardef, $displayParams, $tabindex, 'EditView');
    }  
    
}
?>