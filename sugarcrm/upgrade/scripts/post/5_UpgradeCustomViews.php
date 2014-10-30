<?php
/*
 * Your installation or use of this SugarCRM file is subject to the applicable
 * terms available at
 * http://support.sugarcrm.com/06_Customer_Center/10_Master_Subscription_Agreements/.
 * If you do not agree to all of the applicable terms or do not have the
 * authority to bind the entity as an authorized representative, then do not
 * install or use this SugarCRM file.
 *
 * Copyright (C) SugarCRM Inc. All rights reserved.
 */

class SugarUpgradeUpgradeCustomViews extends UpgradeScript
{
    public $order = 5000;
    public $type = self::UPGRADE_CUSTOM;
    public $version = '7.2';

    public function run()
    {
        // Only run when coming from a version lower than 7.2.
        if (version_compare($this->from_version, '7.2', '<')) {
            $this->fixQuickCreateOrder();
        }

        // Only run when coming from a version lower than 7.6.
        if (version_compare($this->from_version, '7.6', '<')) {
            $this->addStickyResizableColumnsFlag('recordlist');
            $this->addStickyResizableColumnsFlag('history-summary');
        }
    }

    /**
     * Fixes quickcreate modules order to be the same as the Enabled Modules
     * list.
     */
    private function fixQuickCreateOrder() {

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

    /**
     * Adds missing `sticky_resizable_columns` flag on custom list views.
     *
     * @param string $viewName The name of the view.
     */
    private function addStickyResizableColumnsFlag($viewName) {
        $file = 'clients/base/' . $viewName . '/' . $viewName .'.php';
        $customFile = 'custom/' . $file;
        if (!file_exists($file) || !file_exists($customFile)) {
            return;
        }

        require $customFile;
        $customMeta = $viewdefs['base']['view'][$viewName];
        if (isset($customMeta['sticky_resizable_columns'])) {
            return;
        }

        require $file;
        $defaultMeta = $viewdefs['base']['view'][$viewName];
        if (!isset($defaultMeta['sticky_resizable_columns'])) {
            return;
        }

        $customMeta['sticky_resizable_columns'] = $defaultMeta['sticky_resizable_columns'];
        write_array_to_file(
            "viewdefs['base']['view']['$viewName']",
            $customMeta,
            $customFile
        );
    }
}
