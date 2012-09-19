<?php
/*********************************************************************************
 * The contents of this file are subject to
 * *******************************************************************************/

/**
 * SidecarView.php
 *
 * This class extends SugarView to provide sidecar framework specific support.  Modules
 * that may wish to use the sidecar framework may extend this class to provide module
 * specific support.
 *
 * @see include/MVC/View/views/view.sidecar.php
 */

require_once('include/MVC/View/SugarView.php');

class SidecarView extends SugarView
{
    public $configFile = "config.js";

    /**
     * This method checks to see if the configuration file exists and, if not, creates one by default
     *
     */
    public function preDisplay()
    {
        if (!is_file($this->configFile))
        {
            $this->buildConfig();
        }

        $this->ss->assign("configFile", $this->configFile);

    }

    /**
     * This method sets the config file to use and renders the template
     *
     */
    public function display()
    {
        $this->ss->display(get_custom_file_if_exists('include/MVC/View/tpls/sidecar.tpl'));
    }

    /**
     * This method creates the config.js file sidecar to be used by the base platform
     *
     */
    protected function buildConfig(){
        global $sugar_config;
        $sidecarConfig = array(
            'appId' => 'SugarCRM',
            'env' => 'dev',
            'platform' => 'base',
            'additionalComponents' => array(
                'header' => array(
                    'target' => '#header'
                ),
                'footer' => array(
                    'target' => '#footer'
                ),
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
     * This method returns the theme specific CSS code to be used for the view
     *
     * @return string HTML formatted string of the CSS stylesheet files to use for view
     */
    public function getThemeCss()
    {
        $themeObject = SugarThemeRegistry::current();
        $html = '<link rel="stylesheet" type="text/css" href="'.$themeObject->getCSSURL('bootstrap.css').'" />';
        $html .= '<link rel="stylesheet" type="text/css" href="sidecar/lib/jquery-ui/css/smoothness/jquery-ui-1.8.18.custom.css" />';
        $html .= '<link href="http://fonts.googleapis.com/css?family=Open+Sans:300,400,600" rel="stylesheet" type="text/css">';
        return $html;
    }

}
