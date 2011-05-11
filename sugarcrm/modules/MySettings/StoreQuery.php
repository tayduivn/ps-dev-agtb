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

// $Id: StoreQuery.php 55423 2010-03-17 00:59:05Z roger $

class StoreQuery{
	var $query = array();
	
	function addToQuery($name, $val){
		$this->query[$name] = $val;	
	}
	
	function SaveQuery($name){
		global $current_user;
		$current_user->setPreference($name.'Q', $this->query);
	}
	
	function clearQuery($name){
		$this->query = array();
		$this->saveQuery($name);	
	}
	
	function loadQuery($name){
		$saveType = $this->getSaveType($name);
		if($saveType == 'all' || $saveType == 'myitems'){
			global $current_user;
			$this->query = StoreQuery::getStoredQueryForUser($name);
			if(empty($this->query)){
				$this->query = array();	
			}
			if(!empty($this->populate_only) && !empty($this->query['query'])){
				$this->query['query'] = 'MSI';
			}
		}
	}
	
	
	function populateRequest(){
		foreach($this->query as $key=>$val){
            //We don't want to step on the search type, module, or offset if they are in the current request
            if($key != 'advanced' && $key != 'module' && (substr($key, -7) != "_offset" || !isset($_REQUEST[$key]))) {
    			$_REQUEST[$key] = $val;
                $_GET[$key]     = $val;
            }
		}	
	}
	
	function getSaveType($name)
	{
		global $sugar_config;
		$save_query = empty($sugar_config['save_query']) ?
			'all' : $sugar_config['save_query'];

		if(is_array($save_query))
		{
			if(isset($save_query[$name]))
			{
				$saveType = $save_query[$name];
			}
			elseif(isset($save_query['default']))
			{
				$saveType = $save_query['default'];
			}
			else
			{
				$saveType = 'all';
			}	
		}
		else
		{
			$saveType = $save_query;
		}	
		if($saveType == 'populate_only'){
			$saveType = 'all';
			$this->populate_only = true;
		}
		return $saveType;
	}

	
	function saveFromRequest($name){
		if(isset($_REQUEST['query'])){
			if(!empty($_REQUEST['clear_query']) && $_REQUEST['clear_query'] == 'true'){
				$this->clearQuery($name);
				return;	
			}
			$saveType = $this->getSaveType($name);
			
			if($saveType == 'myitems'){
				if(!empty($_REQUEST['current_user_only'])){
					$this->query['current_user_only'] = $_REQUEST['current_user_only'];
					$this->query['query'] = true;
				}
				$this->saveQuery($name);
				
			}else if($saveType == 'all'){
                // Bug 39580 - Added 'EmailTreeLayout','EmailGridWidths' to the list as these are added merely as side-effects of the fact that we store the entire
                // $_REQUEST object which includes all cookies.  These are potentially quite long strings as well.
				$blockVariables = array('mass', 'uid', 'massupdate', 'delete', 'merge', 'selectCount', 'current_query_by_page','EmailTreeLayout','EmailGridWidths');
				$this->query = $_REQUEST;
                foreach($blockVariables as $block) {
                    unset($this->query[$block]);
                }
				$this->saveQuery($name);	
			}
		}
	}
	
	function saveFromGet($name){
		if(isset($_GET['query'])){
			if(!empty($_GET['clear_query']) && $_GET['clear_query'] == 'true'){
				$this->clearQuery($name);
				return;	
			}
			$saveType = $this->getSaveType($name);
			
			if($saveType == 'myitems'){
				if(!empty($_GET['current_user_only'])){
					$this->query['current_user_only'] = $_GET['current_user_only'];
					$this->query['query'] = true;
				}
				$this->saveQuery($name);
				
			}else if($saveType == 'all'){
				$this->query = $_GET;
				$this->saveQuery($name);	
			}
		}
	}
	
	/**
	 * Static method to retrieve the user's stored query for a particular module
	 *
	 * @param string $module
	 * @return array
	 */
	public static function getStoredQueryForUser($module){
		global $current_user;
		return $current_user->getPreference($module.'Q');
	}
}

?>
