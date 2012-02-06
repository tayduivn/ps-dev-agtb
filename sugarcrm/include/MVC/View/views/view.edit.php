<?php
/*********************************************************************************
 * The contents of this file are subject to
 * *******************************************************************************/
/*
 * Created on Apr 13, 2007
 *
 * To change the template for this generated file go to
 * Window - Preferences - PHPeclipse - PHP - Code Templates
 */

require_once('include/EditView/EditView2.php');
 class ViewEdit extends SugarView{
 	var $ev;
 	var $type ='edit';
 	var $useForSubpanel = false;  //boolean variable to determine whether view can be used for subpanel creates
 	var $useModuleQuickCreateTemplate = false; //boolean variable to determine whether or not SubpanelQuickCreate has a separate display function
 	var $showTitle = true;

 	function ViewEdit(){
 		parent::SugarView();
 	}

 	function preDisplay(){
 		$metadataFile = $this->getMetaDataFile();
 		$this->ev = new EditView();
 		$this->ev->ss =& $this->ss;
 		$this->ev->setup($this->module, $this->bean, $metadataFile, 'include/EditView/EditView.tpl');

        //fixing bug #46079: Calculated field has no value if a field in formula is not shown on view
        $fieldDefs = $this->bean->field_defs;
        foreach ($fieldDefs as $field => $def)
        {
            if (isset($def['calculated']) && $def['calculated'] && !empty($def['formula']))
            {
                if ($this->ev->isFieldOnLayout($field))
                {
                    require_once("include/Expressions/Expression/Parser/Parser.php");
                    $triggerFields = Parser::getFieldsFromExpression($def['formula']);
                    foreach($triggerFields as $fieldName)
                    {
                        if (!$this->ev->isFieldOnLayout($fieldName))
                        {
                            $this->ev->defs['templateMeta']['form']['hidden'] .= 
                                '<input type="hidden" name="' . $fieldName . '" value="' . htmlspecialchars($this->bean->$fieldName) . '" />';
                        }
                    }
                }
            }
        }
 	}

 	function display(){
		$this->ev->process();
		echo $this->ev->display($this->showTitle);
 	}
 }
?>
