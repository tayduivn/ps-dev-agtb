<?php
/**
 * Recreate hierarchy search stored procedures
 */
class SugarUpgradeDBInstall extends UpgradeScript
{
    public $order = 7000;
    public $type = self::UPGRADE_DB;

    public function run()
    {
        if($this->db->supports('recursive_query')) {
            $this->db->preInstall();
        }
    }
}
