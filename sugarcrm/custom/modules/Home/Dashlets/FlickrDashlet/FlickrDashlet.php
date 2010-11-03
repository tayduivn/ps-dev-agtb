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
 * $Id$
 * Description: Handles the User Preferences and stores them in a seperate table. 
 * Portions created by SugarCRM are Copyright (C) SugarCRM, Inc.
 * All Rights Reserved.
 * Contributor(s): ______________________________________..
 ********************************************************************************/

require_once('include/Dashlets/Dashlet.php');
require_once('include/Sugar_Smarty.php');

class FlickrDashlet extends Dashlet {
    var $tags = 'dogs'; // tags

    /**
     * Constructor 
     * 
     * @global string current language
     * @param guid $id id for the current dashlet (assigned from Home module)
     * @param array $def options saved for this dashlet
     */
    function FlickrDashlet($id, $def) {
        $this->loadLanguage('FlickrDashlet'); // load the language strings here

        if(!empty($def['tags'])) // set a default height if none is set
            $this->tags = $def['tags'];

        parent::Dashlet($id); // call parent constructor
         
        $this->isConfigurable = true; // dashlet is configurable
        $this->hasScript = true;  // dashlet has javascript attached to it
                
        // if no custom title, use default
        if(empty($def['title'])) $this->title = $this->dashletStrings['LBL_TITLE'];
        else $this->title = $def['title'];        
    }

    /**
     * Displays the dashlet
     * 
     * @return string html to display dashlet
     */
    function display() {
        $ss = new Sugar_Smarty();
        $ss->assign('id', $this->id);
        $ss->assign('tags', $this->tags);
          
        $str = $ss->fetch('custom/modules/Home/Dashlets/FlickrDashlet/FlickrDashlet.tpl');     
        return parent::display($this->dashletStrings['LBL_HELP']) . $str; // return parent::display for title and such
    }
    
    /**
     * Displays the javascript for the dashlet
     * 
     * @return string javascript to use with this dashlet
     */
    function displayScript() {
        $ss = new Sugar_Smarty();

        $ss->assign('id', $this->id);
        
        $str = $ss->fetch('custom/modules/Home/Dashlets/FlickrDashlet/FlickrDashletScript.tpl');     
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
        $ss->assign('titleLbl', $this->dashletStrings['LBL_CONFIGURE_TITLE']);
        $ss->assign('tagsLbl', $this->dashletStrings['LBL_CONFIGURE_TAGS']);
        $ss->assign('saveLbl', $app_strings['LBL_SAVE_BUTTON_LABEL']);
        $ss->assign('title', $this->title);
        $ss->assign('tags', $this->tags);
        $ss->assign('id', $this->id);

        return parent::displayOptions() . $ss->fetch('custom/modules/Home/Dashlets/FlickrDashlet/FlickrDashletOptions.tpl');
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
        $options['tags'] = $_REQUEST['tags'];
        return $options;
    }

    /**
     * Used to save text on textarea blur. Accessed via Home/CallMethodDashlet.php
     * This is an example of how to to call a custom method via ajax
     */    
    function getFeed() {
        $tags = $_REQUEST['tags'];
        $url = 'http://api.flickr.com/services/feeds/photos_public.gne?tags=' . $tags . '&format=json';
        $page = '';
        $fh = fopen($url, 'r');
        while(!feof($fh)) {
            $page .= fread($fh, 1048576);
        }
        fclose($fh);

        $optionsArray = $this->loadOptions();
        $optionsArray['feedArray'] = $page;
        $this->storeOptions($optionsArray);
        echo 'dashletId = "' . $this->id . '";';
        echo $page . ';';
        
    }
}

?>
