<?php
/**
 * Set up PDF manager templates
 */
class SugarUpgradePDFManager extends UpgradeScript
{
    public $order = 7000;
    public $type = self::UPGRADE_DB;
    public $version = '6.6.0';

    public function run()
    {
        if(!$this->toFlavor('pro')) return;
        if(version_compare($this->from_version, "6.6.0", '<')) {
            // starting with 6.6.0, PDF manager templates are installed
            include 'install/seed_data/PdfManager_SeedData.php';
        }
    }
}
