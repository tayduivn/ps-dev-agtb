<?php
/**
 * Searches through the installed relationships to find broken self referencing one-to-many relationships
 * (wrong field used in the subpanel, and the left link not marked as left)
 */
class SugarUpgradeUpgradeCustomRels extends UpgradeScript
{
    public $order = 5000;
    public $type = self::UPGRADE_CUSTOM;

    public function run()
    {
        global $modules_exempt_from_availability_check;
        $modules_exempt_from_availability_check = array();
        require_once('modules/Administration/upgrade_custom_relationships.php');
        upgrade_custom_relationships();
    }
}
