<?php

function scrubProcessToTracker($bean_or_id, $is_bean){
	$focus = '';
	if($is_bean){
		$focus = $bean_or_id;
	}
	else{
		require_once('modules/Touchpoints/Touchpoint.php');
		$focus = new Touchpoint();
		$focus->retrieve($bean_or_id);
	}
	require_once('modules/Trackers/TrackerManager.php');
	$trackerManager = TrackerManager::getInstance();
	$timeStamp = gmdate($GLOBALS['timedate']->get_db_date_time_format());
	if($monitor = $trackerManager->getMonitor('tracker')){
		$monitor->setValue('team_id', $GLOBALS['current_user']->getPrivateTeamID());
		$monitor->setValue('action', 'detailview');
		$monitor->setValue('user_id', $GLOBALS['current_user']->id);
		$monitor->setValue('module_name', 'Touchpoints');
		$monitor->setValue('date_modified', $timeStamp);
		$monitor->setValue('visible', 1);

		if (!empty($focus->id)) {
			$monitor->setValue('item_id', $focus->id);
			$monitor->setValue('item_summary', $focus->full_name);
		}

		//If visible is true, but there is no bean, do not track (invalid/unauthorized reference)
		//Also, do not track save actions where there is no bean id
		if($monitor->visible && empty($focus->id)) {
		   $trackerManager->unsetMonitor($monitor);
		   return;
		}
		$trackerManager->saveMonitor($monitor);
	}
	else{
		sugar_die('Failed adding to tracker');
	}
}

function getDiscrepancyForm($touchpoint_id, $discrepancy_array = array(), $override_data = array()){
	global $app_list_strings;
	global $mod_strings;
	
	$touchpoint_bean = new Touchpoint();
	$touchpoint_bean->retrieve($touchpoint_id);

	if(empty($touchpoint_bean->id)){
		return false;
	}
	
	$post_copy = $_POST;
	$output = "<table width='95%' border='0' cellspacing='1' cellpadding='0'  class='tabForm'>\n";
	$output .= "<tr>\n";
	$output .= "\t<td valign='top' class='dataLabel'><b>Field Name</b></td>\n";
	$output .= "\t<td valign='top' class='dataLabel'><b>Touchpoint Value</b></td>\n";
	$output .= "\t<td valign='top' class='dataLabel'><b>Other Record Value</b></td>\n";
	$output .= "\t<td valign='top' class='dataLabel'><b>Your Entered Value</b></td>\n";
	$output .= "\t<td valign='top' class='dataLabel'><b>Selected Value</b></td>\n";
	$output .= "</tr>\n";


    require('modules/Touchpoints/ScrubMetaData.php');

	foreach($discrepancy_array as $key => $values){
        if($key == 'assigned_user_id') continue;

		// Using final_selected_value as a little hack to get the dropdowns items in the discrepancy page to show up even when two of the options have the same value
		$final_selected_value = 'eq_qe2'.$values['parent'];
		$display_name = isset($mod_strings[$touchpoint_bean->field_defs[$key]['vname']]) ? $mod_strings[$touchpoint_bean->field_defs[$key]['vname']] : $key;
		$input_name = $key;
		if(!empty($override_data[$key]) && isset($touchpoint_bean->$key) && $override_data[$key] != $touchpoint_bean->$key){
			// Using final_selected_value as a little hack to get the dropdowns items in the discrepancy page to show up even when two of the options have the same value
			$final_selected_value = 'eq_qe3'.$override_data[$key];
			unset($post_copy[$key]);
		}
		
		$touchpoint_value = (!empty($values['touchpoint']) ? $values['touchpoint'] : "");
		$parent_value = (!empty($values['parent']) ? $values['parent'] : "");
		$override_value = (!empty($values['override']) ? $values['override'] : "");
		
		$output .= "<tr>\n";
		$output .= "\t<td valign='top' class='dataLabel'>{$display_name}</td>\n";
		$output .= "\t<td valign='top' class='dataLabel'>".(!empty($values['touchpoint']) ? $values['touchpoint'] : "&nbsp;")."</td>\n";
		$output .= "\t<td valign='top' class='dataLabel'>".(!empty($values['parent']) ? $values['parent'] : "&nbsp;")."</td>\n";
		$output .= "\t<td valign='top' class='dataLabel'>".(!empty($values['override']) ? $values['override'] : "&nbsp;")."</td>\n";

        if($key == 'assigned_user_name') {
            $ignore_post_fields[] = 'assigned_user_id';
            $input_name = 'assigned_user_id';
            $touchpoint_value = (!empty($discrepancy_array['assigned_user_id']['touchpoint']) ? $discrepancy_array['assigned_user_id']['touchpoint'] : "");
            $parent_value = (!empty($discrepancy_array['assigned_user_id']['parent']) ? $discrepancy_array['assigned_user_id']['parent'] : "");
            $override_value = (!empty($discrepancy_array['assigned_user_id']['override']) ? $discrepancy_array['assigned_user_id']['override'] : "");
        }
		// Changing this to a dropdown used to select parent value, child value, or override value, by request
		/*
		if(!empty($touchpoint_bean->field_defs[$key]['options'])){
			$dropdown_name = $touchpoint_bean->field_defs[$key]['options'];
			$output .= "\t<td valign='top' class='tabEditViewDF'><select name={$input_name} ".get_select_options_with_id($app_list_strings[$dropdown_name], $final_value)."</select></td>\n";
		}
		else{
			$output .= "\t<td valign='top' class='tabEditViewDF'><input name={$input_name} value={$final_value}></td>\n";
		}*/
		// prepending string as a  little hack to get the dropdowns items in the discrepancy page to show up even when two of the options have the same value
		$options = array('eq_qe1'.$touchpoint_value => 'Use Touchpoint Value', 'eq_qe2'.$parent_value => 'Use Other Record Value', 'eq_qe3'.$override_value => 'Use Entered Value');
		$output .= "\t<td valign='top' class='tabEditViewDF'><select name={$input_name} ".get_select_options_with_id($options, $final_selected_value)."</select></td>\n";
		$output .= "</td>\n";
	}
	$output .= "</table>\n";

	foreach($post_copy as $post_key => $post_value){
		if(in_array($post_key, $ignore_post_fields)){
			continue;
		}
		
		$output .= "<input type=hidden name={$post_key} value='{$post_value}'>\n";
	}
	$output .= "<input type=submit value='Submit'>\n";
	
	return $output;
}

function rescrubTouchpoint($touchpoint_id)
{
    require_once('modules/Touchpoints/Touchpoint.php');
    require_once('modules/Interactions/Interaction.php');
    require_once('modules/LeadAccounts/LeadAccount.php');
    require_once('modules/LeadContacts/LeadContact.php');
    
    global $current_user;
    
    $touchpointFocus = new Touchpoint;
    $touchpointFocus->retrieve($touchpoint_id);
    if ( !empty($touchpointFocus->id) ) {
        // Update assigned user of Lead Company
        $leadaccountFocus = new LeadAccount;
        $leadaccountFocus->retrieve($touchpointFocus->new_leadaccount_id);
        if ( !empty($leadaccountFocus->id) ) {
            $leadaccountFocus->assigned_user_id = $current_user->id;
        }
        
        // Update assigned user of Lead Person
        $leadcontactFocus = new LeadContact;
        $leadcontactFocus->retrieve($touchpointFocus->new_leadcontact_id);
        if ( !empty($leadcontactFocus->id) ) {
            $leadcontactFocus->assigned_user_id = $current_user->id;
        }

        /*
        ** @author: jwhitcraft
        ** SUGARINTERNAL CUSTOMIZATION
        ** Description: Soft Delete all internactions when rescrubbing
        */
        $soft_del_interactions = "UPDATE interactions SET deleted = 1 WHERE source_id = '" . $touchpointFocus->id. "' and source_module = '". $touchpointFocus->module_dir ."';";
        $GLOBALS['db']->query($soft_del_interactions);
        /** END SUGARINTERNAL CUSTOMIZATION **/
        
        // Clear Lead Company/Person relationships and mark as not scrubbed
        $touchpointFocus->scrubbed = 0;
        $touchpointFocus->new_leadaccount_id = '';
        $touchpointFocus->new_leadcontact_id = '';
        $touchpointFocus->save(false);
    }
}
