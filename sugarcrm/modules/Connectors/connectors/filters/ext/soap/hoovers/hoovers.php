<?php
//FILE SUGARCRM flav=pro || flav=sales ONLY
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

require_once('include/connectors/filters/default/filter.php');

class ext_soap_hoovers_filter extends default_filter {
	
public function getList($args, $module) {

	$list = $this->_component->getSource()->getList($args, $module);
	
	//If count was 0 and city, zip, state and/or country value was used, we try to improve searching		
	if(empty($list) && isset($args['bal']['location']['city'])) {
	   $GLOBALS['log']->info("ext_soap_hoovers_filter, unset ['bal']['location']['city'] search term");
	   unset($args['bal']['location']['city']);	
	   $list = $this->_component->getSource()->getList($args, $module);
	}		
	
	if(empty($list) && isset($args['bal']['location']['zip'])) {
	   $GLOBALS['log']->info("ext_soap_hoovers_filter, unset ['bal']['location']['zip'] search term");
	   unset($args['bal']['location']['zip']);	
	   $list = $this->_component->getSource()->getList($args, $module);
	}		
	
	if(empty($list) && isset($args['bal']['location']['state'])) {
	   $GLOBALS['log']->info("ext_soap_hoovers_filter, unset ['bal']['location']['state'] search term");
	   unset($args['bal']['location']['state']);	
	   $list = $this->_component->getSource()->getList($args, $module);
	}	
	
	if(empty($list) && isset($args['bal']['location']['country'])) {
	   $GLOBALS['log']->info("ext_soap_hoovers_filter, unset ['bal']['location']['country'] search term");
	   unset($args['bal']['location']['country']);	
	   $list = $this->_component->getSource()->getList($args, $module);
	}

	//Sometimes Hoovers makes the mistake of returning the first entry that may not be what we want
	if(count($list) == 1 && isset($args['bal']['specialtyCriteria']['companyKeyword'])) {
	   if(preg_match('/^(.*?)([\,|\s]+.*?)$/', $args['bal']['specialtyCriteria']['companyKeyword'], $matches)) {
	   	 $GLOBALS['log']->info("ext_soap_hoovers_filter, change companyKeyword search term");
	   	 $args['bal']['specialtyCriteria']['companyKeyword'] = $matches[1];
	     $list = $this->_component->getSource()->getList($args, $module);
	     if(!empty($list)) {
	        return $list;	
	     }
	   }
	}		
	return $list;

}
	
}

?>