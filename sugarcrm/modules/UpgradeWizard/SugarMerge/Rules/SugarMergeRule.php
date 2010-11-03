<?php
if(!defined('sugarEntry') || !sugarEntry) die('Not A Valid Entry Point');
/*********************************************************************************
 * The contents of this file are subject to the SugarCRM Enterprise Subscription
 * Agreement ("License") which can be viewed at
 * http://www.sugarcrm.com/crm/products/sugar-enterprise-eula.html
 * By installing or using this file, You have unconditionally agreed to the
 * terms and conditions of the License, and You may not use this file except in
 * compliance with the License.  Under the terms of the license, You shall not,
 * among other things: 1) sublicense, resell, rent, lease, redistribute, assign
 * or otherwise transfer Your rights to the Software, and 2) use the Software
 * for timesharing or service bureau purposes such as hosting the Software for
 * commercial gain and/or for the benefit of a third party.  Use of the Software
 * may be subject to applicable fees and any use of the Software without first
 * paying applicable fees is strictly prohibited.  You do not have the right to
 * remove SugarCRM copyrights from the source code or user interface.
 *
 * All copies of the Covered Code must include on each user interface screen:
 *  (i) the "Powered by SugarCRM" logo and
 *  (ii) the SugarCRM copyright notice
 * in the same form as they appear in the distribution.  See full license for
 * requirements.
 *
 * Your Warranty, Limitations of liability and Indemnity are expressly stated
 * in the License.  Please refer to the License for the specific language
 * governing these rights and limitations under the License.  Portions created
 * by SugarCRM are Copyright (C) 2004-2010 SugarCRM, Inc.; All Rights Reserved.
 ********************************************************************************/

class SugarMergeRule
{
    
    /**
     * runRules
     * 
     * This is a static method that checks to see if there are any special rules to run 
     * for a given module when merging metadata files for upgrades.
     * 
     * @param $module String value of the module
     * @param $original_file String value of path to the original metadata file
     * @param $new_file String value of path to the new metadata file (the target instance's default metadata file)
     * @param $custom_file String value of path to the custom metadata file
     * @param $save boolean value indicating whether or not to save changes
     * @return boolean true if a rule was found and run, false otherwise
     */
    public static function runRules($module, $original_file, $new_file, $custom_file=false, $save=true)
    {
    	$check_objects = array('Person');
    	foreach($check_objects as $name) {
    		if(SugarModule::get($module)->moduleImplements($name)) {
    		   $rule_file = 'modules/UpgradeWizard/SugarMerge/Rules/' . $name . 'MergeRule.php';
    		   if(file_exists($rule_file)) {
    		   	  require_once($rule_file);
    		   	  $class_name = $name . 'MergeRule';
    		   	  $instance = new $class_name();
    		   	  return $instance->merge($module, $original_file, $new_file, $custom_file=false, $save=true);
    		   }
    		} 
    	}
    	return false;
    }   

}
?>