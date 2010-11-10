<?php
/*********************************************************************************
 * The contents of this file are subject to
 * *******************************************************************************/
require_once('include/SugarFields/Fields/Base/SugarFieldBase.php');

class SugarFieldPhone extends SugarFieldBase {

    function getListViewSmarty($parentFieldArray, $vardef, $displayParams, $col) {	
        $tabindex = 1;
        $isArray = is_array($parentFieldArray);
        $fieldName = $vardef['name'];
       
        if ( $isArray ) {
        	$fieldNameUpper = strtoupper($fieldName);
            if ( isset($parentFieldArray[$fieldNameUpper])) {
                $parentFieldArray[$fieldName] = $this->formatField($parentFieldArray[$fieldNameUpper],$vardef);
            } else {
                $parentFieldArray[$fieldName] = '';
            }
        } else {
            if ( isset($parentFieldArray->$fieldName) ) {
                $parentFieldArray->$fieldName = $this->formatField($parentFieldArray->$fieldName,$vardef);
            } else {
                $parentFieldArray->$fieldName = '';
            }
        }
    	$this->setup($parentFieldArray, $vardef, $displayParams, $tabindex, false);
        
        $this->ss->left_delimiter = '{';
        $this->ss->right_delimiter = '}';
        $this->ss->assign('col',$vardef['name']);
        $this->ss->assign('usa_format', !empty($vardef['validate_usa_format']) ? true : false);
        return $this->fetch($this->findTemplate('ListView'));
    }	

}
?>