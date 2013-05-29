<?php
/**
 * Rebuild minified JS files
 */
class SugarUpgradeMinifyJS extends UpgradeScript
{
    public $order = 5000;
    public $type = self::UPGRADE_CUSTOM;

    public function run()
    {
        $_REQUEST['root_directory'] = $this->context['source_dir'];
        $_REQUEST['js_rebuild_concat'] = 'rebuild';
        require_once('jssource/minify.php');
    }
}
