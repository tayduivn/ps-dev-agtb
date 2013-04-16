<?php
/**
 * Set up teams for PRO
 */
class SugarUpgradeRebuildTeams extends UpgradeScript
{
    public $order = 4000;
    public $type = self::UPGRADE_DB;

    public function run()
    {
        if(!($this->from_flavor == 'ce' && $this->toFlavor('pro'))) return;

        require_once('modules/Teams/Team.php');
        require_once('modules/Administration/RepairTeams.php');
        process_team_access(false, false,true,'1');
    }
}
