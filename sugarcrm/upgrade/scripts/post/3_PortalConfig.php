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
        if(file_exists('portal2/config.js')) return;

        require_once('install/install_utils.php');
        $this->putFile('portal2/config.js', getPortalJSConfig());
    }
}
