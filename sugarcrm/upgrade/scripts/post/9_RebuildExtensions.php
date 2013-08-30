<?php
/**
 * Register upgrade with the system
 */
class SugarUpgradeRebuildExtensions extends UpgradeScript
{
    public $order = 9500;

    public function run()
    {
        // we just finished with the layouts, we need to rebuild the extensions
        include "include/modules.php";
        require_once("modules/Administration/QuickRepairAndRebuild.php");
        $rac = new RepairAndClear('', '', false, false);
        $rac->rebuildExtensions();
    }
}
