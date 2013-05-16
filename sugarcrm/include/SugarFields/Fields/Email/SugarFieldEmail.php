<?php
/*********************************************************************************
 * The contents of this file are subject to
 * *******************************************************************************/
require_once 'include/SugarFields/Fields/Base/SugarFieldBase.php';

class SugarFieldEmail extends SugarFieldBase
{
    //BEGIN SUGARCRM flav=pro ONLY
    /**
     * Formats a field for the Sugar API, unsets the email
     * record from the data array if the user does not have access
     *
     * @param array $data
     * @param SugarBean $bean
     * @param array $args
     * @param string $fieldName
     * @param array $properties
     */    
    public function apiFormatField(&$data, $bean, $args, $fieldName, $properties)
    {
        // need to remove Email fields if Email1 is not allowed
        if (!empty($bean->field_defs['email']) && !empty($bean->field_defs['email1'])
            && !$bean->ACLFieldAccess('email1', 'access')
            && isset($data['email'])) {
            unset($data['email']);
        }
        parent::apiFormatField($data, $bean, $args, $fieldName, $properties);
    }
    /**
     * This should be called when the bean is saved from the API. 
     * Most fields can just use default, which calls the field's 
     * individual ->save() function instead.
     * 
     * @param SugarBean $bean the bean performing the save
     * @param array $params an array of paramester relevant to the save, which will be an array passed up to the API
     * @param string $field The name of the field to save (the vardef name, not the form element name)
     * @param array $properties Any properties for this field
     */
    public function apiSave(SugarBean $bean, array $params, $field, $properties)
    {
        if (!empty($bean->field_defs['email'])
            && !empty($bean->field_defs['email1'])
            && !$bean->ACLFieldAccess('email1', 'edit')
        ) {
            throw new SugarApiExceptionNotAuthorized('No access to edit records for module: '.$bean->module);
        }
        parent::apiSave($bean, $params, $field, $properties);
    }
    //END SUGARCRM flav=pro ONLY

    /**
     * Format a Raw email array record from the email_address relationship
     * 
     * @param array $rawEmail 
     * @return array
     */
    public function formatEmails(array $rawEmail) 
    {
        static $emailProperties = array(
            'email_address' => true,
            'opt_out' => true,
            'invalid_email' => true,
            'primary_address' => true,
        );

        return array_intersect_key($rawEmail, $emailProperties);
    }
}
