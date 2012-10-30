<?php
if(!defined('sugarEntry') || !sugarEntry) die('Not A Valid Entry Point');
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

class ForecastsConfigApi extends ConfigModuleApi {


    /**
     * Save function for the config settings for a given module.
     * @param $api
     * @param $args 'module' is required, 'platform' is optional and defaults to 'base'
     */
    public function configSave($api, $args) {

        //acl check, only allow if they are module admin
        if(!parent::hasAccess("Forecasts")) {
            throw new SugarApiExceptionNotAuthorized("Current User not authorized to change Forecasts configuration settings");
        }

        $platform = (isset($args['platform']) && !empty($args['platform'])) ?$args['platform'] : 'base';

        $admin = BeanFactory::getBean('Administration');
        //track what settings have changed to determine if timeperiods need rebuilt
        $prior_forecasts_settings = $admin->getConfigForModule('Forecasts', $platform);

        if (!empty($prior_forecasts_settings['is_upgrade']))
        {
            $db = DBManagerFactory::getInstance();
            // check if we need to upgrade opportunities when coming from version below 6.7.x.
            $upgraded = $db->getOne("SELECT count(id) as total FROM upgrade_history WHERE type = 'patch' AND status = 'installed' AND version LIKE '6.7.%'");
            if ($upgraded == 1)
            {
                require_once('modules/UpgradeWizard/uw_utils.php');
                updateOpportunitiesForForecasting();
                $args['has_commits'] = true;
            }
        }
        parent::configSave($api, $args);

        //reload the settings to get the current settings
        $current_forecasts_settings = $admin->getConfigForModule('Forecasts', $platform);

        //if primary settings for timeperiods have changed, then rebuild them
        // TODO:  fix this!  Commenting out, for now, so that work can continue on config settings in other areas
//        if($this->timePeriodSettingsChanged($prior_forecasts_settings, $current_forecasts_settings)) {
//            TimePeriod::rebuildForecastingTimePeriods();
//        }
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
    private function timePeriodSettingsChanged($priorSettings, $currentSettings) {
        if(!isset($priorSettings['timeperiod_interval']) || (isset($currentSettings['timeperiod_interval']) && ($currentSettings['timeperiod_interval'] != $priorSettings['timeperiod_interval']))) {
            return true;
        }
        if(!isset($priorSettings['timeperiod_type']) || (isset($currentSettings['timeperiod_type']) && ($currentSettings['timeperiod_type'] != $priorSettings['timeperiod_type']))) {
            return true;
        }
        if(!isset($priorSettings['timeperiods_start_month']) || (isset($currentSettings['timeperiods_start_month']) && ($currentSettings['timeperiods_start_month'] != $priorSettings['timeperiods_start_month']))) {
            return true;
        }
        if(!isset($priorSettings['timeperiods_start_day']) || (isset($currentSettings['timeperiods_start_day']) && ($currentSettings['timeperiods_start_day'] != $priorSettings['timeperiods_start_day']))) {
            return true;
        }
        if(!isset($priorSettings['timeperiod_leaf_interval']) || (isset($currentSettings['timeperiod_leaf_interval']) && ($currentSettings['timeperiod_leaf_interval'] != $priorSettings['timeperiod_leaf_interval']))) {
            return true;
        }

        return false;
    }

}
