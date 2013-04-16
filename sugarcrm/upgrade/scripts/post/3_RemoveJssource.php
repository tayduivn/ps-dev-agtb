<?php
/**
 * Schedule jssource directories for removal
 */
class SugarUpgradeRemoveJssource extends UpgradeScript
{
    public $order = 3000;
    public $type = self::UPGRADE_CORE;

    public function run()
    {
        $jssource_dirs = array('jssource/src_files/include/javascript/ext-2.0',
    					   'jssource/src_files/include/javascript/ext-1.1.1',
    					   'jssource/src_files/include/javascript/yui'
                          );
        foreach($jssource_dirs as $js_dir)
        {
	        if(file_exists($js_dir))
	        {
	           $this->log("Removing directory: $js_dir");
	           $this->removeDir($js_dir);
	           $this->log("Finished removing $js_dir");
	        }
        }
    }
}
