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

// $Id: ListViewParser.php 23721 2007-06-15 23:52:36Z clee $

//FILE SUGARCRM flav=ent ONLY


require_once ('modules/ModuleBuilder/parsers/parser.modifylistview.php');
require_once 'modules/ModuleBuilder/parsers/views/History.php' ;

class ParserPortalListView extends ParserModifyListView
{
	
	var $listViewDefs = false;
	var $defaults = array();
	var $additional = array();
	var $available = array();
	var $language_module;
    var $columns = array('LBL_DEFAULT' => 'getDefaultFields', 'LBL_AVAILABLE' => 'getAvailableFields');
	
	function init ($module_name)
	{
		global $app_list_strings;
		$this->module_name = $module_name;
		$this->mod_strings = return_module_language($GLOBALS ['current_language'], $this->module_name);
		$class = $GLOBALS ['beanList'] [$this->module_name];
		require_once ($GLOBALS ['beanFiles'] [$class]);
		$this->module = new $class();
		
		include ('portal/modules/' . $this->module_name . '/metadata/listviewdefs.php');
		$this->originalListViewDefs = $viewdefs[$this->module_name]['listview'];
		$this->fixKeys($this->originalListViewDefs);
		$this->customFile = 'custom/portal/modules/' . $this->module_name . '/metadata/listviewdefs.php';
		if (file_exists($this->customFile)) {
			include ($this->customFile);
			$this->listViewDefs = $viewdefs[$this->module_name]['listview'];
			$this->fixKeys($this->listViewDefs);
		} else
		{
			$this->listViewDefs = & $this->originalListViewDefs;
		}
		
		$this->_fromNewToOldMetaData();

		$this->language_module = $this->module_name;
		
		$this->_history = new History ($this->customFile) ;
	}
	
	function _fromNewToOldMetaData()
	{
	    foreach($this->listViewDefs as $key=>$value)
	    {
	        $value['default'] = 'true';
	        $this->listViewDefs[$key] = $value;
	    }
	}

	function addRelateData($fieldname, $listfielddef) {
		$modFieldDef = $this->module->field_defs [ strtolower ( $fieldname ) ];
		if (!empty($modFieldDef['module']) && !empty($modFieldDef['id_name'])) {
			$listfielddef['module'] = $modFieldDef['module'];
			$listfielddef['id'] = strtoupper($modFieldDef['id_name']);
			$listfielddef['link'] = in_array($listfielddef['module'], array('Cases', 
			                                                                //BEGIN SUGARCRM flav!=sales ONLY
			                                                                'Bugs', 
			                                                                //END SUGARCRM flav!=sales ONLY
			                                                                'KBDocuments'));
			$listfielddef['related_fields'] = array (strtolower($modFieldDef['id_name']));
		}
		return $listfielddef;
	}	
	
	function handleSave ()
	{
		if (!file_exists($this->customFile)) {
			//Backup the orginal layout to the history
			$this->_history->append('portal/modules/' . $this->module_name . '/metadata/listviewdefs.php');
		}
		
		$requestfields = $this->_loadLayoutFromRequest();
	    $fields = array();
        foreach($requestfields as $key=>$value) {
            if ($value['default'] == 'true') {
                unset($value['default']);
                $fields[strtoupper($key)] = $value;
            }
        }	
	    mkdir_recursive(dirname($this->customFile));
        if (! write_array_to_file("viewdefs['{$this->module_name}']['listview']", $fields, $this->customFile)) {
            $GLOBALS ['log']->fatal("Could not write $newFile");
        }
	}	

	
	/**
	 * returns unused fields that are available for using in either default or additional list views
	 */
	function getAvailableFields ()
	{
		$this->availableFields = array ( ) ;
		$lowerFieldList = array_change_key_case ( $this->listViewDefs ) ;
		foreach ( $this->originalListViewDefs as $key => $def )
		{
			$key = strtolower ( $key ) ;
			if (! isset ( $lowerFieldList [ $key ] ))
			{
				$this->availableFields [ $key ] = $def ;
			}
		}
		$GLOBALS['log']->debug('parser.modifylistview.php->getAvailableFields(): field_defs='.print_r($this->availableFields,true));
		$modFields = !empty($this->module->field_name_map) ? $this->module->field_name_map : $this->module->field_defs;
		$invalidTypes = array('iframe', 'encrypt');
		foreach ( $modFields as $key => $def )
		{
			$fieldName = strtolower ( $key ) ;
			if (!isset ( $lowerFieldList [ $fieldName ] )) // bug 16728 - check this first, so that other conditions (e.g., studio == visible) can't override and add duplicate entries
			{
                //Similar parsing rules as in parser.portallayoutview.php
                if ((empty($def ['source']) || $def ['source'] == 'db' || $def ['source'] == 'custom_fields') &&
	                empty($def['function']) &&
	                strcmp($key, 'deleted') != 0 &&
	                $def['type'] != 'id' && (empty($def ['dbType']) || $def ['dbType'] != 'id') &&
	                (isset($def['type']) && !in_array($def['type'], $invalidTypes)))
					{
						$label = (isset ( $def [ 'vname' ] )) ? $def [ 'vname' ] : (isset($def [ 'label' ]) ? $def['label'] : $def['name']) ;
						$this->availableFields [ $fieldName ] = array ( 'width' => '10' , 'label' => $label ) ;
					}
			}
		}
		return $this->availableFields;
	}       
	
	function getHistory ()
	{
		return $this->_history ;
	}

    
}

?>
