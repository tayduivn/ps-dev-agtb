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
require_once('include/MVC/View/SugarView.php');

class CalendarViewSaveActivity extends SugarView {

	function CalendarViewSave(){
 		parent::SugarView();
	}
	
	function process(){
		$this->display();
	}
	
	function display(){
		require_once("modules/Calendar/CalendarUtils.php");

		global $beanFiles,$beanList;
		$module = $_REQUEST['current_module'];
		require_once($beanFiles[$beanList[$module]]);
		$bean = new $beanList[$module]();
		$table_name = $bean->table_name;		

		if(!empty($_REQUEST['record']))
			$bean->retrieve($_REQUEST['record']);
	
		if(!$bean->ACLAccess('Save')) {
			$json_arr = array(
				'access' => 'no',
			);
			echo json_encode($json_arr);
			die;	
		}
		
		if(empty($_REQUEST['edit_all_recurrences'])){
			$repeat_fields = array('type','interval','count','until','dow','parent_id');
			foreach($repeat_fields as $suffix)
				unset($_POST['repeat_' . $suffix]);			
		}else if(!empty($_REQUEST['repeat_type']) && !empty($_REQUEST['date_start'])){
			$params = array(
					'type' => $_REQUEST['repeat_type'],
					'interval' => $_REQUEST['repeat_interval'],
					'count' => $_REQUEST['repeat_count'],	
					'until' => $_REQUEST['repeat_until'],	
					'dow' => $_REQUEST['repeat_dow'],			
			);
				
			$repeat_arr = CalendarUtils::build_repeat_sequence($_REQUEST['date_start'],$params);
			$limit = SugarConfig::getInstance()->get('calendar.max_repeat_count',1000);
			
			if(count($repeat_arr) > ($limit - 1)){
				ob_clean();
				$json_arr = array(
					'access' => 'yes',
					'limit_error' => 'true',
					'limit' => $limit,					
				);
				echo json_encode($json_arr);
				die;	
			}									
		}

			
		require_once("modules/{$bean->module_dir}/{$bean->object_name}FormBase.php");
		$fb_object_name = "{$bean->object_name}FormBase";
		$form_base = new $fb_object_name();
		$bean = $form_base->handleSave('', false, false);
		unset($_REQUEST['send_invites'],$_POST['send_invites']); // prevent invites sending for recurring activities

		if($record = $bean->id){			
			if($module == "Meetings"){
				if(!empty($_REQUEST['edit_all_recurrences']))
					CalendarUtils::mark_repeat_deleted($bean);
				if(is_array($repeat_arr) && count($repeat_arr) > 0)
					$repeat_created = CalendarUtils::save_repeat_activities($bean,$repeat_arr);
			}			
			$bean->retrieve($record);								
			$json_arr = CalendarUtils::get_sendback_array($bean);			
			if(is_array($repeat_created))
				$json_arr = array_merge($json_arr,array('repeat' => $repeat_created));
			if(!empty($_REQUEST['edit_all_recurrences']))
				$json_arr['edit_all_recurrences'] = 'true';		
		}else{
			$json_arr = array(
				'access' => 'no',
			);
		}

		ob_clean();
		echo json_encode($json_arr);		
	
	}

}

?>
