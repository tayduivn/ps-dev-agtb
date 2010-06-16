<?php
/*********************************************************************************
 * The contents of this file are subject to
 * *******************************************************************************/
require_once('include/SugarFields/Fields/SugarFieldBase/SugarFieldBase.php');

class SugarFieldHtml extends SugarFieldBase {
   
	function getDetailViewSmarty($parentFieldArray, $vardef, $displayParams){
		$vardef['value'] = $this->getVardefValue($vardef);
        $this->setup($parentFieldArray, $vardef, $displayParams);
        return $this->ss->fetch('include/SugarFields/Fields/SugarFieldHtml/SugarFieldHtmlDetailViewSmarty.tpl');
    }
    
    function getEditViewSmarty($parentFieldArray, $vardef, $displayParams){
    	$vardef['value'] = $this->getVardefValue($vardef);
        $this->setup($parentFieldArray, $vardef, $displayParams);
        return $this->ss->fetch('include/SugarFields/Fields/SugarFieldHtml/SugarFieldHtmlDetailViewSmarty.tpl');
    }
    
    function getVardefValue($vardef){
    	if(empty($vardef['value'])){
			if(!empty($vardef['default']))
				return from_html($vardef['default']);
			elseif(!empty($vardef['default_value']))
				return from_html($vardef['default_value']);
		} else {
			return from_html($vardef['value']);
		}
    }
}
?>