<?php
/**
 * Files to delete for 7.0 - old SAML libs, now moved to vendor/
 */
class SugarUpgradeOldSamlLibs extends UpgradeScript
{
    public $order = 7000;
    public $version = '7.0.0';
    public $type = self::UPGRADE_CORE;

    public function run()
    {
        $this->fileToDelete('modules/Users/authentication/SAMLAuthenticate/lib');
    }
}
