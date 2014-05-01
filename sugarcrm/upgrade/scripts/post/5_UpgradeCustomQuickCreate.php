<?php
/**
 * By installing or using this file, you are confirming on behalf of the entity
 * subscribed to the SugarCRM Inc. product ("Company") that Company is bound by
 * the SugarCRM Inc. Master Subscription Agreement ("MSA"), which is viewable at:
 * http://www.sugarcrm.com/master-subscription-agreement
 *
 * If Company is not bound by the MSA, then by installing or using this file
 * you are agreeing unconditionally that Company will be bound by the MSA and
 * certifying that you have authority to bind Company accordingly.
 *
 * Copyright (C) 2004-2014 SugarCRM Inc. All rights reserved.
 */

/**
 * Adds default order for visible modules.
 */
class SugarUpgradeUpgradeCustomQuickCreate extends UpgradeScript
{
    public $order = 5000;
    public $type = self::UPGRADE_CUSTOM;
    public $version = '7.2';

    public function run()
    {
        // Only run when coming from a version lower than 7.2.
        if (version_compare($this->from_version, '7.2', '>=')) {
            return;
        }

        global $moduleList;
        $enabledModules = array();

        foreach ($moduleList as $module) {

            $quickCreateFile = "modules/$module/clients/base/menus/quickcreate/quickcreate.php";
            $customQuickCreateFile = "custom/$quickCreateFile";

            if (!file_exists($quickCreateFile) || !file_exists($customQuickCreateFile)) {
                continue;
            }
            require $customQuickCreateFile;
            $customMeta = $viewdefs[$module]['base']['menu']['quickcreate'];

            if (!$customMeta['visible'] || isset($customMeta['order'])) {
                continue;
            }
            require $quickCreateFile;
            $defaultMeta = $viewdefs[$module]['base']['menu']['quickcreate'];

            // -1 is default value for non-ordered modules.
            // See ViewConfigureshortcutbar::getQuickCreateModules();
            $customMeta['order'] = isset($defaultMeta['order']) ? $defaultMeta['order'] : -1;
            write_array_to_file(
                "viewdefs['$module']['base']['menu']['quickcreate']",
                $customMeta,
                $customQuickCreateFile
            );
        }

    }
}
