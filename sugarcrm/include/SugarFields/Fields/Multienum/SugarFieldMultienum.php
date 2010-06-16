<?php
/*********************************************************************************
 * The contents of this file are subject to
 * *******************************************************************************/
require_once('include/SugarFields/Fields/Base/SugarFieldBase.php');

class SugarFieldMultienum extends SugarFieldBase {
   
    function setup($parentFieldArray, $vardef, $displayParams, $tabindex, $twopass=true) {
        if ( !isset($vardef['options_list']) && isset($vardef['options']) && !is_array($vardef['options'])) {
            $vardef['options_list'] = $GLOBALS['app_list_strings'][$vardef['options']];
        }
        return parent::setup($parentFieldArray, $vardef, $displayParams, $tabindex, $twopass);
    }

	function getSearchViewSmarty($parentFieldArray, $vardef, $displayParams, $tabindex) {
    	if(!empty($vardef['function']['returns']) && $vardef['function']['returns']== 'html'){
    	   $this->setup($parentFieldArray, $vardef, $displayParams, $tabindex);
           return $this->fetch('include/SugarFields/Fields/Multienum/EditViewFunction.tpl');
    	}else{
    	   $this->setup($parentFieldArray, $vardef, $displayParams, $tabindex);
           return $this->fetch('include/SugarFields/Fields/Multienum/SearchView.tpl');
    	}
    }
    
	public function save(&$bean, $params, $field, $properties, $prefix = ''){
		if ( isset($params[$prefix.$field]) ) {
			if($params[$prefix.$field][0] === '' && !empty($params[$prefix.$field][1]) ) {
				unset($params[$prefix.$field][0]);
			}
			
			$bean->$field = encodeMultienumValue($params[$prefix.$field]);
		}
    }
}
