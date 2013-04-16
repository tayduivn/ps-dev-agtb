<?php
/**
 * Create default connectors for CE->PRO
 */
class SugarUpgradeConnectorDefaults extends UpgradeScript
{
    public $order = 4000;
    public $type = self::UPGRADE_DB;

    public function run()
    {
        if(!($this->from_flavor == 'ce' && $this->toFlavor('pro'))) return;

        include 'modules/Connectors/InstallDefaultConnectors.php';
    }
}
