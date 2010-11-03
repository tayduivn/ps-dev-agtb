<?php
if (!defined('sugarEntry') || !sugarEntry)
    die('Not A Valid Entry Point');
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
 * $Id: QuickCreateDashlet.php,v 1.1 2006/10/11 00:53:31 clint Exp $
 * Description: Handles the User Preferences and stores them in a seperate table. 
 * Portions created by SugarCRM are Copyright (C) SugarCRM, Inc.
 * All Rights Reserved.
 * Contributor(s): ______________________________________..
 ********************************************************************************/

require_once ('include/Dashlets/Dashlet.php');
require_once ('include/Sugar_Smarty.php');

class QuickCreateDashlet extends Dashlet {

    var $qcmodule = false;
    var $detailviewonsave = 0;
    var $moduleList = array ();
    /**
     * Constructor 
     * 
     * @global string current language
     * @param guid $id id for the current dashlet (assigned from Home module)
     * @param array $def options saved for this dashlet
     */
    function QuickCreateDashlet($id, $def) {
		
        $this->loadLanguage('QuickCreateDashlet', 'custom/modules/Home/Dashlets/'); // load the language strings here
        $this->loadQuickCreateModules();
        if (!empty ($def['detailviewonsave']))
            $this->detailviewonsave = $def['detailviewonsave'];
        if (!empty ($def['qcmodule']))
         	 $this->qcmodule = $def['qcmodule'];

        parent :: Dashlet($id); // call parent constructor

        $this->isConfigurable = true; // dashlet is configurable
        $this->hasScript = true; // dashlet has javascript attached to it
        // if no custom title, use default
        $this->title = (!empty ($def['title'])) ? $def['title'] : $this->dashletStrings['TITLE'];
    }

	function filterModuleList(){
		if(isset($this->moduleList['module'])){
			foreach($this->moduleList['module'] as $md=>$module){
				if(!((!empty($GLOBALS['modListHeader'][$module]) || 
				    ((!empty($GLOBALS['modListHeader']['Calendar']) 
				    	|| !empty($GLOBALS['modListHeader']['Activities'])) 
				    	&& in_array($module,$GLOBALS['modInvisListActivities']))) && ACLController::checkAccess($module, 'edit', true)
				 )){
				 		unset($this->moduleList['module'][$md]);
				 		unset($this->moduleList['class'][$md]);
				 		unset($this->moduleList['path'][$md]);
				 	} 
			}
		}
	}
	
	
	
    function loadQuickCreateModules($refresh = true) {
        $quickCreateModule = array ();
		
        if (!$refresh && file_exists('cache/QCModules.php')) {
            include ('cache/QCModules.php');

        }
        if (empty ($quickCreateModule)) {
        	$allModules = array_merge($GLOBALS['moduleList'] ,$GLOBALS['modInvisListActivities']); 
            foreach ($allModules as $module) {
            	
                $dir = dir('modules/' . $module);
                if (file_exists('modules/' . $module . '/tpls/QuickCreate.tpl')) {
                    while ($entry = $dir->read()) {
                        if (strpos(strtolower($entry), 'quickcreate')) {
                            $path = 'modules/' . $module . '/' . $entry;
                            $md = md5($path);
                            $quickCreateModule['module'][$md] = $module;
                            $quickCreateModule['path'][$md] = $path;
                            $quickCreateModule['class'][$md] = str_replace('.php', '', $entry);
                        }
                    }
                }
            }
           
            write_array_to_file('quickCreateModule', $quickCreateModule, 'cache/QCModules.php');
        }
            $this->moduleList = $quickCreateModule;
            $this->filterModuleList();
        }
        /**
         * Displays the dashlet
         * 
         * @return string html to display dashlet
         */
        function display() {

            $ss = new Sugar_Smarty();
            $ss->assign('saving', $this->dashletStrings['SAVING']);
            $ss->assign('saved', $this->dashletStrings['SAVED']);
            $ss->assign('id', $this->id);
            $ss->assign('LBL', $this->dashletStrings);
           
           $str =  $this->loadForm();
           $prestr = '';
           if(empty($str)){
                $str = $ss->fetch('custom/modules/Home/Dashlets/QuickCreateDashlet/QuickCreate.tpl');
            }else{
            	$ss->assign('moduleList', $this->moduleList['module']);
            	$ss->assign('qcmodule', $this->qcmodule);
            	$ss->assign('detailviewonsave', $this->detailviewonsave);
            	$ss->assign('id', $this->id);
            	$prestr  = $ss->fetch('custom/modules/Home/Dashlets/QuickCreateDashlet/QuickSwitch.tpl');	
           }

            return parent :: display($prestr). '<span id="quickcreatedash_' . $this->id . '">'. $str .'</span>'; // return parent::display for title and such
        }
        
        function loadForm(){
        	$str = '';
        	//fill qcmodule with the first module available if one is not set
        	if(empty($this->qcmodule) && !empty($this->moduleList['module'])){
        			$keys = array_keys($this->moduleList['module']);
        			$this->qcmodule = $keys[0];
        	}
        	if($this->qcmodule && !empty ($this->moduleList['path'][$this->qcmodule])) {
                $old_modStrings = $GLOBALS['mod_strings'];
                require_once ($this->moduleList['path'][$this->qcmodule]);
                $module = $this->moduleList['module'][$this->qcmodule];
                $class = $this->moduleList['class'][$this->qcmodule];
                
                $qc = new $class ($module, 'modules/' . $module . '/tpls/QuickCreate.tpl');
                $qc->viaAJAX = true;
                $qc->process();
                $str = $qc->display();
                $GLOBALS['mod_strings'] = $old_modStrings;
                $str .= '</table>';
                
                
        	}
        	$str = str_replace('SUGAR.subpanelUtils.inlineSave(' , 'QuickCreateDash.inlineSave("' . $this->id . '",', $str);
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
            $ss->assign('LBL', $this->dashletStrings);
            $ss->assign('title', $this->title);

            $ss->assign('moduleList', $this->moduleList['module']);
            $ss->assign('qcmodule', $this->qcmodule);
            $ss->assign('detailviewonsave', $this->detailviewonsave);
            $ss->assign('id', $this->id);

            return parent :: displayOptions() . $ss->fetch('custom/modules/Home/Dashlets/QuickCreateDashlet/QuickCreateOptions.tpl');
        }
        
     /** Displays the javascript for the dashlet
     * 
     * @return string javascript to use with this dashlet
     */
    function displayScript() {
        $ss = new Sugar_Smarty();
        $ss->assign('LBL', $this->dashletStrings);
        $ss->assign('id', $this->id);
        
        $str = $ss->fetch('custom/modules/Home/Dashlets/QuickCreateDashlet/QuickCreateScript.tpl');     
        return $str; // return parent::display for title and such
    }

        /**
         * called to filter out $_REQUEST object when the user submits the configure dropdown
         * 
         * @param array $req $_REQUEST
         * @return array filtered options to save
         */
        function saveOptions($req) {
            global $sugar_config, $timedate, $current_user, $theme;
            $options = array ();
            $options['title'] = $_REQUEST['title'];
            $options['qcmodule'] = $_REQUEST['qcmodule'];
			$options['detailviewonsave'] = $_REQUEST['detailviewonsave'];
            return $options;
        }
        
          function QuickSwitch() {
        if(isset($_REQUEST['load'])) {
            $this->qcmodule = $_REQUEST['load'];
        }
        $str = $this->loadForm();
        $json = getJSONobj();
        echo 'result = ' . $json->encode(array('id' => $_REQUEST['id'], 
                                       'form' => $str));
    }

    }
?>