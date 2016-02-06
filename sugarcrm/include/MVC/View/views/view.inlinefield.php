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

class ViewInlineField extends ViewAjax 
{
	var $type ='detail';
 	
	function display(){
		$bean = $this->bean;
        $fieldName = $_REQUEST['field'];
        $fieldData = $bean->$fieldName;

        $fieldType = 'text';
        if ( isset($bean->field_defs[$fieldName]['type']) ) {
            $fieldType = $bean->field_defs[$fieldName]['type'];
        }
        $sugarField = SugarFieldHandler::getSugarField($fieldType);
        $inputField = $sugarField->getInlineEdit($bean, $bean->field_defs[$fieldName], array());

        $theForm = "<form method='POST' action='index.php' id='InlineEditor' onsubmit='return false;'><input type='hidden' name='record' value='".$bean->id."'><input type='hidden' name='module' value='".$bean->module_dir."'><input type='hidden' name='action' value='inlinefieldsave'><input type='hidden' name='field' value='".$fieldName."'>";
        echo $theForm.$inputField."<button onclick='InlineEditor.save(this); return false;'>Save<button></form>";
	}
}

