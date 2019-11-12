<?php
// FILE SUGARCRM flav=ent ONLY
/*
 * Your installation or use of this SugarCRM file is subject to the applicable
 * terms available at
 * http://support.sugarcrm.com/Resources/Master_Subscription_Agreements/.
 * If you do not agree to all of the applicable terms or do not have the
 * authority to bind the entity as an authorized representative, then do not
 * install or use this SugarCRM file.
 *
 * Copyright (C) SugarCRM Inc. All rights reserved.
 */

class ConsoleConfigurationDefaults
{
    public static $consoleDefaults = [
        'enabled_modules' => [
            // Renewals console
            'da438c86-df5e-11e9-9801-3c15c2c53980' => [
                'Accounts',
                'Opportunities',
            ],
        ],
        'order_by_primary' => [
            // Renewals console
            'da438c86-df5e-11e9-9801-3c15c2c53980' => [
                'Accounts' => 'date_modified',
                'Opportunities' => 'date_closed',
            ],
        ],
        'order_by_secondary' => [
            // Renewals console
            'da438c86-df5e-11e9-9801-3c15c2c53980' => [
                'Accounts' => '',
                'Opportunities' => '',
            ],
        ],
        'filter_def' => [
            // Renewals console
            'da438c86-df5e-11e9-9801-3c15c2c53980' => [
                'Accounts' => [
                    [
                        '$owner' => '',
                    ],
                ],
                'Opportunities' => [
                    [
                        '$owner' => '',
                    ],
                ],
            ],
        ],
    ];

    /**
     * Sets up the default ConsoleConfiguration settings
     * @return array The config settings
     */
    public static function setupConsoleConfigurationSettings()
    {
        $admin = BeanFactory::newBean('Administration');

        // Get the default Console Configuration settings
        $consoleConfig = self::getDefaults();

        // Get the current configured Console Configuration settings
        $adminConfig = $admin->getConfigForModule('ConsoleConfiguration');

        // If admin has already been set up, override any default settings
        // for each console if there are already saved settings for it
        if (!empty($adminConfig['is_setup'])) {
            foreach ($adminConfig as $configName => $configValue) {
                if ($configName === 'is_setup') {
                    $consoleConfig[$configName] = $configValue;
                } else if (isset($consoleConfig[$configName])) {
                    foreach ($configValue as $consoleId => $consoleSetting) {
                        $consoleConfig[$configName][$consoleId] = $consoleSetting;
                    }
                }
            }
        }

        foreach ($consoleConfig as $name => $value) {
            $admin->saveSetting('ConsoleConfiguration', $name, $value, 'base');
        }

        return $consoleConfig;
    }

    /**
     * Returns the default values for ConsoleConfiguration settings
     *
     * @param int $isSetup pass in if you want is_setup to be 1 or 0, 0 by default
     * @return array default config settings to use for Console Configuration
     */
    public static function getDefaults($isSetup = 0)
    {
        // If isSetup happens to get passed as a boolean false, change to 0 for the db
        if ($isSetup === false) {
            $isSetup = 0;
        }

        return array_merge(self::$consoleDefaults, ['is_setup' => $isSetup]);
    }
}
