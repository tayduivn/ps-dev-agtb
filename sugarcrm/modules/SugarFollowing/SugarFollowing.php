<?php
/*********************************************************************************
 * The contents of this file are subject to the SugarCRM Enterprise End User
 * License Agreement ("License") which can be viewed at
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
 * by SugarCRM are Copyright (C) 2004-2006 SugarCRM, Inc.; All Rights Reserved.
 ********************************************************************************/


class SugarFollowing extends Basic {
	
	var $new_schema = true;
	var $module_dir = 'SugarFollowing';
	var $object_name = 'SugarFollowing';
	var $table_name = 'sugarfollowing';
	var $importable = false;

		var $id;
		var $name;
		var $date_entered;
		var $date_modified;
		var $modified_user_id;
		var $modified_by_name;
		var $created_by;
		var $created_by_name;
		var $description;
		var $deleted;
		var $created_by_link;
		var $modified_user_link;
		var $assigned_user_id;
		var $assigned_user_name;
		var $assigned_user_link;
		var $module;
		var $record_id;
		var $tag;
	function SugarFollowing(){	
		parent::Basic();
	}
	
	static function generateIcon($on, $module, $record){
		if($on){
			return '<div class="follow"><div class="on" onclick="DCMenu.removeFromFollowing(this, \''.$module. '\',  \''.$record. '\');">&nbsp;</div></div>';
		}else{
			
			return '<div class="follow"><div class="off" onclick="DCMenu.addToFollowing(this, \''.$module. '\',  \''.$record. '\');">&nbsp;</div></div>';
		
		}
	}
	
	static function generateGUID($module, $record){
		return  md5($module . $record . $GLOBALS['current_user']->id);

	}
	
	static function getFollowers($bean, $notifyUser){
		//send notifications to followers, but ensure to not query for the assigned_user.
		$query = "SELECT users.* FROM users INNER JOIN sugarfollowing ON sugarfollowing.created_by = users.id WHERE sugarfollowing.module = '{$bean->module_dir}' AND sugarfollowing.record_id = '{$bean->id}' AND sugarfollowing.deleted = 0 AND users.id != '{$bean->assigned_user_id}'";
		$list = $notifyUser->process_list_query($query, 0, -1, -1);
		$userList = $list['list'];
		$userList[] = $notifyUser;
		return $userList;
	}
	
	static function isUserFollowing($module, $record){
		$id = SugarFollowing::generateGUID($module, $record);

		$result = $GLOBALS['db']->query("SELECT id FROM sugarfollowing WHERE id='$id' and deleted=0");
		if($row = $GLOBALS['db']->fetchByAssoc($result)){
			return true;
		}
		return false;
	}

	
}
?>