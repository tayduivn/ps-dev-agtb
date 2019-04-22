<?php
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

class VisualPipelineDefaults
{
    /**
     * Sets up the default PipelineConfig settings
     * @return array The config settings
     */
    public static function setupPipelineSettings()
    {
        $admin = BeanFactory::newBean('Administration');
        // get current settings
        $adminConfig = $admin->getConfigForModule('VisualPipeline');
        $pipelineConfig = self::getDefaults();

        // if admin has already been set up
        if (!empty($adminConfig['is_setup'])) {
            foreach ($adminConfig as $key => $val) {
                $pipelineConfig[$key] = $val;
            }
        }

        foreach ($pipelineConfig as $name => $value) {
            $admin->saveSetting('VisualPipeline', $name, $value, 'base');
        }

        return $pipelineConfig;
    }

    /**
     * Returns the default values for Visual Pipelines to use
     *
     * @param int $isSetup pass in if you want is_setup to be 1 or 0, 0 by default
     * @return array default config settings for Visual Pipelines to use
     */
    public static function getDefaults($isSetup = 0)
    {
        // If isSetup happens to get passed as a boolean false, change to 0 for the db
        if ($isSetup === false) {
            $isSetup = 0;
        }

        // default visual pipeline config setup
        return array(
            // this is used to indicate whether the admin wizard should be shown on first run (for admin only, otherwise a message telling a non-admin to tell their admin to set it up)
            'is_setup' => $isSetup,
            // which modules can use pipeline
            'enabled_modules' => array(
                'Cases',
                'Leads',
                'Opportunities',
                'Tasks',
            ),
            'table_header' => array(
                'Cases' => 'status',
                'Leads' => 'status',
                'Opportunities' => 'sales_status',
                'Tasks' => 'status',
            ),
            'hidden_values' => array(
                'Cases' => array(),
                'Leads' => array(),
                'Opportunities' => array(
                    'Closed Won',
                    'Closed Lost',
                ),
                'Tasks' => array(),
            ),
            'tile_header' => array(
                'Cases' => 'name',
                'Leads' => 'name',
                'Opportunities' => 'name',
                'Tasks' => 'name',
            ),
            'tile_body_fields' => array(
                'Cases' => array(
                    'account_name',
                    'priority',
                ),
                'Leads' => array(
                    'account_name',
                ),
                'Opportunities' => array(
                    'account_name',
                    'date_closed',
                    'amount',
                ),
                'Tasks' => array(
                    'contact_name',
                    'parent_name',
                    'date_due',
                ),
            ),
            'records_per_column' => array(
                'Cases' => '10',
                'Leads' => '10',
                'Opportunities' => '10',
                'Tasks' => '10',
            ),
            'header_colors' => array(
                '#36850F',
                '#0679C8',
                '#AB173C',
                '#854EDB',
                '#00856F',
                '#016FAA',
                '#BC3CCD',
                '#BD5800',
                '#1202F5',
                '#E61718',
                '#717171',
                '#222222',
            ),
        );
    }
}
