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

        
class ImportViewStep1 extends ImportView
{
 	
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
        $this->ss->assign("EXTERNAL_SOURCES_OPTIONS", get_select_options_with_id($this->getImportableExternalEAPMs(),'') );
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

    private function getImportableExternalEAPMs()
    {
        require_once('include/externalAPI/ExternalAPIFactory.php');

        return ExternalAPIFactory::getModuleDropDown('Import', FALSE, FALSE, 'eapm_import_list');
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

document.getElementById('gonext').onclick = function()
{
    clear_all_errors();
    var sourceSelected = false;
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
    }
    if ( !sourceSelected ) {
        add_error_style('importstep1','source\'][\'' + (document.getElementById('importstep1').source.length - 1) + '',"{$mod_strings['ERR_MISSING_REQUIRED_FIELDS']} {$mod_strings['LBL_WHAT_IS']}");
        isError = true;
    }
    return !isError;
}


YAHOO.util.Event.onDOMReady(function(){

    function toggleExternalSource(el)
    {
        var trEl = document.getElementById('external_sources_tr');
        var currentVisibility = trEl.style.display;
        var newVisibility = (currentVisibility == 'none') ? '' : 'none';
        trEl.style.display = newVisibility;
    }
    
    YAHOO.util.Event.addListener(['ext_source','csv_source'], "click", toggleExternalSource);

});
-->
</script>

EOJAVASCRIPT;
    }
}

?>
