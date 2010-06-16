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
 ********************************************************************************/

require_once('include/MVC/View/views/view.list.php');
require_once('include/connectors/sources/SourceFactory.php');

class ViewDisplayProperties extends ViewList 
{
 	/**
	 * @see SugarView::process()
	 */
	public function process() 
	{
 		$this->options['show_all'] = false;
 		$this->options['show_javascript'] = true;
 		$this->options['show_footer'] = false;
 		$this->options['show_header'] = false;
 	    parent::process();
 	}
 	
    /**
	 * @see SugarView::display()
	 */
	public function display() 
	{
    	require_once('include/connectors/utils/ConnectorUtils.php');
    	$source = $_REQUEST['source_id'];
        $sources = ConnectorUtils::getConnectors();
        $modules_sources = ConnectorUtils::getDisplayConfig(); 	
    	//$json = getJSONobj();
    	
    	$enabled_modules = array();
    	$disabled_modules = array();
    	
    	//Find all modules this source has been enabled for
    	foreach($modules_sources as $module=>$mapping) {
    		foreach($modules_sources[$module] as $entry) {
    			if($entry == $source) {
    			   $enabled_modules[$module] = $module;
    			}
    		}  			
   		}

    	
    	global $moduleList, $beanList;
    	//Do filtering here?
    	$count = 0;
   		global $current_user;
		$access = get_admin_modules_for_user($current_user);
	    $d = dir('modules');
		while($e = $d->read()){
			if(substr($e, 0, 1) == '.' || !is_dir('modules/' . $e))continue;
			if(empty($enabled_modules[$e]) && file_exists('modules/' . $e . '/metadata/studio.php') && file_exists('modules/' . $e . '/metadata/detailviewdefs.php') && isset($GLOBALS [ 'beanList' ][$e]) && (in_array($e, $access) || is_admin($current_user))) // installed modules must also exist in the beanList
			{
				$disabled_modules[$e] = $e;
			}
		}
		
		asort($enabled_modules);
    	asort($disabled_modules);

    	//$enabled = $json->encode($enabled_modules);
    	//$disabled = $json->encode($disabled_modules);
    	//$script = "addTable('{$module}', '{$enabled}', '{$disabled}', '{$source}', '{$GLOBALS['theme']}');\n";    		
    	//$this->ss->assign('new_modules_sources', $modules_sources);
    	//$this->ss->assign('dynamic_script', $script);
    	
    	$this->ss->assign('enabled_modules', $enabled_modules);
    	$this->ss->assign('disabled_modules', $disabled_modules);
    	$this->ss->assign('source_id', $source);
    	$this->ss->assign('mod', $GLOBALS['mod_strings']);
    	$this->ss->assign('APP', $GLOBALS['app_strings']);
    	$this->ss->assign('theme', $GLOBALS['theme']);
    	echo $this->ss->fetch('modules/Connectors/tpls/display_properties.tpl');
    }
}