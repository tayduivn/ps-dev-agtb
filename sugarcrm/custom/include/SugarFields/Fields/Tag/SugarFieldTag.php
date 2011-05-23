<?php

require_once('include/SugarFields/Fields/Base/SugarFieldBase.php');

class SugarFieldTag extends SugarFieldBase {
	
	function assignAllTags($module, $tpl_str = 'all_tags'){
		$all_tags = IBMHelper::getModuleTags($module);

		$all_tags_str = '';
		foreach($all_tags as $tag) {
			$all_tags_str .= "'" . str_replace("'", "\'", $tag) . "',";
		}
		if(!empty($all_tags_str)){
			$all_tags_str = substr($all_tags_str, 0, -1);
		}

		$this->ss->assign($tpl_str."_str", $all_tags_str);
		$this->ss->assign($tpl_str."_arr", $all_tags);
	}
	
	function getWirelessEditViewSmarty($parentFieldArray, $vardef, $displayParams, $tabindex) {
		$this->assignAllTags($vardef['tags_module']);
		$this->ss->assign('selected', isset($vardef['value'])? unencodeMultienum($vardef['value']):'');
		return parent::getWirelessEditViewSmarty($parentFieldArray, $vardef, $displayParams, $tabindex);
	}
	
	function getWirelessDetailViewSmarty($parentFieldArray, $vardef, $displayParams, $tabindex) {
		if(!empty($vardef['value'])){
			$vardef['value'] = substr(implode(", ", explode("^,^", $vardef['value'])), 1, -1);
		} 
		return parent::getWirelessDetailViewSmarty($parentFieldArray, $vardef, $displayParams, $tabindex);
	}
	
	function getEditViewSmarty($parentFieldArray, $vardef, $displayParams, $tabindex) {
		$this->assignAllTags($vardef['tags_module']);
		return parent::getEditViewSmarty($parentFieldArray, $vardef, $displayParams, $tabindex);
	}

    function getSearchViewSmarty($parentFieldArray, $vardef, $displayParams, $tabindex) {
        // Use the basic field type for searches, no need to format/unformat everything... for now
    	$this->setup($parentFieldArray, $vardef, $displayParams, $tabindex);

		$this->assignAllTags($vardef['tags_module']);
    
    	return $this->fetch('custom/include/SugarFields/Fields/Tag/SearchForm.tpl');
    }    
}
