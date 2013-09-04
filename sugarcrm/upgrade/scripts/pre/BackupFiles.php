<?php
/**
 * Backup all files that are going to be overwritten to
 *  upload/upgrades/backup/UPGRADE_NAME-restore
 */
class SugarUpgradeBackupFiles extends UpgradeScript
{
    public $order = 100;
    public $type = self::UPGRADE_CORE;

    public function run()
    {
        if(empty($this->manifest['copy_files']['from_dir'])) {
            return;
        }

        $zip_from_dir = $this->context['temp_dir']."/".$this->manifest['copy_files']['from_dir'];

        $files = $this->findFiles($zip_from_dir);
        $this->log("**** Backup started");
        foreach($files as $file) {
            if(!$this->backupFile($file)) {
                $this->log("FAILED to back up $file");
            }
        }

        $this->log("**** Backup complete");
    }
}
