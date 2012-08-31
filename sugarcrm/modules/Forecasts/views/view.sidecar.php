<?php
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

require_once('modules/Forecasts/api/ForecastsFiltersApi.php');
require_once('modules/Forecasts/api/ForecastsChartApi.php');

class ForecastsViewSidecar extends SidecarView
{
    function __construct($bean = null, $view_object_map = array())
    {
        $this->options['show_footer'] = false;
        $this->options['show_subpanels'] = false;
        $this->options['show_search'] = false;
        $this->options['use_table_container'] = false;
        parent::__construct($bean = null, $view_object_map = array());
    }

    /**
     * Override the display method to set Forecasts specific variables and use a custom layout template
     *
     */
    function display()
    {
        global $current_user, $sugar_config;
        $forecastInitData = $this->forecastsInitialization();

        // begin initializing all default params
        $this->ss->assign("initData" , json_encode($forecastInitData));
        $this->ss->assign("token", session_id());
        $this->ss->assign("module", $this->module);
        $this->ss->display(get_custom_file_if_exists("modules/Forecasts/tpls/SidecarView.tpl"));
    }

    /**
     * Returns an Array of initial default data settings for Forecasts module
     *
     * @return array Array of initial default data for Forecasts module
     */
    function forecastsInitialization() {
        global $current_user, $app_list_strings;

        $returnInitData = array();
        $defaultSelections = array();

        require_once('modules/Forecasts/api/ForecastsCurrentUserApi.php');
        $forecastsCurrentUserApi = new ForecastsCurrentUserApi();
        $data = $forecastsCurrentUserApi->retrieveCurrentUser($forecastsCurrentUserApi,array());
        $selectedUser = $data["current_user"];

        $returnInitData["initData"]["selectedUser"] = $selectedUser;
        $defaultSelections["selectedUser"] = $selectedUser;

        $forecasts_timeframes_dom = TimePeriod::get_not_fiscal_timeperiods_dom();
        // TODO:  These should probably get moved in with the config/admin settings, or by themselves since this file will probably going away.
        $id = TimePeriod::getCurrentId();
        $defaultSelections["timeperiod_id"]["id"] = $id;
        $defaultSelections["timeperiod_id"]["label"] = $forecasts_timeframes_dom[$id];

        // INVESTIGATE:  these need to be more dynamic and deal with potential customizations based on how filters are built in admin and/or studio
        $defaultSelections["category"] = array("70");
        $defaultSelections["group_by"] = 'forecast';
        $defaultSelections["dataset"] = 'likely';

        // push in defaultSelections
        $returnInitData["defaultSelections"] = $defaultSelections;

        return $returnInitData;
    }

    /**
     * Override the buildConfig method to create a config.js file with specific settings for Forecasts.
     * Todo: This method will need to be removed in the future when everything shifts to the base platform
     */
    protected function buildConfig(){
        global $sugar_config;
        $sidecarConfig = array(
            'appId' => 'SugarCRM',
            'env' => 'dev',
            'platform' => 'base',
            'additionalComponents' => array(
                'alert' => array(
                    'target' => '#alert'
                )
            ),
            'serverUrl' => $sugar_config['site_url'].'/rest/v10',
            'unsecureRoutes' => array('login', 'error'),
            'clientID' => 'sugar',
            'authStore'  => 'sugarAuthStore',
            'keyValueStore' => 'sugarAuthStore'
        );
        $configString = json_encode($sidecarConfig);
        $sidecarJSConfig = '(function(app) {app.augment("config", ' . $configString . ', false);})(SUGAR.App);';
        sugar_file_put_contents($this->configFile, $sidecarJSConfig);
    }


    /**
     * Override the _displayJavascript function to output sidecar libraries for this view
     * Todo: Change to use minified libraries or at least allow for some way (developerMode?) to switch to non-minified
     */
    public function _displayJavascript()
    {
        parent::_displayJavascript();

echo "<script src='sidecar/lib/jquery-ui/js/jquery-ui-1.8.18.custom.min.js'></script>
<script src='sidecar/lib/backbone/underscore.js'></script>
<script src='sidecar/lib/backbone/backbone.js'></script>
<script src='sidecar/lib/handlebars/handlebars-1.0.0.beta.6.js'></script>
<script src='sidecar/lib/stash/stash.js'></script>
<script src='sidecar/lib/async/async.js'></script>
<script src='sidecar/lib/chosen/chosen.jquery.js'></script>
<script src='sidecar/lib/sinon/sinon.js'></script>
<script src='sidecar/lib/sugarapi/sugarapi.js'></script>
<script src='sidecar/src/app.js'></script>
<script src='sidecar/src/utils/utils.js'></script>
<script src='sidecar/src/utils/date.js'></script>
<script src='sidecar/src/core/cache.js'></script>
<script src='sidecar/src/core/events.js'></script>
<script src='sidecar/src/core/error.js'></script>
<script src='sidecar/src/view/template.js'></script>
<script src='sidecar/src/core/context.js'></script>
<script src='sidecar/src/core/controller.js'></script>
<script src='sidecar/src/core/router.js'></script>
<script src='sidecar/src/core/language.js'></script>
<script src='sidecar/src/core/metadata-manager.js'></script>
<script src='sidecar/src/core/acl.js'></script>
<script src='sidecar/src/core/user.js'></script>
<script src='sidecar/src/utils/logger.js'></script>
<script src='sidecar/src/data/bean.js'></script>
<script src='sidecar/src/data/bean-collection.js'></script>
<script src='sidecar/src/data/data-manager.js'></script>
<script src='sidecar/src/data/validation.js'></script>
<script src='sidecar/src/view/hbt-helpers.js'></script>
<script src='sidecar/src/view/view-manager.js'></script>
<script src='sidecar/src/view/component.js'></script>
<script src='sidecar/src/view/view.js'></script>
<script src='sidecar/src/view/field.js'></script>
<script src='sidecar/src/view/layout.js'></script>
<script src='sidecar/src/view/alert.js'></script>";

    }

}