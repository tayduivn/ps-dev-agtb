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

require_once('include/SugarFields/Fields/Base/SugarFieldBase.php');
require_once('modules/Currencies/Currency.php');

class SugarFieldInt extends SugarFieldBase {
    public function formatField($rawField, $vardef){
        if ( !empty($vardef['disable_num_format']) ) {
            return $rawField;
        }
        if ( $rawField === '' || $rawField === NULL ) {
            return '';
        }
            
        return format_number($rawField,0,0);
    }

    public function unformatField($formattedField, $vardef){
        if ( $formattedField === '' || $formattedField === NULL ) {
            return '';
        }
        return (int)unformat_number($formattedField);
    }

    public function unformatSearchRequest(&$inputData, &$field) {
        $field['value'] = $this->unformatField($field['value'],$field);
    }

    function getSearchViewSmarty($parentFieldArray, $vardef, $displayParams, $tabindex) {
        // Use the basic field type for searches, no need to format/unformat everything... for now
    	$this->setup($parentFieldArray, $vardef, $displayParams, $tabindex);
        
    
    	return $this->fetch('include/SugarFields/Fields/Int/SearchForm.tpl');
    }    
}