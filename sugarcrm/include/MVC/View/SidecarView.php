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
    protected $configFile = "config.js";

    /**
     * This method checks to see if the configuration file exists and, if not, creates one by default
     *
     */
    public function preDisplay()
    {
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
     * This method returns the theme specific CSS code to be used for the view
     *
     * @return string HTML formatted string of the CSS stylesheet files to use for view
     */
    public function getThemeCss()
    {
        // this is left empty since we are generating the CSS via the API
    }

}
