<?php
/**
 * Rebuild dashlets cache
 */
class SugarUpgradeRebuildDashlets extends UpgradeScript
{
    public $order = 3000;
    public $type = self::UPGRADE_CUSTOM;

    public function run()
    {
        if(is_file(sugar_cached('dashlets/dashlets.php'))) {
            unlink(sugar_cached('dashlets/dashlets.php'));
        }

        require_once('include/Dashlets/DashletCacheBuilder.php');
        $dc = new DashletCacheBuilder();
        $dc->buildCache();
    }
}
