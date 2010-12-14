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

class SugarFieldBool extends SugarFieldBase {
	/**
	 *
	 * @return The html for a drop down if the search field is not 'my_items_only' or a dropdown for all other fields.
	 *			This strange behavior arises from the special needs of PM. They want the my items to be checkboxes and all other boolean fields to be dropdowns.			
	 * @author Navjeet Singh
	 * @param $parentFieldArray - 
	 **/
	function getSearchViewSmarty($parentFieldArray, $vardef, $displayParams, $tabindex) {
		$this->setup($parentFieldArray, $vardef, $displayParams, $tabindex);
		if( preg_match("/current_user_only.*/", $vardef['name']) || preg_match("/favorites_only.*/", $vardef['name']) )
			return $this->fetch('include/SugarFields/Fields/Bool/EditView.tpl');
		else
			return $this->fetch('include/SugarFields/Fields/Bool/SearchView.tpl');
		
	}
    	
    public function getEmailTemplateValue($inputField, $vardef, $displayParams = array(), $tabindex = 0){
        global $app_list_strings;
        // This does not return a smarty section, instead it returns a direct value
        if ( $inputField == 'bool_true' || $inputField === true ) { // Note: true must be absolute true
            return $app_list_strings['checkbox_dom']['1'];
        } else if ( $inputField == 'bool_false' || $inputField === false){ // Note: false must be absolute false
            return $app_list_strings['checkbox_dom']['2'];
        } else { // otherwise we return blank display
            return '';
        }
    }

    public function unformatField($formattedField, $vardef){
        if ( empty($formattedField) ) {
            $unformattedField = false;
            return $unformattedField;
        }
        if ( $formattedField == '0' || $formattedField == 'off' || $formattedField == 'false' || $formattedField == 'no' ) {
            $unformattedField = false;
        } else {
            $unformattedField = true;
        }
        
        return $unformattedField;
    }

}

?>
