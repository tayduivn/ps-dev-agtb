<?php
/**
 * Rebuild JS language caches
 */
class SugarUpgradeRebuildJS extends UpgradeScript
{
    public $order = 3000;
    public $type = self::UPGRADE_CUSTOM;

    public function run()
    {
        if(empty($this->upgrader->config['js_lang_version']))
        	$this->upgrader->config['js_lang_version'] = 1;
        else
        	$this->upgrader->config['js_lang_version'] += 1;

        //remove lanugage cache files
        require_once('include/SugarObjects/LanguageManager.php');
        LanguageManager::clearLanguageCache();
    }
}
