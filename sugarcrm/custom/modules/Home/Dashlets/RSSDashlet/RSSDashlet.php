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
 * $Id: RSSDashlet.php,v 1.1 2006/10/11 00:53:31 clint Exp $
 * Description: Handles the User Preferences and stores them in a seperate table. 
 * Portions created by SugarCRM are Copyright (C) SugarCRM, Inc.
 * All Rights Reserved.
 * Contributor(s): ______________________________________..
 ********************************************************************************/

require_once('include/Dashlets/Dashlet.php');
require_once('include/Sugar_Smarty.php');

class RSSDashlet extends Dashlet {
    var $rss_urls = array('http://rss.news.yahoo.com/rss/topstories','http://www.sugarcrm.com/crm/index2.php?no_html=1&task=returnRSS&option=com_rss_feed_manager&channel=Corporate&stype=rss20');
    var $height = '200'; // height of the pad
    var $auto_scroll = true;
    var $images_dir = 'custom/modules/Home/Dashlets/RSSDashlet/images';
     var $scroll_speed = 50;

    /**
     * Constructor 
     * 
     * @global string current language
     * @param guid $id id for the current dashlet (assigned from Home module)
     * @param array $def options saved for this dashlet
     */
    function RSSDashlet($id, $def) {
        $this->loadLanguage('RSSDashlet', 'custom/modules/Home/Dashlets/'); // load the language strings here
            
        if(!empty($def['height'])) // set a default height if none is set
            $this->height = $def['height'];
            
        if(!empty($def['rss_urls']))
            $this->rss_urls = $def['rss_urls'];
        
        if(!empty($def['auto_scroll']))
            $this->auto_scroll = $def['auto_scroll'];
         else
            $this->auto_scroll = false;
            
         if(isset($def['scroll_speed']))
            $this->scroll_speed = $def['scroll_speed'];
         else
            $this->scroll_speed = 50;

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
        //$ss->assign('rss_url', $this->rss_url);
        $ss->assign('saving', $this->dashletStrings['LBL_SAVING']);
        $ss->assign('saved', $this->dashletStrings['LBL_SAVED']);
        $ss->assign('id', $this->id);
        $ss->assign('height', $this->height);
        $ss->assign('auto_scroll', $this->auto_scroll);
        $output = "";
        foreach($this->rss_urls as $rss_url){
            if(!empty($rss_url)){
                $output .=   $this->getRSSOutput($rss_url);
            }
        }
        $ss->assign('rss_output', $output); 
        $ss->assign('scroll_speed', $this->scroll_speed);
        $str = $ss->fetch('custom/modules/Home/Dashlets/RSSDashlet/RSSDashlet.tpl');
        $this->displayScript();     
        return parent::display($this->dashletStrings['LBL_DBLCLICK_HELP']) . $str; // return parent::display for title and such
    }
    
    /**
     * Displays the javascript for the dashlet
     * 
     * @return string javascript to use with this dashlet
     */
    function displayScript() {
        $ss = new Sugar_Smarty();
        $ss->assign('saving', $this->dashletStrings['LBL_SAVING']);
        $ss->assign('saved', $this->dashletStrings['LBL_SAVED']);
        $ss->assign('id', $this->id);
        
        $str = $ss->fetch('custom/modules/Home/Dashlets/RSSDashlet/RSSDashletScript.tpl');     
        return $str; // return parent::display for title and such
    }
        
    /**
     * Displays the configuration form for the dashlet
     * 
     * @return string html to display form
     */
    function displayOptions() {
        global $app_strings, $sugar_version, $sugar_config;
        
        $ss = new Sugar_Smarty();
        $ss->assign('titleLbl', $this->dashletStrings['LBL_CONFIGURE_TITLE']);
        $ss->assign('heightLbl', $this->dashletStrings['LBL_CONFIGURE_HEIGHT']);
        $ss->assign('rssUrlLbl', $this->dashletStrings['LBL_CONFIGURE_RSSURL']);
        $ss->assign('autoScrollLbl', $this->dashletStrings['LBL_AUTO_SCROLL']);
        $ss->assign('scrollSpeedLbl', $this->dashletStrings['LBL_SCROLL_SPEED']);
        $ss->assign('saveLbl', $app_strings['LBL_SAVE_BUTTON_LABEL']);
        $ss->assign('title', $this->title);
        $ss->assign('height', $this->height);
        $ss->assign('auto_scroll', $this->auto_scroll);
        $ss->assign('rss_urls', $this->rss_urls);
        $ss->assign('id', $this->id);
        $ss->assign('images_dir', $this->images_dir);
        $ss->assign('scroll_speed', $this->scroll_speed);
        $ss->assign('sugar_version', $sugar_version);
        $ss->assign('js_custom_version', $sugar_config['js_custom_version']);
        
        return parent::displayOptions() . $ss->fetch('custom/modules/Home/Dashlets/RSSDashlet/RSSDashletOptions.tpl');
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
        
         if(!empty($_REQUEST['horizVal'])) {
            $options['scroll_speed'] = $_REQUEST['horizVal'];
        }
        else {
           $options['scroll_speed'] = 0;
        }
        $GLOBALS['log']->debug("DENE".$_REQUEST['horizVal']);
         if(!empty($_REQUEST['auto_scroll'])) {
            $options['auto_scroll'] = $_REQUEST['auto_scroll'];
        }
        else {
           $options['auto_scroll'] = false;
        }

        $options['rss_urls'] = br2nl($_REQUEST['rss_urls']);
        if(is_numeric($_REQUEST['height'])) {
            if($_REQUEST['height'] > 0 && $_REQUEST['height'] <= 300) $options['height'] = $_REQUEST['height'];
            elseif($_REQUEST['height'] > 300) $options['height'] = '300';
            else $options['height'] = '100';            
        }
         
        return $options;
    }
    
    function getRSSOutput($url){
        require_once('include/domit_rss/xml_domit_rss_lite.php');
        //instantiate rss document
        $cacheDir = './cache/feeds/';
        $cacheTime = 3600;
        
        $rssdoc = new xml_domit_rss_document_lite($url, $cacheDir, $cacheTime);
        
        //get total number of channels
        $totalChannels = $rssdoc->getChannelCount();
        
        $output = "<html><head><title>RSS Test</title></head><body><table class='tabForm'>";
        //loop through each channel
        for ($i = 0; $i < $totalChannels; $i++) {
            //get reference to current channel
            $currChannel = $rssdoc->getChannel($i);
            
            //echo channel info
            $output .= "<tr><td class='dataLabel'><a href=\"" . $currChannel->getLink() . "\" target=\"_child\">" . 
                               $currChannel->getTitle() . "</a>";
             $output .= "  " . $currChannel->getDescription() . "</td></tr>";
            
            //get total number of items
            $totalItems = $currChannel->getItemCount();
            
            //loop through each item
            for ($j = 0; $j < $totalItems; $j++) {
                //get reference to current item
                $currItem = $currChannel->getItem($j);
            
                //echo item info
                 $output .= "<tr><td class='dataLabel'><a href=\"" . $currItem->getLink() . "\" target=\"_child\">" . 
                        $currItem->getTitle() . "</a> " . $currItem->getDescription() . "</td></tr>";
            
            }
        }
        
         //$output .= $rssdoc->toNormalizedString(true);
         $output .= "</table></body></html>";
         return $output;
    }
    

}
?>
