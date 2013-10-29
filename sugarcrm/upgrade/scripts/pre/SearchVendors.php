<?php
 if(!defined('sugarEntry') || !sugarEntry) die('Not A Valid Entry Point');
/*********************************************************************************
 * The contents of this file are subject to the SugarCRM Master Subscription
 * Agreement ("License") which can be viewed at
 * http://www.sugarcrm.com/crm/master-subscription-agreement
 * By installing or using this file, You have unconditionally agreed to the
 * terms and conditions of the License, and You may not use this file except in
 * compliance with the License.  Under the terms of the license, You shall not,
 * among other things: 1) sublicense, resell, rent, lease, redistribute, assign
 * or otherwise transfer Your rights to the Software, and 2) use the Software
 * for timesharing or service bureau purposes such as hosting the Software for
 * commercial gain and/or for the benefit of a third party.  Use of the Software
 * may be subject to applicable fees and any use of the Software without first
 * paying applicable fees is strictly prohibited.  You do not have the right to
 * remove SugarCRM copyrights from the source code or user interface.
 *
 * All copies of the Covered Code must include on each user interface screen:
 *  (i) the "Powered by SugarCRM" logo and
 *  (ii) the SugarCRM copyright notice
 * in the same form as they appear in the distribution.  See full license for
 * requirements.
 *
 * Your Warranty, Limitations of liability and Indemnity are expressly stated
 * in the License.  Please refer to the License for the specific language
 * governing these rights and limitations under the License.  Portions created
 * by SugarCRM are Copyright (C) 2004-2012 SugarCRM, Inc.; All Rights Reserved.
 ********************************************************************************/
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
