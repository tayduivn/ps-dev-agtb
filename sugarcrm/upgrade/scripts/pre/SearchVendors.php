<?php
 if(!defined('sugarEntry') || !sugarEntry) die('Not A Valid Entry Point');
/*
 * Your installation or use of this SugarCRM file is subject to the applicable
 * terms available at
 * http://support.sugarcrm.com/06_Customer_Center/10_Master_Subscription_Agreements/.
 * If you do not agree to all of the applicable terms or do not have the
 * authority to bind the entity as an authorized representative, then do not
 * install or use this SugarCRM file.
 *
 * Copyright (C) SugarCRM Inc. All rights reserved.
 */
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
    public $type = self::UPGRADE_CUSTOM;

    public $directories = array(
        'include\/HTMLPurifier',
        'include\/HTTP_WebDAV_Server',
        'include\/Pear',
        'include\/Smarty',
        'XTemplate',
        'Zend',
        'include\/lessphp',
        'log4php',
        'include\/nusoap',
        'include\/oauth2-php',
        'include\/pclzip',
        'include\/reCaptcha',
        'include\/tcpdf',
        'include\/ytree',
        'include\/SugarSearchEngine\/Elastic\/Elastica',

    );

    protected static $excludedScanDirectories = array(
        'backup',
        'tmp',
        'temp',
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
            $fileContents = file_get_contents($file);
            foreach ($this->directories AS $directory) {
                if (preg_match("/(include|require|require_once|include_once)[\s('\"]*({$directory})/",$fileContents) > 0) {
                    $this->filesToFix[] = $file;
                }
            }
        }
    }

    public function run()
    {
        if(version_compare($this->from_version, '7.0', ">=")) {
            // omit checks on 7.x
            return;
        }
        $this->checkForVendors();
        if (!empty($this->filesToFix)) {
            // if there are fails to fix, fail the upgrade with a message about the files that need fixed
            $files_to_fix = implode("\r\n", $this->filesToFix);
            $this->log(
                "Files found that contain paths to directories that have been moved to vendor:\r\n{$files_to_fix}"
            );
            $this->fail();
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
            $fileParts = pathinfo($path . '/' . $filename);

            $extension = !empty($fileParts['extension']) ? $fileParts['extension'] : '';
            if ($item->isDir() && in_array($filename, self::$excludedScanDirectories)) {
                continue;
            } elseif ($item->isDir()) {
                $data[$filename] = self::scanDir($path . $filename . "/");
            } elseif ($extension != 'php') {
                continue;
            } else {
                $data[$filename] = $path . $filename;
            }
        }
        return $data;
    }
}
