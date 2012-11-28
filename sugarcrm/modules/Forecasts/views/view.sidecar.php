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

require_once('modules/Forecasts/clients/base/api/ForecastsFiltersApi.php');
require_once('modules/Forecasts/clients/base/api/ForecastsChartApi.php');
require_once("include/SugarTheme/SidecarTheme.php");

class ForecastsViewSidecar extends SidecarView
{
    public function __construct($bean = null, $view_object_map = array())
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
    public function display()
    {
        //Load sidecar theme css
        $theme = new SidecarTheme();
        $this->ss->assign("css_url", getVersionedPath($theme->getCSSURL()));

        $module = $this->module;
        $displayTemplate = SugarAutoLoader::existingCustomOne("modules/Forecasts/tpls/SidecarView.tpl");

        // begin initializing all default params
        $this->ss->assign("token", session_id());
        $this->ss->assign("module", $module);
        $this->ss->display($displayTemplate);
    }

    /**
     * Returns an Array of initial default data settings for Forecasts module
     *
     * @param bool $returnOnlyUserData skip all the other initial data?
     * @return array Array of initial default data for Forecasts module
     */
    public function forecastsInitialization($returnOnlyUserData=false) {
        global $current_user, $app_list_strings;

        $returnInitData = array();

        return $returnInitData;

        $defaultSelections = array();

        require_once('modules/Forecasts/clients/base/api/ForecastsCurrentUserApi.php');
        $forecastsCurrentUserApi = new ForecastsCurrentUserApi();
        $data = $forecastsCurrentUserApi->retrieveCurrentUser($forecastsCurrentUserApi,array());
        $selectedUser = $data["current_user"];
        $returnInitData["initData"]["selectedUser"] = $selectedUser;
        $defaultSelections["selectedUser"] = $selectedUser;

        if(!$returnOnlyUserData) {
            $forecasts_timeframes_dom = TimePeriod::get_not_fiscal_timeperiods_dom();
            // TODO:  These should probably get moved in with the config/admin settings, or by themselves since this file will probably going away.
            $id = TimePeriod::getCurrentId();
            $defaultSelections["timeperiod_id"]["id"] = $id ? $id : '';
            $defaultSelections["timeperiod_id"]["label"] = $id ? $forecasts_timeframes_dom[$id] : '';

            // INVESTIGATE:  these need to be more dynamic and deal with potential customizations based on how filters are built in admin and/or studio
            $admin = BeanFactory::getBean("Administration");
            $forecastsSettings = $admin->getConfigForModule("Forecasts", "base");
            $defaultSelections["category"] = array("include");
            $defaultSelections["group_by"] = 'forecast';
            $defaultSelections["dataset"] = 'likely';
        }
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
                    'target' => '#alerts'
                )
            ),
            'serverUrl' => $sugar_config['site_url'].'/rest/v10',
            'siteUrl' => $sugar_config['site_url'],
            'loadCss' => false,
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

        /**
         * load js files for sidecar forecasts
         * @see jssource/JSGroupings.php - $sidecar_forecasts
         * @see sidecar/src/include-manifest.php - files defined for sidecar
         * it will be better if we load sidecar.min.js
         * but it (sidecar.min.js) loads jquery library that is loaded and extended already in sugar_grp1_jquery.js -
         * so in this case we have errors on the page
         */
        if ( !inDeveloperMode() )
        {   
            /*
             * This section saves off the system jQuery into a var so that the sidecar version can be loaded
             * into $.  Then, we extend all the plugins that were loaded in the system version to the sidecar
             * version so everything plays nice.
             */
            echo "<script type='text/javascript'>var systemJq = jQuery.noConflict(true);</script>\n";
            echo "<script type='text/javascript' src='sidecar/minified/sidecar.min.js'></script>\n";
            echo "<script type='text/javascript'>$.extend($.fn, systemJq.fn);</script>\n";
                        
            if  ( !is_file(sugar_cached("include/javascript/sidecar_forecasts.js")) ) {
                $_REQUEST['root_directory'] = ".";
                require_once("jssource/minify_utils.php");
                ConcatenateFiles(".");
            }
            echo getVersionedScript('cache/include/javascript/sidecar_forecasts.js') . "\n";

        } else {

            require('sidecar/src/include-manifest.php');
            if (!empty($buildFiles['sidecar']))
            {
                /*
                 * This section saves off the system jQuery into a var so that the sidecar version can be loaded
                 * into $.  Then, we extend all the plugins that were loaded in the system version to the sidecar
                 * version so everything plays nice.
                 */
                echo "<script type='text/javascript'>var systemJq = jQuery.noConflict(true);</script>\n";
                foreach ( $buildFiles['sidecar'] as $file)
                {
                    echo "<script type='text/javascript' src='sidecar/{$file}'></script>\n";
                }
                echo "<script type='text/javascript'>$.extend($.fn, systemJq.fn);</script>\n";
            }

            echo "<script type='text/javascript' src='include/javascript/jquery/jquery.dataTables.min.js'></script>\n";
            echo "<script type='text/javascript' src='include/javascript/jquery/jquery.jeditable.js'></script>\n";
            echo "<script type='text/javascript'>jQuery.noConflict();</script>\n";

            require_once('jssource/JSGroupings.php');
            if ( !empty($sidecar_forecasts) && is_array($sidecar_forecasts) )
            {
                foreach ( $sidecar_forecasts as $_file => $dist )
                {
                    echo "<script src='".$_file."'></script>\n";
                }
            }
        }
    }

}