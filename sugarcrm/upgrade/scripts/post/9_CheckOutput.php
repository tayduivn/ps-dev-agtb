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
  * Scan modules to find print_r, var_dump, exit, die using and replace it with custom sugar_upgrade_* functions.
  */
class SugarUpgradeCheckOutput extends UpgradeScript
{
    public $order = 9200;
    public $type = self::UPGRADE_CUSTOM;

    protected $excludedScanDirectories = array(
        'backup',
        'disabled',
        'tmp',
        'temp',
    );

    public $filesToFix = array();

    protected $sePattern = <<<ENDP
if\s*\(\s*!\s*defined\s*\(\s*['|"]sugarEntry['|"]\s*\)\s*(\|\|\s*!\s*sugarEntry\s*)?\)\s*{?\s*die\s*\(\s*['|"](.*?)['|"]\s*\)\s*;\s*}?
ENDP;

    public function checkFiles($files)
    {
        foreach ($files as $file) {
            if (is_array($file)) {
                $this->checkFiles($file);
                continue;
            }

            // check for any occurrence of the directories and flag them
            $fileContents = file_get_contents($file);

            // skip entryPoint check die
            $checkEntryPoint = false;
            if (preg_match("#($this->sePattern)#i", $fileContents, $seMatch)) {
                $checkEntryPoint = true;
                $fileContents = preg_replace("#{$this->sePattern}#i", '', $fileContents);
            }

            if (preg_match_all('#([^_])\b(print_r|var_dump|exit|die)\b(.*?(,(.*?)\)?)?);#is', $fileContents, $matchAll, PREG_SET_ORDER)) {

                $changedContents = $fileContents;
                $changedContentsFlag = false;

                foreach($matchAll as $match) {
                    // skip print_r with second param
                    if ('print_r' == $match[2] && !empty($match[5])) {
                        continue;
                    }

                    $pattern = $match[0];
                    $replace = preg_replace('#([^_])\b(print_r|var_dump|exit|die)\b([^_])#is', '\\1sugar_upgrade_\\2\\3', $pattern);

                    if (!empty($pattern) && !empty($replace)) {
                        $changedContents = preg_replace("#" . preg_quote($pattern) . "#is", $replace, $changedContents);
                        $changedContentsFlag = true;
                    }

                    if (true == $checkEntryPoint && !empty($seMatch[0])) {
                        $changedContents = preg_replace('#^(<\?php\s*)#is', '\\1' . $seMatch[0], $changedContents);
                        $changedContentsFlag = true;
                        $checkEntryPoint = false;
                    }
                }

                if (true == $changedContentsFlag) {
                    $this->backupFile($file);
                    $this->putFile($file, $changedContents);
                }
            }
        }
    }

    /**
     * Scan directory and build the list of PHP files it contains
     * @param string $path
     * @return array Files data
     */
    protected function getPhpFiles($path)
    {
        global $bwcModules;
        $ds = explode(DIRECTORY_SEPARATOR, $path);

        if (($ds[0] == 'custom') && ($ds[1] == 'modules') && in_array($ds[2], $bwcModules)) {
            return array();
        }

        $data = array();
        if(!is_dir($path)) {
            return array();
        }
        $path = rtrim($path, "/") . "/";
        $iter = new DirectoryIterator($path);
        foreach ($iter as $item) {
            if ($item->isDot()) {
                continue;
            }

            $filename = $item->getFilename();
            if(strpos($filename, ".suback.php") !== false) {
                // we'll ignore .suback files, they are old upgrade backups
                continue;
            }

            if ($item->isDir() && in_array($filename, $this->excludedScanDirectories)) {
                continue;
            } elseif ($item->isDir()) {
                if(strtolower($filename) == 'disable' || strtolower($filename) == 'disabled') {
                    // skip disable dirs
                    continue;
                }
                $data = array_merge($data, $this->getPhpFiles($path . $filename . "/"));
            } elseif ($item->getExtension() != 'php') {
                continue;
            } else {
                $data[] = $path . $filename;
            }
        }
        return $data;
    }

    public function run()
    {
        // run only when upgrade version is less than 7.0.0
        if (!version_compare($this->from_version, '7.0.0', "<")) {
            return;
        }

        $files = $this->getPhpFiles("custom/");
        $this->checkFiles($files);
    }
}
