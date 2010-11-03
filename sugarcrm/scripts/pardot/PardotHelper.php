<?php
if(!defined('sugarEntry') || !sugarEntry) die('Not A Valid Entry Point');

class PardotHelper {
	
	public static function updateProspectActivities($touchpoint_id, $after_this_date_time = ''){
		require_once('modules/Touchpoints/Touchpoint.php');
		require_once('modules/LeadContacts/LeadContact.php');
		require_once('modules/Contacts/Contact.php');
		
		$touchpoint = new Touchpoint();
		$touchpoint->retrieve($touchpoint_id);
		if(empty($touchpoint->id)){
			return 'touchpoint_doesnt_exist';
		}
		if(empty($touchpoint->prospect_id_c)){
			return 'no_prospect_id';
		}
		
		$parent_id = $touchpoint->new_leadcontact_id;
		$parent_type = 'LeadContacts';
		if(!empty($touchpoint->new_leadcontact_id)){
			$lc = new LeadContact();
			$lc->retrieve($touchpoint->new_leadcontact_id);
			if(!empty($lc->id) && !empty($lc->contact_id)){
				$contact = new Contact();
				$contact->retrieve($lc->contact_id);
				if(!empty($contact->id)){
					$parent_id = $contact->id;
					$parent_type = 'Contacts';
				}
			}
		}
		else{
			$parent_id = null;
			$parent_type = null;
		}
		
		require_once('scripts/pardot/pardotApi.class.php');
		$pardot = pardotApi::magic();
		
		$prospect = $pardot->getProspectById($touchpoint->prospect_id_c);
		
		// This runs an update on the touchpoint data before creating the interactions
		$output = array();
		$return = null;
		$command = 'php scripts/pardot/updateTouchpointFromProspect.php '
			. escapeshellarg($touchpoint_id) . ' ' . escapeshellarg($touchpoint->prospect_id_c);
		exec($command, $output, $return);
		
		if(!empty($prospect->visitor_activities)){
			require_once('modules/Interactions/Interaction.php');
			$override_data = array(
				'modified_user_id' => $GLOBALS['current_user']->id,
				'created_by' => $GLOBALS['current_user']->id,
				'assigned_user_id' => $touchpoint->assigned_user_id,
				'parent_id' => $parent_id,
				'parent_type' => $parent_type,
				'source_id' => $touchpoint->id,
				'source_module' => 'Touchpoints',
			);
			
			/* Uncomment if you want to default to the last activity sync time
			if(empty($after_this_date_time)){
				$dt_res = $GLOBALS['db']->query("select value from config where category = 'pardot' and name = 'last_activity_sync'");
				$dt_row = $GLOBALS['db']->fetchByAssoc($dt_res);
				$after_this_date_time = $dt_row['value'];
			}
			*/
			
			$page_views = array();
			foreach($prospect->visitor_activities as $activity){
				// If the date passed is before the last dates, we skip them
				if(!empty($after_this_date_time) && strtotime($activity->created_at) < strtotime($after_this_date_time)){
					continue;
				}
				
				if($activity->type_name == 'Secret iframe form'){
					continue;
				}
								
				// Check if this new record is of type Page Views, and handle that accordingly
				$is_page_view = self::processPageViews($page_views, $activity, $touchpoint->id);
				if($is_page_view){
					continue;
				}
				
				// Defensive code to avoid creating multiple interactions for the same pardot activity
				$check_for_interaction_query = "select id from interactions where visitor_activity_id = '{$activity->id}' and deleted = 0";
				$update_id = false;
				if($res = $GLOBALS['db']->query($check_for_interaction_query)){
					$row = $GLOBALS['db']->fetchByAssoc($res);
					if(!empty($row['id'])){
						$update_id = $row['id'];
					}
				}
				
				// If we have a previously existing interaction, we skip it.
				// If we need to revert this behavior to update previous interactions as well, remove the 3 lines below this comment
				if($update_id){
					continue;
				}
				
				// Unset it in case it was set from the last loop in this foreach
				$override_data['campaign_id'] = '';
				
				// While creating this, we make sure the campaign ids exist for the activities
				if(isset($activity->form_handler_id)){
					// If we successfully have a campaign id, we set it in the override data array to go into the interaction in the next code block
					if($campaign_id = self::verifyCampaignExistsOrCreate($activity->form_handler_id, $activity->details)){
						$override_data['campaign_id'] = $campaign_id;
					}
				}
				
				// Create or update an interaction from the pardot activity
				$interaction_data = $activity->getInteractionData($override_data);
				$interaction = new Interaction();
				if($update_id){ // if we have an interaction id, we update the previous one
					$interaction_data['id'] = $update_id;
					$interaction->retrieve($update_id);
				}
				$interaction->populateFromRow($interaction_data);
				$interaction->save(false);
			}
			
			// Now we process the page views if we have any
			$override_data['campaign_id'] = '';
			if(!empty($page_views)){
				$interaction = new Interaction();
				if(!empty($page_views['interaction_id'])){
					$interaction->retrieve($page_views['interaction_id']);
					$interaction->name = $page_views['number'];
				}
				else{
					$override_data['name'] = $page_views['number'];
					$override_data['type'] = 'Page Views';
					$override_data['visitor_activity_id'] = $page_views['visitor_activity_id'];
					$interaction->populateFromRow($override_data);
				}
				$interaction->save(false);
			}
		}
		
		return 'success';
	}
	
	public static function processPageViews(&$return_data, $pardot_activity, $touchpoint_id){
		$is_page_view = false;
		
		// If this is true, we know this is a page view activity
		if(strpos($pardot_activity->type_name, 'Visitor') === 0){
			$is_page_view = true;
			
			$search =  array('Visitor: ', ' page views');
			$replace = array('', '');
			$number = str_replace($search, $replace, $pardot_activity->type_name);
			
			if(!empty($return_data['number'])){
				// We increment the value, since we already set the 
				$return_data['number'] += $number;
			}
			else{
				// Otherwise we set the number and find the associated interaction to update, if it exists
				$return_data['number'] = $number;
				
				$query = "SELECT id FROM interactions \n".
						 "WHERE source_id = '{$touchpoint_id}' AND source_module = 'Touchpoints' \n".
						 "  AND type = 'Page Views' AND deleted = 0 \n";
				$res = $GLOBALS['db']->query($query);
				if($res){
					$row = $GLOBALS['db']->fetchByAssoc($res);
					if(!empty($row['id'])){
						$return_data['interaction_id'] = $row['id'];
					}
				}
				
				$return_data['visitor_activity_id'] = $pardot_activity->id;
			}
		}
		
		return $is_page_view;
	}
	
	public static function verifyCampaignExistsOrCreate($pardot_campaign_id, $pardot_campaign_name){
		if(empty($pardot_campaign_id))
			return false;

		$pardot_campaign_name = trim($pardot_campaign_name);

		$sugar_campaign_id = '';

		// We try to find a campaign based on pardot campaign id
		$campaign_query = "select id from campaigns inner join campaigns_cstm on campaigns.id = campaigns_cstm.id_c where pardot_campaign_id_c = '{$pardot_campaign_id}' and deleted = 0";
		if($res = $GLOBALS['db']->query($campaign_query)){
			if($row = $GLOBALS['db']->fetchByAssoc($res)){
				$sugar_campaign_id = $row['id'];
			}
		}

		$campaign_query = "select id from campaigns inner join campaigns_cstm on campaigns.id = campaigns_cstm.id_c ".
							"where name = '".$GLOBALS['db']->quote($pardot_campaign_name)."' and deleted = 0 and (pardot_campaign_id_c is null or pardot_campaign_id_c = '')";
		if(strpos($pardot_campaign_name, "...") === 0){
			$end_campaign_name = substr($pardot_campaign_name, 3);
			$campaign_query = "select id from campaigns inner join campaigns_cstm on campaigns.id = campaigns_cstm.id_c ".
							"where name like '%".$GLOBALS['db']->quote($end_campaign_name)."' and deleted = 0 and (pardot_campaign_id_c is null or pardot_campaign_id_c = '')";
		}

		// We didn't find one based on pardot campaign id, but we'll try and search based on the name. If we find an exact match, we'll assume that this is it
		if(empty($sugar_campaign_id) && !empty($pardot_campaign_name)){
			if($res = $GLOBALS['db']->query($campaign_query)){
				if($row = $GLOBALS['db']->fetchByAssoc($res)){
					$sugar_campaign_id = $row['id'];
					$campaign_update = "update campaigns inner join campaigns_cstm on campaigns.id = campaigns_cstm.id_c ".
										"set pardot_campaign_id_c = '{$pardot_campaign_id}' where id = '{$row['id']}'";
					$GLOBALS['db']->query($campaign_update);
				}
			}
		}

		// We could not find a campaign in sugar internal, create one
		if(empty($sugar_campaign_id)){
			require_once('include/TimeDate.php');
			$timedate = new TimeDate();
			
			// Set the current user to be an admin to create the campaign in case someone doesn't have access to campaigns
			$admin_user = new User();
			$admin_user->getSystemUser();
			$temp_user = $GLOBALS['current_user'];
			$GLOBALS['current_user'] = $admin_user;
			
			require_once('modules/Campaigns/Campaign.php');
			$campaign = new Campaign();
			$campaign->pardot_campaign_id_c = $pardot_campaign_id;
			$campaign->name = $pardot_campaign_name;
			$campaign->status = 'Active';
			$campaign->type = 'Web';
			$campaign->end_date = $timedate->to_display_date(date('Y-m-d'), false);
			$campaign->display_in_leads_dropdown_c = true;
			$campaign->team_id = '1';
			$campaign->assigned_user_id = '1';
			$campaign->campaign_rating_c = 'A';
			$campaign->save(false);
			
			$sugar_campaign_id = $campaign->id;
			
			// Set the user back now that we're done
			$GLOBALS['current_user'] = $temp_user;
		}
		
		return $sugar_campaign_id;
	}
}
