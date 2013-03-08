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
 */

require_once('include/MVC/View/SugarView.php');
require_once('include/SugarTheme/SidecarTheme.php');

class SidecarView extends SugarView
{
    protected $configFile = "config.js";

    /**
     * This method checks to see if the configuration file exists and, if not, creates one by default
     *
     */
    public function preDisplay()
    {
        global $app_strings;

        //Rebuild config file if it doesn't exist
        if(!file_exists('config.js')) {
           require_once('install/install_utils.php');
           handleSidecarConfig();
        }
        $this->ss->assign("configFile", $this->configFile);

        $sugarSidecarPath = sugar_cached("include/javascript/sugar_sidecar.min.js");
        $this->ss->assign("sugarSidecarPath", $sugarSidecarPath);
        //If the files doesn't exist, it probably means the cache has been nuked and we need to rebuild.
        if (!is_file($sugarSidecarPath))
        {
            $_REQUEST['root_directory'] = ".";
            require_once("jssource/minify_utils.php");
            ConcatenateFiles(".");
        }

        // TODO: come up with a better way to deal with the various JS files
        // littered in sidecar.tpl.
        $voodooFile = 'custom/include/javascript/voodoo.js';
        if (SugarAutoLoader::fileExists($voodooFile)) {
            $this->ss->assign('voodooFile', $voodooFile);
        }

        //Load sidecar theme css
        $theme = new SidecarTheme();
        $this->ss->assign("css_url", $theme->getCSSURL());
        $this->ss->assign("developerMode", inDeveloperMode());

        //Loading label
        $this->ss->assign('LBL_LOADING', $app_strings['LBL_ALERT_TITLE_LOADING']);

        $slFunctionsPath = inDeveloperMode() ? "cache/Expressions/functions_cache_debug.js" : "cache/Expressions/functions_cache.js";
        if (!is_file($slFunctionsPath)) {
            $GLOBALS['updateSilent'] = true;
            include("include/Expressions/updatecache.php");
        }
        $this->ss->assign("SLFunctionsPath", $slFunctionsPath);
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
     * This method returns the theme specific CSS code to be used for the view
     *
     * @return string HTML formatted string of the CSS stylesheet files to use for view
     */
    public function getThemeCss()
    {
        // this is left empty since we are generating the CSS via the API
    }

    protected function _initSmarty()
    {
        $this->ss = new Sugar_Smarty();
        // no app_strings and mod_strings needed for sidecar
    }
}
