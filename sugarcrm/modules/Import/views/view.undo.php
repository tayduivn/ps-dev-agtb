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
 * $Id: view.undo.php 31561 2008-02-04 18:41:10Z jmertic $
 * Description: view handler for undo step of the import process
 * Portions created by SugarCRM are Copyright (C) SugarCRM, Inc.
 * All Rights Reserved.
 ********************************************************************************/

require_once('modules/Import/views/ImportView.php');
        
class ImportViewUndo extends ImportView 
{	

 	/** 
     * @see SugarView::display()
     */
 	public function display()
    {
        global $mod_strings, $current_user, $current_language;
        
        $this->ss->assign("IMPORT_MODULE", $_REQUEST['import_module']);
        // lookup this module's $mod_strings to get the correct module name
        $old_mod_strings = $mod_strings;
        $module_mod_strings = 
            return_module_language($current_language, $_REQUEST['import_module']);
        $this->ss->assign("MODULENAME",$module_mod_strings['LBL_MODULE_NAME']);
        // reset old ones afterwards
        $mod_strings = $old_mod_strings;
        
        $last_import = new UsersLastImport();
        $this->ss->assign('UNDO_SUCCESS',$last_import->undo($_REQUEST['import_module']));
        $this->ss->assign("JS", json_encode($this->_getJS()));
        $submitContent = "<table width='100%' cellpadding='0' cellspacing='0' border='0' style='margin-top: 10px;'><tr><td align='right'>";
        $submitContent .= "<input title=\"".$mod_strings['LBL_MODULE_NAME']."&nbsp;".$module_mod_strings['LBL_MODULE_NAME']."\" accessKey='' class='button' type='submit' name='button' id='importmore' name='importmore' value=\"".$mod_strings['LBL_MODULE_NAME']."&nbsp;".$module_mod_strings['LBL_MODULE_NAME']."\">";
        $submitContent .= "&nbsp;<input title=\"".$mod_strings['LBL_FINISHED'].$module_mod_strings['LBL_MODULE_NAME']."\" accessKey='' class='button primary' type='submit' name='finished' id='finished' value=\"".$mod_strings['LBL_IMPORT_COMPLETE']."\">";
    	$submitContent .= "</td></tr></table>";

        $content = $this->ss->fetch('modules/Import/tpls/undo.tpl');
        $this->ss->assign("CONTENT", json_encode($content));
        $this->ss->assign("SUBMITCONTENT", json_encode($submitContent));
        
        
        $this->ss->display('modules/Import/tpls/wizardWrapper.tpl');
    }
    
    /**
     * Returns JS used in this view
     */
    private function _getJS()
    {
        return <<<EOJAVASCRIPT

document.getElementById('finished').onclick = function(){
    document.getElementById('importundo').module.value = document.getElementById('importundo').import_module.value;
    document.getElementById('importundo').action.value = 'index';
    SUGAR.importWizard.closeDialog();
}

document.getElementById('importmore').onclick = function(){
    document.getElementById('importundo').action.value = 'Step1';
    
       	var success = function(data) {		
			eval(data.responseText);
			importWizardDialogDiv = document.getElementById('importWizardDialogDiv');
			submitDiv = document.getElementById('submitDiv');
			importWizardDialogDiv.innerHTML = response['html'];
			submitDiv.innerHTML = response['submitContent'];
			eval(response['script']);
		} 
        var formObject = document.getElementById('importundo');
		YAHOO.util.Connect.setForm(formObject);
		var cObj = YAHOO.util.Connect.asyncRequest('POST', "index.php", {success: success, failure: success});
		
}

EOJAVASCRIPT;
    }
}
