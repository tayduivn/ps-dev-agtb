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
require_once('include/externalAPI/ExternalAPIFactory.php');
        
class ImportViewStep1 extends ImportView
{

    protected $pageTitleKey = 'LBL_STEP_1_TITLE';

 	/** 
     * @see SugarView::display()
     */
 	public function display()
    {
        global $mod_strings, $app_strings, $current_user;
        global $sugar_config;

        $this->ss->assign("MODULE_TITLE", $this->getModuleTitle(false));
        $this->ss->assign("DELETE_INLINE_PNG",  SugarThemeRegistry::current()->getImage('delete_inline','align="absmiddle" alt="'.$app_strings['LNK_DELETE'].'" border="0"'));
        $this->ss->assign("PUBLISH_INLINE_PNG",  SugarThemeRegistry::current()->getImage('publish_inline','align="absmiddle" alt="'.$mod_strings['LBL_PUBLISH'].'" border="0"'));
        $this->ss->assign("UNPUBLISH_INLINE_PNG",  SugarThemeRegistry::current()->getImage('unpublish_inline','align="absmiddle" alt="'.$mod_strings['LBL_UNPUBLISH'].'" border="0"'));
        $this->ss->assign("IMPORT_MODULE", $_REQUEST['import_module']);
        $this->ss->assign("JAVASCRIPT", $this->_getJS());


        $showModuleSelection = ($_REQUEST['import_module'] == 'Administration');
        $importableModulesOptions = array();
        $importablePersonModules = array();
        //If we are coming from the admin link, get the module list.
        if($showModuleSelection)
        {
            $tmpImportable = $this->getImportableModules();
            $importableModulesOptions = get_select_options_with_id($tmpImportable, '');
            $importablePersonModules = $this->getImportablePersonModulesJS();
            $this->ss->assign("IMPORT_MODULE", key($tmpImportable));
        }
        else
        {
            $this->instruction = 'LBL_SELECT_DS_INSTRUCTION';
            $this->ss->assign('INSTRUCTION', $this->getInstruction());
        }
        $this->ss->assign("PERSON_MODULE_LIST", json_encode($importablePersonModules));
        $this->ss->assign("showModuleSelection", $showModuleSelection);
        $this->ss->assign("IMPORTABLE_MODULES_OPTIONS", $importableModulesOptions);

        $this->ss->assign("EXTERNAL_SOURCES", $this->getAllImportableExternalEAPMs());
        $this->ss->assign("EXTERNAL_AUTHENTICATED_SOURCES", json_encode($this->getAuthenticatedImportableExternalEAPMs()) );
        $selectExternal = !empty($_REQUEST['application']) ? $_REQUEST['application'] : '';
        $this->ss->assign("selectExternalSource", $selectExternal);

        $content = $this->ss->fetch('modules/Import/tpls/step1.tpl');
        $this->ss->assign("CONTENT",$content);
        $this->ss->display('modules/Import/tpls/wizardWrapper.tpl');
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

    private function getImportablePersonModulesJS()
    {
        global $beanList;
        $results = array();
        foreach ($beanList as $moduleName => $beanName)
        {
            if( class_exists($beanName) )
            {
                $tmp = new $beanName();
                if( isset($tmp->importable) && $tmp->importable && ($tmp instanceof Person))
                    $results[$moduleName] = $moduleName;
            }
        }

        return $results;
    }

    private function getAllImportableExternalEAPMs()
    {
        ExternalAPIFactory::clearCache();
        return ExternalAPIFactory::getModuleDropDown('Import', TRUE, FALSE);
    }

    private function getAuthenticatedImportableExternalEAPMs()
    {
        return ExternalAPIFactory::getModuleDropDown('Import', FALSE, FALSE);
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
    var isCsvSource = document.getElementById('csv_source').checked;
    if( isCsvSource )
    {
        document.getElementById('importstep1').action.value = 'Step2';
        return true;
    }
    else
    {
        if(selectedExternalSource == '')
        {
            add_error_style('importstep1','external_source',"{$mod_strings['ERR_MISSING_REQUIRED_FIELDS']} {$mod_strings['LBL_EXTERNAL_SOURCE']}");
            return false;
        }

        document.getElementById('importstep1').action.value = 'ExtStep1';
        document.getElementById('importstep1').external_source.value = selectedExternalSource;
        return true;
    }
}


YAHOO.util.Event.onDOMReady(function(){

    var oButtonGroup = new YAHOO.widget.ButtonGroup("smtpButtonGroup");

    function toggleExternalSource(el)
    {
        var trEl = document.getElementById('external_sources_tr');
        var externalSourceBttns = oButtonGroup.getButtons();

        if(this.value == 'csv')
        {
            trEl.style.display = 'none';
            document.getElementById('gonext').disabled = false;
            document.getElementById('ext_source_sign_in_bttn').style.display = 'none';

            //Turn off ext source selection
            oButtonGroup.set("checkedButton", null, true);
            for(i=0;i<externalSourceBttns.length;i++)
            {
                externalSourceBttns[i].set("checked", true, true);
            }
        }
        else
        {
            trEl.style.display = '';
            document.getElementById('gonext').disabled = true;

            //Highlight the first selection by default
            if(externalSourceBttns.length >= 1)
            {
                selectedExternalSource = externalSourceBttns[0].get("value");
                externalSourceBttns[0].set("checked", true, true);
                isExtSourceValid(selectedExternalSource);
            }
        }
    }
    
    YAHOO.util.Event.addListener(['ext_source','csv_source'], "click", toggleExternalSource);

    function isExtSourceAuthenticated(source)
    {
        if( typeof(auth_sources[source]) != 'undefined')
            return true;
        else
            return false;
    }
    
    function isExtSourceValid(v)
    {
        if(v == '')
        {
            document.getElementById('ext_source_sign_in_bttn').style.display = 'none';
            return '';
        }
        if( !isExtSourceAuthenticated(v) )
        {
            document.getElementById('ext_source_sign_in_bttn').style.display = '';
            document.getElementById('gonext').disabled = true;
        }
        else
        {
            document.getElementById('ext_source_sign_in_bttn').style.display = 'none';
            document.getElementById('gonext').disabled = false;
        }
    }

    function openExtAuthWindow()
    {
        var import_module = document.getElementById('importstep1').import_module.value;
        var url = "index.php?module=EAPM&return_module=Import&action=EditView&application=" + selectedExternalSource + "&return_action=" + import_module;
        document.location = url;
    }

    function setImportModule()
    {
        var selectedModuleEl = document.getElementById('admin_import_module');
        if(!selectedModuleEl)
        {
            return;
        }

        //Check if the module selected by the admin is a person type module, if not hide
        //the external source.
        var selectedModule = selectedModuleEl.value;
        document.getElementById('importstep1').import_module.value = selectedModule;
        if( personModules[selectedModule] )
        {
            document.getElementById('ext_source_tr').style.display = '';
        }
        else
        {
            document.getElementById('ext_source_tr').style.display = 'none';
            document.getElementById('external_sources_tr').style.display = 'none';
            document.getElementById('csv_source').checked = true;
        }

    }
    YAHOO.util.Event.addListener('ext_source_sign_in_bttn', "click", openExtAuthWindow);
    YAHOO.util.Event.addListener('admin_import_module', "change", setImportModule);


    oButtonGroup.subscribe('checkedButtonChange', function(e)
    {
        selectedExternalSource = e.newValue.get('value');
        isExtSourceValid(selectedExternalSource);
    });

    function initExtSourceSelection()
    {
        var el1 = YAHOO.util.Dom.get('ext_source');
        if(selectedExternalSource == '')
            return;
            
        el1.checked = true;
        toggleExternalSource();
        isExtSourceValid(selectedExternalSource);
    }
    initExtSourceSelection();

    setImportModule();
});
-->
</script>

EOJAVASCRIPT;
    }
}

?>
