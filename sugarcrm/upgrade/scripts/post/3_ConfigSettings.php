<?php
 if(!defined('sugarEntry') || !sugarEntry) die('Not A Valid Entry Point');
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
 * Copyright (C) 2004-2014 SugarCRM Inc. All rights reserved.
 */
/**
 * Update config.php settings
 */
class SugarUpgradeConfigSettings extends UpgradeScript
{
    public $order = 3000;
    public $type = self::UPGRADE_CUSTOM;

    public function run()
    {
        // Fill the missing settings.
        $defaultSettings = get_sugar_config_defaults();
        foreach ($defaultSettings as $key => $defaultValue) {

            if (!array_key_exists($key, $this->upgrader->config)) {
                $this->log("Setting $key does not exist. Setting the default value.");
                $this->upgrader->config[$key] = $defaultValue;
            }

        }

        $this->upgrader->config['sugar_version'] = $this->to_version;

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


        // We no longer have multiple themes support.

        // We removed the ability for the user to choose his preferred theme.
        // In the future, we'll add this feature back, in the new Sidecar Themes
        // format.
        // Backward compatibilty modules look and feel must be in accordance to
        // Sidecar modules, thus there is only one possible theme: `RacerX`
        $this->upgrader->config['default_theme'] = 'RacerX';
    }
}
