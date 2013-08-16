<?php
/*
 * By installing or using this file, you are confirming on behalf of the entity
 * subscribed to the SugarCRM Inc. product ("Company") that Company is bound by
 * the SugarCRM Inc. Master Subscription Agreement (“MSA”), which is viewable at:
 * http://www.sugarcrm.com/master-subscription-agreement
 *
 * If Company is not bound by the MSA, then by installing or using this file
 * you are agreeing unconditionally that Company will be bound by the MSA and
 * certifying that you have authority to bind Company accordingly.
 *
 * Copyright  2004-2013 SugarCRM Inc.  All rights reserved.
 */

require_once 'modules/UpgradeWizard/SidecarUpdate/SidecarAbstractMetaDataUpgrader.php';
require_once 'include/MetaDataManager/MetaDataConverter.php';

class SidecarMenuMetaDataUpgrader extends SidecarAbstractMetaDataUpgrader
{
    protected $curModStrings;
    protected $curAppStrings;
    protected $curUser;

    /**
     *  Converts a Menu.php to a header.php
     */
    public function convertLegacyViewDefsToSidecar()
    {
        global $moduleList;
        global $current_language;

        $mc = new MetaDataConverter();

        foreach ($moduleList as $module) {

            $this->logUpgradeStatus('Converting ' . ' menu defs for ' . $this->module);
            // needed for Legacy Menus
            $mod_strings = return_module_language($current_language, $module);

            foreach (glob("custom/Extension/modules/{$module}/Ext/Menus/*.php", GLOB_NOSORT) as $file) {
                @include $file;
                if (empty($module_menu)) {
                    continue;
                }
                $newExtLocation = "custom/Extension/modules/{$module}/Ext/clients/base/menus/header/";

                if (!is_dir($newExtLocation)) {
                    sugar_mkdir($newExtLocation, null, true);
                }

                $newMenu = $mc->fromLegacyMenu($module, $module_menu, true);

                write_array_to_file($newMenu['name'], $newMenu['data'], $newExtLocation . "/" . basename($file));

                unset($module_menu);
            }

            $legacyCustomMenu = "custom/modules/{$module}/Menu.php";
            @include $legacyCustomMenu;

            if (empty($module_menu)) {
                continue;
            }

            $newMenuLocation = "custom/modules/{$module}/clients/base/menus/header/header.php";
            if (!is_dir(dirname($newMenuLocation))) {
                sugar_mkdir(dirname($newMenuLocation), null, true);
            }

            $newMenu = $mc->fromLegacyMenu($module, $module_menu);

            write_array_to_file($newMenu['name'], $newMenu['data'], $newMenuLocation);

            unset($module_menu);
        }
    }
}
