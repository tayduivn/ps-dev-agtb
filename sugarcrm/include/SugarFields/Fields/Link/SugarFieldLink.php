<?php
/*********************************************************************************
 * The contents of this file are subject to
 * *******************************************************************************/
require_once('include/SugarFields/Fields/Base/SugarFieldBase.php');
require_once('vendors/Smarty/plugins/function.sugar_replace_vars.php');

class SugarFieldLink extends SugarFieldBase {
    public function apiFormatField(&$data, $bean, $args, $fieldName, $properties) {
    	// this is only for generated links
    	if(isset($bean->field_defs[$fieldName]['gen']) && $bean->field_defs[$fieldName]['gen'] == 1) {
	        $params = array(
	            'use_curly' => true,
	            'subject' => $bean->field_defs[$fieldName]['default'],
	            'fields' => $bean->fetched_row,
	            );
			$nothing = '';
	        $data[$fieldName] = smarty_function_sugar_replace_vars($params, $nothing);
	    } else {
            parent::apiFormatField($data, $bean, $args, $fieldName, $properties);
        }
    }
}
