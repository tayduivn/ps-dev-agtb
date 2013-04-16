<?php
/**
 * Remove files that were scheduled to be deleted
 * Files are backed up to custom/backup
 */
class SugarUpgradeRemoveFiles extends UpgradeScript
{
    public $order = 9000;

    public $backup_dir = 'custom/backup';
    // ALL since some DB-only modules may request file deletions
    public $type = self::UPGRADE_ALL;

    public function run()
    {
        if(empty($this->state['files_to_delete'])) {
            return;
        }

    	$this->ensureDir($this->backup_dir);

	    foreach($this->state['files_to_delete'] as $file) {
	        $this->backup($file);
	        $this->log("Removing $file");
	        if(is_dir($file)) {
	            $this->removeDir($file);
	        } else {
	            @unlink($file);
	        }
	    }
    }

    /**
     * Backup the file
     * @param unknown_type $file
     */
    protected function backup($file)
    {
        $path = pathinfo($file, PATHINFO_DIRNAME);
        if(!empty($path)) {
            $this->ensureDir($this->backup_dir. '/' .  $path);
        }
        if(is_dir($file)) {
            $this->copyDir($file, $this->backup_dir . '/'. $file);
        } else {
            copy($file, $this->backup_dir. '/' . $file);
        }

    }
}
