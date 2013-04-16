<?php
/**
 * Install FTS logic hook
 */
class SugarUpgradeFTSHook extends UpgradeScript
{
    public $order = 5000;
    public $type = self::UPGRADE_CUSTOM;

    public function run()
    {
        if(file_exists('Extension/application/Ext/LogicHooks/SugarFTSHooks.php')) return;

        $hook = array(1, 'fts', 'include/SugarSearchEngine/SugarSearchEngineQueueManager.php', 'SugarSearchEngineQueueManager', 'populateIndexQueue');
        check_logic_hook_file('application', 'after_save', $hook);
    }
}
