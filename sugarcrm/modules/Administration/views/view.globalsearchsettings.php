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

require_once 'modules/Administration/Administration.php';
require_once 'modules/Home/UnifiedSearchAdvanced.php';

/**
 *
 * Globalsearch settings page
 *
 */
class AdministrationViewGlobalsearchsettings extends SugarView
{
     /**
     * @see SugarView::_getModuleTitleParams()
     */
    protected function _getModuleTitleParams($browserTitle = false)
    {
        global $mod_strings;

        return array(
           "<a href='index.php?module=Administration&action=index'>".translate('LBL_MODULE_NAME', 'Administration')."</a>",
           $mod_strings['LBL_GLOBAL_SEARCH_SETTINGS']
           );
    }

    /**
     * @see SugarView::_getModuleTab()
     */
    protected function _getModuleTab()
    {
        return 'Administration';
    }

    /**
     * @see SugarView::display()
     */
    public function display()
    {
        global $mod_strings, $app_strings, $app_list_strings, $current_user;
        $sugarConfig = SugarConfig::getInstance();

        // Setup smarty template
        $sugar_smarty = new Sugar_Smarty();
        $sugar_smarty->assign('APP', $app_strings);
        $sugar_smarty->assign('MOD', $mod_strings);
        $sugar_smarty->assign('moduleTitle', $this->getModuleTitle(false));

        // Enabled/disabled modules list
        $usa = new UnifiedSearchAdvanced();
        $modules = $usa->retrieveEnabledAndDisabledModules();
        $sugar_smarty->assign('enabled_modules', json_encode($modules['enabled']));
        $sugar_smarty->assign('disabled_modules', json_encode($modules['disabled']));

        // List of available engines
        // TODO: make engines dynamic again
        $defaultEngine = 'Elastic';
        $sugar_smarty->assign("fts_type", get_select_options_with_id($app_list_strings['fts_type'], $defaultEngine));

        // Engine configuration, use defaults in case we cannot find one
        $engineConfig = $sugarConfig->get('full_text_engine.' . $defaultEngine, false);
        if (!$engineConfig) {
            $engineConfig = array('host' => '127.0.0.1', 'port' => '9200');
        }
        $sugar_smarty->assign("fts_host", $engineConfig['host']);
        $sugar_smarty->assign("fts_port", $engineConfig['port']);

        // Hide schedule button if no valid connection
        $showSchedButton = $this->isAvailable();
        $sugar_smarty->assign("showSchedButton", $showSchedButton);

        // Reindex scheduled button
        $justRequestedAScheduledIndex = !empty($_REQUEST['sched']) ? true : false;
        $sugar_smarty->assign('justRequestedAScheduledIndex', $justRequestedAScheduledIndex);

        // Hide FTS configuration
        $hide_fts_config = (bool) $sugarConfig->get('hide_full_text_engine_config', false);
        $sugar_smarty->assign("hide_fts_config", $hide_fts_config);

        echo $sugar_smarty->fetch(SugarAutoLoader::existingCustomOne('modules/Administration/templates/GlobalSearchSettings.tpl'));
    }

    /**
     * Check if engine is available
     * @return boolean
     */
    protected function isAvailable()
    {
        try {
            return SearchEngine::getInstance()->isAvailable(true);
        } catch (Exception $e) {
            return false;
        }
    }
}
