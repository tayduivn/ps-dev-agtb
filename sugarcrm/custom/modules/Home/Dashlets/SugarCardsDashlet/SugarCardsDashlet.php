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
 * Description: Handles the User Preferences and stores them in a seperate table. 
 * Portions created by SugarCRM are Copyright (C) SugarCRM, Inc.
 * All Rights Reserved.
 * Contributor(s): ______________________________________..
 ********************************************************************************/

require_once('include/Dashlets/Dashlet.php');
require_once('include/Sugar_Smarty.php');

class SugarCardsDashlet extends Dashlet {
	static $start = 1;
	static $end = 49;
    /**
     * Constructor 
     * 
     * @global string current language
     * @param guid $id id for the current dashlet (assigned from Home module)
     * @param array $def options saved for this dashlet
     */
    function SugarCardsDashlet($id, $def) {
        $this->loadLanguage('SugarCardsDashlet','custom/modules/Home/Dashlets/'); // load the language strings here
        parent::Dashlet($id); // call parent constructor
        $this->isConfigurable = false; // dashlet is configurable
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
        $ss->assign('img', SugarCardsDashlet::getImage());
        $ss->assign('id', $this->id); 
        $checked = '';
        $disableSplashScreen = $GLOBALS['current_user']->getPreference('disableSplashScreen');
        if(empty($disableSplashScreen)){
        	$checked = "CHECKED";
        }
        $ss->assign('next', get_image($GLOBALS['image_path'] . '/' .'next', 'border="0" align="absmiddle"'));
        $ss->assign('prev', get_image($GLOBALS['image_path'] . '/' .'previous', 'border="0" align="absmiddle"'));
        $ss->assign('start', get_image($GLOBALS['image_path'] . '/' .'start', 'border="0" align="absmiddle"'));
        $ss->assign('end', get_image($GLOBALS['image_path'] . '/' .'end', 'border="0" align="absmiddle"'));
        $ss->assign('endIndex',  SugarCardsDashlet::$end -  SugarCardsDashlet::$start + 1);
        $ss->assign('checked', $checked);
        $str = $ss->fetch('custom/modules/Home/Dashlets/SugarCardsDashlet/SugarCardsDashlet.tpl');     
        return parent::display() . $str . '<br />'; // return parent::display for title and such
    }
    
    function displayScript() {
        $ss = new Sugar_Smarty();
        $ss->assign('lang', $this->dashletStrings);
        $ss->assign('id', $this->id);
        $str = $ss->fetch('custom/modules/Home/Dashlets/SugarCardsDashlet/SugarCardsDashletScript.tpl');     
        return $str; // return parent::display for title and such
    }
    
    function getImage(){
    	$path = 'custom/modules/Home/Dashlets/SugarCardsDashlet/cards/';
    	
    	if(isset($_REQUEST['number'])){
    		$img = $_REQUEST['number'] + SugarCardsDashlet::$start - 1;
    		if($img < 10 && strlen($img) == 1) $img = '0' . $img;
    		if($img < SugarCardsDashlet::$start)$img = SugarCardsDashlet::$end;
    		if(!file_exists('custom/modules/Home/Dashlets/SugarCardsDashlet/cards/' . $img . '.jpg')){
    			$img = '05';
    		}
    	}
    	if(empty($img)){
    		$img = mt_rand(SugarCardsDashlet::$start, SugarCardsDashlet::$end);
    		if($img < 10) $img = '0' . $img;
    	}
    	
    
    	return array('image'=>$path . $img . '.jpg', 'number'=>$img - SugarCardsDashlet::$start + 1);
    }
    
    function newImage(){
    	ob_clean();
    	$img = SugarCardsDashlet::getImage();
    	echo $img['image'];
    	sugar_cleanup(true);
    	
    }
    
    function getScreen($url){
	 $disableSplashScreen = $GLOBALS['current_user']->getPreference('disableSplashScreen');
	 if(!$disableSplashScreen){
	 		$img = SugarCardsDashlet::getImage();
	 		$image = $img['image'];
    		echo '<head><title>Break Away</title><link rel="stylesheet" type="text/css" href="themes/Sugar/navigation.css" /><link rel="stylesheet" type="text/css" href="themes/Sugar/style.css" /><link rel="stylesheet" type="text/css" href="themes/Sugar/colors.sugar.css" id="current_color_style" /><link rel="stylesheet" type="text/css" href="themes/Sugar/fonts.normal.css" id="current_font_style"/></head><body style="background-color:#333333;cursor: pointer; cursor: hand;" onclick=\'document.location="'. $url. '";\'><div  align="center" style="position:relative;top:25px"><table><tr><td colspan="2" align="center"><button class="button"><H3>Click To Continue</H3></button><br><br><img src="'. $image . '" onclick=\'document.location="'. $url. '";\'></td></tr><tr><td colspan="2" align="right"><span id="dots"></span></td></tr></table>';
			echo '<br><script>var count = 25; function updateDots(){count--; if(count==1){document.location="'. $url. '";}document.getElementById("dots").innerHTML= count; setTimeout("updateDots();", 1000);}updateDots();</script></div>';
			sugar_cleanup(true);
    	}
    
    }
    
    function changeSplashScreen(){
		$disable = empty($_REQUEST['checked']); 
    	$GLOBALS['current_user']->setPreference('disableSplashScreen', $disable);
    }
    


}

?>
