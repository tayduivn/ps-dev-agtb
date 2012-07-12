<?php
if(!defined('sugarEntry') || !sugarEntry) die('Not A Valid Entry Point');

/*********************************************************************************
 *The contents of this file are subject to the SugarCRM Professional End User License Agreement
 *("License") which can be viewed at http://www.sugarcrm.com/EULA.
 *By installing or using this file, You have unconditionally agreed to the terms and conditions of the License, and You may
 *not use this file except in compliance with the License. Under the terms of the license, You
 *shall not, among other things: 1) sublicense, resell, rent, lease, redistribute, assign or
 *otherwise transfer Your rights to the Software, and 2) use the Software for timesharing or
 *service bureau purposes such as hosting the Software for commercial gain and/or for the benefit
 *of a third party.  Use of the Software may be subject to applicable fees and any use of the
 *Software without first paying applicable fees is strictly prohibited.  You do not have the
 *right to remove SugarCRM copyrights from the source code or user interface.
 * All copies of the Covered Code must include on each user interface screen:
 * (i) the "Powered by SugarCRM" logo and
 * (ii) the SugarCRM copyright notice
 * in the same form as they appear in the distribution.  See full license for requirements.
 *Your Warranty, Limitations of liability and Indemnity are expressly stated in the License.  Please refer
 *to the License for the specific language governing these rights and limitations under the License.
 *Portions created by SugarCRM are Copyright (C) 2004 SugarCRM, Inc.; All Rights Reserved.
 ********************************************************************************/

//FILE SUGARCRM flav=ent ONLY
require_once('modules/ModuleBuilder/MB/AjaxCompose.php');

class ViewPortalTheme extends SugarView
{
	function ViewPortalSync()
	{
	    $GLOBALS['log']->debug('ViewPortalSync constructor');
	    parent::SugarView();
	}

	/**
	 * @see SugarView::_getModuleTitleParams()
	 */
	protected function _getModuleTitleParams($browserTitle = false)
	{
	    global $mod_strings;
	    
    	return array(
    	   translate('LBL_MODULE_NAME','Administration'),
    	   ModuleBuilderController::getModuleTitle(),
    	   );
    }

	// DO NOT REMOVE - overrides parent ViewEdit preDisplay() which attempts to load a bean for a non-existent module
	function preDisplay() 
	{
	}

    /**
     * This function loads portal config vars from db and sets them for the view
     * @see SugarView::display() for more info
   	 */
	function display() 
	{
        global $current_user;

        $smarty = new Sugar_Smarty();
        $smarty->assign('mod', $GLOBALS['mod_strings']);
        $smarty->assign("token", $_REQUEST[session_name()]);
        $smarty->assign("siteURL", $GLOBALS['sugar_config']['site_url']);
        $ajax = new AjaxCompose();
        $ajax->addCrumb(translate('LBL_SUGARPORTAL', 'ModuleBuilder'), 'ModuleBuilder.main("sugarportal")');
        $ajax->addCrumb(translate('LBL_SUGARPORTAL', 'ModuleBuilder'), 'ModuleBuilder.getContent("module=ModuleBuilder&action=portaltheme")');
        $ajax->addSection('center', translate('LBL_SUGARPORTAL', 'ModuleBuilder'), $smarty->fetch('modules/ModuleBuilder/tpls/portaltheme.tpl'));
        echo $ajax->getJavascript();
	}
}
