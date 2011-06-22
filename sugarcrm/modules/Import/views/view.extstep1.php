<?php
if(!defined('sugarEntry') || !sugarEntry) die('Not A Valid Entry Point');
/*********************************************************************************
 * The contents of this file are subject to the SugarCRM Enterprise Subscription
 * Agreement ("License") which can be viewed at
 * http://www.sugarcrm.com/crm/products/sugar-enterprise-eula.html
 * By installing or using this file, You have unconditionally agreed to the
 * terms and conditions of the License, and You may not use this file except in
 * compliance with the License.  Under the terms of the license, You shall not,
 * among other things: 1) sublicense, resell, rent, lease, redistribute, assign
 * or otherwise transfer Your rights to the Software, and 2) use the Software
 * for timesharing or service bureau purposes such as hosting the Software for
 * commercial gain and/or for the benefit of a third party.  Use of the Software
 * may be subject to applicable fees and any use of the Software without first
 * paying applicable fees is strictly prohibited.  You do not have the right to
 * remove SugarCRM copyrights from the source code or user interface.
 *
 * All copies of the Covered Code must include on each user interface screen:
 *  (i) the "Powered by SugarCRM" logo and
 *  (ii) the SugarCRM copyright notice
 * in the same form as they appear in the distribution.  See full license for
 * requirements.
 *
 * Your Warranty, Limitations of liability and Indemnity are expressly stated
 * in the License.  Please refer to the License for the specific language
 * governing these rights and limitations under the License.  Portions created
 * by SugarCRM are Copyright (C) 2004-2007 SugarCRM, Inc.; All Rights Reserved.
 ********************************************************************************/
/*********************************************************************************
 * $Id: view.step1.php 31561 2008-02-04 18:41:10Z jmertic $
 * Description: view handler for step 1 of the import process
 * Portions created by SugarCRM are Copyright (C) SugarCRM, Inc.
 * All Rights Reserved.
 ********************************************************************************/
require_once('modules/Import/views/ImportView.php');

        
class ImportViewExtStep1 extends ImportView
{

    protected $pageTitleKey = 'LBL_CONFIRM_EXT_TITLE';

 	/** 
     * @see SugarView::display()
     */
 	public function display()
    {
        $source = !empty($_REQUEST['exteranl_source']) ? $_REQUEST['exteranl_source'] : '';
        $importModule = $_REQUEST['import_module'];
        global $mod_strings, $app_strings, $current_user;
        global $sugar_config;


        $mappingFile = $this->getMappingFile($source);
        if ( $mappingFile == null ) {
            $this->_showImportError($mod_strings['ERR_MISSING_MAP_NAME'],$_REQUEST['import_module'],'Step1');
            return;
        }

        // get list of required fields
        $required = array();
        foreach ( array_keys($this->bean->get_import_required_fields()) as $name ) {
            $properties = $this->bean->getFieldDefinition($name);
            if (!empty ($properties['vname']))
                $required[$name] = str_replace(":","",translate($properties['vname'] ,$this->bean->module_dir));
            else
                $required[$name] = str_replace(":","",translate($properties['name'] ,$this->bean->module_dir));
        }

        $this->ss->assign("MODULE_TITLE", $this->getModuleTitle());
        $this->ss->assign("rows",$this->getMappingRows($importModule) );
        $this->ss->assign("IMPORT_MODULE", $importModule);
        $this->ss->assign("JAVASCRIPT", $this->_getJS($required));

        $this->ss->display('modules/Import/tpls/extstep1.tpl');
    }

    private function getMappingRows($module)
    {
        global $app_strings, $current_language;
        $columns = array();
        $mappedFields = array();
        $mod_strings = return_module_language($current_language, $module);
        $ignored_fields = array();

        $mappingConfig = array(
          'first_name' => array('sugar_key' => 'first_name', 'sugar_label' => 'LBL_FIRST_NAME'),
          'last_name' => array('sugar_key' => 'last_name', 'sugar_label' => 'LBL_LAST_NAME'),
        );

        foreach($mappingConfig as $externalKey => $sugarMapping)
        {
            // See if we have any field map matches
            $defaultValue = $externalKey;

            // build string of options
            $fields  = $this->bean->get_importable_fields();
            $options = array();
            $defaultField = '';
            foreach ( $fields as $fieldname => $properties )
            {
                // get field name
                if (!empty ($properties['vname']))
					$displayname = str_replace(":","",translate($properties['vname'] ,$this->bean->module_dir));
                else
					$displayname = str_replace(":","",translate($properties['name'] ,$this->bean->module_dir));
                // see if this is required
                $req_mark  = "";
                $req_class = "";
                if ( array_key_exists($fieldname, $this->bean->get_import_required_fields()) ) {
                    $req_mark  = ' ' . $app_strings['LBL_REQUIRED_SYMBOL'];
                    $req_class = ' class="required" ';
                }
                // see if we have a match
                $selected = '';
                if ( !empty($defaultValue) && !in_array($fieldname,$mappedFields) && !in_array($fieldname,$ignored_fields) )
                {
                    if ( strtolower($fieldname) == strtolower($defaultValue)
                        || strtolower($fieldname) == str_replace(" ","_",strtolower($defaultValue))
                        || strtolower($displayname) == strtolower($defaultValue)
                        || strtolower($displayname) == str_replace(" ","_",strtolower($defaultValue)) )
                    {
                        $selected = ' selected="selected" ';
                        $defaultField = $fieldname;
                        $mappedFields[] = $fieldname;
                    }
                }
                // get field type information
                $fieldtype = '';
                if ( isset($properties['type'])
                        && isset($mod_strings['LBL_IMPORT_FIELDDEF_' . strtoupper($properties['type'])]) )
                    $fieldtype = ' [' . $mod_strings['LBL_IMPORT_FIELDDEF_' . strtoupper($properties['type'])] . '] ';
                if ( isset($properties['comment']) )
                    $fieldtype .= ' - ' . $properties['comment'];
                $options[$displayname.$fieldname] = '<option value="'.$fieldname.'" title="'. $displayname . htmlentities($fieldtype) . '"'
                    . $selected . $req_class . '>' . $displayname . $req_mark . '</option>\n';
            }

            // get default field value
            $defaultFieldHTML = '';
            if ( !empty($defaultField) ) {
                $defaultFieldHTML = getControl($module,$defaultField,$fields[$defaultField],( isset($default_values[$defaultField]) ? $default_values[$defaultField] : '' ));
            }

            if ( isset($default_values[$defaultField]) )
                unset($default_values[$defaultField]);

            // Bug 27046 - Sort the column name picker alphabetically
            ksort($options);

            $columns[] = array(
                'field_choices' => implode('',$options),
                'default_field' => $defaultFieldHTML,
                'cell1'         => str_replace(":",'',$mod_strings[$sugarMapping['sugar_label']]),
                'show_remove'   => false,
                );
        }

        return $columns;
    }

    private function getMappingFile($source)
    {
        $classname = 'ImportMap' . ucfirst($source);
        if ( file_exists("modules/Import/{$classname}.php") )
            require_once("modules/Import/{$classname}.php");
        elseif ( file_exists("custom/modules/Import/{$classname}.php") )
            require_once("custom/modules/Import/{$classname}.php");
        else
            return null;

        if ( class_exists($classname) )
        {
            $mapping_file = new $classname;
            return $mapping_file;
        }
        else
            return null;
    }

    private function getImportableExternalEAPMs()
    {
        require_once('include/externalAPI/ExternalAPIFactory.php');

        return ExternalAPIFactory::getModuleDropDown('Import', FALSE, FALSE, 'eapm_import_list');
    }

    /**
     * Returns JS used in this view
     */
    private function _getJS($required)
    {
        global $mod_strings;
        $print_required_array = "";
        foreach ($required as $name=>$display) {
            $print_required_array .= "required['$name'] = '". $display . "';\n";
        }
        $sqsWaitImage = SugarThemeRegistry::current()->getImageURL('sqsWait.gif');
        return <<<EOJAVASCRIPT
<script type="text/javascript">
<!--

document.getElementById('goback').onclick = function(){
    document.getElementById('extstep1').action.value = 'Step1';
    return true;
}

document.getElementById('gonext').onclick = function(){
    // validate form
    clear_all_errors();
    var form = document.getElementById('extstep1');
    var hash = new Object();
    var required = new Object();
    $print_required_array
    var isError = false;
    for ( i = 0; i < form.length; i++ ) {
		if ( form.elements[i].name.indexOf("colnum",0) == 0) {
            if ( form.elements[i].value == "-1") {
                continue;
            }
            if ( hash[ form.elements[i].value ] == 1) {
                isError = true;
                add_error_style('extstep1',form.elements[i].name,"{$mod_strings['ERR_MULTIPLE']}");
            }
            hash[form.elements[i].value] = 1;
        }
    }

    // check for required fields
	for(var field_name in required) {
		// contacts hack to bypass errors if full_name is set
		if (field_name == 'last_name' &&
				hash['full_name'] == 1) {
			continue;
		}
		if ( hash[ field_name ] != 1 ) {
            isError = true;
            add_error_style('extstep1',form.colnum_0.name,
                "{$mod_strings['ERR_MISSING_REQUIRED_FIELDS']} " + required[field_name]);
		}
	}

    // return false if we got errors
	if (isError == true) {
		return false;
	}

    // Move on to next step
    document.getElementById('extstep1').action.value = 'dupcheck';
    return true;
}

YAHOO.util.Event.onDOMReady(function(){
    var selects = document.getElementsByTagName('select');
    for (var i = 0; i < selects.length; ++i ){
        if (selects[i].name.indexOf("colnum_") != -1 ) {
            // fetch the field input control via ajax
            selects[i].onchange = function(){
                var module    = document.getElementById('extstep1').import_module.value;
                var fieldname = this.value;
                var matches   = /colnum_([0-9]+)/i.exec(this.name);
                var fieldnum  = matches[1];
                if ( fieldname == -1 ) {
                    document.getElementById('defaultvaluepicker_'+fieldnum).innerHTML = '';
                    return;
                }

                document.getElementById('defaultvaluepicker_'+fieldnum).innerHTML = '<img src="{$sqsWaitImage}" />'
                YAHOO.util.Connect.asyncRequest('GET', 'index.php?module=Import&action=GetControl&import_module='+module+'&field_name='+fieldname,
                    {
                        success: function(o)
                        {
                            document.getElementById('defaultvaluepicker_'+fieldnum).innerHTML = o.responseText;
                            SUGAR.util.evalScript(o.responseText);
                            enableQS(true);
                        },
                        failure: function(o) {/*failure handler code*/}
                    });
            }
        }
    }
    var inputs = document.getElementsByTagName('input');
    for (var i = 0; i < inputs.length; ++i ){
        if (inputs[i].id.indexOf("deleterow_") != -1 ) {
            inputs[i].onclick = function(){
                this.parentNode.parentNode.parentNode.removeChild(this.parentNode.parentNode);
            }
        }
    }
});
-->
</script>

EOJAVASCRIPT;
    }
}

?>
