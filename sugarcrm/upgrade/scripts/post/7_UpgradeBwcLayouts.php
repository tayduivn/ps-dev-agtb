<?php
if (!defined('sugarEntry') || !sugarEntry) {
    die('Not A Valid Entry Point');
}
/*
 * Your installation or use of this SugarCRM file is subject to the applicable
 * terms available at
 * http://support.sugarcrm.com/06_Customer_Center/10_Master_Subscription_Agreements/.
 * If you do not agree to all of the applicable terms or do not have the
 * authority to bind the entity as an authorized representative, then do not
 * install or use this SugarCRM file.
 *
 * Copyright (C) SugarCRM Inc. All rights reserved.
 */

require_once "modules/UpgradeWizard/SugarMerge/MergeUtils.php";

/**
 * Upgrade BWC layouts to Sidecar
 */
class SugarUpgradeUpgradeBwcLayouts extends UpgradeScript
{
    public $order = 7000;
    public $type = self::UPGRADE_CUSTOM;

    public function run()
    {
        if (empty($this->upgrader->state['bwc_modules'])) {
            $this->log('BWC modules are not registered by pre-upgrade script.');
            return;
        }

        $oldBwcModules = $this->upgrader->state['bwc_modules'];
        $newBwcModules = $this->getBwcModules();

        $modulesToUpgrade = array_diff($oldBwcModules, $newBwcModules);
        if (!$modulesToUpgrade) {
            $this->log('Nothing to upgrade. Exiting.');
        }

        $upgrader = new SidecarMetaDataUpgraderBwc($modulesToUpgrade);
        $upgrader->upgrade();
    }

    protected function getBwcModules()
    {
        $bwcModules = array();
        include 'include/modules.php';

        return $bwcModules;
    }
}

/**
 * Metadata upgrader which upgrades the metadata of only specified modules
 */
class SidecarMetaDataUpgraderBwc extends SidecarMetaDataUpgrader
{
    protected $modules;

    public function __construct(array $modules)
    {
        $this->modules = $modules;
    }

    public function getMBModules()
    {
        return $this->modules;
    }
}
