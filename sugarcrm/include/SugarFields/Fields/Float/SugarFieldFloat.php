<?php

/********************************************************************************
 *The contents of this file are subject to the SugarCRM Professional End User License Agreement
 *("License") which can be viewed at http://www.sugarcrm.com/EULA.
 *By installing or using this file, You have unconditionally agreed to the terms and conditions of the License, and You may
 *not use this file except in compliance with the License. Under the terms of the license, You
 *shall not, among other things: 1) sublicense, resell, rent, lease, redistribute, assign or
 *otherwise transfer Your rights to the Software, and 2) use the Software for timesharing or
 *service bureau purposes such as hosting the Software for commercial gain and/or for the benefit
 *of a third party.  Use of the Software may be subject to applicable fees and any use of the
 *Software without first paying applicable fees is strictly prohibited.  You do not have the
 *right to remove SugarCRM copyrights from the source code or user interface.
 * All copies of the Covered Code must include on each user interface screen:
 * (i) the "Powered by SugarCRM" logo and
 * (ii) the SugarCRM copyright notice
 * in the same form as they appear in the distribution.  See full license for requirements.
 *Your Warranty, Limitations of liability and Indemnity are expressly stated in the License.  Please refer
 *to the License for the specific language governing these rights and limitations under the License.
 *Portions created by SugarCRM are Copyright (C) 2004 SugarCRM, Inc.; All Rights Reserved.
 ********************************************************************************/

require_once('include/SugarFields/Fields/Int/SugarFieldInt.php');

class SugarFieldFloat extends SugarFieldInt 
{
    public function formatField($rawField, $vardef){
        // A null precision uses the user prefs / system prefs by default
        $precision = null;
        if ( isset($vardef['precision']) ) {
            $precision = $vardef['precision'];
        }
        
        if ( $rawField === '' || $rawField === NULL ) {
            return '';
        }

        return format_number($rawField,$precision,$precision);
    }

    public function apiFormatField(&$data, $bean, $args, $fieldName, $properties){
        $data[$fieldName] = isset($bean->$fieldName) && is_numeric($bean->$fieldName)
                            ? (float)$bean->$fieldName : null;
    }

    public function unformatField($formattedField, $vardef){
        if ( $formattedField === '' || $formattedField === NULL ) {
            return '';
        }
        if (is_array($formattedField)) {
            $formattedField = array_shift($formattedField);
        }
        return (float)unformat_number($formattedField);
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
        $value = str_replace($settings->num_grp_sep,"",$value);
        $dec_sep = $settings->dec_sep;
        if ( $dec_sep != '.' ) {
            $value = str_replace($dec_sep,".",$value);
        }
        if ( !is_numeric($value) ) {
            return false;
        }
        
        return $value;
    }

    /**
     * For Floats we need to round down to the precision of the passed in value, since the db's could be showing
     * something different
     *
     * @param Number $value                         The value for which we are trying to filter
     * @param String $fieldName                     What field we are trying to modify
     * @param SugarBean $bean                       The associated SugarBean
     * @param SugarQuery $q                         The full query object
     * @param SugarQuery_Builder_Where $where       The where object for the filter
     * @param String $op                            The filter operation we are trying to do
     * @return bool
     * @throws SugarApiExceptionInvalidParameter
     */
    public function fixForFilter(
        &$value,
        $fieldName,
        SugarBean $bean,
        SugarQuery $q,
        SugarQuery_Builder_Where $where,
        $op
    ) {
        // if we have an array, pull the first value
        if (is_array($value)) {
            $v = $value[1];
        } else {
            $v = $value;
        }

        $decimal_separator_location = substr(strrchr($v, '.'), 1);
        // if we don't have a decimal, just use the normal methods back up the chain
        // since it's a whole number that is being searched on
        if ($decimal_separator_location === false) {
            return true;
        }
        // ROUND(<value>, <precision>) is the standard across all DB's we support
        $field = "ROUND($fieldName, ". strlen($decimal_separator_location) . ")";

        switch($op){
            case '$equals':
                $q->whereRaw("$field = $value");
                return false;
            case '$not_equals':
                $q->whereRaw("$field != $value");
                return false;
            case '$between':
                if (!is_array($value) || count($value) != 2) {
                    throw new SugarApiExceptionInvalidParameter(
                        '$between requires an array with two values.'
                    );
                }
                $q->whereRaw("$field BETWEEN $value[0] AND $value[1]");
                return false;
            case '$lt':
                $q->whereRaw("$field < $value");
                return false;
            case '$lte':
                $q->whereRaw("$field <= $value");
                return false;
            case '$gt':
                $q->whereRaw("$field > $value");
                return false;
            case '$gte':
                $q->whereRaw("$field >= $value");
                return false;
        }

        return true;
    }
}
