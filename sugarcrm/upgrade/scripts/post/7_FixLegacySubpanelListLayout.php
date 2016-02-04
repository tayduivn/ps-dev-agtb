<?php
/*
 * Your installation or use of this SugarCRM file is subject to the applicable
 * terms available at
 * http://support.sugarcrm.com/Resources/Master_Subscription_Agreements/.
 * If you do not agree to all of the applicable terms or do not have the
 * authority to bind the entity as an authorized representative, then do not
 * install or use this SugarCRM file.
 *
 * Copyright (C) SugarCRM Inc. All rights reserved.
 */

/**
 * Fix Subpanel-list.php
 *
 * affected files: modules/<custom module name>/clients/base/views/subpanel-list/subpanel-list.php
 */
class SugarUpgradeFixLegacySubpanelListLayout extends UpgradeScript
{
    public $order = 7910;
    public $type = self::UPGRADE_CUSTOM;

    public function run()
    {
        // Only run this when coming from a version lower than 7.7.0
        if (version_compare($this->from_version, '7.7.0', '>=')) {
            return;
        }

        $customModules = $this->getCustomModules();
        foreach ($customModules as $moduleName) {

            $moduleSubpanelListFile = "modules/$moduleName/clients/base/views/subpanel-list/subpanel-list.php";
            if (file_exists($moduleSubpanelListFile)) {
                $this->process($moduleSubpanelListFile);
            }
        }
    }

    /**
     * Fix subpanel-list layout defs
     * @param string $file
     */
    public function process($file)
    {
        if (is_dir($file)) {
            return;
        }

        $viewdefs = array();
        require $file;

        $toCopyFile = false;
        if (!empty($viewdefs)) {
            $module = key($viewdefs);
            $defs = $viewdefs[$module]['base']['view']['subpanel-list'];

            foreach ($defs['panels'][0]['fields'] as $fieldName => $details) {
                if (isset($details['name']) && !isset($details['link']) && $details['name'] === 'name') {

                    $details['link'] = true;
                    $defs['panels'][0]['fields'][$fieldName]=$details;
                    $toCopyFile = true;
                }
            }
        }

        if ($toCopyFile) {
            $strToFile = "<?php\n\n";
            $strToFile .= "/* This file was updated by 7_FixLegacySubpanelListLayout.php */\n";
            $strToFile .= '$module_name = \'' . $module . '\';' . "\n";
            $strToFile .= "\$viewdefs[\$module]['base']['view']['subpanel-list'] = " . var_export($defs, true) . ";\n";

            $this->upgrader->backupFile($file);
            sugar_file_put_contents_atomic($file, $strToFile);
        }
    }

    /**
     * Get SugarCRM instance None-PMSE custom modules
     *
     * @return array
     */
    protected function getCustomModules()
    {
        // Find all the custom classes we want to convert.
        // don't make $beanList as global
        $beanList = array();
        foreach(SugarAutoLoader::existing('include/modules_override.php', SugarAutoLoader::loadExtension("modules")) as $modExtFile) {
            include $modExtFile;
        }

        return array_keys($beanList);
    }
}
