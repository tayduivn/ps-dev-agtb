<?php
require_once('pardotData.abstract.php');

require_once('pardotVisitor.class.php');
require_once('pardotVisitorActivity.class.php');
require_once('pardotList.class.php');
require_once('pardotCampaign.class.php');
require_once('pardotProfile.class.php');

class pardotProspect extends pardotData {
    /* stock pardot fields */
    var $id;
    var $salutation;
    var $first_name;
    var $last_name;
    var $email;
    var $password;
    var $company;
    var $website;
    var $job_title;
    var $department;
    var $country;
    var $address_one;
    var $city;
    var $state;
    var $territory;
    var $zip;
    var $phone;
    var $fax;
    var $source;
    var $annual_revenue;
    var $employees;
    var $industry;
    var $years_in_business;
    var $comments;
    var $notes;
    var $score;
    var $grade;
    var $last_activity_at;
    var $recent_interaction;
    var $crm_lead_fid;
    var $crm_contact_fid;
    var $crm_owner_fid;
    var $crm_account_fid;
    var $crm_opportunity_fid;
    var $crm_opportunity_created_at;
    var $crm_opportunity_updated_at;
    var $crm_opportunity_value;
    var $crm_opportunity_status;
    var $crm_last_activity;
    var $crm_is_sale_won;
    var $is_do_not_email;
    var $is_do_not_call;
    var $opted_out;
    var $is_reviewed;
    var $is_starred;
    var $created_at;
    var $updated_at;

    var $campaign;
    var $profile;
    
    /* our custom pardot fields */
    var $Number_of_CRM_Users;
    var $eval_name;
    var $data_migration;
    var $Call_Back_c;
    var $yancAction;
    var $agreement;
    var $edition;
    var $record;
    var $registered_eval_c;
    var $evalcreate;
    var $status;
    var $campaign_name;
    var $lead_source_description;
    var $user;
    var $team_id;
    var $assigned_user_id;
    var $option;
    var $task;
    var $formType;
    var $invalidinstance;
    var $description = '';
    
    
    function loadFromSimpleXML($simpleXML) {
	$success = false;
	foreach ($simpleXML->children() as $key => $value) {
	    $key = (string) $key;
	    switch ($key) {
	    case 'visitors' :
		$this->visitors = array();
		foreach ($value->children() as $visitorXML) {
		    $visitor = new pardotVisitor();
		    if ($visitor->loadFromSimpleXML($visitorXML)) {
			$this->visitors[] = $visitor;
			$success = true;
		    } else {
			trigger_error("There was a problem loading a visitor", E_USER_NOTICE);
		    }
		}
		break;
	    case 'visitor_activities' :
		$this->visitor_activities = array();
		foreach ($value->children() as $activityXML) {
		    $activity = new pardotVisitorActivity();
		    if ($activity->loadFromSimpleXML($activityXML)) {
			$this->visitor_activities[] = $activity;
			$success = true;
		    } else {
			trigger_error("There was a problem loading an activity", E_USER_NOTICE);
		    }
		}
		break;
	    case 'last_activity' :
		$this->last_activity = array();
		foreach ($value->children as $activityXML) {
		    $activity = new pardotVisitorActivity();
		    if ($activity->loadFromSimpleXML($activityXML)) {
			$this->last_activity[] = $activity;
			$success = true;
		    } else {
			trigger_error("There was a problem loading an activity", E_USER_NOTICE);
		    }
		}
	    case 'lists' :
		$this->lists = array();
		foreach ($value->children() as $listXML) {
		    $list = new pardotList();
		    if ($list->loadFromSimpleXML($listXML)) {
			$this->lists[] = $list;
			$success = true;
		    } else {
			trigger_error("There was a problem loading a list", E_USER_NOTICE);
		    }
		}
		break;
	    case 'campaign' :
		$campaign = new pardotCampaign();
		if ($campaign->loadFromSimpleXML($value)) {
		    $this->campaign = $campaign;
		    $success = true;
		} else {
		    trigger_error("There was a problem loading a campaign", E_USER_NOTICE);
		}
		break;
	    case 'profile' :
		$profile = new pardotProfile();
		if ($profile->loadFromSimpleXML($value)) {
		    $this->profile = $profile;
		    $success = true;
		} else {
		    trigger_error("There was a problem loading a profile", E_USER_NOTICE);
		}
		break;
	    default: 
		$value = (string) $value;
		$this->$key = $value;
		$success = true;
		break;
	    }
	}

	return $success;
    }
    function getTouchpointData() {
	static $fieldMappings = array('id' => 'prospect_id_c',
				      'salutation' => '',
				      'first_name' => 'first_name',
				      'last_name' => 'last_name',
				      'email' => 'email1',
				      'password' => '',
				      'company' => 'company_name',
				      'website' => 'website',
				      'job_title' => 'title',
				      'department' => 'department',
				      'country' => 'primary_address_country',
				      'address_one' => 'primary_address_street',
				      'city' => 'primary_address_city',
				      'state' => 'primary_address_state',
				      'territory' => '',
				      'zip' => 'primary_address_postalcode',
				      'phone' => 'phone_work',
				      'fax' => 'phone_fax',
				      'source' => 'lead_source',
				      'annual_revenue' => 'annual_revenue',
				      'employees' => 'employees',
				      'industry' => 'industry',
				      'years_in_business' => '',
				      'comments' => '',
				      'notes' => '',
				      'score' => 'score',
				      'grade' => '',
				      'last_activity_at' => '',
				      'recent_interaction' => '',
				      'crm_lead_fid' => '',
				      'crm_contact_fid' => '',
				      'crm_owner_fid' => '',
				      'crm_account_fid' => '',
				      'crm_opportunity_fid' => '',
				      'crm_opportunity_created_at' => '',
				      'crm_opportunity_updated_at' => '',
				      'crm_opportunity_value' => '',
				      'crm_opportunity_status' => '',
				      'crm_last_activity' => '',
				      'crm_is_sale_won' => '',
				      'is_do_not_email' => 'email_opt_out',
				      'is_do_not_call' => 'do_not_call',
				      //'opted_out' => 'email_opt_out', // 2010/04/05 - SADEK - REMOVED THIS - IT DOESN'T ACTUALLY GET SET
				      'is_reviewed' => '',
				      'is_starred' => '',
				      'created_at' => '',
				      'updated_at' => '',

				      'Number_of_CRM_Users' => 'potential_users_c',
				      'eval_name' => '',
				      'data_migration' => 'data_migration',
				      'Call_Back_c' => 'call_back_c',
				      'yancAction' => '',
				      'agreement' => 'agreement',
				      'edition' => '',
				      'record' => '',
				      'registered_eval_c' => '',
				      'evalcreate' => '',
				      'status' => '',
				      'campaign_name' => 'campaign_id',
				      'lead_source_description' => 'lead_source_description',
				      'user' => '',
				      'team_id' => '',
				      'assigned_user_id' => '',
				      'option' => '',
				      'task' => '',
				      'formType' => '',
				      'invalidinstance' => '',
				      
				      'purchasing_timeline_c' => 'replace_timeline',
				      'trial_name' => 'trial_name',
				      'trial_expiration_c' => 'trial_expiration_c',			      
				      /*
				      * @author: DEE
				      * SUGARINTERNAL CUSTOMIZATION
				      * ITREQUEST 16710	
				      * Ability to map who the lead is assigned to and who it is submitted by. This is for importing partner leads
				      * Missing portal name mapping
	                              */
				      'partner_assigned_to_c' => 'partner_assigned_to_c',
				      'lead_submitter_c' => 'lead_submitter_c',
				      'portal_name' => 'portal_name',
				      /* END SUGARINTERNAL CUSTOMIZATION */
				      /*
				      * @author: DTam
				      * SUGARINTERNAL CUSTOMIZATION
				      * ITREQUEST 18828	
				      * Add CE form handler submissions
	                              */
				      'ce_usage_time_c' => 'ce_usage_time_c',
				      'ce_production_stage_c' => 'ce_production_stage_c',
				      'ce_user_profile_c' => 'ce_user_profile_c',
				      'ce_interest_level_c' => 'ce_interest_level_c',
				      /* END SUGARINTERNAL CUSTOMIZATION */
				      );
	$output = array('description' => '');
	foreach ($fieldMappings as $from => $to) {
	    if (isset($this->$from)) {
		if ($to) {
		    $output[$to] = $this->$from;
		} else {
		    $output[$from] = $this->$from;
		}
	    }
	}
	/* BEGIN jparsons ondemand eval creation */
	if ($output['evalcreate']) {
	    $dmg = isset($output['data_migration']) ? "yes" : "no";
	    $evg = isset($output['agreement']) ? "yes" : "no";
	    
	    $description = sprintf("Data Migration: %s\nEval Agreement: %s\n", $dmg, $evg);
	    $output['description'] .= $description;
	}
	/* END jparsons ondemand eval creation*/
	if (!empty($output['trial_name'])) {
	    $output['trial_name_c'] = 'http://trial.sugarcrm.com/' . $output['trial_name'];
	}
	return $output;
    }
}
