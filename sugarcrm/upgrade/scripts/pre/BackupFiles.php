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

        $rest_dir = $this->config['upload_dir']."/upgrades/backup/".pathinfo($this->context['zip'], PATHINFO_FILENAME) . "-restore";
        $this->ensureDir($rest_dir);
        $this->cleanDir($rest_dir);
        $files = $this->findFiles($zip_from_dir);
        foreach($files as $file) {
            $this->log("Backing up $file");
            $this->ensureDir(dirname("$rest_dir/$file"));
            // backup only existing files
            if(!file_exists("{$this->context['source_dir']}/$file")) continue;
            if(!copy("{$this->context['source_dir']}/$file", "$rest_dir/$file")) {
                $this->log("FAILED to back up $file");
            }
        }

        $this->log("**** Backup complete");
    }
}
