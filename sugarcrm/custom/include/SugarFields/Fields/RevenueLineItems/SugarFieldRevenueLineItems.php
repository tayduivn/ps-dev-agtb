<?php

/* START jvink - customizations
 * Revenue Line Items Search Field
 */

require_once('include/SugarFields/Fields/Base/SugarFieldBase.php');

class SugarFieldRevenueLineItems extends SugarFieldBase {

	function getDetailViewSmarty($parentFieldArray, $vardef, $displayParams, $tabindex) {

		return parent::getDetailViewSmarty($parentFieldArray, $vardef, $displayParams, $tabindex);
    }

	function getEditViewSmarty($parentFieldArray, $vardef, $displayParams, $tabindex) {

		return $this->getSmartyView($parentFieldArray, $vardef, $displayParams, $tabindex, 'EditView');
    }  
    
}
?>
