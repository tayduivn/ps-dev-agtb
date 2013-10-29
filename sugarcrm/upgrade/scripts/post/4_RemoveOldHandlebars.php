<?php
/**
 * Remove old Handlebars templates using hbt extension
 */
class SugarUpgradeRemoveOldHandlebars extends UpgradeScript
{
    public $order = 4000;
    public $type = self::UPGRADE_CORE;
    public $version = "7.1.5";

    public function run()
    {
        // only run this when coming from a [6.6.0,  7.0.0) upgrade
        if (version_compare($this->from_version, '7.0.0', '>=')
            || version_compare($this->from_version, '6.6.0', '<')) {
            return;
        }
        $this->removeHbts('clients'); //Get the base templates first
        $iter = new GlobIterator('modules/*', FilesystemIterator::SKIP_DOTS);
        foreach ($iter as $module) {
            if ($module->isDir()) {
                $clients = $module->getPathname() . '/clients';
                if (file_exists($clients) && is_dir($clients)) {
                    $this->removeHbts($clients);
                }
            }
        }
    }

    /**
     * Remove .hbt files under $path
     * @param $path {String} Path to examine
     */
    public function removeHbts($path)
    {
        $dir = new RecursiveDirectoryIterator($path);
        $iter = new RecursiveIteratorIterator($dir);
        $hbts = new RegexIterator($iter, '/.*\.hbt$/', RegexIterator::GET_MATCH);
        foreach ($hbts as $hbt) {
            list($filename) = $hbt;
            $this->fileToDelete($filename);
        }
    }
}
