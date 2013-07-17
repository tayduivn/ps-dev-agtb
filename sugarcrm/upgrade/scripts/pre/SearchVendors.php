<?php
/**
 * Class SugarUpgradeSearchVendors
 * This class will check the custom directory
 * for any reference of files that have moved to the vendor
 * directory.  If a reference is found it will add it to an array
 * and fail the upgrade with a message regarding the files that need fixed
 */
class SugarUpgradeSearchVendors extends UpgradeScript
{
    public $order = 50;

    public $directories = array(
        'include/HTMLPurifier',
        'include/HTTP_WebDAV_Server',
        'include/Pear',
        'include/Smarty',
        'XTemplate',
        'Zend',
        'include/lessphp',
        'log4php',
        'include/nusoap',
        'include/oauth2-php',
        'include/pclzip',
        'include/reCaptcha',
        'include/tcpdf',
        'include/ytree',
        'include/SugarSearchEngine/Elastic/Elastica',

    );
    public $filesToFix = array();

    /**
     * This method checks for directories that have been moved that are referenced
     * in custom code
     */
    public function checkForVendors()
    {
        $files = self::scanDir("custom/");
        $this->checkFiles($files);
    }

    public function checkFiles($files)
    {
        foreach ($files as $name => $file) {
            if (is_array($file)) {
                $this->checkFiles($file);
                continue;
            }
            // check for any occurrence of the directories and flag them
            $count = 0;
            str_replace($this->directories, '', file_get_contents($file), $count);
            if ($count > 0) {
                $this->filesToFix[] = $file;
            }
        }
    }

    public function run()
    {
        $this->checkForVendors();
        if (!empty($this->filesToFix)) {
            // if there are fails to fix, fail the upgrade with a message about the files that need fixed
            $files_to_fix = implode("\r\n", $this->filesToFix);
            $this->fail(
                "Files found that contain paths to directories that have been moved to vendor:\r\n{$files_to_fix}"
            );
        }
    }

    /**
     * Scan directory and build the list of files it contains
     * @param string $path
     * @return array Files data
     */
    public static function scanDir($path)
    {
        $data = array();
        $iter = new DirectoryIterator("./" . $path);
        foreach ($iter as $item) {
            if ($item->isDot()) {
                continue;
            }
            $filename = $item->getFilename();
            if ($item->isDir()) {
                $data[$filename] = self::scanDir($path . $filename . "/");
            } else {
                $data[$filename] = $path . $filename;
            }
        }
        return $data;
    }
}
