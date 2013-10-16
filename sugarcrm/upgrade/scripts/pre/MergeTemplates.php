<?php
/**
 * Merge viewdefs files between old and new code
 */
class SugarUpgradeMergeTemplates extends UpgradeScript
{
    public $order = 200;
    public $type = self::UPGRADE_CUSTOM;

    public function run()
    {
        if(empty($this->context['new_source_dir'])) {
            $this->log("**** Merge skipped - no new source dir");
            return;
        }
        $this->log("**** Merge started ");
        require_once('modules/UpgradeWizard/SugarMerge/SugarMerge.php');
        if (file_exists($this->context['new_source_dir'].'/modules/UpgradeWizard/SugarMerge/SugarMerge7.php')) {
            require_once($this->context['new_source_dir'].'/modules/UpgradeWizard/SugarMerge/SugarMerge7.php');
        } else {
            if (file_exists('modules/UpgradeWizard/SugarMerge/SugarMerge7.php')) {
                require_once('modules/UpgradeWizard/SugarMerge/SugarMerge7.php');
            } else {
                $this->error('SugarMerge7.php not found, this file is required for Sugar7 Upgrades', true);
            }
        }
        $merger = new SugarMerge7($this->context['new_source_dir']);
        $merger->mergeAll();
        $this->log("**** Merge finished ");
    }
}
