<?php
if(!defined('sugarEntry') || !sugarEntry) die('Not A Valid Entry Point');
/*********************************************************************************
 *The contents of this file are subject to the SugarCRM Professional End User License Agreement
 *("License") which can be viewed at http://www.sugarcrm.com/EULA.
 *By installing or using this file, You have unconditionally agreed to the terms and conditions of the License, and You may
 *not use this file except in compliance with the License. Under the terms of the license, You
 *shall not, among other things: 1) sublicense, resell, rent, lease, redistribute, assign or
 *otherwise transfer Your rights to the Software, and 2) use the Software for timesharing or
 *service bureau purposes such as hosting the Software for commercial gain and/or for the benefit
 *of a third party.  Use of the Software may be subject to applicable fees and any use of the
 *Software without first paying applicable fees is strictly prohibited.  You do not have the
 *right to remove SugarCRM copyrights from the source code or user interface.
 * All copies of the Covered Code must include on each user interface screen:
 * (i) the "Powered by SugarCRM" logo and
 * (ii) the SugarCRM copyright notice
 * in the same form as they appear in the distribution.  See full license for requirements.
 *Your Warranty, Limitations of liability and Indemnity are expressly stated in the License.  Please refer
 *to the License for the specific language governing these rights and limitations under the License.
 *Portions created by SugarCRM are Copyright (C) 2004 SugarCRM, Inc.; All Rights Reserved.
 ********************************************************************************/

class ForecastsDefaults
{

    /**
     * Sets up the default forecasts config settings
     *
     * @param bool $isUpgrade if this is being called in an upgrade setting
     * @param string $currentVersion if isUpgrade == true, the current version the user has
     * @param string $targetVersion if isUpgrade == true, the version the user is upgrading to
     */
    public static function setupForecastSettings($isUpgrade=false,$currentVersion="670",$targetVersion="670")
    {
        $isSetup = false;
        $admin = BeanFactory::getBean('Administration');

        $forecastConfig = self::getDefaults();

        // Any version-specific changes to the defaults can be added here
        // and determined by $currentVersion & $targetVersion

        if($isUpgrade) {
            // get current settings
            $adminConfig = $admin->getConfigForModule('Forecasts');
            // if admin has already been set up
            if(!empty($adminConfig['is_setup'])) {
                foreach($adminConfig as $key => $val) {
                    $forecastConfig[$key] = $val;
                }
            }
        }

        foreach ($forecastConfig as $name => $value)
        {
            if(is_array($value))
            {
                $admin->saveSetting('Forecasts', $name, json_encode($value), 'base');
            } else {
                $admin->saveSetting('Forecasts', $name, $value, 'base');
            }
        }
    }

    /**
     * Returns the default values for Forecasts to use
     *
     * @param int $isSetup pass in if you want is_setup to be 1 or 0, 0 by default
     * @return array default config settings for Forecasts to use
     */
    public static function getDefaults($isSetup=0) {
        // If isSetup happens to get passed as a boolean false, change to 0 for the db
        if($isSetup === false) {
            $isSetup = 0;
        }

        // default forecast config setup
        return array(
            // this is used to indicate whether the admin wizard should be shown on first run (for admin only, otherwise a message telling a non-admin to tell their admin to set it up)
            'is_setup' => $isSetup,
            // sets whether forecasting timeperiods will be set up based on fiscal or calendar periods, options come from forecasts_timeperiod_types_dom
            'timeperiod_type' => 'chronological', //options:  'chronological' or 'fiscal'
            // the timeperiod intervals users can forecasts over, options come from forecasts_timeperiod_options_dom
            'timeperiod_interval' => 'Annual',
            // the leaf interval that gets the extra week if main period is fiscal + quaterly, options come from forecasts_timeperiod_leaf_quarterly_options_dom, (first, middle, last)
            'timeperiod_leaf_interval' => 'Quarter',
            'timeperiod_start_month' => '7',
            'timeperiod_start_day' => '1',
            // number of timeperiods forward from the current that are displayed
            'timeperiods_shown_forward' => 4,
            // number of timeperiods in the past from the current that are displayed
            'timeperiods_shown_backward' => 4,
            // used to indicate the available option for grouping opportunities
            'forecast_categories' => 'show_binary',  // options:  'show_binary', 'show_buckets', 'show_custom_buckets'
            // used to reference the app_list_string entry to indicate the commit stage list to use
            'buckets_dom' => 'commit_stage_binary_dom', // options:  commit_stage_binary_dom, commit_stage_dom, commit_stage_extended_dom
            // the defined binary ranges the different buckets opportunities will fall in by default based on their probability
            'show_binary_ranges' => array('include' => array('min' => 70, 'max' => 100), 'exclude' => array('min' => 0, 'max' => 69)),
            // the defined bucket ranges the different buckets opportunities will fall in by default based on their probability
            'show_buckets_ranges' => array('include' => array('min' => 85, 'max' => 100), 'upside' => array('min' => 70, 'max' => 84), 'exclude' => array('min' => 0, 'max' => 69)),
            //BEGIN SUGARCRM flav=ent ONLY
            // the defined custom ranges the different buckets opportunities will fall in by default based on their probability
            'show_custom_ranges' => array('include' => array('min' => 70, 'max' => 100), 'exclude' => array('min' => 0, 'max' => 69)),
            //END SUGARCRM flav=ent ONLY

            //sales_stage_won are all sales_stage opportunity values indicating the opportunity is won
            'sales_stage_won' => array('Closed Won'),
            //sales_stage_lost are all sales_stage opportunity values indicating the opportunity is lost
            'sales_stage_lost' => array('Closed Lost'),
            // whether or not to show the likely column in the forecasts worksheets
            'show_worksheet_likely' => 1,
            // whether or not to show the best column in the forecasts worksheets
            'show_worksheet_best' => 1,
            // whether or not to show the worst column in the forecasts worksheets
            'show_worksheet_worst' => 0,
            // whether or not to show the likely total in the forecasts projected view
            'show_projected_likely' => 1,
            // whether or not to show the best total in the forecasts projected view
            'show_projected_best' => 1,
            // whether or not to show the worst total in the forecasts projected view
            'show_projected_worst' => 0,
        );
    }

    /**
     * Returns a Forecasts config default given the key for the default
     * @param $key
     * @return mixed
     */
    public static function getConfigDefaultByKey($key) {
        $forecastsDefault = self::getDefaults();
        return $forecastsDefault[$key];
    }


    /**
     * Runs SQL to upgrade columns specific for Forecasts modules.  This is a helper function called from silentUpgrade_step2.php and end.php
     * for upgrade script code that runs SQL to update tables.
     *
     * @static
     */
    public static function upgradeColumns() {
        $db = DBManagerFactory::getInstance();

        //Update the currency_id and base_rate columns for existing records so that we have currency_id and base_rate values set up correctly
        $tables = array('opportunities', 'products', 'worksheet', 'forecasts', 'forecast_schedule', 'quotes', 'quota');
        foreach($tables as $table)
        {
            //Update base_rate for existing records with currency_id values that are not the base currency
            $db->query("UPDATE {$table} t, currencies c SET t.base_rate = c.conversion_rate WHERE t.currency_id IS NOT NULL AND t.currency_id <> '-99' AND t.currency_id = c.id");
            //Update currency_id and base_rate for records with NULL values or where currency_id is base (-99)
            $db->query("UPDATE {$table} SET currency_id = '-99', base_rate = 1 WHERE currency_id IS NULL OR currency_id = '-99'");
        }
    }

}
