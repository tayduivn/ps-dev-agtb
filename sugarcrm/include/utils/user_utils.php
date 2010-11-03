<?php
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

/**
 * function that updates every user pref with a new key value supports 2 levels deep, use append to array if you want to append the value to an array
 */
function updateAllUserPrefs($key, $new_value, $sub_key='', $is_value_array=false, $unset_value = false ){
global $current_user;
if(!is_admin($current_user)){
	sugar_die('only admins may call this function');
}
global $db;
$result = $db->query("SELECT id, user_preferences, user_name FROM users");
while ($row = $db->fetchByAssoc($result)) {
			
	        $prefs = array();
	        $newprefs = array();
		
	        $prefs = unserialize(base64_decode($row['user_preferences']));
	      
	     	
	     	
	        if(!empty($sub_key)){
	        	
	        	if($is_value_array ){
	        		if(!isset($prefs[$key][$sub_key])){
	        			continue;
	        		}
	        			
	        		if(empty($prefs[$key][$sub_key])){
	        			$prefs[$key][$sub_key] = array();	
	        		}
	        		$already_exists = false;
	        		foreach($prefs[$key][$sub_key] as $k=>$value){
	        			if($value == $new_value){
	        				
	        				$already_exists = true;	
	        				if($unset_value){
	        					unset($prefs[$key][$sub_key][$k]);
	        				}
	        			}	
	        		}
	        		if(!$already_exists && !$unset_value){
	        			$prefs[$key][$sub_key][] = $new_value;	
	        		}
	        	}
	        	else{
	        		if(!$unset_value)$prefs[$key][$sub_key] = $new_value;
	        	}
	        	
	        }else{
	        	
	        		if($is_value_array ){
	        		if(!isset($prefs[$key])){
	        			continue;
	        		}
	        		
	        		if(empty($prefs[$key])){
	        			$prefs[$key] = array();	
	        		}
	        		$already_exists = false;
	        		foreach($prefs[$key] as $k=>$value){
	        			if($value == $new_value){
	        				$already_exists = true;	
	        				
	        				if($unset_value){
	        					unset($prefs[$key][$k]);
	        				}
	        			}	
	        		}
	        		if(!$already_exists && !$unset_value){
	        			
	        			$prefs[$key][] = $new_value;	
	        		}
	        	}else{
	        		if(!$unset_value)$prefs[$key] = $new_value;
	        	}
	        }	
	  		
        	$newstr = $GLOBALS['db']->quote(base64_encode(serialize($prefs)));
       		$db->query("UPDATE users SET user_preferences = '{$newstr}' WHERE id = '{$row['id']}'");
		
}
	       
	
        unset($prefs);
        unset($newprefs);
        unset($newstr);
}








?>