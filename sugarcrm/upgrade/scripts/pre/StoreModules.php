<?php
/**
  * Store old modules list so we could use it to compare to new modules list
  * and update display tabs, etc.
  */
class SugarUpgradeStoreModules extends UpgradeScript
{
    public $order = 200;
    // DB because DB scripts may need the data
    public $type = self::UPGRADE_DB;

    public function run()
    {
        include 'include/modules.php';
        $this->upgrader->state['old_modules'] = $moduleList;
    }
}