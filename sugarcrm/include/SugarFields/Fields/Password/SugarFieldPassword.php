<?php
/*********************************************************************************
 * The contents of this file are subject to
 * *******************************************************************************/
require_once('include/SugarFields/Fields/Base/SugarFieldBase.php');

class SugarFieldPassword extends SugarFieldBase 
{
    /**
     * @see SugarFieldBase::importSanitize()
     */
    public function importSanitize(
        $value,
        $vardef,
        $focus,
        ImportFieldSanitize $settings
        )
    {
        $value = md5($value);
        
        return $value;
    }

   /**
     * This function will blank out any password field
     * 
     * @param array $data
     * @param SugarBean $bean
     * @param array $args
     * @param string $fieldName
     * @param array $properties
     */
    public function apiFormatField(array &$data, SugarBean $bean, array $args, $fieldName, $properties)
    {
        $data[$fieldName] = '';
    }    
}
?>