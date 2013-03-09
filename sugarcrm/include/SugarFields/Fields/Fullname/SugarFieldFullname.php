<?php
/*********************************************************************************
 * The contents of this file are subject to
 * *******************************************************************************/
require_once('include/SugarFields/Fields/Base/SugarFieldBase.php');

class SugarFieldFullname extends SugarFieldBase
{
    public function apiFormatField(&$data, $bean, $args, $fieldName, $properties) {
        global $locale, $app_list_strings;

        $nameparts = array();
        foreach(array('first_name', 'last_name', 'salutation', 'title') as $field) {
            if(!empty($bean->$field)) {
                $nameparts[$field] = $bean->$field;
            } else {
                $nameparts[$field] = '';
            }
        }
        $bean->ACLFilterFieldList($nameparts, array(), array("blank_value" => true));

        if(!empty($nameparts['salutation']) && isset($this->field_defs['salutation']['options'])
        		&& isset($app_list_strings[$this->field_defs['salutation']['options']])
        		&& isset($app_list_strings[$this->field_defs['salutation']['options']][$nameparts['salutation']]) ) {

        	$nameparts['salutation'] = $app_list_strings[$this->field_defs['salutation']['options']][$nameparts['salutation']];
        } else {
            $nameparts['salutation'] = '';
        }

         $data[$fieldName] = $locale->getLocaleFormattedName($nameparts['first_name'], $nameparts['last_name'], $nameparts['salutation'], $nameparts['title']);
    }

    function getDetailViewSmarty($parentFieldArray, $vardef, $displayParams, $tabindex)
	{
        $this->setup($parentFieldArray, $vardef, $displayParams, $tabindex);
        return $this->fetch($this->findTemplate('DetailView'));
    }

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
        if ( property_exists($focus,'first_name') && property_exists($focus,'last_name') ) {
            $name_arr = preg_split('/\s+/',$value);

            if ( count($name_arr) == 1) {
                $focus->last_name = $value;
            }
            else {
                // figure out what comes first, the last name or first name
                if ( strpos($settings->default_locale_name_format,'l') > strpos($settings->default_locale_name_format,'f') ) {
                    $focus->first_name = array_shift($name_arr);
                    $focus->last_name = join(' ',$name_arr);
                }
                else {
                    $focus->last_name = array_shift($name_arr);
                    $focus->first_name = join(' ',$name_arr);
                }
            }
        }
    }
}