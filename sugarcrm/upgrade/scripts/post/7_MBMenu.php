<?php
 if(!defined('sugarEntry') || !sugarEntry) die('Not A Valid Entry Point');
/*********************************************************************************
 * The contents of this file are subject to the SugarCRM Master Subscription
 * Agreement ("License") which can be viewed at
 * http://www.sugarcrm.com/crm/master-subscription-agreement
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
 * by SugarCRM are Copyright (C) 2004-2012 SugarCRM, Inc.; All Rights Reserved.
 ********************************************************************************/

/**
 * If MB module does not have menu, create one
 */
class SugarUpgradeMBMenu extends UpgradeScript
{
    public $order = 7200;

    /**
     * Add default menu for module
     * @param string $module
     */
    protected function addMenu($moduleName)
    {
        $menu = array();
        // Create default menu for the module
        $menu[] = array(
                'route' => "#$moduleName/create",
                'label' => 'LNK_NEW_RECORD',
                'acl_action' => 'create',
                'acl_module' => $moduleName,
                'icon' => 'icon-plus',
        );

        // Handle link to vCard
        if (is_subclass_of(BeanFactory::getBean($moduleName), 'Person')) {
            $vCardRoute = (in_array($moduleName, $GLOBALS['bwcModules']))
                ? '#bwc/index.php?' . http_build_query(array('module' => $moduleName, 'action' => 'ImportVCard'))
                : "#$moduleName/vcard-import";
            $menu[] = array(
                'route' => $vCardRoute,
                'label' => 'LNK_IMPORT_VCARD',
                'acl_action' => 'create',
                'acl_module' => $moduleName,
                'icon' => 'icon-plus',
            );
        }

        $menu[] = array(
                'route' => "#$moduleName",
                'label' => 'LNK_LIST',
                'acl_action' => 'list',
                'acl_module' => $moduleName,
                'icon' => 'icon-reorder',
        );
        $menu[] = array(
                'route' => '#bwc/index.php?' . http_build_query(
                        array(
                                'module' => 'Import',
                                'action' => 'Step1',
                                'import_module' => $moduleName,
                        )
                ),
                'label' => 'LNK_IMPORT_'.strtoupper($moduleName),
                'acl_action' => 'import',
                'acl_module' => $moduleName,
                'icon' => 'icon-upload',
        );
        $content = <<<END
<?php
/* Created by SugarUpgrader for module $moduleName */
\$viewdefs['$moduleName']['base']['menu']['header'] =
END;
        $content .= var_export($menu, true) . ";\n";
        $this->ensureDir("modules/$moduleName/clients/base/menus/header");
        $this->putFile("modules/$moduleName/clients/base/menus/header/header.php", $content);
        $this->log("Added default menu file for $moduleName");
    }

    public function run()
    {
        global $mod_strings;

        if (!empty($this->upgrader->state['MBModules'])) {
            foreach ($this->upgrader->state['MBModules'] as $moduleName) {
                if (!file_exists("modules/$moduleName")) {
                    continue;
                }
                if (!file_exists("modules/$moduleName/clients/base/menus/header/header.php")
                    && !file_exists("custom/modules/$moduleName/clients/base/menus/header/header.php")
                ) {
                    $this->addMenu($moduleName);
                }
            }
        }

        // Do it also for bwcModules since some of them may not have Menu.php and we need it
        // Also some non-MB modules marked as BWC in post scripts and should have valid menu as well.
        foreach ($GLOBALS['bwcModules'] as $moduleName) {
            if (!file_exists("modules/$moduleName")) {
                continue;
            }
            if (!file_exists("modules/$moduleName/clients/base/menus/header/header.php")
                && !file_exists("custom/modules/$moduleName/clients/base/menus/header/header.php")
            ) {
                //Check if this module explcitly doesn't have a menu. In that case don't add one now.
                if (file_exists("modules/$moduleName/Menu.php")) {
                    //Need to make sure the mod_strings match the requested module or Menus may fail
                    $curr_mod_strings = $mod_strings;
                    $mod_strings = return_module_language("en_us", $moduleName);
                    $module_menu = array();
                    include "modules/$moduleName/Menu.php";
                    $mod_strings = $curr_mod_strings;
                    if (empty($module_menu)) {
                        continue;
                    }
                }
                $this->addMenu($moduleName);
            }
        }
    }
}
