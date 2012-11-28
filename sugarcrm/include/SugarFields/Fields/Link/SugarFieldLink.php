<?php
/*********************************************************************************
 * The contents of this file are subject to
 * *******************************************************************************/
require_once('include/SugarFields/Fields/Base/SugarFieldBase.php');
require_once('include/Smarty/plugins/function.sugar_replace_vars.php');

class SugarFieldLink extends SugarFieldBase {
    public function apiFormatField(&$data, $bean, $args, $fieldName, $properties) {
        $params = array(
            'use_curly' => true,
            'subject' => $bean->field_defs[$fieldName]['default'],
            'fields' => $bean->fetched_row,
            );
		$nothing = '';
        $data[$fieldName] = smarty_function_sugar_replace_vars($params, $nothing);
    }
}
