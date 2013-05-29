<?php
/**
 * add language pack config information to config.php
 */
class SugarUpgradeImportLang extends UpgradeScript
{
    public $order = 3000;
    public $type = self::UPGRADE_CUSTOM;

    public function run()
    {
        if(!$this->toFlavor('pro')) return;
        if(!is_file('install/lang.config.php')){
       	    return;
       	}
		$this->log('install/lang.config.php exists lets import the file/array insto sugar_config/config.php');
		include('install/lang.config.php');

		foreach($config['languages'] as $k=>$v){
			$this->upgrader->config['languages'][$k] = $v;
		}
    }
}
