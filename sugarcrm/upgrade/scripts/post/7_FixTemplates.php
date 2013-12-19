<?php
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
 * Copyright (C) 2004-2013 SugarCRM Inc.  All rights reserved.
 ********************************************************************************/

/**
 * Scan all ModuleBuilder modules and look for files with whitespace
 * after closing tag. Fix those files.
 */
class SugarUpgradeFixTemplates extends UpgradeScript
{
    public $order = 7100;
    public $type = self::UPGRADE_CUSTOM;

    /**
     * List of files being deleted.
     * We don't need to fix them, since they are not with us for long anyway
     * @var array
     */
    protected $deleted = array();
    /**
     * Directories where bad files can be found
     * @var array
     */
    protected $template_dirs = array("metadata", "language", "language/application");

    public function run()
    {
        if(version_compare($this->from_version, '7.0', '>=')) {
            // right now there's no need to run this on 7
            return;
        }

        if(empty($this->upgrader->state['MBModules'])) {
            // No MB modules - nothing to do
            return;
        }

        if(!empty($this->upgrader->state['files_to_delete'])) {
            $this->deleted = array_flip($this->upgrader->state['files_to_delete']);
        }

        foreach($this->upgrader->state['MBModules'] as $MBModule) {
            foreach($this->template_dirs as $tdir) {
                if(is_dir("modules/$MBModule/$tdir")) {
                    foreach(glob("modules/$MBModule/$tdir/*.php") as $phpfile) {
                        $this->fixTemplate($phpfile);
                    }
                }
                if(is_dir("custom/modules/$MBModule/$tdir")) {
                    foreach(glob("custom/modules/$MBModule/$tdir/*.php") as $phpfile) {
                        $this->fixTemplate($phpfile);
                    }
                }
            }
        }
    }

    /**
     * Check file for space after closing tag
     * If found, delete the space and the tag and save the file
     * @param string $filename
     */
    protected function fixTemplate($filename)
    {
        if(!is_file($filename)) {
            return;
        }
        $fcont = file_get_contents($filename);
        if(!preg_match('/\?>\n\s+$/', $fcont)) {
            // no extra spaces, skip it
            return;
        }
        $this->log("Fixing whitespace after closing tag in $filename");
        $fcont = preg_replace('/\?>\n\s+$/', '', $fcont);
        $this->backupFile($filename);
        file_put_contents($filename, $fcont);
    }
}
