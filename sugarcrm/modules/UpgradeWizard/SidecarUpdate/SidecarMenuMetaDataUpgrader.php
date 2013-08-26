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

class SidecarMenuMetaDataUpgrader extends SidecarAbstractMetaDataUpgrader
{
    protected $curModStrings;
    protected $curAppStrings;
    protected $filesToDelete = array();

    /**
     *  Converts a Menu.php to a header.php
     */
    public function convertLegacyViewDefsToSidecar2()
    {
        global $current_language;

        foreach ($GLOBALS['moduleList'] as $module) {

            $this->logUpgradeStatus('Converting menu defs for ' . $this->module);
            // needed for Legacy Menus
            $GLOBALS['mod_strings'] = return_module_language($current_language, $module);

            // reset ACLs so that ACL checks in menu files won't cause any mess
            SugarACL::setACL($module, array(new SidecarMenuMetaDataUpgraderACL()));

            foreach (glob("custom/Extension/modules/{$module}/Ext/Menus/*.php", GLOB_NOSORT) as $file) {
                $module_menu = null;
                include $file;
                if (empty($module_menu)) {
                    continue;
                }
                $newExtLocation = "custom/Extension/modules/{$module}/Ext/clients/base/menus/header/";

                if (!is_dir($newExtLocation)) {
                    sugar_mkdir($newExtLocation, null, true);
                }

                $newMenu = $this->metaDataConverter->fromLegacyMenu($module, $module_menu, true);
                if(empty($newMenu['data'])) {
                    continue;
                }
                $content = "<?php \n";
                foreach($newMenu['data'] as $menuItem) {
                    $content .= "\${$newMenu['name']}[] = ".var_export($menuItem, true).";\n";
                }

                sugar_file_put_contents($newExtLocation . "/" . basename($file), $content);
                $this->filesToDelete[] = $file;
            }

            $module_menu = null;
            $legacyCustomMenu = "custom/modules/{$module}/Menu.php";
            if(file_exists($legacyCustomMenu)) {
                include $legacyCustomMenu;
            } else {
                continue;
            }

            if (empty($module_menu)) {
                continue;
            }

            $newMenuLocation = "custom/modules/{$module}/clients/base/menus/header/header.php";
            sugar_mkdir(dirname($newMenuLocation), null, true);

            $newMenu = $this->metaDataConverter->fromLegacyMenu($module, $module_menu);

            write_array_to_file($newMenu['name'], $newMenu['data'], $newMenuLocation);
            $this->filesToDelete[] = $legacyCustomMenu;

            unset($module_menu);
            SugarACL::resetACLs($module);
        }
    }

    /**
     * Get files to be removed
     * @return array
     */
    public function getFilesForRemoval()
    {
        return $this->filesToDelete;
    }
}

/**
 * This is a mock ACL so that Menu files that have ACLs won't do weird things
 */
class SidecarMenuMetaDataUpgraderACL extends SugarACLStrategy
{
    public function checkAccess($module, $action, $context)
    {
        return true;
    }
}
