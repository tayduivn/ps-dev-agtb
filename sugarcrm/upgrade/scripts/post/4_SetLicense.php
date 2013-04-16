<?php
/**
 * Update license for CE->PRO upgrade
 */
class SugarUpgradeSetLicense extends UpgradeScript
{
    public $order = 4000;
    public $type = self::UPGRADE_DB;

    public function run()
    {
        if(!($this->from_flavor == 'ce' && $this->toFlavor('pro'))) return;
        $admin = new Administration();
		$category = 'license';
		$admin->saveSetting($category, 'users', 0);
		foreach(array('num_lic_oc','key','expire_date') as $k){
			$admin->saveSetting($category, $k, '');
		}
    }
}
