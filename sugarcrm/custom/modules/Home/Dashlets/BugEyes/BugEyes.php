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
 * $Id: JotPadDashlet.php,v 1.3 2006/08/22 21:31:42 wayne Exp $
 * Description: Handles the User Preferences and stores them in a seperate table. 
 * Portions created by SugarCRM are Copyright (C) SugarCRM, Inc.
 * All Rights Reserved.
 * Contributor(s): ______________________________________..
 ********************************************************************************/

require_once('include/Dashlets/Dashlet.php');
require_once('include/Sugar_Smarty.php');
class BugEyes extends Dashlet {

    /**
     * Constructor 
     * 
     * @global string current language
     * @param guid $id id for the current dashlet (assigned from Home module)
     * @param array $def options saved for this dashlet
     */
    function BugEyes($id, $def) {
        $this->loadLanguage('BugEyes', 'custom/modules/Home/Dashlets/'); // load the language strings here
        $this->id = $id;
        $this->title = $this->dashletStrings['LBL_TITLE'];
        parent::Dashlet($id); // call parent constructor
         
        //$this->isConfigurable = true; // dashlet is configurable
        $this->hasScript = true;  // dashlet has javascript attached to it
                
       
    }

    /**
     * Displays the dashlet
     * 
     * @return string html to display dashlet
     */
    function display() {
     
        $ss = new Sugar_Smarty();
        $ss->assign('Label', $this->dashletStrings);
        $ss->assign('id', $this->id);
        $str = $ss->fetch('custom/modules/Home/Dashlets/BugEyes/BugEyes.tpl');     
        return parent::display('') . $str; // return parent::display for title and such
    }
    
    /**
     * Displays the javascript for the dashlet
     * 
     * @return string javascript to use with this dashlet
     */
    function displayScript() {
        $ss = new Sugar_Smarty();
       	$str = $ss->fetch('custom/modules/Home/Dashlets/BugEyes/BugEyesScript.tpl');     
        return $str;
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
        $ss->assign('saveLbl', $app_strings['LBL_SAVE_BUTTON_LABEL']);
        $ss->assign('height', $this->height);
        $ss->assign('heightLbl', $this->dashletStrings['LBL_CONFIGURE_HEIGHT']);
        $ss->assign('displayOnStartupLbl', $this->dashletStrings['LBL_DISPLAY_ON_STARTUP']);
        $ss->assign('title', $this->title);
        $ss->assign('id', $this->id);
        $ss->assign('display_on_startup', $this->displayOnStartup);
        

        return parent::displayOptions() . $ss->fetch('custom/modules/Home/Dashlets/MapsDashlet/MapsDashletOptions.tpl');
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
         if(!empty($_REQUEST['display_on_startup'])) {
            $options['displayOnStartup'] = $_REQUEST['display_on_startup'];
        }
        else {
           $options['displayOnStartup'] = false;
        }
        
        if(is_numeric($_REQUEST['height'])) {
            if($_REQUEST['height'] > 0 && $_REQUEST['height'] <= 600) $options['height'] = $_REQUEST['height'];
            elseif($_REQUEST['height'] > 600) $options['height'] = '600';
            else $options['height'] = '100';            
        }

        return $options;
    }
    
    function lookup($number=false){
    	
    	if(empty($number) ){
    		$number = clean_string($_REQUEST['number'], 'NUMBER');
    	}
    	if(empty($type) ){
    		$type = clean_string($_REQUEST['type']);
    	}
    	if($type != 'bug' && $type != 'case'){
    		echo '';
    		return;
    	}
    	$fctype = ucfirst($type);
    	require_once('modules/' . $fctype . 's/'. $fctype . '.php');
    	switch($type){
    		case 'bug': $bug = new Bug(); $log = 'work_log';break;
    		case 'case': $bug = new aCase();$log = 'resolution';break;
    	}
    	$num_name = $type . '_number';
    	$bug->$num_name = $number;
    	$query = "SELECT id FROM $bug->table_name "; 
    	$bug->add_team_security_where_clause($query);
    	$query .= " WHERE $num_name='$number' AND deleted=0";
    	$result = $GLOBALS['db']->query($query);
    	$row = $GLOBALS['db']->fetchByAssoc($result);
    	if(!empty($row['id'])){
    		$bug->retrieve($row['id']);
    		
    	}
    	$bug->number = $bug->$num_name;
    	$bug->log = $bug->$log;
    	$ss = new Sugar_Smarty();
    	$ss->assign('APP', $GLOBALS['app_strings']);
    	$ss->assign('label', $this->dashletStrings);
    	$ss->assign('bug', $bug);
    	
    	$ss->assign('fctype', $fctype);
    	$ss->assign('type', $type);
    	echo $ss->fetch('custom/modules/Home/Dashlets/BugEyes/TabView.tpl');
    	
    	
    }


}

?>