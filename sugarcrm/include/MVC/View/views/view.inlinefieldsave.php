<?php
/*********************************************************************************
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
require_once('include/MVC/View/views/view.ajax.php');
require_once('include/formbase.php');

class ViewInlineFieldSave extends ViewAjax {
	var $type ='detail';
	function ViewInlineField(){
 		parent::ViewAjax();
 	}
 	
	function display(){
		$bean = $this->bean;
        $fieldName = $_REQUEST['field'];

        $prev_row = $bean->fetched_row;
        populateFromPost('',$bean);
        $bean->save();
        $bean->retrieve($bean->id);
        $new_row = $bean->fetched_row;
        $bean->format_all_fields();
        $field_list = array();
        foreach ( $prev_row as $idx => $contents ) {
            if ( $contents != $new_row[$idx] ) {
                // Looks like this field changed
                if ( isset($bean->field_defs[$idx]) ) {
                    $vardef = $bean->field_defs[$idx];
                    $sugarField = SugarFieldHandler::getSugarField($vardef['type']);
                    $field_list[$idx] = $sugarField->getInlineView($bean, $vardef, array());
                } else {
                    $field_list[$idx] = $bean->$idx;
                }
            }
        }

        $json = new JSON(JSON_LOOSE_TYPE);

        echo $json->encode($field_list);
	}
}

