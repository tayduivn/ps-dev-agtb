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
 *
 * $Id: JotPadDashlet.php 28381 2007-10-18 21:40:33Z bsoufflet $
 * Description: Handles the User Preferences and stores them in a separate table.
 * Portions created by SugarCRM are Copyright (C) SugarCRM, Inc.
 * All Rights Reserved.
 * Contributor(s): ______________________________________..
 ********************************************************************************/
//FILE SUGARCRM flav=int ONLY
require_once('include/Dashlets/Dashlet.php');


class GadgetDashlet extends Dashlet {

    /**
     * Constructor
     *
     * @global string current language
     * @param guid $id id for the current dashlet (assigned from Home module)
     * @param array $def options saved for this dashlet
     */
    function GadgetDashlet($id, $def) {
        $this->loadLanguage('GadgetDashlet'); // load the language strings here
		include('modules/Home/Dashlets/GadgetDashlet/GadgetDashletList.php');
		$this->gadgets = $googleGadgets;
        if(!empty($def['gadget']))  // load default text is none is defined
            $this->gadget = $def['gadget'];
        else
            $this->gadget = '';
        if(!empty($def['category']))  // load default text is none is defined
            $this->category = $def['category'];
        else
            $this->category = '';
        parent::Dashlet($id); // call parent constructor

        $this->isConfigurable = true; // dashlet is configurable
        $this->hasScript = true;  // dashlet has javascript attached to it

        // if no custom title, use default
        if(empty($def['title'])) $this->title = $this->dashletStrings['LBL_TITLE'];
        else $this->title = $def['title'];

        if(isset($def['autoRefresh'])) $this->autoRefresh = $def['autoRefresh'];
    }

    /**
     * Displays the dashlet
     *
     * @return string html to display dashlet
     */
    function display() {
        $ss = new Sugar_Smarty();
        $ss->assign('lang', $this->dashletStrings);
        $ss->assign('id', $this->id);
        if(!empty($this->gadgets[$this->category][$this->gadget])){
         	$ss->assign('gadget',$this->gadgets[$this->category][$this->gadget]);
        }
        $str = $ss->fetch('modules/Home/Dashlets/GadgetDashlet/GadgetDashlet.tpl');
        return parent::display($this->category . ' ' . $this->gadget) . $str . '<br />'; // return parent::display for title and such
    }

    /**
     * Displays the javascript for the dashlet
     *
     * @return string javascript to use with this dashlet
     */
    function displayScript() {
        $ss = new Sugar_Smarty();
        $ss->assign('lang', $this->dashletStrings);
        $ss->assign('id', $this->id);
                $json = getJSONobj();
         $ss->assign('gadgets', $json->encode($this->gadgets));
        $str = $ss->fetch('modules/Home/Dashlets/GadgetDashlet/GadgetDashletScript.tpl');
        return $str; // return parent::display for title and such
    }

    /**
     * Displays the configuration form for the dashlet
     *
     * @return string html to display form
     */
    function displayOptions() {
        global $app_strings;

        $ss = new Sugar_Smarty();
        $this->dashletStrings['LBL_SAVE'] = $app_strings['LBL_SAVE_BUTTON_LABEL'];
        $this->dashletStrings['LBL_CLEAR'] = $app_strings['LBL_CLEAR_BUTTON_LABEL'];
        $ss->assign('lang', $this->dashletStrings);
        $ss->assign('id', $this->id);
        $ss->assign('title', $this->title);
        $ss->assign('gadget', $this->gadget);
        $ss->assign('category', $this->category);
        if($this->isAutoRefreshable()) {
       		$ss->assign('isRefreshable', true);
			$ss->assign('autoRefresh', $GLOBALS['app_strings']['LBL_DASHLET_CONFIGURE_AUTOREFRESH']);
			$ss->assign('autoRefreshOptions', $this->getAutoRefreshOptions());
			$ss->assign('autoRefreshSelect', $this->autoRefresh);
		}

        $str = $ss->fetch('modules/Home/Dashlets/GadgetDashlet/GadgetDashletOptions.tpl');
        return parent::displayOptions() . $str;
    }

    /**
     * called to filter out $_REQUEST object when the user submits the configure dropdown
     *
     * @param array $req $_REQUEST
     * @return array filtered options to save
     */
    function saveOptions($req) {
        global $sugar_config, $timedate, $current_user, $theme;
        $options = array();
        $options['title'] = $_REQUEST['title'];
        $options['gadget'] = $_REQUEST['gadget'];
        $options['category'] = $_REQUEST['category'];
        $options['autoRefresh'] = empty($req['autoRefresh']) ? '0' : $req['autoRefresh'];
        return $options;
    }



}

?>
