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

use Sugarcrm\Sugarcrm\SearchEngine\AdminSettings;

/**
 * Upgrade script to update the FTS settings.
 */
class SugarUpgradeUpdateFTSSettings extends UpgradeScript
{
    public $order = 9605;
    public $type = self::UPGRADE_CUSTOM;

    /**
     * The AdminSetting class instance
     * @var object
     */
    protected $ftsAdmin;

    /**
     * {@inheritdoc}
     */
    public function run()
    {
        if (version_compare($this->from_version, '7.7', '<')) {
            $this->updateModuleList();
        }
    }

    /**
     * Update the modules' FTS enabled/disabled settings.
     */
    public function updateModuleList()
    {
        try {
            $this->ftsAdmin = new AdminSettings();
            list($enabled, $disabled) = $this->mergeModuleList();

            //save the settings to ext files
            if (isset($this->ftsAdmin)) {
                $this->ftsAdmin->saveFTSModuleListSettings($enabled, $disabled);
            }
        } catch (Exception $e) {
            $this->log("SugarUpgradeUpdateFTSSettings: updating FTS module list got exceptions!");
        }

    }

    /**
     * Merge the old and new settings for the modules.
     * @return array
     */
    protected function mergeModuleList()
    {
        $oldModules = $this->getOldModuleList();
        $newModules = $this->getNewModuleList();

        //the new module list is supposed to contain all the modules from the old list
        $extraModules = array_diff(array_keys($oldModules), array_keys($newModules));
        if (!empty($extraModules)) {
            $this->log("SugarUpgradeUpdateFTSSettings failure: extra modules from the old list found!");
            return array(array(), array());
        }

        $modules = array_merge($newModules, $oldModules);
        $enabled = array_keys($modules, true);
        sort($enabled);
        $disabled = array_keys($modules, false);
        sort($disabled);

        return array($enabled, $disabled);
    }

    /**
     * Get the old settings from Unified Search.
     * @return array
     */
    protected function getUsaModuleList()
    {
        require_once 'modules/Home/UnifiedSearchAdvanced.php';
        $usa = new UnifiedSearchAdvanced();
        $usaModules = $usa->getUnifiedSearchModulesDisplay();
        return $usaModules;
    }

    /**
     * Get the old module list for merge.
     * @return array
     */
    protected function getOldModuleList()
    {
        $usaModules = $this->getUsaModuleList();
        $modules = array();
        foreach ($usaModules as $module => $data) {
            //Knowledge Base module changed from "KBDocuments" to "KBContents"
            if ($module === "KBDocuments") {
                $name = "KBContents";
            } else {
                $name = $module;
            }
            $modules[$name] = $data['visible'];
        }
        return $modules;
    }

    /**
     * Get the new settings from Full Text Search.
     * @return array
     */
    protected function getFTSModuleList()
    {
        if (isset($this->ftsAdmin)) {
            return $this->ftsAdmin->getModules();
        }
        return array(array(), array());
    }

    /**
     * Get the new module list for merge.
     * @return array
     */
    protected function getNewModuleList()
    {
        list($enabled, $disabled) = $this->getFTSModuleList();

        //compose the full module list with
        //'enabled_module' => true,
        //'disabled_module' => false,
        return array_merge(array_fill_keys($enabled, true), array_fill_keys($disabled, false));
    }
}
