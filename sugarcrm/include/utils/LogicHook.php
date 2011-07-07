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
 * $Id: additionalDetails.php 13782 2006-06-06 17:58:55Z majed $
 *********************************************************************************/

/**
 * Predefined logic hooks
 * after_ui_frame
 * after_ui_footer
 * after_save
 * before_save
 * before_retrieve
 * after_retrieve
 * process_record
 * before_delete
 * after_delete
 * before_restore
 * after_restore
 * server_roundtrip
 * before_logout
 * after_logout
 * after_login
 * login_failed
 *
 */
class LogicHook{

	var $bean = null;
	
	function LogicHook(){	
	}
	
	/**
	 * Static Function which returns and instance of LogicHook
	 *
	 * @return unknown
	 */
	function initialize(){
		if(empty($GLOBALS['logic_hook']))
			$GLOBALS['logic_hook'] = new LogicHook();
		return $GLOBALS['logic_hook'];
	}
	
	function setBean(&$bean){
		$this->bean =& $bean;
		return $this;
	}
	
	/**
	 * Provide a means for developers to create upgrade safe business logic hooks.
	 * If the bean is null, then we assume this call was not made from a SugarBean Object and
	 * therefore we do not pass it to the method call.
	 *
	 * @param string $module_dir
	 * @param string $event
	 * @param array $arguments
	 * @param SugarBean $bean
	 */
	function call_custom_logic($module_dir, $event, $arguments = null){
		// declare the hook array variable, it will be defined in the included file.
		$hook_array = null;
	
		if(!empty($module_dir)){
			// This will load an array of the hooks to process
			if(file_exists("custom/modules/$module_dir/logic_hooks.php")){
				$GLOBALS['log']->debug('Including module specific hook file for '.$module_dir);
				include("custom/modules/$module_dir/logic_hooks.php");
				$this->process_hooks($hook_array, $event, $arguments);
				$hook_array = null;
			}
		}
		// Now load the generic array if it exists.
		if(file_exists('custom/modules/logic_hooks.php')){
			$GLOBALS['log']->debug('Including generic hook file');
			include('custom/modules/logic_hooks.php');
			$this->process_hooks($hook_array, $event, $arguments);
		}
	}

	/**
	 * This is called from call_custom_logic and actually performs the action as defined in the
	 * logic hook. If the bean is null, then we assume this call was not made from a SugarBean Object and
	 * therefore we do not pass it to the method call.
	 *
	 * @param array $hook_array
	 * @param string $event
	 * @param array $arguments
	 * @param SugarBean $bean
	 */
	function process_hooks($hook_array, $event, $arguments){
		// Now iterate through the array for the appropriate hook
		if(!empty($hook_array[$event])){
			foreach($hook_array[$event] as $hook_details){
				if(!file_exists($hook_details[2])){
					$GLOBALS['log']->error('Unable to load custom logic file: '.$hook_details[2]);
					continue;
				}
				include_once($hook_details[2]);
				$hook_class = $hook_details[3];
				$hook_function = $hook_details[4];
	
				// Make a static call to the function of the specified class
				//TODO Make a factory for these classes.  Cache instances accross uses
				if($hook_class == $hook_function){
					$GLOBALS['log']->debug('Creating new instance of hook class '.$hook_class.' with parameters');
					if(!is_null($this->bean))
						$class = new $hook_class($this->bean, $event, $arguments);
					else
						$class = new $hook_class($event, $arguments);
				}else{
					$GLOBALS['log']->debug('Creating new instance of hook class '.$hook_class.' without parameters');
					$class = new $hook_class();
					if(!is_null($this->bean))
						$class->$hook_function($this->bean, $event, $arguments);
					else
						$class->$hook_function($event, $arguments);
				}
			}
		}
	}
}
?>