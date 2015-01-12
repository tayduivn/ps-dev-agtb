<?php
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

class SugarUpgradeEnableLegacyDashboard extends UpgradeScript
{
    public $order = 8999;
    public $type = self::UPGRADE_CUSTOM;

    /**
     * Add settings of enabling legacy dashboard to the file "config_override.php".
     *
     * @throws Exception if the file "config_override.php" is not writable or the writing fails.
     */
    public function run()
    {
        // If the from_version is less than 7, we need to enable the legacy dashboards
        if (version_compare($this->from_version, '7.0.0', '<')) {

            // Append the new settings to the end of the file, without changing the existing content.
            // Note:
            // If these settings are already in config_override.php, there will be double entries in the file.
            // Since these values are appended in the end, the previously values are overridden.
            // Reloading the file will get the correct values.
            $newSettings = "\n";
            $newSettings .= "\$sugar_config['enable_legacy_dashboards'] = true;\n";
            $newSettings .= "\$sugar_config['lock_homepage'] = true;\n";

            if ($this->appendOverrideConfig($newSettings) === true) {
                $this->log('Legacy Dashboards Enabled!');
            }
        }
    }

    /**
     * Append a string of new settings to the end of the file "config_override.php".
     *
     * @param string $newStr the string of added configs.
     * @throws Exception if the file "config_override.php" is not writable or the writing fails.
     * @return true if everything goes well.
     */
    public function appendOverrideConfig($newStr)
    {
        if ( !file_exists('config_override.php') ) {
            touch('config_override.php');
        }
        if ( !(make_writable('config_override.php')) ||  !(is_writable('config_override.php')) ) {
            throw new Exception("Unable to write to the config_override.php file. Check the file permissions");
        }
        $fp = sugar_fopen('config_override.php', 'a');
        if ($fp === false) {
            throw new Exception("Failed writing to the config_override.php file.");
        } else {
            fwrite($fp, $newStr);
            fclose($fp);
        }
        return true;
    }
}
