<?php
/**
 * Create config.js if it did not exist
 */
class SugarUpgradeSidecarConfig extends UpgradeScript
{
    public $order = 3000;
    public $type = self::UPGRADE_CUSTOM;

    public function run()
    {
        if(!$this->toFlavor('pro')) return;
        if(file_exists('config.js')) return;

        require_once 'ModuleInstall/ModuleInstaller.php';
        $this->putFile('config.js', ModuleInstaller::getJSConfig(ModuleInstaller::getBaseConfig()));
    }
}
