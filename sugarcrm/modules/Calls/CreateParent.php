<?php
if(!defined('sugarEntry') || !sugarEntry) die('Not A Valid Entry Point');
/*********************************************************
 * Create related Parent and attach created Call to the record
 *
 * Author: Felix Nilam
 * Date: 05/10/2007
 ********************************************************/

global $current_user;
global $timedate;
global $app_list_strings;
global $app_strings;
global $mod_strings;

require_once('fonality/include/InboundCall/inbound_call_config.php');
require_once('fonality/include/InboundCall/utils.php');
require_once('fonality/include/normalizePhone/normalizePhone.php');
require_once('modules/Contacts/Contact.php');
require_once('modules/Accounts/Account.php');
require_once('modules/Leads/Lead.php');
require_once('modules/Opportunities/Opportunity.php');
require_once('modules/Cases/Case.php');
require_once('modules/Calls/Call.php');

// Get relevant information from URL
$call_start_time = $_REQUEST['call_start_time'];
$create_type = $_REQUEST['type'];
$parent_type = $_REQUEST['parent_type'];
$parent_id = $_REQUEST['parent_id'];
$call_notes = $_REQUEST['call_notes'];
$direction = $_REQUEST['direction'];
$phone_no = $_REQUEST['phone_no'];
$contact_id = $_REQUEST['contact_id'];
if(empty($direction)){
	$direction = "Inbound";
}

function createNewCall($start_time, $phone, $direction, $description)
{
	global $current_user;
	$newcall = new Call();
	$newcall->team_id = $currrent_user->default_team;
	$newcall->assigned_user_id = $current_user->id;
	$newphone = $phone;
	if(empty($phone)){
		$unknown_phone = 1;
	} else {
		$nphone = normalizePhone($phone);
		if(empty($nphone)){
			$unknown_phone = 1;
		}
	}
	if($unknown_phone){
		$newphone = "Unknown";
	}
	if($direction == "Inbound"){
		$newcall->name = "New Call from $newphone";
	} else {
		$newcall->name = "New Call to $newphone";
	}
	$newcall->date_start = $start_time;
	$newcall->duration_hours = 0;
	$newcall->duration_minutes = 15;
	$newcall->phone_number_c = $nphone;
	$newcall->status = "Held";
	$newcall->direction = $direction;
	$newcall->description = $description;
	$newcall->save();
	$new_call_id = $newcall->id;
	return $new_call_id;
}

// Store new call id in cookie to be accessed later to 
// update the duration
setcookie("call_assistant_id", $new_call_id, time()+86400);

switch($create_type){

/*******************************************
 * Handle New Call
 */
	case "New":
	// Update the automatically created call
	$new_call_id = createNewCall($call_start_time, $phone_no, $direction, $call_notes);
	
	header("Location: index.php?module=Calls&action=EditView&record=".$new_call_id."&status=Held");
	break;
	
/*******************************************
 * Handle New Lead
 */
	case "NewLead":
	// Create a new Lead
	$lead = new Lead();
	$lead->first_name = "new";
	$lead->last_name = "new";
	$lead->team_id = $currrent_user->default_team;
	if(!empty($phone_no)){
		$lead->phone_work = $phone_no;
	}
	$lead->assigned_user_id = $current_user->id;
	$lead->description = $call_notes;
	$new_lead_id = $lead->save();

	// Update the automatically created call
	$new_call_id = createNewCall($call_start_time, $phone_no, $direction, $call_notes);
	$call = new Call();
	$call->retrieve($new_call_id);
	$call->parent_type = "Leads";
	$call->parent_id = $new_lead_id;
	$call->save();
	
	header("Location: index.php?module=Leads&action=EditView&record=".$new_lead_id);
	break;
	
/*******************************************
 * Handle New Contact
 */
	case "NewContact":
	// Create a new Contact
	$contact = new Contact();
	$contact->first_name = "new";
	$contact->last_name = "new";
	if(!empty($phone_no)){
		$contact->phone_work = $phone_no;
	}
	$contact->team_id = $currrent_user->default_team;
	$contact->assigned_user_id = $current_user->id;
	$contact->description = $call_notes;
	$new_contact_id = $contact->save();

	// Update the automatically created call
	$new_call_id = createNewCall($call_start_time, $phone_no, $direction, $call_notes);
	$call = new Call();
	$call->retrieve($new_call_id);
	$call->parent_type = 'Contacts';
	$call->parent_id = $new_contact_id;
	$call->save();
	$call->load_relationship('contacts');
	$call->contacts->add($new_contact_id);
	
	header("Location: index.php?module=Contacts&action=EditView&record=".$new_contact_id);
	break;
	
/*******************************************
 * Handle New Account
 */
	case "NewAccount":
	// Create a new Account
	$account = new Account();
	$account->name = "New Account";
	if(!empty($phone_no)){
		$account->phone_office = $phone_no;
	}
	$account->team_id = $currrent_user->default_team;
	$account->assigned_user_id = $current_user->id;
	$account->description = $call_notes;
	$account->save();
	$new_account_id = $account->id;

	// Update the automatically created call
	$new_call_id = createNewCall($call_start_time, $phone_no, $direction, $call_notes);
	$call = new Call();
	$call->retrieve($new_call_id);
	$call->parent_type = 'Accounts';
	$call->parent_id = $new_account_id;
	$call->save();
	
	header("Location: index.php?module=Accounts&action=EditView&record=".$new_account_id);
	break;
	
/*******************************************
 * Handle New Case
 */
	case "NewCase":
	// Create a new Case
	$case = new aCase();
	$case->name = "New Case";
	$case->team_id = $currrent_user->default_team;
	$case->assigned_user_id = $current_user->id;
	$case->description = $call_notes;
	$new_case_id = $case->save();

	// Update the automatically created call
	$new_call_id = createNewCall($call_start_time, $phone_no, $direction, $call_notes);
	$call = new Call();
	$call->retrieve($new_call_id);
	$call->parent_type = 'Cases';
	$call->parent_id = $new_case_id;
	$call->save();
	
	header("Location: index.php?module=Cases&action=EditView&record=".$new_case_id);
	break;
	
/*******************************************
 * Handle New Opportunity
 */
	case "NewOpp":
	// Create a new Opportunity
	$opp = new Opportunity();
	$opp->name = "New Opportunity";
	$opp->team_id = $currrent_user->default_team;
	$opp->amount = 0;
	$opp->date_closed = $timedate->to_display_date(date("Y-m-d"), false);
	$opp->sales_stage = $app_list_strings['sales_stage_default_key'];
	$opp->assigned_user_id = $current_user->id;
	$opp->description = $call_notes;
	$new_opp_id = $opp->save();

	// Update the automatically created call
	$new_call_id = createNewCall($call_start_time, $phone_no, $direction, $call_notes);
	$call = new Call();
	$call->retrieve($new_call_id);
	$call->parent_type = 'Opportunities';
	$call->parent_id = $new_opp_id;
	$call->save();
	
	header("Location: index.php?module=Opportunities&action=EditView&record=".$new_opp_id);
	break;
	
/*******************************************
 * Handle Open Planned Call
 */
	case "Planned":
	// Update the planned call and delete the automatically created one
	$new_call_id = createNewCall($call_start_time, $phone_no, $direction, $call_notes);
	$call = new Call();
	$call->retrieve($new_call_id);
	$start_date = $call->date_start;
	$phone_number = $call->phone_number_c;
	$call->mark_deleted($new_call_id);
	$call->retrieve($parent_id);
	$call->date_start = $start_date;
	$call->direction = $direction;
	if(!empty($call->description)){
		$call->description = $call->description . "\n\n".$call_notes;
	} else {
		$call->description = $call_notes;
	}
	$call->status = 'Held';
	$call->phone_number_c = $phone_number;
	$call->save();
	
	// Override the call id in cookie
	setcookie("call_assistant_id", $new_call_id, time()+86400);

	header("Location: index.php?module=Calls&action=EditView&record=".$parent_id."&status=Held");
	break;
	
/*******************************************
 * Handle Create Call
 */
	case "Calls":
	$new_call_id = createNewCall($call_start_time, $phone_no, $direction, $call_notes);
	$call = new Call();
	$call->retrieve($new_call_id);
	$call->parent_type = $parent_type;
	$call->parent_id = $parent_id;
	$call->save();
	
	if(!empty($contact_id)){
		$call->load_relationship('contacts');
		$call->contacts->add($contact_id);
	}
	
	header("Location: index.php?module=Calls&action=EditView&record=".$new_call_id."&status=Held");
	break;

/*******************************************
 * Handle Create Case
 */
	case "Cases":
	$case = new aCase();
	$case->name = "New Case";
	$case->team_id = $currrent_user->default_team;
	$case->assigned_user_id = $current_user->id;
	$case->status = 'New';
	if($parent_type == 'Accounts'){
		$case->account_id = $parent_id;
	} else if($parent_type == 'Contacts'){
		$this_contact = new Contact();
		$this_contact->retrieve($parent_id);
		if(!empty($this_contact->account_id)){
			$case->account_id = $this_contact->account_id;
		}
	}
	$case->description = $call_notes;
	$new_case_id = $case->save();
	
	if($parent_type == 'Contacts'){
		$case->retrieve($new_case_id);
		$case->load_relationship('contacts');
		$case->contacts->add($parent_id);
	}

	$new_call_id = createNewCall($call_start_time, $phone_no, $direction, $call_notes);
	$call = new Call();
	$call->retrieve($new_call_id);
	$call->parent_type = 'Cases';
	$call->parent_id = $new_case_id;
	$call->save();

	if($parent_type == 'Contacts'){
		$call->retrieve($new_call_id);
		$call->load_relationship('contacts');
		$call->contacts->add($parent_id);
	}

	header("Location: index.php?module=Cases&action=EditView&record=".$new_case_id);
	break;

/*******************************************
 * Handle Create Opportunity
 */
	case "Opportunities":
	$opportunity = new Opportunity();
	$opportunity->name = "New Opportuntity";
	$opportunity->team_id = $currrent_user->default_team;
	$opportunity->assigned_user_id = $current_user->id;
	$opportunity->description = $call_notes;
	$opportunity->amount = 0;
	$opportunity->date_closed = $timedate->to_display_date(date("Y-m-d"), false);
	$opportunity->sales_stage = $app_list_strings['sales_stage_default_key'];
	if($parent_type == 'Accounts'){
		$opportunity->account_id = $parent_id;
	} else if($parent_type == 'Contacts'){
		$this_contact = new Contact();
		$this_contact->retrieve($parent_id);
		if(!empty($this_contact->account_id)){
			$opportunity->account_id = $this_contact->account_id;
		}
	}
	$new_opp_id = $opportunity->save();
	
	if($parent_type == 'Contacts'){
		$opportunity->retrieve($new_opp_id);
		$opportunity->load_relationship('contacts');
		$opportunity->contacts->add($parent_id);
	}

	$new_call_id = createNewCall($call_start_time, $phone_no, $direction, $call_notes);
	$call = new Call();
	$call->retrieve($new_call_id);
	$call->parent_type = 'Opportunities';
	$call->parent_id = $new_opp_id;
	$call->save();

	if($parent_type == 'Contacts'){
		$call->retrieve($new_call_id);
		$call->load_relationship('contacts');
		$call->contacts->add($parent_id);
	}

	header("Location: index.php?module=Opportunities&action=EditView&record=".$new_opp_id);
	break;

/*******************************************
 * Handle Create Contact
 */
	case "Contacts":
	if($parent_type == 'Accounts'){
		$contact = new Contact();
		$contact->team_id = $currrent_user->default_team;
		$contact->assigned_user_id = $current_user->id;
		$contact->first_name = 'New';
		$contact->last_name = 'New';
		if(!empty($phone_no)){
			$contact->phone_work = $phone_no;
		}
		$contact->description = $call_notes;
		$contact->account_id = $parent_id;
		$new_contact_id = $contact->save();

		$new_call_id = createNewCall($call_start_time, $phone_no, $direction, $call_notes);
		$call = new Call();
		$call->retrieve($new_call_id);
		$call->parent_type = 'Accounts';
		$call->parent_id = $parent_id;
		$call->save();

		$call->retrieve($new_call_id);
		$call->load_relationship('contacts');
		$call->contacts->add($new_contact_id);

		header("Location: index.php?module=Contacts&action=EditView&record=".$new_contact_id);
	}
	break;

	default:
	break;
}
?>
