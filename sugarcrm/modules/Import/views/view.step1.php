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
require_once('include/MVC/View/SugarView.php');

        
class ImportViewStep1 extends SugarView 
{	
 	/**
     * @see SugarView::getMenu()
     */
    public function getMenu(
        $module = null
        )
    {
        global $mod_strings, $current_language;
        
        if ( empty($module) )
            $module = $_REQUEST['import_module'];
        
        $old_mod_strings = $mod_strings;
        $mod_strings = return_module_language($current_language, $module);
        $returnMenu = parent::getMenu($module);
        $mod_strings = $old_mod_strings;
        
        return $returnMenu;
    }
    
 	/**
     * @see SugarView::_getModuleTab()
     */
 	protected function _getModuleTab()
    {
        global $app_list_strings, $moduleTabMap;
        
 		// Need to figure out what tab this module belongs to, most modules have their own tabs, but there are exceptions.
        if ( !empty($_REQUEST['module_tab']) )
            return $_REQUEST['module_tab'];
        elseif ( isset($moduleTabMap[$_REQUEST['import_module']]) )
            return $moduleTabMap[$_REQUEST['import_module']];
        // Default anonymous pages to be under Home
        elseif ( !isset($app_list_strings['moduleList'][$_REQUEST['import_module']]) )
            return 'Home';
        else
            return $_REQUEST['import_module'];
 	}
 	
 	/**
	 * @see SugarView::_getModuleTitleParams()
	 */
	protected function _getModuleTitleParams($browserTitle = false)
	{
	    global $mod_strings, $app_list_strings;
	    
	    $iconPath = $this->getModuleTitleIconPath($this->module);
	    $returnArray = array();
	    if (!empty($iconPath) && !$browserTitle) {
	        $returnArray[] = "<a href='index.php?module={$_REQUEST['import_module']}&action=index'><img src='{$iconPath}' alt='{$app_list_strings['moduleList'][$_REQUEST['import_module']]}' title='{$app_list_strings['moduleList'][$_REQUEST['import_module']]}' align='absmiddle'></a>";
    	}
    	else {
    	    $returnArray[] = $app_list_strings['moduleList'][$_REQUEST['import_module']];
    	}
	    $returnArray[] = "<a href='index.php?module=Import&action=Step1&import_module={$_REQUEST['import_module']}'>".$mod_strings['LBL_MODULE_NAME']."</a>";
	    $returnArray[] = $mod_strings['LBL_STEP_1_TITLE'];
    	
	    return $returnArray;
    }
    
 	/** 
     * @see SugarView::display()
     */
 	public function display()
    {
        global $mod_strings, $app_strings, $current_user;
        global $sugar_config;

        $this->ss->assign("MODULE_TITLE", $this->getModuleTitle());
        $this->ss->assign("DELETE_INLINE_PNG",  SugarThemeRegistry::current()->getImage('delete_inline','align="absmiddle" alt="'.$app_strings['LNK_DELETE'].'" border="0"'));
        $this->ss->assign("PUBLISH_INLINE_PNG",  SugarThemeRegistry::current()->getImage('publish_inline','align="absmiddle" alt="'.$mod_strings['LBL_PUBLISH'].'" border="0"'));
        $this->ss->assign("UNPUBLISH_INLINE_PNG",  SugarThemeRegistry::current()->getImage('unpublish_inline','align="absmiddle" alt="'.$mod_strings['LBL_UNPUBLISH'].'" border="0"'));
        $this->ss->assign("IMPORT_MODULE", $_REQUEST['import_module']);
        $this->ss->assign("JAVASCRIPT", $this->_getJS());

        $showModuleSelection = ($_REQUEST['import_module'] == 'Administration');
        $importableModulesOptions = array();
        if($showModuleSelection)
        {
            $importableModulesOptions = get_select_options_with_id($this->getImportableModules(), '');
        }
        $this->ss->assign("showModuleSelection", $showModuleSelection);
        $this->ss->assign("IMPORTABLE_MODULES_OPTIONS", $importableModulesOptions);

        // handle publishing and deleting import maps
        if (isset($_REQUEST['delete_map_id'])) {
            $import_map = new ImportMap();
            $import_map->mark_deleted($_REQUEST['delete_map_id']);
        }

        if (isset($_REQUEST['publish']) ) {
            $import_map = new ImportMap();
            $result = 0;

            $import_map = $import_map->retrieve($_REQUEST['import_map_id'], false);

            if ($_REQUEST['publish'] == 'yes') {
                $result = $import_map->mark_published($current_user->id,true);
                if (!$result) {
                    $this->ss->assign("ERROR",$mod_strings['LBL_ERROR_UNABLE_TO_PUBLISH']);
                }
            }
            elseif ( $_REQUEST['publish'] == 'no') {
                // if you don't own this importmap, you do now!
                // unless you have a map by the same name
                $result = $import_map->mark_published($current_user->id,false);
                if (!$result) {
                    $this->ss->assign("ERROR",$mod_strings['LBL_ERROR_UNABLE_TO_UNPUBLISH']);
                }
            }

        }

        // show any custom mappings
        if (sugar_is_dir('custom/modules/Import') && $dir = opendir('custom/modules/Import'))
        {
            while (($file = readdir($dir)) !== false)
            {
                if (sugar_is_file("custom/modules/Import/{$file}") && strpos($file,".php") !== false)
                {
	                require_once("custom/modules/Import/{$file}");
	                $classname = str_replace('.php','',$file);
	                $mappingClass = new $classname;
	                $custom_mappings[] = $mappingClass->name;
                }
            }
        }


        // get user defined import maps
        $this->ss->assign('is_admin',is_admin($current_user));
        $import_map_seed = new ImportMap();
        $custom_imports_arr = $import_map_seed->retrieve_all_by_string_fields(
            array(
                'assigned_user_id' => $current_user->id,
                'is_published'     => 'no',
                'module'           => $_REQUEST['import_module'],
                )
            );

        if ( count($custom_imports_arr) ) {
            $custom = array();
            foreach ( $custom_imports_arr as $import) {
                $custom[] = array(
                    "IMPORT_NAME" => $import->name,
                    "IMPORT_ID"   => $import->id,
                    );
            }
            $this->ss->assign('custom_imports',$custom);
        }

        // get globally defined import maps
        $published_imports_arr = $import_map_seed->retrieve_all_by_string_fields(
            array(
                'is_published' => 'yes',
                'module'       => $_REQUEST['import_module'],
                )
            );

        if ( count($published_imports_arr) ) {
            $published = array();
            foreach ( $published_imports_arr as $import) {
                $published[] = array(
                    "IMPORT_NAME" => $import->name,
                    "IMPORT_ID"   => $import->id,
                    );
            }
            $this->ss->assign('published_imports',$published);
        }

        $this->ss->display('modules/Import/tpls/step1.tpl');
    }

    private function getImportableModules()
    {
        global $beanList;
        $importableModules = array();
        foreach ($beanList as $moduleName => $beanName)
        {
            if( class_exists($beanName) )
            {
                $tmp = new $beanName();
                if( isset($tmp->importable) && $tmp->importable )
                    $importableModules[$moduleName] = $moduleName;
            }
        }

        asort($importableModules);
        return $importableModules;
    }
    /**
     * Returns JS used in this view
     */
    private function _getJS()
    {
        global $mod_strings;
        
        return <<<EOJAVASCRIPT
<script type="text/javascript">
<!--
document.getElementById('custom_enclosure').onchange = function()
{
    document.getElementById('importstep1').custom_enclosure_other.style.display = ( this.value == 'other' ? '' : 'none' );
}

document.getElementById('gonext').onclick = function()
{
    clear_all_errors();
    var sourceSelected = false;
    var typeSelected = false;
    var isError = false;
    var inputs = document.getElementsByTagName('input');
    for (var i = 0; i < inputs.length; ++i ){ 
        if ( !sourceSelected && inputs[i].name == 'source' ){
            if (inputs[i].checked) {
                sourceSelected = true;
                if ( inputs[i].value == 'other' && document.getElementById('importstep1').custom_delimiter.value == '' ) {
                    add_error_style('importstep1','custom_delimiter',"{$mod_strings['ERR_MISSING_REQUIRED_FIELDS']} {$mod_strings['LBL_CUSTOM_DELIMITER']}");
                    isError = true;
                }
            }
        }
        if ( !typeSelected && inputs[i].name == 'type' ){
            if (inputs[i].checked) {
                typeSelected = true;
            }
        }
    }
    if ( !sourceSelected ) {
        add_error_style('importstep1','source\'][\'' + (document.getElementById('importstep1').source.length - 1) + '',"{$mod_strings['ERR_MISSING_REQUIRED_FIELDS']} {$mod_strings['LBL_WHAT_IS']}");
        isError = true;
    }
    if ( !typeSelected ) {
        add_error_style('importstep1','type\'][\'1',"{$mod_strings['ERR_MISSING_REQUIRED_FIELDS']} {$mod_strings['LBL_IMPORT_TYPE']}");
        isError = true;
    }
    return !isError;
}

YAHOO.util.Event.onDOMReady(function()
{ 
    var inputs = document.getElementsByTagName('input');
    for (var i = 0; i < inputs.length; ++i ){ 
        if (inputs[i].name == 'source' ) {
            inputs[i].onclick = function() 
            {
                parentRow = this.parentNode.parentNode;
                switch(this.value) {
                case 'other':
                    enclosureRow = document.getElementById('customEnclosure').parentNode.removeChild(document.getElementById('customEnclosure'));
                    parentRow.parentNode.insertBefore(enclosureRow, document.getElementById('customDelimiter').nextSibling);
                    document.getElementById('customDelimiter').style.display = '';
                    document.getElementById('customEnclosure').style.display = '';
                    break;
                case 'tab': case 'csv':
                    enclosureRow = document.getElementById('customEnclosure').parentNode.removeChild(document.getElementById('customEnclosure'));
                    parentRow.parentNode.insertBefore(enclosureRow, parentRow.nextSibling);
                    document.getElementById('customDelimiter').style.display = 'none';
                    document.getElementById('customEnclosure').style.display = '';
                    break;
                default:
                    document.getElementById('customDelimiter').style.display = 'none';
                    document.getElementById('customEnclosure').style.display = 'none';
                }
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
