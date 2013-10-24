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
require_once 'modules/UpgradeWizard/UpgradeDriver.php';

/**
 * Test upgrader class
 *
 * Used for unit tests on upgrader
 */
class TestUpgrader extends UpgradeDriver
{
    /**
     * List of upgrade scripts
     * @var string
     */
    protected $scripts = array();

    public function __construct($admin)
    {
        $context = array(
            "admin" => $admin->user_name,
            "log" => "cache/upgrade.log",
            "source_dir" => realpath(dirname(__FILE__)."/../../"),
        );
        parent::__construct($context);
    }

    public function cleanState()
    {
        $statefile = $this->cacheDir('upgrades/').self::STATE_FILE;
        if(file_exists($statefile)) {
            unlink($statefile);
        }
    }

    public function runStage($stage)
    {
        return $this->run($stage);
    }

    public function setDb($db)
    {
        $this->db = $db;
    }

    /**
     * Get script object for certain script
     * @param string $stage
     * @param string $script
     * @return UpgradeScript
     */
    public function getScript($stage, $script)
    {
        if(empty($this->scripts[$stage])) {
            $this->scripts[$stage] = $this->getScripts(dirname($script), $stage);
        }
        return $this->scripts[$stage][$script];
    }

    public function getTempDir()
    {
        if (empty($this->context['temp_dir'])) {
            $this->context['temp_dir'] = '';
        }
        return $this->context['temp_dir'];
    }

    public function setVersions($from, $flav_from, $to, $flav_to)
    {
        $this->from_version = $from;
        $this->from_flavor = $flav_from;
        $this->to_version = $to;
        $this->to_flavor = $flav_to;
    }
}
