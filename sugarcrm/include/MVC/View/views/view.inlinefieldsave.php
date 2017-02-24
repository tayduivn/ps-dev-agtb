<?php
/*
 * Your installation or use of this SugarCRM file is subject to the applicable
 * terms available at
 * http://support.sugarcrm.com/Resources/Master_Subscription_Agreements/.
 * If you do not agree to all of the applicable terms or do not have the
 * authority to bind the entity as an authorized representative, then do not
 * install or use this SugarCRM file.
 *
 * Copyright (C) SugarCRM Inc. All rights reserved.
 */
require_once('include/formbase.php');

class ViewInlineFieldSave extends ViewAjax 
{
	var $type ='detail';
 	
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

        $json = new JSON();

        echo $json->encode($field_list);
	}
}
