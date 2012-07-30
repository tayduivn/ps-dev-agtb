<?php
/*********************************************************************************
 * The contents of this file are subject to
 * *******************************************************************************/
require_once('include/MVC/View/SugarView.php');

class ViewSidecar extends SugarView
{
    public $configFile = "config.js";
    /**
     * Constructor
     *
     * @see SugarView::SugarView()
     */
 	public function __construct()
 	{
        $this->options['show_title'] = false;
        $this->options['show_header'] = false;
        $this->options['show_footer'] = false;
        $this->options['show_javascript'] = false;
        $this->options['show_subpanels'] = false;
        $this->options['show_search'] = false;
 		parent::SugarView();
 	}

    public function display()
    {
        if (!is_file($this->configFile)) {
            $this->buildConfig();
        }
        $this->ss->assign("configFile", $this->configFile);
        $this->ss->display('include/MVC/View/tpls/sidecar.tpl');
    }

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
            'clientID' => 'sugar'
        );
        $configString = json_encode($sidecarConfig);
        $sidecarJSConfig = '(function(app) {app.augment("config", ' . $configString . ', false);})(SUGAR.App);';
        sugar_file_put_contents($this->configFile, $sidecarJSConfig);
    }
}
