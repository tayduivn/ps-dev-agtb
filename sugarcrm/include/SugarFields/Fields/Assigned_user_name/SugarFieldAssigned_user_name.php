<?php
/*********************************************************************************
 * The contents of this file are subject to
 * *******************************************************************************/
require_once('include/SugarFields/Fields/Base/SugarFieldBase.php');

class SugarFieldAssigned_user_name extends SugarFieldBase {

	function getSearchViewSmarty($parentFieldArray, $vardef, $displayParams, $tabindex) {
    	$vardef['options'] = get_user_array(false);
		if(!empty($vardef['function']['returns']) && $vardef['function']['returns']== 'html'){
    	   $this->setup($parentFieldArray, $vardef, $displayParams, $tabindex);
           return $this->fetch('include/SugarFields/Fields/Multienum/EditViewFunction.tpl');
    	}else{
    	   $this->setup($parentFieldArray, $vardef, $displayParams, $tabindex);
           return $this->fetch('include/SugarFields/Fields/Assigned_user_name/SearchView.tpl');
    	}
    }
}
