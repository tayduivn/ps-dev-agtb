<?php
/**
 * move blowfish dir from cache to custom
 */
class SugarUpgradeMoveBlowfish extends UpgradeScript
{
    public $order = 3000;
    public $type = self::UPGRADE_CUSTOM;

    public function run()
    {
        if(file_exists($this->cacheDir("blowfish")) && !file_exists("custom/blowfish")) {
           $this->log('Renaming cache/blowfish');
           rename($this->cacheDir("blowfish"), "custom/blowfish");
           $this->log('Renamed cache/blowfish to custom/blowfish');
        }
    }
}
