<?php
 if(!defined('sugarEntry') || !sugarEntry) die('Not A Valid Entry Point');
/*********************************************************************************
 * By installing or using this file, you are confirming on behalf of the entity
 * subscribed to the SugarCRM Inc. product ("Company") that Company is bound by
 * the SugarCRM Inc. Master Subscription Agreement (“MSA”), which is viewable at:
 * http://www.sugarcrm.com/master-subscription-agreement
 *
 * If Company is not bound by the MSA, then by installing or using this file
 * you are agreeing unconditionally that Company will be bound by the MSA and
 * certifying that you have authority to bind Company accordingly.
 *
 * Copyright (C) 2004-2014 SugarCRM Inc.  All rights reserved.
 ********************************************************************************/

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
