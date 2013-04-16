<?php
/**
 * Set up FTS when upgrading CE->PRO
 */
class SugarUpgradeFTS extends UpgradeScript
{
    public $order = 7000;
    public $type = self::UPGRADE_DB;

    public function run()
    {
        if(!($this->from_flavor == 'ce' && $this->toFlavor('pro'))) return;

        if($this->db->supports('fulltext') && $this->db->full_text_indexing_installed()) {
            $this->db->full_text_indexing_setup();
        }
    }
}
