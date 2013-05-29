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
        $merger = new SugarMerge($this->context['new_source_dir']);
        $merger->mergeAll();
        $this->log("**** Merge finished ");
    }
}