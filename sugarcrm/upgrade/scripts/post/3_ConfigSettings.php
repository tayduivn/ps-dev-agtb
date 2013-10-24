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
 * Update config.php settings
 */
class SugarUpgradeConfigSettings extends UpgradeScript
{
    public $order = 3000;
    public $type = self::UPGRADE_CUSTOM;

    public function run()
    {
        $this->upgrader->config['sugar_version'] = $this->to_version;

	    if(!isset($this->upgrader->config['default_permissions'])) {
		    $this->upgrader->config['default_permissions'] = array (
				'dir_mode' => 02770,
				'file_mode' => 0660,
				'user' => '',
				'group' => '',
    		);
	    }

	    if(!isset($this->upgrader->config['logger'])){
		    $this->upgrader->config['logger'] =array (
				'level'=>'fatal',
				'file' =>
				array (
						'ext' => '.log',
						'name' => 'sugarcrm',
						'dateFormat' => '%c',
						'maxSize' => '10MB',
						'maxLogs' => 10,
						'suffix' => '', // bug51583, change default suffix to blank for backwards comptability
				),
		    );
	    }

	    if (!isset($this->upgrader->config['lead_conv_activity_opt'])) {
	        $this->upgrader->config['lead_conv_activity_opt'] = 'copy';
	    }

	    if(!isset($this->upgrader->config['resource_management'])){
	        $this->upgrader->config['resource_management'] = array (
	                'special_query_limit' => 50000,
	                'special_query_modules' =>
	                array (
	                        0 => 'Reports',
	                        1 => 'Export',
	                        2 => 'Import',
	                        3 => 'Administration',
	                        4 => 'Sync',
	                ),
	                'default_limit' => 1000,
	        );
	    }
	    if(!isset($this->upgrader->config['default_theme'])) {
	        $this->upgrader->config['default_theme'] = 'Sugar';
	    }

	    if(!isset($this->upgrader->config['default_max_tabs'])) {
	        $this->upgrader->config['default_max_tabs'] = '7';
	    }
    }
}
