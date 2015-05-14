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

use Sugarcrm\Sugarcrm\SearchEngine\SearchEngine;
use Sugarcrm\Sugarcrm\SearchEngine\Engine\Elastic;

/**
 *
 * This module gets and saves data for the FTS settings admin page.
 *
 */
class FullTextSearchSettingsAdmin
{
    /**
     * The name of the ext file
     * @var string
     */
    const EXT_FILE_NAME = 'full_text_search_admin.php';

    /**
     * Search the list of enabled and disabled modules
     * @return array
     */
    public function getModuleList()
    {
        $engine = $this->getSearchEngine();

        $list = array();
        if (!$engine->isAvailable(true)) {
            return $list;
        }

        //Get the enabled list from MetaDataHelper
        $enabled = $engine->getMetaDataHelper()->getAllEnabledModules();
        sort($enabled);
        $list['enabled_modules'] = $enabled;

        //Get the full list
        $mdm = \MetaDataManager::getManager();
        //$modules = array_keys($mdm->getModuleList());
        $modules = $mdm->getTabList();

        //Subtract the enabled list for the disabled
        $disabled = array_diff($modules, $enabled);
        sort($disabled);
        $list['disabled_modules'] = $disabled;

        return $list;
    }

     /**
     * Get SearchEngine
     * @return Elastic
     */
    protected function getSearchEngine()
    {
        $searchEngine = SearchEngine::getInstance()->getEngine();
        return $searchEngine;
    }

    /**
     * Save the modules to the extension files.
     * @param array $enabledModules the list of enabled modules
     * @param array $disabledModules the list of disabled modules
     */
    public function saveFTSModuleListSettings($enabledModules, $disabledModules)
    {
        $this->writeFTSSettingsToModules($enabledModules, true);
        $this->writeFTSSettingsToModules($disabledModules, false);

        $modules = array_merge($enabledModules, $disabledModules);
        include_once 'modules/Administration/QuickRepairAndRebuild.php';
        $repair = new RepairAndClear();
        $repair->repairAndClearAll(array('rebuildExtensions'), $modules, true, false);
    }

    /**
     * Write FTS settings for a list of modules
     * @param array $modules the list of modules
     * @param boolean $isEnabled the module is enabled or not
     */
    public function writeFTSSettingsToModules($modules, $isEnabled)
    {
        foreach ($modules as $module) {
            $this->writeFTSToVardefFile($module, $isEnabled);
        }
    }

    /**
     * Write the FTS setting to a module's extension file.
     * @param string $module the name of the module
     * @param boolean $isEnabled the module is enabled or not
     * @return bool
     */
    public function writeFTSToVardefFile($module, $isEnabled)
    {
        if (empty($module)) {
            return false;
        }

        //compose the content to write
        $moduleName = BeanFactory::getObjectName($module);
        $out =  "<?php\n // created: " . date('Y-m-d H:i:s') . "\n";
        $out .= override_value_to_string_recursive(array($moduleName, "full_text_search"), "dictionary", $isEnabled);
        $out .= "\n";

        //write to the file
        $file = "custom/Extension/modules/" . $module . "/Ext/Vardefs/" . self::EXT_FILE_NAME;
        if ($fh = @sugar_fopen($file, 'w')) {
            fputs($fh, $out);
            fclose($fh);
            return true;
        } else {
            return false;
        }
    }
}
