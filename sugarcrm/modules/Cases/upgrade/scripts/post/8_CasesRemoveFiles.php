<?php
/*
 * By installing or using this file, you are confirming on behalf of the entity
 * subscribed to the SugarCRM Inc. product ("Company") that Company is bound by
 * the SugarCRM Inc. Master Subscription Agreement ("MSA"), which is viewable at:
 * http://www.sugarcrm.com/master-subscription-agreement
 *
 * If Company is not bound by the MSA, then by installing or using this file
 * you are agreeing unconditionally that Company will be bound by the MSA and
 * certifying that you have authority to bind Company accordingly.
 *
 * Copyright (C) 2004-2014 SugarCRM Inc.  All rights reserved.
 */

class SugarUpgradeCasesRemoveFiles extends UpgradeScript
{
    /**
     * When to run this upgrade script
     * @var int
     */
    public $order = 8501;

    /**
     * Type of upgrade script
     *
     * @var int
     */
    public $type = self::UPGRADE_CORE;

    /**
     * Lets Run This Upgrade Script!
     */
    public function run()
    {
        $files = array();
        // if we are coming from before 7.2.1, these files need to be deleted
        if (version_compare($this->from_version, '7.2.1', '<')) {
            $files[] = 'modules/Cases/CasesApiHelper.php';
        }

        if (!empty($files)) {
            $this->fileToDelete($files);
        }
    }
}
