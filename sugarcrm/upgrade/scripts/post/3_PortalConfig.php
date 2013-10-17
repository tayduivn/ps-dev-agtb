<?php
/**
 * Create portal config if does not exist
 */
class SugarUpgradePortalConfig extends UpgradeScript
{
    public $order = 3000;
    public $type = self::UPGRADE_CUSTOM;
    public $version = "7.1.5";

    public function run()
    {
        if(!$this->toFlavor('ent')) return;

        global $mod_strings;
        //Set portal log level to `ERROR`
        $fieldKey = 'logLevel';
        $fieldValue = 'ERROR';
        $admin = new Administration();
        if(!$admin->saveSetting('portal', $fieldKey, json_encode($fieldValue), 'support')){
            $this->fail(sprintf($this->mod_strings['ERROR_UW_PORTAL_CONFIG_DB'], 'portal', $fieldKey, $fieldValue));
        }

        require_once 'ModuleInstall/ModuleInstaller.php';
        $this->putFile('portal2/config.js', ModuleInstaller::getJSConfig(ModuleInstaller::getPortalConfig()));
    }
}
