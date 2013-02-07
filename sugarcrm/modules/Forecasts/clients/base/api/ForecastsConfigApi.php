<?php
if (!defined('sugarEntry') || !sugarEntry) die('Not A Valid Entry Point');
/********************************************************************************
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

require_once('include/api/ConfigModuleApi.php');

class ForecastsConfigApi extends ConfigModuleApi
{

    public function registerApiRest()
    {
        return
            array(
                'forecastsConfigGet' => array(
                    'reqType' => 'GET',
                    'path' => array('Forecasts', 'config'),
                    'pathVars' => array('module', ''),
                    'method' => 'config',
                    'shortHelp' => 'Retrieves the config settings for a given module',
                    'longHelp' => 'include/api/help/config_get_help.html',
                ),
                'forecastsConfigCreate' => array(
                    'reqType' => 'POST',
                    'path' => array('Forecasts', 'config'),
                    'pathVars' => array('module', ''),
                    'method' => 'forecastsConfigSave',
                    'shortHelp' => 'Creates the config entries for the Forecasts module.',
                    'longHelp' => 'modules/Forecasts/clients/base/api/help/ForecastsConfigPut.html',
                ),
                'forecastsConfigUpdate' => array(
                    'reqType' => 'PUT',
                    'path' => array('Forecasts', 'config'),
                    'pathVars' => array('module', ''),
                    'method' => 'forecastsConfigSave',
                    'shortHelp' => 'Updates the config entries for the Forecasts module',
                    'longHelp' => 'modules/Forecasts/clients/base/api/help/ForecastsConfigPut.html',
                ),
            );
    }

    /**
     * Save function for the config settings for Forecasts' special needs.
     * @param $api
     * @param $args 'module' is required, 'platform' is optional and defaults to 'base'
     */
    public function forecastsConfigSave($api, $args)
    {
        //acl check, only allow if they are module admin
        if (!parent::hasAccess("Forecasts")) {
            throw new SugarApiExceptionNotAuthorized("Current User not authorized to change Forecasts configuration settings");
        }

        $platform = (isset($args['platform']) && !empty($args['platform'])) ? $args['platform'] : 'base';

        $admin = BeanFactory::getBean('Administration');
        //track what settings have changed to determine if timeperiods need rebuilt
        $prior_forecasts_settings = $admin->getConfigForModule('Forecasts', $platform);

        //If this is a first time setup, default prior settings for timeperiods to 0 so we may correctly recalculate
        //how many timeperiods to build forward and backward.  If we don't do this we would need the defaults to be 0
        if (empty($prior_forecasts_settings['is_setup'])) {
            $prior_forecasts_settings['timeperiod_shown_forward'] = 0;
            $prior_forecasts_settings['timeperiod_shown_backward'] = 0;
        }

        $upgraded = 0;
        if (!empty($prior_forecasts_settings['is_upgrade'])) {
            $db = DBManagerFactory::getInstance();
            // check if we need to upgrade opportunities when coming from version below 6.7.x.
            $upgraded = $db->getOne("SELECT count(id) as total FROM upgrade_history WHERE type = 'patch' AND status = 'installed' AND version LIKE '6.7.%'");
            if ($upgraded == 1) {
                //TODO-sfa remove this once the ability to map buckets when they get changed is implemented (SFA-215).
                $args['has_commits'] = true;
            }
        }

        //BEGIN SUGARCRM flav=ent ONLY
        if (isset($args['show_custom_buckets_options'])) {
            $json = getJSONobj();
            $_args = array(
                'dropdown_lang' => isset($_SESSION['authenticated_user_language']) ? $_SESSION['authenticated_user_language'] : $GLOBALS['current_language'],
                'dropdown_name' => 'commit_stage_custom_dom',
                'view_package' => 'studio',
                'list_value' => $json->encode($args['show_custom_buckets_options'])
            );
            $_REQUEST['view_package'] = 'studio';
            require_once 'modules/ModuleBuilder/parsers/parser.dropdown.php';
            $parser = new ParserDropDown ();
            $parser->saveDropDown($_args);
            unset($args['show_custom_buckets_options']);
        }
        //END SUGARCRM flav=ent ONLY

        if ($upgraded || empty($prior_forecasts_settings['is_setup'])) {
            require_once('modules/UpgradeWizard/uw_utils.php');
            updateOpportunitiesForForecasting();
        }

        //reload the settings to get the current settings
        $current_forecasts_settings = parent::configSave($api, $args);

        //if primary settings for timeperiods have changed, then rebuild them
        if ($this->timePeriodSettingsChanged($prior_forecasts_settings, $current_forecasts_settings)) {
            $timePeriod = TimePeriod::getByType($current_forecasts_settings['timeperiod_interval']);
            $timePeriod->rebuildForecastingTimePeriods($prior_forecasts_settings, $current_forecasts_settings);
        }
        return $current_forecasts_settings;
    }

    /**
     * compares two sets of forecasting settings to see if the primary timeperiods settings are the same
     *
     * @param $priorSettings
     * @param $currentSettings
     *
     * @return boolean
     */
    public function timePeriodSettingsChanged($priorSettings, $currentSettings)
    {
        if (!isset($priorSettings['timeperiod_shown_backward']) || (isset($currentSettings['timeperiod_shown_backward']) && ($currentSettings['timeperiod_shown_backward'] != $priorSettings['timeperiod_shown_backward']))) {
            return true;
        }
        if (!isset($priorSettings['timeperiod_shown_forward']) || (isset($currentSettings['timeperiod_shown_forward']) && ($currentSettings['timeperiod_shown_forward'] != $priorSettings['timeperiod_shown_forward']))) {
            return true;
        }
        if (!isset($priorSettings['timeperiod_interval']) || (isset($currentSettings['timeperiod_interval']) && ($currentSettings['timeperiod_interval'] != $priorSettings['timeperiod_interval']))) {
            return true;
        }
        if (!isset($priorSettings['timeperiod_type']) || (isset($currentSettings['timeperiod_type']) && ($currentSettings['timeperiod_type'] != $priorSettings['timeperiod_type']))) {
            return true;
        }
        if (!isset($priorSettings['timeperiod_start_date']) || (isset($currentSettings['timeperiod_start_date']) && ($currentSettings['timeperiod_start_date'] != $priorSettings['timeperiod_start_date']))) {
            return true;
        }
        if (!isset($priorSettings['timeperiod_leaf_interval']) || (isset($currentSettings['timeperiod_leaf_interval']) && ($currentSettings['timeperiod_leaf_interval'] != $priorSettings['timeperiod_leaf_interval']))) {
            return true;
        }

        return false;
    }

}
