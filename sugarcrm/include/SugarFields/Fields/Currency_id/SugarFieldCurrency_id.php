<?php
/*********************************************************************************
 * The contents of this file are subject to
 * *******************************************************************************/
require_once('include/SugarFields/Fields/Base/SugarFieldBase.php');

class SugarFieldCurrency_id extends SugarFieldBase {
    /**
     * Formats a field for the Sugar API
     * @see SugarFieldBase::apiFormatField
     */
    public function apiFormatField(&$data, $bean, $args, $fieldName, $properties) {
        if (!empty($bean->$fieldName)) {
            $data[$fieldName] = $bean->$fieldName;
        } else {
            $data[$fieldName] = "-99";
        }
    }

}
?>