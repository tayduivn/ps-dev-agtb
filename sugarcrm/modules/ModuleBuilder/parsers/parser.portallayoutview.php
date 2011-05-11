<?php
if (! defined('sugarEntry') || ! sugarEntry)
die('Not A Valid Entry Point');
/**
 * LICENSE: The contents of this file are subject to the SugarCRM Professional
 * End User License Agreement ("License") which can be viewed at
 * http://www.sugarcrm.com/EULA.  By installing or using this file, You have
 * unconditionally agreed to the terms and conditions of the License, and You
 * may not use this file except in compliance with the License.  Under the
 * terms of the license, You shall not, among other things: 1) sublicense,
 * resell, rent, lease, redistribute, assign or otherwise transfer Your
 * rights to the Software, and 2) use the Software for timesharing or service
 * bureau purposes such as hosting the Software for commercial gain and/or for
 * the benefit of a third party.  Use of the Software may be subject to
 * applicable fees and any use of the Software without first paying applicable
 * fees is strictly prohibited.  You do not have the right to remove SugarCRM
 * copyrights from the source code or user interface.
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
 * by SugarCRM are Copyright (C) 2006 SugarCRM, Inc.; All Rights Reserved.
 */
//FILE SUGARCRM flav=ent ONLY

require_once ('modules/ModuleBuilder/parsers/parser.modifylayoutview.php');
require_once 'modules/ModuleBuilder/parsers/views/History.php' ;

class ParserPortalLayoutView extends ParserModifyLayoutView
{

    var $maxColumns; // number of columns in this layout
    var $usingWorkingFile = false; // if a working file exists (used by view.edit.php among others to determine the title for the layout edit panel)
    var $language_module; // set to module name for studio, passed to the smarty template and used by sugar_translate
    var $_sourceFiles = array(); // private
    var $_customFile; // private
    var $_workingFile; // private
    var $_module; // private
    var $_view; // private
    var $_viewdefs; // private
    var $_fieldDefs; // private


    /**
     * Constructor
     */
    function init ($module, $view, $submittedLayout = false)
    {
        $GLOBALS['log']->debug("in ParserPortalLayoutView");
        $file = "portal/modules/{$module}/metadata/" . strtolower($view) . "defs.php";
        $this->_customFile = "custom/" . $file;
        $this->_workingFile = "custom/working/" . $file;
        $this->_sourceFile = $file;
        $this->_module = $module;
        $this->_view = strtolower($view);
        $this->language_module = $module;

        if (is_file($this->_workingFile))
        {
            $this->_sourceFile = $this->_workingFile;
            $this->usingWorkingFile = true;
        }
        else if (is_file($this->_customFile))
        {
            $this->_sourceFile = $this->_customFile;
        }

        // get the fieldDefs from the bean
        $class = $GLOBALS ['beanList'] [$module];
        require_once ($GLOBALS ['beanFiles'] [$class]);
        $bean = new $class();
        $this->_fieldDefs = & $bean->field_defs;

        $this->loadModule($this->_module, $this->_view);

        // now fix the layout so that it is compatible with the latest metadata definition = rename data section as a panel within a panel section
        $defs =$this->_viewdefs['data'];
        unset($this->_viewdefs['data']);
        $this->_viewdefs['panels'] = array($this->_parseData($defs)); // put into a canonical format
        $this->maxColumns = $this->_viewdefs ['templateMeta'] ['maxColumns'];

        $GLOBALS['log']->debug("ParserPortalLayoutView: after loadModule");
        if ($submittedLayout)
        {
            // replace the definitions with the new submitted layout
            $this->_loadLayoutFromRequest();
        } else
        {
            $this->_padFields(); // destined for a View, so we want to add in (empty) fields
        }
		
		$this->_history = new History ( $this->_customFile ) ;


    }

    function _parseData ($panel)
    {
        $fields = array();
        if (empty($panel))
        return;
        foreach ($panel as $rowID => $row)
        {
            foreach ($row as $colID => $col)
            {
                $properties = array();

                if (! empty($col))
                {
                    if (is_string($col))
                    {
                        $properties ['name'] = $col;
                    } else if (! empty($col ['field']))
                    {
                        // portal metadata uses 'field' to identify the fieldname; new metadata uses 'name'
                        $col['name'] = $col['field'];
                        unset($col['field']);
                        $properties = $col;
                    }
                } else
                {
                    $properties ['name'] = translate('LBL_FILLER');
                }

                if (! empty($properties ['name']))
                {

                    // get this field's label - if it has not been explicity provided, see if the fieldDefs has a label for this field, and if not fallback to the field name
                    if (! isset($properties ['label']))
                    {
                        if (! empty($this->_fieldDefs [$properties ['name']] ['vname']))
                        {
                            $properties['label'] = translate($this->_fieldDefs[$properties ['name']]['vname'], $this->_module);
                        } else
                        {
                            $properties ['label'] = $properties ['name'];
                        }
                    } else {
                    	$properties['label'] = translate($this->_fieldDefs[$properties ['name']]['vname'], $this->_module);
                    }                    

                    $displayData[$rowID] [$colID] = $properties;

                }
            }
        }
        return $displayData;
    }

    function _fromNewToOldMetaData()
    {
        $GLOBALS['log']->debug("_fromNewToOldMetaData(): START=".print_r($this->_viewdefs,true));
        if (isset($this->_viewdefs['panels'])) // check this as we might be called twice in succession by a save action - once to write the working file and once to handleSave
        {
            // recreate the original portal metafile format - replace the panels section with 'data', and rename field 'name' to 'field'
            $defs = $this->_viewdefs['panels'][0];
            $this->_viewdefs['data'] = $defs;
            unset($this->_viewdefs['panels']);
            foreach($this->_viewdefs['data'] as $rowID=>$row)
            {
                foreach($row as $fieldID=>$field)
                {
                    if ((! empty($this->_fieldDefs [$field ['name']] ['auto_increment']) && 
                            $this->_fieldDefs [$field ['name']] ['auto_increment']) ||
                        !empty($this->_fieldDefs [$field ['name']]['calculated']))
                    {
                        $field['readOnly'] = true;
                    }
                	$field['field'] = $field['name'];
                    unset($field['name']);
                    $this->_viewdefs['data'][$rowID][$fieldID] = $field;
                }
            }
        }
        $GLOBALS['log']->debug("_fromNewToOldMetaData(): END=".print_r($this->_viewdefs,true));
    }

    function writeWorkingFile ()
    {
        $this->_fromNewToOldMetaData();
        parent::writeWorkingFile();
    }

    function handleSave ()
    {
        $this->_fromNewToOldMetaData();
        parent::handleSave();
    }

    function _getOrigFieldViewDefs ()
    {
        $origFieldDefs = array();
        if (file_exists($this->_sourceFile))
        {
            include ($this->_sourceFile);
            $origdefs = $viewdefs [$this->_module] [$this->_view]['data'];
            foreach ($origdefs as $row)
            {
                foreach ($row as $fieldDef)
                {
                    if (is_array($fieldDef))
                    {
                        $fieldName = $fieldDef ['field'];
                        $fieldDef['name'] = $fieldName;
                        unset($fieldDef['field']);

                    } else
                    {
                        $fieldName = $fieldDef;
                    }
                    $origFieldDefs [$fieldName] = $fieldDef;
                }
            }
        }
        return $origFieldDefs;
    }

    /* getModelFields
     *
     * Overrides _getModelFields from parent class.  For portal fields, we ignore the
     * ((!empty($def['studio']) && $def['studio'] == 'visible') check because it is
     * insufficient.  Studio visible fields do not necessary map to portal fields.  For
     * example, fields that call functions should not be permissible since the files for
     * these functions may not be present in the portal side.
     *
     */
    function _getModelFields ()
    {
        $modelFields = array();
        $origViewDefs = $this->_getOrigFieldViewDefs();
        foreach ($origViewDefs as $field => $def)
        {
            if (!empty($field))
            {
                if (! is_array($def)) {
                    $def = array('name' => $field);
                }
                // get this field's label - if it has not been explicitly provided, see if the fieldDefs has a label for this field, and if not fallback to the field name
                if (! isset($def ['label']))
                        {
                            if (! empty($this->_fieldDefs [$field] ['vname']))
                            {
                                $def ['label'] = $this->_fieldDefs [$field] ['vname'];
                            } else
                            {
                                $def ['label'] = $field;
                            }
                        }
                $modelFields[$field] = array('name' => $field, 'label' => $def ['label']);
            }
        }
        
        $invalidTypes = array('parent', 'parent_type', 'iframe', 'encrypt');
        foreach ($this->_fieldDefs as $field => $def)
        {
        	/**
        	 * Here are the checks:
        	 * 1) It is a database or custom field (not non-db)
        	 * 2) The field does not invoke a function
        	 * 3) The field is not the deleted field
        	 * 4) The field is not an id field
        	 * 5) The field type is not in the $invalidTypes Array
        	 */
        	if ((empty($def ['source']) || $def ['source'] == 'db' || $def ['source'] == 'custom_fields') &&
                empty($def['function']) && strcmp($field, 'deleted') != 0 &&
                $def['type'] != 'id' && (empty($def ['dbType']) || $def ['dbType'] != 'id') &&
                (isset($def['type']) && !in_array($def['type'], $invalidTypes)))
            {
            	$label = isset($def['vname']) ? $def ['vname'] : $def['name'];
                //$GLOBALS['log']->debug($label . ',' . $def['type']);
            	$modelFields [$field] = array('name' => $field, 'label' => $label);
            }
        }
        return $modelFields;
    }
    
    /**
     * @return Array list of fields in this module that have the calculated property
     */
    public function getCalculatedFields() {
    	$ret = array();
    	foreach ($this->_fieldDefs as $field => $def)
        {
        	if(!empty($def['calculated']) && !empty($def['formula']))
        	{
        		$ret[] = $field;
        	}
        }
        
        return $ret;
    }
	
	function getHistory ()
	{
		return $this->_history ;
	}

    function getFieldDefs()
    {
        return $this->_fieldDefs;
    }

}
?>