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

class ViewPortalConfig extends SugarView
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
        $portalFields = array('appStatus'=>'offline', 'logoURL'=>
        '', 'maxQueryResult'=>'20', 'fieldsToDisplay'=>'5', 'maxSearchQueryResult'=>'3', 'defaultUser'=>'');
        $userList = get_user_array();
        $userList[''] = '';
        require_once("modules/MySettings/TabController.php");
        $controller = new TabController();
        $tabs = $controller->get_tabs_system();
        $disabledModulesFlag = false;
        $disabledModules = array();
        // TODO: maybe consolidate this with the portal module list in install/install_utils.php
        $maybeDisabledModules = array('Bugs','KBDocuments', 'Cases');
        foreach ($maybeDisabledModules as $moduleName) {
          if (in_array($moduleName, $tabs[1])) {
              $disabledModules[]=translate($moduleName);
              $disabledModulesFlag = true;
          }
        };

        $admin = new Administration();
       	$admin->retrieveSettings();

        $smarty = new Sugar_Smarty();
        $smarty->assign('disabledDisplayModulesList', $disabledModules);
        $smarty->assign('disabledDisplayModules', $disabledModulesFlag);
        foreach ($portalFields as $fieldName=>$fieldDefault) {
            if (isset($admin->settings['portal_'.$fieldName])) {
                $smarty->assign($fieldName, json_decode(html_entity_decode($admin->settings['portal_'.$fieldName])));
            } else {
                $smarty->assign($fieldName,$fieldDefault);
            }
        }
        $smarty->assign('userList', $userList);
        $smarty->assign('welcome', $GLOBALS['mod_strings']['LBL_SYNCP_WELCOME']);
        $smarty->assign('mod', $GLOBALS['mod_strings']);
        if (isset($_REQUEST['label']))
        {
            $smarty->assign('label',$_REQUEST['label']);
        }
        $options = (!empty($GLOBALS['system_config']->settings['system_portal_url'])) ? $GLOBALS['system_config']->settings['system_portal_url'] : 'https://';
        $smarty->assign('options',$options);
        $ajax = new AjaxCompose();
        $ajax->addCrumb(translate('LBL_SUGARPORTAL', 'ModuleBuilder'), 'ModuleBuilder.main("sugarportal")');
        $ajax->addCrumb(translate('LBL_SUGARPORTAL', 'ModuleBuilder'), 'ModuleBuilder.getContent("module=ModuleBuilder&action=portalconfig")');
        $ajax->addSection('center', translate('LBL_SUGARPORTAL', 'ModuleBuilder'), $smarty->fetch('modules/ModuleBuilder/tpls/portalconfig.tpl'));
		$GLOBALS['log']->debug($smarty->fetch('modules/ModuleBuilder/tpls/portalconfig.tpl'));
        echo $ajax->getJavascript();
	}
}
