<?php
/*********************************************************************************
 * The contents of this file are subject to
 * *******************************************************************************/
require_once('include/SugarFields/Fields/Base/SugarFieldBase.php');

class SugarFieldEnum extends SugarFieldBase {
   
	function getDetailViewSmarty($parentFieldArray, $vardef, $displayParams, $tabindex) {
		if(!empty($vardef['function']['returns']) && $vardef['function']['returns']== 'html'){
    		  $this->setup($parentFieldArray, $vardef, $displayParams, $tabindex);
        	  return "<span id='{$vardef['name']}'>" . $this->fetch('include/SugarFields/Fields/Enum/DetailViewFunction.tpl') . '</span>';
    	}else{
    		  return parent::getDetailViewSmarty($parentFieldArray, $vardef, $displayParams, $tabindex);
    	}
    }
    
    function getEditViewSmarty($parentFieldArray, $vardef, $displayParams, $tabindex) {

    	if(empty($displayParams['size'])) {
		   $displayParams['size'] = 6;
		}
    	
    	if(isset($vardef['function']) && !empty($vardef['function']['returns']) && $vardef['function']['returns']== 'html'){
    		  $this->setup($parentFieldArray, $vardef, $displayParams, $tabindex);
        	  return $this->fetch('include/SugarFields/Fields/Enum/EditViewFunction.tpl');
    	}else{
    		  return parent::getEditViewSmarty($parentFieldArray, $vardef, $displayParams, $tabindex);
    	}
    }
    
    //BEGIN SUGARCRM flav=pro || flav=sales ONLY
    function getWirelessDetailViewSmarty($parentFieldArray, $vardef, $displayParams, $tabindex) {
    	if ( is_array($vardef['options']) )
            $this->ss->assign('value', $vardef['options'][$vardef['value']]);
        else
            $this->ss->assign('value', $GLOBALS['app_list_strings'][$vardef['options']][$vardef['value']]);
		if(!empty($vardef['function']['returns']) && $vardef['function']['returns']== 'html'){
    		  $this->setup($parentFieldArray, $vardef, $displayParams, $tabindex);
        	  return $this->fetch('include/SugarFields/Fields/Enum/WirelessDetailViewFunction.tpl');
    	}else{
    		  return parent::getWirelessDetailViewSmarty($parentFieldArray, $vardef, $displayParams, $tabindex);
    	}
    }
    //END SUGARCRM flav=pro || flav=sales ONLY
    
    //BEGIN SUGARCRM flav=pro || flav=sales ONLY
    function getWirelessEditViewSmarty($parentFieldArray, $vardef, $displayParams, $tabindex){
    	$this->ss->assign('field_options', is_array($vardef['options']) ? $vardef['options'] : $GLOBALS['app_list_strings'][$vardef['options']]);
    	$this->ss->assign('selected', isset($vardef['value'])?$vardef['value']:'');
    	if(!empty($vardef['function']['returns']) && $vardef['function']['returns']== 'html'){
    		  $this->setup($parentFieldArray, $vardef, $displayParams, $tabindex);
        	  return $this->fetch('include/SugarFields/Fields/Enum/WirelessEditViewFunction.tpl');
    	}else{
    		  return parent::getWirelessEditViewSmarty($parentFieldArray, $vardef, $displayParams, $tabindex);
    	}
    }
    //END SUGARCRM flav=pro || flav=sales ONLY
    
	function getSearchViewSmarty($parentFieldArray, $vardef, $displayParams, $tabindex) {
		
		if(empty($displayParams['size'])) {
		   $displayParams['size'] = 6;
		}
		
    	if(!empty($vardef['function']['returns']) && $vardef['function']['returns']== 'html'){
    		  $this->setup($parentFieldArray, $vardef, $displayParams, $tabindex);
        	  return $this->fetch('include/SugarFields/Fields/Enum/EditViewFunction.tpl');
    	}else{
    		  $this->setup($parentFieldArray, $vardef, $displayParams, $tabindex);
        	  return $this->fetch('include/SugarFields/Fields/Enum/SearchView.tpl');
    	}
    }
    
    //BEGIN SUGARCRM flav=pro || flav=sales ONLY
    function getWirelessSearchViewSmarty($parentFieldArray, $vardef, $displayParams, $tabindex) {
    	if(!empty($vardef['function']['returns']) && $vardef['function']['returns']== 'html'){
    		  $this->setup($parentFieldArray, $vardef, $displayParams, $tabindex);
        	  return $this->fetch('include/SugarFields/Fields/Enum/EditViewFunction.tpl');
    	}else{
    		  $this->setup($parentFieldArray, $vardef, $displayParams, $tabindex);
        	  return $this->fetch('include/SugarFields/Fields/Enum/SearchView.tpl');
    	}
    }
    //END SUGARCRM flav=pro || flav=sales ONLY

    function displayFromFunc( $displayType, $parentFieldArray, $vardef, $displayParams, $tabindex ) {
        if ( isset($vardef['function']['returns']) && $vardef['function']['returns'] == 'html' ) {
            return parent::displayFromFunc($displayType, $parentFieldArray, $vardef, $displayParams, $tabindex);
        }

        $displayTypeFunc = 'get'.$displayType.'Smarty';
        return $this->$displayTypeFunc($parentFieldArray, $vardef, $displayParams, $tabindex);
    }
    
	public function formatField($rawField, $vardef){
		global $app_list_strings;
		
		if(!empty($vardef['options'])){
			$option_array_name = $vardef['options'];
			
			if(!empty($app_list_strings[$option_array_name][$rawField])){
				return $app_list_strings[$option_array_name][$rawField];
			}else {
				return $rawField;
			}
		} else {
			return $rawField;
		}
    }
    
}
?>