<?php
/*********************************************************************************
 * The contents of this file are subject to
 * *******************************************************************************/
require_once('include/SugarFields/Fields/Base/SugarFieldBase.php');

class SugarFieldEmail extends SugarFieldBase {
    public function apiFormatField(&$data, $bean, $args, $fieldName, $properties) {
    	// need to remove Email fields if Email1 is not allowed
    	if(!empty($bean->field_defs['email']) && !empty($bean->field_defs['email1']) && !$bean->ACLFieldAccess('email1', 'access') && isset($data['email'])) {
    		unset($data['email']);
    	}
    	parent::apiFormatField($data, $bean, $args, $fieldName, $properties);
    }
	/**
     * This should be called when the bean is saved from the API. Most fields can just use default, which calls the field's individual ->save() function instead.
     * @param SugarBean $bean - the bean performing the save
     * @param array $params - an array of paramester relevant to the save, which will be an array passed up to the API
     * @param string $field - The name of the field to save (the vardef name, not the form element name)
     * @param array $properties - Any properties for this field
     */
    public function apiSave(SugarBean $bean, array $params, $field, $properties) {
		if(!empty($bean->field_defs['email']) && !empty($bean->field_defs['email1']) && !$bean->ACLFieldAccess('email1', 'edit')) {
        	throw new SugarApiExceptionNotAuthorized('No access to edit records for module: '.$bean->module);
        }
    }    
}
