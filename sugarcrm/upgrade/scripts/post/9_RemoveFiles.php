<?php
/**
 * Remove files that were scheduled to be deleted
 * Files are backed up to custom/backup
 */
class SugarUpgradeRemoveFiles extends UpgradeScript
{
    public $order = 9000;

    // ALL since some DB-only modules may request file deletions
    public $type = self::UPGRADE_ALL;

    public function run()
    {
        if(empty($this->state['files_to_delete'])) {
            return;
        }

	    foreach($this->state['files_to_delete'] as $file) {
	        $this->backupFile($file);
	        $this->log("Removing $file");
	        if(is_dir($file)) {
	            $this->removeDir($file);
	        } else {
	            $this->unlink($file);
	        }
	    }
    }
}
