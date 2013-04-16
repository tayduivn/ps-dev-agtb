<?php
/**
 * Create ACL actions for module Users
 */
class SugarUpgradeAddUserActions extends UpgradeScript
{
    public $order = 3000;
    public $type = self::UPGRADE_DB;

    public function run()
    {
        if(!$this->toFlavor('pro')) return;
        // add User field in Role
		include_once("modules/ACLActions/ACLAction.php");
		ACLAction::addActions('Users', 'module');

    }
}
