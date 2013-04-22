<?php
/**
 * Create portal config if does not exist
 */
class SugarUpgradePortalConfig extends UpgradeScript
{
    public $order = 3000;
    public $type = self::UPGRADE_CUSTOM;

    public function run()
    {
        if(!$this->toFlavor('ent')) return;

        require_once 'ModuleInstall/ModuleInstaller.php';
        $this->putFile('portal2/config.js', ModuleInstaller::getJSConfig(ModuleInstaller::getPortalConfig()));
    }
}
