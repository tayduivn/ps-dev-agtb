<?php
if(!file_exists('modules/UpgradeWizard/SidecarUpdate/SidecarMetaDataUpgrader.php')) return;

require_once 'modules/UpgradeWizard/SidecarUpdate/SidecarMetaDataUpgrader.php';
/**
 * Upgrade sidecar portal metadata
 */
class SugarUpgradeUpdatePortalMobile extends UpgradeScript
{
    public $order = 7000;
    public $type = self::UPGRADE_CUSTOM;

    public function run()
    {
        if(!file_exists('modules/UpgradeWizard/SidecarUpdate/SidecarMetaDataUpgrader.php')) return;
        // TODO: fix uw_utils references in SidecarMetaDataUpgrader
        $smdUpgrader = new SidecarMetaDataUpgrader2($this);
        $smdUpgrader->upgrade();

        // Log failures if any
        $failures = $smdUpgrader->getFailures();
        if (!empty($failures)) {
            $this->log('Sidecar Upgrade: ' . count($failures) . ' metadata files failed to upgrade through the silent upgrader:');
            $this->log(print_r($failures, true));
        } else {
            $this->log('Sidecar Upgrade: Mobile/portal metadata upgrade ran with no failures:');
            $this->log($smdUpgrader->getCountOfFilesForUpgrade() . ' files were upgraded.');
        }
        $this->fileToDelete(SidecarMetaDataUpgrader::getFilesForRemoval());
    }
}

/**
 * Decorator class to override logging behavior of SidecarMetaDataUpgrader
 */
class SidecarMetaDataUpgrader2 extends SidecarMetaDataUpgrader
{
    public function __construct($upgrade)
    {
        $this->upgrade = $upgrade;
    }

    public function logUpgradeStatus($msg)
    {
        $this->upgrade->log($msg);
    }
}
