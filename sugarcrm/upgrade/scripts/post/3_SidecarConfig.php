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

        require_once('install/install_utils.php');
        $this->putFile('config.js', getSidecarJSConfig());
    }
}
