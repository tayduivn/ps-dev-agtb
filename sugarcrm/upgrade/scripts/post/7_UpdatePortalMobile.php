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
if(!file_exists('modules/UpgradeWizard/SidecarUpdate/SidecarMetaDataUpgrader.php')) return;

require_once 'modules/UpgradeWizard/SidecarUpdate/SidecarMetaDataUpgrader.php';
/**
 * Upgrade sidecar portal metadata
 */
class SugarUpgradeUpdatePortalMobile extends UpgradeScript
{
    public $order = 7000;
    public $type = self::UPGRADE_CUSTOM;

    public function run()
    {
        if(version_compare($this->from_version, '7.0', '>=')) {
            // right now there's no need to run this on 7
            return;
        }

        if(!file_exists('modules/UpgradeWizard/SidecarUpdate/SidecarMetaDataUpgrader.php')) return;
        // TODO: fix uw_utils references in SidecarMetaDataUpgrader
        $smdUpgrader = new SidecarMetaDataUpgrader2($this);
        $smdUpgrader->upgrade();

        // Log failures if any
        $failures = $smdUpgrader->getFailures();
        if (!empty($failures)) {
            $this->log('Sidecar Upgrade: ' . count($failures) . ' metadata files failed to upgrade through the silent upgrader:');
            $this->log(print_r($failures, true));
        } else {
            $this->log('Sidecar Upgrade: Mobile/portal metadata upgrade ran with no failures:');
            $this->log($smdUpgrader->getCountOfFilesForUpgrade() . ' files were upgraded.');
        }
        $this->fileToDelete(SidecarMetaDataUpgrader::getFilesForRemoval());
    }
}

/**
 * Decorator class to override logging behavior of SidecarMetaDataUpgrader
 */
class SidecarMetaDataUpgrader2 extends SidecarMetaDataUpgrader
{
    public function __construct($upgrade)
    {
        $this->upgrade = $upgrade;
    }

    public function logUpgradeStatus($msg)
    {
        $this->upgrade->log($msg);
    }

    public function getMBModules()
    {
        if(!empty($this->upgrade->state['MBModules'])) {
            return $this->upgrade->state['MBModules'];
        }
        return array();
    }
}
