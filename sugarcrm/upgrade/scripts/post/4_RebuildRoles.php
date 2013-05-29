<?php
/**
 * Create roles for CE->PRO
 */
class SugarUpgradeRebuildRoles extends UpgradeScript
{
    public $order = 4000;
    public $type = self::UPGRADE_DB;

    public function run()
    {
        global $ACLActions, $beanList, $beanFiles;

        if(!($this->from_flavor == 'ce' && $this->toFlavor('pro'))) return;

	    require_once('modules/ACLFields/ACLField.php');

	    include('modules/ACLActions/actiondefs.php');
        include('include/modules.php');
        include("modules/ACL/install_actions.php");
    }
}
