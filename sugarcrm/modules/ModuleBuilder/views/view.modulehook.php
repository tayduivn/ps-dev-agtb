<?php
/*********************************************************************************
 * The contents of this file are subject to the SugarCRM Enterprise End User
 * License Agreement ("License") which can be viewed at
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
 * by SugarCRM are Copyright (C) 2004-2006 SugarCRM, Inc.; All Rights Reserved.
 ********************************************************************************/
require_once('modules/ModuleBuilder/MB/AjaxCompose.php');

class ViewModulehook extends SugarView
{
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

    function display()
	{
        $smarty = new Sugar_Smarty();
        global $mod_strings;
        $bak_mod_strings=$mod_strings;
        $smarty->assign('mod_strings', $mod_strings);

        $module_name = $_REQUEST['view_module'];
        if(! isset($_REQUEST['view_package']) || $_REQUEST['view_package'] == 'studio' || empty ( $_REQUEST [ 'view_package' ] ) ) {
            $this->module = new Stdclass;
            $this->module->name = $module_name;
            $lh = new LogicHook();
            $lh->scanHooksDir("custom/Extension/modules/$module_name/Ext/LogicHooks");
            $lh->scanHooksDir("custom/Extension/applicaion/Ext/LogicHooks");
            $this->module->hooks = $lh->getHooksList();
            $package = new stdClass;
            $package->name = '';
        } else {
            require_once('modules/ModuleBuilder/MB/ModuleBuilder.php');
            $mb = new ModuleBuilder();
            $mb->getPackage($_REQUEST['view_package']);
            $package = $mb->packages[$_REQUEST['view_package']];

            $package->getModule($module_name);
            $this->module = $package->modules[$module_name];
        }

        $smarty->assign('package', $package);
        $smarty->assign('module', $this->module);
        if(isset($_REQUEST['hook']) && isset($_REQUEST['type']) && isset($this->module->hooks[$_REQUEST['type']][$_REQUEST['hook']])) {
            $smarty->assign("hookdata", $this->module->hooks[$_REQUEST['type']][$_REQUEST['hook']]);
            $smarty->assign('type', $_REQUEST['type']);
            $smarty->assign('hook', $_REQUEST['hook']);
        } else {
            $smarty->assign("hookdata", array());
        }
        $smarty->assign("hook_types", $mod_strings["hook_types"]);

        $ajax = new AjaxCompose();
        $ajax->addCrumb($bak_mod_strings['LBL_MODULEBUILDER'], 'ModuleBuilder.main("mb")');
        $ajax->addCrumb($package->name,'ModuleBuilder.getContent("module=ModuleBuilder&action=package&package='.$package->name.'")');
        $ajax->addCrumb($module_name, 'ModuleBuilder.getContent("module=ModuleBuilder&action=module&view_package='.$package->name.'&view_module='. $module_name . '")');
        $ajax->addCrumb($bak_mod_strings['LBL_HOOKS'], '');
        $ajax->addSection('east', translate('LBL_SECTION_FIELDEDITOR','ModuleBuilder'),$smarty->fetch('modules/ModuleBuilder/tpls/MBModule/hook.tpl'));
        $_REQUEST['field'] = '';

        echo $ajax->getJavascript();
    }
}