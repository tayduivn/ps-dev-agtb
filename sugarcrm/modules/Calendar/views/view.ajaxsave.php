<?php
require_once('include/MVC/View/SugarView.php');

class CalendarViewAjaxSave extends SugarView {

	function CalendarViewAjaxSave(){
 		parent::SugarView();
	}
	
	function process(){
		$this->display();
	}
	
	function display(){
		require_once("modules/Calendar/CalendarUtils.php");
		
		$field_list = CalendarUtils::get_fields();

		global $beanFiles,$beanList;
		$module = $_REQUEST['current_module'];
		require_once($beanFiles[$beanList[$module]]);
		$bean = new $beanList[$module]();
		$type = strtolower($beanList[$module]);
		$table_name = $bean->table_name;
		$jn = $type."_id_c";
		

		if(!empty($_REQUEST['record']))
			$bean->retrieve($_REQUEST['record']);
	
		if(!$bean->ACLAccess('Save')) {
			$json_arr = array(
				'success' => 'no',
			);
			echo json_encode($json_arr);
			die;	
		}

		require_once('include/formbase.php');
		
		$bean = populateFromPost("",$bean);
			
		/*foreach($field_list[$_REQUEST['current_module']] as $field){
			$bean->$field = $_REQUEST[$field];
		}*/
		
		//$bean->date_end = $_REQUEST['date_start'];
		
		if(!$_REQUEST['reminder_checked'])
			$bean->reminder_time = -1;


		/*if(empty($_REQUEST['rec_id_c']) && empty($_REQUEST['no_recurrence']) ){
			$bean->repeat_type_c = $_REQUEST['repeat_type_c'];
			$bean->repeat_interval_c = $_REQUEST['repeat_interval_c'];
			$bean->repeat_end_date_c = $_REQUEST['repeat_end_date_c'];
			$bean->repeat_days_c = $_REQUEST['repeat_days_c'];	
		}*/

		if(empty($_REQUEST['record']) && strpos($_POST['user_invitees'],$bean->assigned_user_id) === false)
			$_POST['user_invitees'] .= ",".$bean->assigned_user_id;
		// fill invites and save the entry
		$this->save_activity($bean); 


		if($r_id = $bean->id){

			$u = new User();
			$u->retrieve($bean->assigned_user_id);

			$arr_rec = array();
			/*if(empty($_REQUEST['rec_id_c']) && empty($_REQUEST['no_recurrence']) && ($_REQUEST['repeat_type_c'] != '') ){
				$arr_rec = $this->save_recurrence($bean,$jn);
			}*/
			/*if(empty($_REQUEST['rec_id_c']) && $_REQUEST['repeat_type_c'] == '' && empty($_REQUEST['no_recurrence'])){
				remove_recurrence($bean,$table_name,$jn,$bean->id);
			}*/

			$bean->retrieve($r_id);
	
			if(isset($bean->parent_name))
				$bean->parent_name = $_REQUEST['parent_name'];
	
			$bean->fill_in_additional_parent_fields();
	

			global $timedate;
			$start = CalendarUtils::to_timestamp_from_uf($bean->date_start);
			
			if($_REQUEST['current_module'] == 'Calls')
				$users = $bean->get_call_users();
			if($_REQUEST['current_module'] == 'Meetings')
				$users = $bean->get_meeting_users();
			$user_ids = array();	
			foreach($users as $u)
				$user_ids[] = $u->id;


			$field_arr = array();
			foreach($field_list[$_REQUEST['current_module']] as $field){
				$field_arr[$field] = $bean->$field;
			}
	
			$description = $bean->description;
			$description = str_replace("\r\n","<br>",$description);
			$description = str_replace("\r","<br>",$description);
			$description = str_replace("\n","<br>",$description);
			$description = html_entity_decode($description,ENT_QUOTES);
			$field_arr['description'] = $description;

			$json_arr = array(
				'success' => 'yes',
				'type' => $type,
				'module_name' => $bean->module_dir,
				'start' => $start,
				'time_start' => CalendarUtils::timestamp_to_user_formated2($start,$GLOBALS['timedate']->get_time_format()),
				//'rec_id_c' => $bean->$jn,
				'detailview' => 1,	
				'editview' => 1,		
				'record_name' => html_entity_decode($bean->name,ENT_QUOTES),
				'record' => $bean->id,
				
				'users' => $user_ids,
				/*
				'repeat_type_c' => $bean->repeat_type_c,
				'repeat_interval_c' => $bean->repeat_interval_c,
				'repeat_end_date_c' => $bean->repeat_end_date_c,
				'repeat_days_c' => $bean->repeat_days_c,				
				'arr_rec' => $arr_rec,	
				*/
			);
	
			$json_arr = array_merge($json_arr,$field_arr);

		}else{
			$json_arr = array(
				'success' => 'no',
			);
		}

		ob_clean();
		echo json_encode($json_arr);
		
	
	}	
	
	
	function save_recurrence($bean,$jn){	
	
		$arr_rec = array();
		
		$rtype = $_REQUEST['repeat_type_c'];
		$repeat_days = $_REQUEST['repeat_days_c'];
		$interval = $_REQUEST['repeat_interval_c'];

		$timezone = $GLOBALS['timedate']->getUserTimeZone();
		global $timedate;

		$start_unix = CalendarUtils::to_timestamp_from_uf($_REQUEST['date_start']);
		$end_unix = CalendarUtils::to_timestamp($GLOBALS['timedate']->swap_formats($_REQUEST['repeat_end_date_c'],$GLOBALS['timedate']->get_date_format(),'Y-m-d H:i:s'));
		$end_unix = $end_unix + 60*60*24 - 1;
		$start_day = date("w",$start_unix - date('Z',$start_unix)) + 1;
		$start_dayM = date("j",$start_unix - date('Z',$start_unix));

		if($rtype == 'Weekly' || $rtype == 'Monthly (day)')
			$start_unix = $start_unix - 60*60*24*($start_day - 1);

		remove_recurence($bean,$table_name,$jn,$bean->id);

		$ft = true;

		if(!empty($_REQUEST['repeat_type_c']) && !empty($_REQUEST['repeat_end_date_c'])){
	
			if(empty($interval) && $interval == 0)
				$interval = 1;
			$cur_date = $start_unix;	
	
			$i = 0;
			if($rtype == 'Weekly' || $rtype == 'Monthly (day)')
				$i--;
	
			while($cur_date <= $end_unix){
	
				$i++;
		
				if($rtype == 'Daily'){
					$step = 60*60*24;			
				}
			
				else if($rtype == 'Monthly'){
					$step = 60*60*24 * date('t',$cur_date - date('Z',$cur_date));
					$this_month = date('n',$cur_date - date('Z',$cur_date));
					$next_month = date('n',$cur_date + $step - date('Z',$cur_date));	
					if($next_month - $this_month == 2 || $next_month - $this_month == -10){	
						$day_number = intval(date('d',$cur_date + $step - date('Z',$cur_date))) + 1;							
						$step += 60*60*24 * date('t',$cur_date + $step - $day_number*24*3600 - date('Z',$cur_date));
						$i++;
					}						
				}
		
				else if($rtype == 'Yearly'){
					$step = 60*60*24 * 365;
					if( date('d',$cur_date + $step - date('Z',$cur_date)) !=  date('d',$cur_date - date('Z',$cur_date)) )
						$step += 60*60*24;					
				}
		
				else if($rtype == 'Weekly'){
					$step = 60*60*24*7;
			
					if($i % $interval == 0)
						for($d = $start_day; $d < 7; $d++)
							if(strpos($repeat_days,(string)($d + 1)) !== false){
								if($cur_date + $d*60*60*24 > $end_unix)
									break;
								$arr_rec[] = clone_activity($bean,$cur_date + $d*60*60*24,$jn);															
							}							
					$start_day = 0;							
				}
				
				/*else if($rtype == 'Monthly (day)'){
					$step = 60*60*24 * date('t',$cur_date - date('Z',$cur_date));
			
					if($i % $interval == 0 && $start_dayM <= $start_day)
						for($d = $start_day; $d < 7; $d++){
							$dd = date('w',$cur_date + $d*60*60*24 - date('Z',$cur_date));
							if(strpos($repeat_days,(string)($dd + 1)) !== false){
								if($cur_date + $d*60*60*24 > $end_unix)
									break;
								$arr_rec[] = clone_activity($bean,$cur_date + $d*60*60*24,$jn);															
							}
						}							
					$start_day = 0;	
					$start_dayM = 0;						
				}*/	
				else
					die("Bad type");		
		
				$cur_date += $step;		
				
				if($i % $interval != 0)
					continue;		
				if($cur_date > $end_unix)
					break;			
				if($rtype == 'Weekly' || $rtype == 'Monthly (day)')
					continue;	

				$arr_rec[] = $this->clone_activity($bean,$cur_date,$jn);
			}
		}
		return $arr_rec;
	}
	
	
	
	function clone_activity($bean,$cur_date,$jn){
		require_once("modules/Calendar/CalendarUtils.php");
			
		$obj = clone $bean;
		$obj->id = "";				
		$obj->date_start = CalendarUtils::timestamp_to_user_formated2($cur_date);
		$obj->date_end = CalendarUtils::timestamp_to_user_formated2($cur_date);
		$obj->$jn = $bean->id;
					
		$this->save_activity($obj,false);
		$obj_id = $obj->id;	
		
		$date_unix = $cur_date;	
		return 	array(
			'record' => $obj_id,
			'start' => $date_unix,
		);
	}
	
	
	function save_activity(&$bean,$notify = true){	
	

		if($_REQUEST['current_module'] == 'Meetings'){		


				if(!empty($_POST['user_invitees'])) {
			    		$userInvitees = explode(',', trim($_POST['user_invitees'], ','));
			    	} else {
			    		$userInvitees = array();	
			    	}
			    
				// Calculate which users to flag as deleted and which to add
				$deleteUsers = array();
			    	$bean->load_relationship('users');
			    	// Get all users for the meeting
			    	$q = 'SELECT mu.user_id, mu.accept_status FROM meetings_users mu WHERE mu.meeting_id = \''.$bean->id.'\'';
			    	$r = $bean->db->query($q);
			    	$acceptStatusUsers = array();
			    	while($a = $bean->db->fetchByAssoc($r)) {
			    		  if(!in_array($a['user_id'], $userInvitees)) {
			    		  	$deleteUsers[$a['user_id']] = $a['user_id'];
			    		  } else {
			    		  	$acceptStatusUsers[$a['user_id']] = $a['accept_status'];  
			    		  }
			    	}
			    		
			    	if(count($deleteUsers) > 0) {
			    		$sql = '';
			    		foreach($deleteUsers as $u) {
			    		        $sql .= ",'" . $u . "'";
			    		}
			    		$sql = substr($sql, 1);
			    		// We could run a delete SQL statement here, but will just mark as deleted instead
			    		$sql = "UPDATE meetings_users set deleted = 1 where user_id in ($sql) AND meeting_id = '". $bean->id . "'";
			    		$bean->db->query($sql);
			    	}	  
			    
				// Get all contacts for the meeting
			    	if(!empty($_POST['contact_invitees'])) {
			    	   $contactInvitees = explode(',', trim($_POST['contact_invitees'], ','));
			    	} else {
			    	   $contactInvitees = array();	
			    	}
			    
				$deleteContacts = array();
			    	$bean->load_relationship('contacts');
			    	$q = 'SELECT mu.contact_id, mu.accept_status FROM meetings_contacts mu WHERE mu.meeting_id = \''.$bean->id.'\'';
			    	$r = $bean->db->query($q);
			    	$acceptStatusContacts = array();
			    	while($a = $bean->db->fetchByAssoc($r)) {
			    		  if(!in_array($a['contact_id'], $contactInvitees)) {
			    		  	 $deleteContacts[$a['contact_id']] = $a['contact_id'];
			    		  }	else {
			    		  	 $acceptStatusContacts[$a['contact_id']] = $a['accept_status'];
			    		  }
			    	}
			    	
			    	if(count($deleteContacts) > 0) {
			    		$sql = '';
			    		foreach($deleteContacts as $u) {
			    		        $sql .= ",'" . $u . "'";
			    		}
			    		$sql = substr($sql, 1);
			    		// We could run a delete SQL statement here, but will just mark as deleted instead
			    		$sql = "UPDATE meetings_contacts set deleted = 1 where contact_id in ($sql) AND meeting_id = '". $bean->id . "'";
			    		$bean->db->query($sql);
			    	}
		
				if(!empty($_POST['lead_invitees'])) {
			    	   $leadInvitees = explode(',', trim($_POST['lead_invitees'], ','));
			    	} else {
			    	   $leadInvitees = array();	
			    	}
			    
				$deleteLeads = array();
			    	$bean->load_relationship('leads');
			    	$q = 'SELECT mu.lead_id, mu.accept_status FROM meetings_leads mu WHERE mu.meeting_id = \''.$bean->id.'\'';
			    	$r = $bean->db->query($q);
			    	$acceptStatusLeads = array();
			    	while($a = $bean->db->fetchByAssoc($r)) {
			    		  if(!in_array($a['lead_id'], $leadInvitees)) {
			    		  	 $deleteLeads[$a['lead_id']] = $a['lead_id'];
			    		  }	else {
			    		  	 $acceptStatusLeads[$a['lead_id']] = $a['accept_status'];
			    		  }
			    	}
			    	
			    	if(count($deleteLeads) > 0) {
			    		$sql = '';
			    		foreach($deleteLeads as $u) {
			    		        $sql .= ",'" . $u . "'";
			    		}
			    		$sql = substr($sql, 1);
			    		// We could run a delete SQL statement here, but will just mark as deleted instead
			    		$sql = "UPDATE meetings_leads set deleted = 1 where lead_id in ($sql) AND meeting_id = '". $bean->id . "'";
			    		$bean->db->query($sql);
			    	}
			    	////	END REMOVE
			    	///////////////////////////////////////////////////////////////////////////
		
			    
			    	///////////////////////////////////////////////////////////////////////////
			    	////	REBUILD INVITEE RELATIONSHIPS
			    	$bean->users_arr = array();
			    	$bean->users_arr = $userInvitees;
			    	$bean->contacts_arr = array();
			    	$bean->contacts_arr = $contactInvitees;
				$bean->leads_arr = array();
			    	$bean->leads_arr = $leadInvitees;
		
			    	if(!empty($_POST['parent_id']) && $_POST['parent_type'] == 'Contacts') {
			    		$bean->contacts_arr[] = $_POST['parent_id'];
			    	}
			    
				if(!empty($_POST['parent_id']) && $_POST['parent_type'] == 'Leads') {
			    		$bean->leads_arr[] = $_POST['parent_id'];
			    	}
			    	
			    	
			    	$bean->save($notify);	    	

			    	
			    	// Process users
			    	$existing_users = array();
			    	if(!empty($_POST['existing_invitees'])) {
			    		$existing_users =  explode(",", trim($_POST['existing_invitees'], ','));
			    	}
			    
			    	foreach($bean->users_arr as $user_id) {
			    		if(empty($user_id) || isset($existing_users[$user_id]) || isset($deleteUsers[$user_id])) {
			    			continue;
			    		}
			    		
			    		if(!isset($acceptStatusUsers[$user_id])) {			    			
			    			$bean->users->add($user_id);
			    		} else {
			    			// update query to preserve accept_status
			    			$qU  = 'UPDATE meetings_users SET deleted = 0, accept_status = \''.$acceptStatusUsers[$user_id].'\' ';
			    			$qU .= 'WHERE meeting_id = \''.$bean->id.'\' ';
			    			$qU .= 'AND user_id = \''.$user_id.'\'';
			    			$bean->db->query($qU);
			    		} 
			    	}
			    	
				// Process contacts
			    	$existing_contacts =  array();
			    	if(!empty($_POST['existing_contact_invitees'])) {
			    	   $existing_contacts =  explode(",", trim($_POST['existing_contact_invitees'], ','));
			    	}
			    	    
			    	foreach($bean->contacts_arr as $contact_id) {
			    		if(empty($contact_id) || isset($exiting_contacts[$contact_id]) || isset($deleteContacts[$contact_id])) {
			    			continue;
			    		}
			    		
			    		if(!isset($acceptStatusContacts[$contact_id])) {
			    		    $bean->contacts->add($contact_id);
			    		} else {
			    			// update query to preserve accept_status
			    			$qU  = 'UPDATE meetings_contacts SET deleted = 0, accept_status = \''.$acceptStatusContacts[$contact_id].'\' ';
			    			$qU .= 'WHERE meeting_id = \''.$bean->id.'\' ';
			    			$qU .= 'AND contact_id = \''.$contact_id.'\'';
			    			$bean->db->query($qU);
			    		}
			    	}
		
				// Process leads
			    	$existing_leads =  array();
			    	if(!empty($_POST['existing_lead_invitees'])) {
			    	   $existing_leads =  explode(",", trim($_POST['existing_lead_invitees'], ','));
			    	}
			    	    
			    	foreach($bean->leads_arr as $lead_id) {
			    		if(empty($lead_id) || isset($exiting_leads[$lead_id]) || isset($deleteLeads[$lead_id])) {
			    			continue;
			    		}
			    		
			    		if(!isset($acceptStatusLeads[$lead_id])) {
			    		    $bean->leads->add($lead_id);
			    		} else {
			    			// update query to preserve accept_status
			    			$qU  = 'UPDATE meetings_leads SET deleted = 0, accept_status = \''.$acceptStatusLeads[$lead_id].'\' ';
			    			$qU .= 'WHERE meeting_id = \''.$bean->id.'\' ';
			    			$qU .= 'AND lead_id = \''.$lead_id.'\'';
			    			$bean->db->query($qU);
			    		}
			    	}
		}
	
		if($_REQUEST['current_module'] == 'Calls'){
	
			    	if(!empty($_POST['user_invitees'])) {
			    		$userInvitees = explode(',', trim($_POST['user_invitees'], ','));
			    	}else {
			    		$userInvitees = array();	
			    	}
			    
				// Calculate which users to flag as deleted and which to add
				$deleteUsers = array();
			    	$bean->load_relationship('users');
			    	// Get all users for the call
			    	$q = 'SELECT mu.user_id, mu.accept_status FROM calls_users mu WHERE mu.call_id = \''.$bean->id.'\'';
			    	$r = $bean->db->query($q);
			    	$acceptStatusUsers = array();
			    	while($a = $bean->db->fetchByAssoc($r)) {
			    		  if(!in_array($a['user_id'], $userInvitees)) {
			    		  	 $deleteUsers[$a['user_id']] = $a['user_id'];
			    		  } else {
			    		     $acceptStatusUsers[$a['user_id']] = $a['accept_status'];  
			    		  }
			    	}
			    		
			    	if(count($deleteUsers) > 0) {
			    		$sql = '';
			    		foreach($deleteUsers as $u) {
					// make sure we don't delete the assigned user
					if ( $u != $bean->assigned_user_id )
			    		        $sql .= ",'" . $u . "'";
			    		}
			    		$sql = substr($sql, 1);
			    		// We could run a delete SQL statement here, but will just mark as deleted instead
			    		$sql = "UPDATE calls_users set deleted = 1 where user_id in ($sql) AND call_id = '". $bean->id . "'";
			    		$bean->db->query($sql);
			    	}	  
			    
				// Get all contacts for the call
			    	if(!empty($_POST['contact_invitees'])) {
			    	   $contactInvitees = explode(',', trim($_POST['contact_invitees'], ','));
			    	} else {
			    	   $contactInvitees = array();	
			    	}
			    
				$deleteContacts = array();
			    	$bean->load_relationship('contacts');
			    	$q = 'SELECT mu.contact_id, mu.accept_status FROM calls_contacts mu WHERE mu.call_id = \''.$bean->id.'\'';
			    	$r = $bean->db->query($q);
			    	$acceptStatusContacts = array();
			    	while($a = $bean->db->fetchByAssoc($r)) {
			    		  if(!in_array($a['contact_id'], $contactInvitees)) {
			    		  	 $deleteContacts[$a['contact_id']] = $a['contact_id'];
			    		  }else{
			    		  	 $acceptStatusContacts[$a['contact_id']] = $a['accept_status'];
			    		  }
			    	}
			    	
			    	if(count($deleteContacts) > 0) {
			    		$sql = '';
			    		foreach($deleteContacts as $u) {
			    		        $sql .= ",'" . $u . "'";
			    		}
			    		$sql = substr($sql, 1);
			    		// We could run a delete SQL statement here, but will just mark as deleted instead
			    		$sql = "UPDATE calls_contacts set deleted = 1 where contact_id in ($sql) AND call_id = '". $bean->id . "'";
			    		$bean->db->query($sql);
			    	}
		
				if(!empty($_POST['lead_invitees'])) {
			    	   $leadInvitees = explode(',', trim($_POST['lead_invitees'], ','));
			    	} else {
			    	   $leadInvitees = array();	
			    	}
			    
				// Calculate which leads to flag as deleted and which to add
				$deleteLeads = array();
			    	$bean->load_relationship('leads');
			    	// Get all leads for the call
			    	$q = 'SELECT mu.lead_id, mu.accept_status FROM calls_leads mu WHERE mu.call_id = \''.$bean->id.'\'';
			    	$r = $bean->db->query($q);
			    	$acceptStatusLeads = array();
			    	while($a = $bean->db->fetchByAssoc($r)) {
			    		  if(!in_array($a['lead_id'], $leadInvitees)) {
			    		  	 $deleteLeads[$a['lead_id']] = $a['lead_id'];
			    		  } else {
			    		     $acceptStatusLeads[$a['user_id']] = $a['accept_status'];  
			    		  }
			    	}
			    		
			    	if(count($deleteLeads) > 0) {
			    		$sql = '';
			    		foreach($deleteLeads as $u) {
					// make sure we don't delete the assigned user
					if ( $u != $bean->assigned_user_id )
			    		        $sql .= ",'" . $u . "'";
			    		}
			    		$sql = substr($sql, 1);
			    		// We could run a delete SQL statement here, but will just mark as deleted instead
			    		$sql = "UPDATE calls_leads set deleted = 1 where lead_id in ($sql) AND call_id = '". $bean->id . "'";
			    		$bean->db->query($sql);
			    	}
			    	////	END REMOVE
			    	///////////////////////////////////////////////////////////////////////////
		
			    
			    	///////////////////////////////////////////////////////////////////////////
			    	////	REBUILD INVITEE RELATIONSHIPS
			    	$bean->users_arr = array();
			    	$bean->users_arr = $userInvitees;
			    	$bean->contacts_arr = array();
			    	$bean->contacts_arr = $contactInvitees;
				$bean->leads_arr = array();
			    	$bean->leads_arr = $leadInvitees;
		
			    	if(!empty($_POST['parent_id']) && $_POST['parent_type'] == 'Contacts') {
			    		$bean->contacts_arr[] = $_POST['parent_id'];
			    	}
			    
				if(!empty($_POST['parent_id']) && $_POST['parent_type'] == 'Leads') {
			    		$bean->leads_arr[] = $_POST['parent_id'];
			    	}	
			    	
			    	
			    	$bean->save($notify);
			    	
			    
			    	// Process users
			    	$existing_users = array();
			    	if(!empty($_POST['existing_invitees'])) {
			    	   $existing_users =  explode(",", trim($_POST['existing_invitees'], ','));
			    	}
			    
			    	foreach($bean->users_arr as $user_id) {
			    	    if(empty($user_id) || isset($existing_users[$user_id]) || isset($deleteUsers[$user_id])) {
			    			continue;
			    		}
			    		
			    		if(!isset($acceptStatusUsers[$user_id])) {
			    			$bean->users->add($user_id);
			    		} else {
			    			// update query to preserve accept_status
			    			$qU  = 'UPDATE calls_users SET deleted = 0, accept_status = \''.$acceptStatusUsers[$user_id].'\' ';
			    			$qU .= 'WHERE call_id = \''.$bean->id.'\' ';
			    			$qU .= 'AND user_id = \''.$user_id.'\'';
			    			$bean->db->query($qU);
			    		} 
			    	}
			    	
				// Process contacts
			    	$existing_contacts =  array();
			    	if(!empty($_POST['existing_contact_invitees'])) {
			    	   $existing_contacts =  explode(",", trim($_POST['existing_contact_invitees'], ','));
			    	}
			    	    
			    	foreach($bean->contacts_arr as $contact_id) {
			    		if(empty($contact_id) || isset($exiting_contacts[$contact_id]) || isset($deleteContacts[$contact_id])) {
			    			continue;
			    		}
			    		
			    		if(!isset($acceptStatusContacts[$contact_id])) {
			    		    $bean->contacts->add($contact_id);
			    		} else {
			    			// update query to preserve accept_status
			    			$qU  = 'UPDATE calls_contacts SET deleted = 0, accept_status = \''.$acceptStatusContacts[$contact_id].'\' ';
			    			$qU .= 'WHERE call_id = \''.$bean->id.'\' ';
			    			$qU .= 'AND contact_id = \''.$contact_id.'\'';
			    			$bean->db->query($qU);
			    		}
			    	}
		
				// Process leads
			    	$existing_leads =  array();
			    	if(!empty($_POST['existing_lead_invitees'])) {
			    	   $existing_contacts =  explode(",", trim($_POST['existing_lead_invitees'], ','));
			    	}
			    	    
			    	foreach($bean->leads_arr as $lead_id) {
			    		if(empty($lead_id) || isset($existing_leads[$lead_id]) || isset($deleteLeads[$lead_id])) {
			    			continue;
			    		}
			    		
			    		if(!isset($acceptStatusLeads[$lead_id])) {
			    		    $bean->leads->add($lead_id);
			    		} else {
			    			// update query to preserve accept_status
			    			$qU  = 'UPDATE calls_leads SET deleted = 0, accept_status = \''.$acceptStatusLeads[$lead_id].'\' ';
			    			$qU .= 'WHERE call_id = \''.$bean->id.'\' ';
			    			$qU .= 'AND lead_id = \''.$lead_id.'\'';
			    			$bean->db->query($qU);
			    		}
			    	} 
		}
	}
	

}

?>