<?php
/**
 * Update entry in systems table
 */
class SugarUpgradeSetSystemID extends UpgradeScript
{
    public $order = 4000;
    public $type = self::UPGRADE_DB;

    public function run()
    {
        if(!($this->from_flavor == 'ce' && $this->toFlavor('pro'))) return;
        $system = new System();
        $system->system_key = $this->config['unique_key'];
        $system->user_id = '1';
        $system->last_connect_date = TimeDate::getInstance()->nowDb();
        $system_id = $system->retrieveNextKey(false, true);
        $this->db->query( "INSERT INTO config (category, name, value) VALUES ( 'system', 'system_id', '" . $system_id . "')" );
    }
}
