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

// $Id: populateSeedData.php,v 1.125 2006/06/16 22:15:55 wayne Exp $

require_once('config.php');
require_once('sugar_version.php');


if(isset($sugar_config['i18n_test']) && $sugar_config['i18n_test'] == true) 
	require_once('modules/Contacts/contactSeedData_jp.php');
else
	require_once('modules/Contacts/contactSeedData.php');

require_once('modules/Contacts/Contact.php');
require_once('modules/Leads/Lead.php');
require_once('modules/Accounts/Account.php');
require_once('modules/Opportunities/Opportunity.php');
require_once('modules/Tasks/Task.php');

require_once('include/utils.php');
require_once('include/language/en_us.lang.php');

require_once('modules/Currencies/Currency.php');

require_once('install/UserDemoData.php');
require_once('install/TeamDemoData.php');

// BEGIN SUGARCRM flav=pro ONLY 
require_once('modules/Teams/Team.php');
require_once('modules/TaxRates/TaxRate.php');
require_once('modules/Shippers/Shipper.php');
require_once('modules/Manufacturers/Manufacturer.php');
require_once('modules/ProductCategories/ProductCategory.php');
require_once('modules/ProductTypes/ProductType.php');
require_once('modules/ProductTemplates/ProductTemplate.php');
// END SUGARCRM flav=pro ONLY 

global $first_name_array;
global $first_name_count;
global $last_name_array;
global $last_name_count;
global $company_name_array;
global $company_name_count;
global $street_address_array;
global $street_address_count;
global $city_array;
global $city_array_count;
global $app_list_strings;
global $sugar_config;

if(empty($app_list_strings))$app_list_strings = return_app_list_strings_language('en_us');

// Seed the random number generator with a fixed constant.  This will make all installs of the same code
// have the same seed data.  This facilitates cross database testing..
mt_srand(93285903);



// BEGIN SUGARCRM flav=pro ONLY 
	//Turn disable_workflow to Yes so that we don't run workflow for any seed modules
	$_SESSION['disable_workflow'] = "Yes";
// END SUGARCRM flav=pro ONLY 



$db = & PearDatabase::getInstance();
require_once('include/TimeDate.php');
$timedate = new TimeDate();

// Set the max time to ~10 minutes (helps Windows load the seed data)
ini_set("max_execution_time", "601");

// ensure we have enough memory
$memory_needed  = 108;
$memory_limit   = ini_get('memory_limit');
if( $memory_limit != "" && $memory_limit != "-1" ){ // if memory_limit is set
    rtrim($memory_limit, 'M');
    $memory_limit_int = (int) $memory_limit;
    if( $memory_limit_int < $memory_needed ){
        ini_set("memory_limit", "$memory_needed" . "M");
    }
}

$large_scale_test = empty($sugar_config['large_scale_test']) ?
	false : $sugar_config['large_scale_test'];

$seed_user = new User();
$user_demo_data = new UserDemoData($seed_user, $large_scale_test);
if(isset($sugar_config['i18n_test']) && $sugar_config['i18n_test'] == true) {
	$user_demo_data->create_demo_data_jp();
} else {
	$user_demo_data->create_demo_data();
}
$number_contacts = 1000;
$number_companies = 150;
$number_leads = 1000;

$large_scale_test = empty($sugar_config['large_scale_test']) ?
	false : $sugar_config['large_scale_test'];

// If large scale test is set to true, increase the seed data.
if($large_scale_test)
{
	// increase the cuttoff time to 1 hour
	ini_set("max_execution_time", "3600");
	$number_contacts = 100000;
	$number_companies = 15000;
	$number_leads = 100000;
}

// BEGIN SUGARCRM flav=pro ONLY 

$seed_team = new Team();
$team_demo_data = new TeamDemoData($seed_team, $large_scale_test);
if(isset($sugar_config['i18n_test']) && $sugar_config['i18n_test'] == true) {
	$team_demo_data->create_demo_data_jp();
} else {
	$team_demo_data->create_demo_data();
}
// END SUGARCRM flav=pro ONLY 

function add_digits($quantity, &$string, $min = 0, $max = 9)
{
	for($i=0; $i < $quantity; $i++)
	{
		$string .= mt_rand($min,$max);
	}
}

function create_phone_number()
{
	$phone = "(";
	add_digits(3, $phone);
	$phone .= ") ";
	add_digits(3, $phone);
	$phone .= "-";
	add_digits(4, $phone);

	return $phone;
}

function create_date()
{
	global $timedate;

	$date = date($timedate->get_date_format(), mktime(0, 0, 0, date("m"), date("d")+mt_rand(0,365), date("Y")));
	return $date;
}

function create_time()
{
	return mt_rand(6,19).":".(mt_rand(0,3)*15).":00";
}

function create_past_date()
{
global $timedate;

	$date = date($timedate->get_date_format(), mktime(0, 0, 0, date("m"), date("d")+mt_rand(-365,-1), date("Y")));
	return $date;
}

$possible_duration_hours_arr = array( 0, 1, 2, 3);
$possible_duration_minutes_arr = array('00' => '00','15' => '15', '30' => '30', '45' => '45');

$account_ids = Array();
$accounts = Array();
$opportunity_ids = Array();

// Determine the assigned user for all demo data.  This is the default user if set, or admin
$assigned_user_name = "admin";
if(!empty($sugar_config['default_user_name']) &&
	!empty($sugar_config['create_default_user']) &&
	$sugar_config['create_default_user'])
{
	$assigned_user_name = $sugar_config['default_user_name'];
}

// Look up the user id for the assigned user
$seed_user = new User();

$assigned_user_id = $seed_user->retrieve_user_id($assigned_user_name);

$casePriorityTemp = $app_list_strings['case_priority_dom'];
$caseStatusTemp = $app_list_strings['case_status_dom'];
foreach($casePriorityTemp as $k => $p) { $casePriority[] = $k; }
foreach($caseStatusTemp as $s) { $caseStatus[] = $s; }


for($i = 0; $i < $number_companies; $i++)
{
	$account_name = $company_name_array[mt_rand(0,$company_name_count-1)].' '.mt_rand(1,1000000);

	// Create new accounts.
	$account = new Account();
	$account->name = $account_name;
	$account->phone_office = create_phone_number();
	$account->assigned_user_id = $assigned_user_id;

	$whitespace = array(" ", ".", "&", "\/");
	$website = str_replace($whitespace, "", strtolower($account->name));
	$account->website = 'www.'.$website.'.com';

	$account->billing_address_street = $street_address_array[mt_rand(0,$street_address_count-1)];
	$account->billing_address_city = $city_array[mt_rand(0,$city_array_count-1)];

	if($i % 3 == 1)
	{
		$account->billing_address_state = "NY";
// BEGIN SUGARCRM flav=pro ONLY 
		$account->team_id = "East";
// END SUGARCRM flav=pro ONLY 
		$assigned_user_id = mt_rand(9,10);
		if($assigned_user_id == 9)
		{
			$account->assigned_user_name = "will";
			$account->assigned_user_id = $account->assigned_user_name."_id";
		}
		else
		{
			$account->assigned_user_name = "chris";
			$account->assigned_user_id = $account->assigned_user_name."_id";
		}

		$account->assigned_user_id = $account->assigned_user_name."_id";
	}
	else
	{
		$account->billing_address_state = "CA";
// BEGIN SUGARCRM flav=pro ONLY 
		$account->team_id = "West";
// END SUGARCRM flav=pro ONLY 
		$assigned_user_id = mt_rand(6,8);
		if($assigned_user_id == 6)
		{
			$account->assigned_user_name = "sarah";
		}
		else if($assigned_user_id == 7)
		{
			$account->assigned_user_name = "sally";
		}
		else
		{
			$account->assigned_user_name = "max";
		}

		$account->assigned_user_id = $account->assigned_user_name."_id";
	}


// BEGIN SUGARCRM flav=pro ONLY 
	// If this is a large scale test, switch to the bulk teams 90% of the time.
	if ($large_scale_test && mt_rand(0,100) < 90) {
		$assigned_team = $team_demo_data->get_random_team();

		$account->team_id = $assigned_team."_id";
		$account->assigned_user_id = $account->team_id;
		$account->assigned_user_name = $assigned_team;
	}
// END SUGARCRM flav=pro ONLY 


	$account->billing_address_postalcode = mt_rand(10000, 99999);
	$account->billing_address_country = 'USA';

	$account->shipping_address_street = $account->billing_address_street;
	$account->shipping_address_city = $account->billing_address_city;
	$account->shipping_address_state = $account->billing_address_state;
	$account->shipping_address_postalcode = $account->billing_address_postalcode;
	$account->shipping_address_country = $account->billing_address_country;

	$key = array_rand($app_list_strings['industry_dom']);
	$account->industry = $app_list_strings['industry_dom'][$key];

	$account->account_type = "Customer";

	$account->save();

	$account_ids[] = $account->id;
	$accounts[] = $account;

	// Create a case for the account
	$case = new aCase();
	$case->account_id = $account->id;
	$case->priority = $casePriority[mt_rand(0,2)];
	$case->status = $caseStatus[mt_rand(0,4)];

if(isset($sugar_config['i18n_test']) && $sugar_config['i18n_test'] == true) {
	$case_seed_names = array(
		'ã??ã‚Œã?®ãƒ—ãƒ©ã‚°ã‚’å·®ã?—è¾¼ã‚€ã?“ã?¨ã‚’æ‚©ã?¿ã‚’æŒ?ã?£ã?¦ã?„ã‚‹',
		'ã‚·ã‚¹ãƒ†ãƒ ã?¯ä½™ã‚Šã?«é€Ÿã?„è¡Œã?£ã?¦ã?„ã‚‹',
		'å¤§ã??ã?„ã‚«ã‚¹ã‚¿ãƒ åŒ–ã‚’ç”¨ã?„ã‚‹å¿…è¦?æ€§ã?®æ?´åŠ©',
		'ä»˜åŠ çš„ã?ªå…?è¨±è¨¼ã‚’è³¼å…¥ã?™ã‚‹å¿…è¦?æ€§',
		'é–“é?•ã?£ã?Ÿãƒ–ãƒ©ã‚¦ã‚¶ã‚’ä½¿ç”¨ã?™ã‚‹å ´å?ˆã?®è­¦å‘Šãƒ¡ãƒƒã‚»ãƒ¼ã‚¸'
	);
} else {
	$case_seed_names = array(
		'Having Trouble Plugging It In',
		'System is Performing Too Fast',
		'Need assistance with large customization',
		'Need to Purchase Additional Licenses',
		'Warning message when using the wrong browser'
	);
}
	$case->name = $case_seed_names[mt_rand(0,4)];
	$case->assigned_user_id = $account->assigned_user_id;
	$case->assigned_user_name = $account->assigned_user_name;
// BEGIN SUGARCRM flav=pro ONLY 
	$case->team_id = $account->team_id;
// END SUGARCRM flav=pro ONLY 
	$case->save();


	// Create simple notes:
if(isset($sugar_config['i18n_test']) && $sugar_config['i18n_test'] == true) {
	$note_seed_names_and_Descriptions = array(
		array('ã‚ˆã‚Šå¤šã??ã?®ã‚¢ã‚«ã‚¦ãƒ³ãƒˆæƒ…å ± ','ã?“ã‚Œã?¯3,000äººã?®ãƒ¦ãƒ¼ã‚¶ãƒ¼ã?®æ©Ÿä¼šã?«å›žã‚‹ã?“ã?¨ã?Œã?§ã??ã‚‹'),
		array('å‘¼ã?³å‡ºã?—æƒ…å ±','ç§?é?”ã?¯å‘¼å‡ºã?—ã‚’æœ‰ã?—ã?Ÿã€‚  å‘¼å‡ºã?—ã?¯ã?†ã?¾ã??ã?„ã?„ã?Ÿã€‚'),
		array('èª•ç”Ÿæ—¥æƒ…å ±','æ‰€æœ‰è€…ã?¯10æœˆã?«ç”Ÿã?¾ã‚Œã?Ÿ'),
		array('ä¼‘æ—¥ã‚®ãƒ•ãƒˆ','ä¼‘æ—¥ã‚®ãƒ•ãƒˆã?¯èª?ã‚?ã‚‰ã‚Œã?Ÿã€‚  ã??ã‚Œã‚‰æ?¥å¹´ã?®ãƒªã‚¹ãƒˆã?«ã?¾ã?Ÿç½®ã?‹ã‚Œã‚‹ã€‚')
	);
} else {
	$note_seed_names_and_Descriptions = array(
		array('More Account Information','This could turn into a 3,000 user opportunity'),
		array('Call Information','We had a call.  The call went well.'),
		array('Birthday Information','The Owner was born in October'),
		array('Holliday Gift','The holliday gift was appreciated.  Put them on the list for next year as well.')
	);
}
	$note = new Note();
	$note->parent_type = 'Accounts';
	$note->parent_id = $account->id;
	$seed_data_index = mt_rand(0,3);
	$note->name = $note_seed_names_and_Descriptions[$seed_data_index][0];
	$note->description = $note_seed_names_and_Descriptions[$seed_data_index][1];
	$note->assigned_user_id = $account->assigned_user_id;
	$note->assigned_user_name = $account->assigned_user_name;
// BEGIN SUGARCRM flav=pro ONLY 
	$note->team_id = $account->team_id;
// END SUGARCRM flav=pro ONLY 
	$note->save();


	// Create simple calls:
if(isset($sugar_config['i18n_test']) && $sugar_config['i18n_test'] == true) {
	$call_seed_data_names = array(
		'æ??æ¡ˆã?•ã‚Œã?Ÿå?–ã‚Šå¼•ã??ã?®ã‚ˆã‚Šå¤šã??ã?®æƒ…å ±ã‚’æ‰‹ã?«å…¥ã‚Œã?ªã?•ã?„',
		'å·¦ãƒ¡ãƒƒã‚»ãƒ¼ã‚¸',
		'è‹¦å¢ƒã?¯ã€?å‘¼ã?¶',
		'æ¤œè¨Žãƒ—ãƒ­ã‚»ã‚¹ã‚’è«–è­°ã?—ã?ªã?•ã?„'
	);
} else {
	$call_seed_data_names = array(
		'Get More information on the proposed deal',
		'Left a message',
		'Bad time, will call back',
		'Discuss Review Process'
	);
}
	$call = new Call();
	$call->parent_type = 'Accounts';
	$call->parent_id = $account->id;
	$call->name = $call_seed_data_names[mt_rand(0,3)];
	$call->assigned_user_id = $account->assigned_user_id;
	$call->assigned_user_name = $account->assigned_user_name;
	$call->direction='Outbound';
	$call->date_start = create_date();
	$call->time_start = create_time();
	$call->duration_hours='0';
	$call->duration_minutes='30';
	$call->account_id =$account->id;
	$call->status='Planned';


// BEGIN SUGARCRM flav=pro ONLY 
	$call->team_id = $account->team_id;
// END SUGARCRM flav=pro ONLY 
	$call->save();



	//Create new opportunities
	$opp = new Opportunity();

// BEGIN SUGARCRM flav=pro ONLY 
	$opp->team_id = $account->team_id;
// END SUGARCRM flav=pro ONLY 

	$opp->assigned_user_id = $account->assigned_user_id;
	$opp->assigned_user_name = $account->assigned_user_name;

	$opp->name = $account_name." - 1000 units";
	$opp->date_closed = create_date();

	$key = array_rand($app_list_strings['lead_source_dom']);
	$opp->lead_source = $app_list_strings['lead_source_dom'][$key];

	$key = array_rand($app_list_strings['sales_stage_dom']);
	$opp->sales_stage = $app_list_strings['sales_stage_dom'][$key];

	// If the deal is already one, make the date closed occur in the past.
	if($opp->sales_stage == "Closed Won" || $opp->sales_stage == "Closed Lost")
	{
		$opp->date_closed = create_past_date();
	}

	$key = array_rand($app_list_strings['opportunity_type_dom']);
	$opp->opportunity_type = $app_list_strings['opportunity_type_dom'][$key];

	$amount = array("10000", "25000", "50000", "75000");
	$key = array_rand($amount);
	$opp->amount = $amount[$key];

	$probability = array("10", "70", "40", "60");
	$key = array_rand($probability);
	$opp->probability = $probability[$key];

	$opp->save();

	$opportunity_ids[] = $opp->id;
	// Create a linking table entry to assign an account to the opportunity.
	$opp->set_relationship('accounts_opportunities', array('opportunity_id'=>$opp->id ,'account_id'=> $account->id), false);

}

$titles = array("President",
				"VP Operations",
				"VP Sales",
				"Director Operations",
				"Director Sales",
				"Mgr Operations",
				"IT Developer",
				"");

$account_max = count($account_ids) - 1;

$first_name_max = $first_name_count - 1;
$last_name_max = $last_name_count - 1;
$street_address_max = $street_address_count - 1;
$city_array_max = $city_array_count - 1;
$lead_source_max = count($app_list_strings['lead_source_dom']) - 1;
$lead_status_max = count($app_list_strings['lead_status_dom']) - 1;
$title_max = count($titles) - 1;
$lead_status_keys = array_keys($app_list_strings['lead_status_dom']);
$lead_source_keys = array_keys($app_list_strings['lead_source_dom']);


for($i=0; $i<$number_contacts; $i++)
{
	$contact = new Contact();

	$contact->first_name = $first_name_array[mt_rand(0,$first_name_max)];
	$contact->last_name = $last_name_array[mt_rand(0,$last_name_max)];
	$contact->assigned_user_id = $account->assigned_user_id;
	$contact->email1 = $contact->first_name."_".$contact->last_name."@example.com";
	$contact->primary_address_street = $street_address_array[mt_rand(0,$street_address_max)];
	$contact->primary_address_city = $city_array[mt_rand(0,$city_array_max)];
	$contact->lead_source = $app_list_strings['lead_source_dom'][array_rand($app_list_strings['lead_source_dom'])];
	$contact->title = $titles[mt_rand(0,$title_max)];

// BEGIN SUGARCRM flav=pro ONLY 
/* comment out the non-pro code
for($i=0; $i<1000; $i++)
{
// END SUGARCRM flav=pro ONLY 
		$contact = new Contact();

	$contact->first_name = ucfirst(strtolower($first_name_array[$i]));
	$contact->last_name = ucfirst(strtolower($last_name_array[$i]));
	$contact->assigned_user_id = $assigned_user_id;
	$contact->email1 = strtolower($contact->first_name)."_".strtolower($contact->last_name)."@example.com";
	$key = array_rand($street_address_array);
	$contact->primary_address_street = $street_address_array[$key];
	$key = array_rand($city_array);
	$contact->primary_address_city = $city_array[$key];

	$key = array_rand($app_list_strings['lead_source_dom']);
	$contact->lead_source = $app_list_strings['lead_source_dom'][$key];

	$key = array_rand($titles);
	$contact->title = $titles[$key];

// BEGIN SUGARCRM flav=pro ONLY 
*/
// END SUGARCRM flav=pro ONLY 
	$contact->phone_work = create_phone_number();
	$contact->phone_home = create_phone_number();
	$contact->phone_mobile = create_phone_number();

	$account_number = mt_rand(0,$account_max);
	$account_id = $account_ids[$account_number];

	// Fill in a bogus address
	$contacts_account = $accounts[$account_number];
	$contact->primary_address_state = $contacts_account->billing_address_state;
// BEGIN SUGARCRM flav=pro ONLY 
	$contact->team_id = $contacts_account->team_id;
// END SUGARCRM flav=pro ONLY 
	$contact->assigned_user_id = $contacts_account->assigned_user_id;
	$contact->assigned_user_name = $contacts_account->assigned_user_name;

	$contact->primary_address_postalcode = mt_rand(10000,99999);
	$contact->primary_address_country = 'USA';

	$contact->save();

	// Create a linking table entry to assign an account to the contact.
	$contact->set_relationship('accounts_contacts', array('contact_id'=>$contact->id ,'account_id'=> $account_id), false);

	// This assumes that there will be one opportunity per company in the seed data.
	$opportunity_key = array_rand($opportunity_ids);
	$contact->set_relationship('opportunities_contacts', array('contact_id'=>$contact->id ,'opportunity_id'=> $opportunity_ids[$opportunity_key], 'contact_role'=>$app_list_strings['opportunity_relationship_type_default_key']), false);

	//Create new tasks
	$task = new Task();

	$key = array_rand($task->default_task_name_values);
	$task->name = $task->default_task_name_values[$key];
	$task->date_due = create_date();
	$task->time_due = date("H:i:s",time());
	$task->date_due_flag = 'off';
// BEGIN SUGARCRM flav=pro ONLY 
	$task->team_id = $contacts_account->team_id;
// END SUGARCRM flav=pro ONLY 
	$task->assigned_user_id = $contacts_account->assigned_user_id;
	$task->assigned_user_name = $contacts_account->assigned_user_name;

	$key = array_rand($app_list_strings['task_status_dom']);
	$task->status = $app_list_strings['task_status_dom'][$key];
	$task->contact_id = $contact->id;
	if ($contact->primary_address_city == "San Mateo") {
		$task->parent_id = $account_id;
		$task->parent_type = 'Accounts';
		$task->save();
	}

	//Create new meetings
	$meeting = new Meeting();

	$key = array_rand($meeting->default_meeting_name_values);
	$meeting->name = $meeting->default_meeting_name_values[$key];
	$meeting->date_start = create_date();
	$meeting->time_start = date("H:i",time());
	$meeting->duration_hours = array_rand($possible_duration_hours_arr);
	$meeting->duration_minutes = array_rand($possible_duration_minutes_arr);
	$meeting->assigned_user_id = $assigned_user_id;
// BEGIN SUGARCRM flav=pro ONLY 
	$meeting->team_id = $contacts_account->team_id;
// END SUGARCRM flav=pro ONLY 
	$meeting->assigned_user_id = $contacts_account->assigned_user_id;
	$meeting->assigned_user_name = $contacts_account->assigned_user_name;
	$meeting->description = 'Meeting to discuss project plan and hash out the details of implementation';

	$key = array_rand($app_list_strings['meeting_status_dom']);
	$meeting->status = $app_list_strings['meeting_status_dom'][$key];
	$meeting->contact_id = $contact->id;
	$meeting->parent_id = $account_id;
	$meeting->parent_type = 'Accounts';

    // dont update vcal
    $meeting->update_vcal  = false;

	$meeting->save();

	// leverage the seed user to set the acceptance status on the meeting.
	$seed_user->id = $meeting->assigned_user_id;
    $meeting->set_accept_status($seed_user,'accept');

	//Create new emails
	$email = new Email();

	$key = array_rand($email->default_email_subject_values);
	$email->name = $email->default_email_subject_values[$key];
	$email->date_start = create_date();
	$email->time_start = date("H:i",time());
	$email->duration_hours = array_rand($possible_duration_hours_arr);
	$email->duration_minutes = array_rand($possible_duration_minutes_arr);
	$email->assigned_user_id = $assigned_user_id;
// BEGIN SUGARCRM flav=pro ONLY 
	$email->team_id = $contacts_account->team_id;
// END SUGARCRM flav=pro ONLY 
	$email->assigned_user_id = $contacts_account->assigned_user_id;
	$email->assigned_user_name = $contacts_account->assigned_user_name;
	$email->description = 'Discuss project plan and hash out the details of implementation';

	$email->status = 'sent';
	$email->contact_id = $contact->id;
	$email->parent_id = $account_id;
	$email->parent_type = 'Accounts';
	$email->save();
}

for($i=0; $i<$number_leads; $i++)
{
	$lead = new Lead();

	$lead->account_name = $company_name_array[mt_rand(0,$company_name_count-1)].' '.mt_rand(1,1000000);

	$lead->first_name = $first_name_array[mt_rand(0,$first_name_max)];
	$lead->last_name = $last_name_array[mt_rand(0,$last_name_max)];
	$lead->assigned_user_id = //$account->assigned_user_id;
	$lead->email1 = $lead->first_name."_".$lead->last_name."@example.com";
	$lead->primary_address_street = $street_address_array[mt_rand(0,$street_address_max)];
	$lead->primary_address_city = $city_array[mt_rand(0,$city_array_max)];
	$lead->lead_source = $app_list_strings['lead_source_dom'][array_rand($app_list_strings['lead_source_dom'])];
	$lead->title = $titles[mt_rand(0,$title_max)];

	$lead->phone_work = create_phone_number();
	$lead->phone_home = create_phone_number();
	$lead->phone_mobile = create_phone_number();

	// Fill in a bogus address
	$lead->primary_address_state = "CA";

	$leads_account = $accounts[$account_number];
	$lead->primary_address_state = $leads_account->billing_address_state;

	$lead->status = $lead_status_keys[mt_rand(0,$lead_status_max)];
	$lead->lead_source = $lead_source_keys[mt_rand(0,$lead_source_max)];

	if($i % 3 == 1)
	{
		$lead->billing_address_state = "NY";
// BEGIN SUGARCRM flav=pro ONLY 
			$lead->team_id = "East";
// END SUGARCRM flav=pro ONLY 
			$assigned_user_id = mt_rand(9,10);
			if($assigned_user_id == 9)
			{
				$lead->assigned_user_name = "will";
				$lead->assigned_user_id = $lead->assigned_user_name."_id";
			}
			else
			{
				$lead->assigned_user_name = "chris";
				$lead->assigned_user_id = $lead->assigned_user_name."_id";
			}

			$lead->assigned_user_id = $lead->assigned_user_name."_id";
		}
		else
		{
			$lead->billing_address_state = "CA";
// BEGIN SUGARCRM flav=pro ONLY 
			$lead->team_id = "West";
// END SUGARCRM flav=pro ONLY 
			$assigned_user_id = mt_rand(6,8);
			if($assigned_user_id == 6)
			{
				$lead->assigned_user_name = "sarah";
			}
			else if($assigned_user_id == 7)
			{
				$lead->assigned_user_name = "sally";
			}
			else
			{
				$lead->assigned_user_name = "max";
			}

			$lead->assigned_user_id = $lead->assigned_user_name."_id";
		}


	// If this is a large scale test, switch to the bulk teams 90% of the time.
	if ($large_scale_test && mt_rand(0,100) < 90)
	{
		$assigned_team = $team_demo_data->get_random_team();

		$lead->team_id = $assigned_team."_id";
		$lead->assigned_user_id = $account->team_id;
		$lead->assigned_user_name = $assigned_team;
	}
	$lead->primary_address_postalcode = mt_rand(10000,99999);
	$lead->primary_address_country = 'USA';

	$lead->save();

}

// BEGIN SUGARCRM flav=pro ONLY 

//create timeperiods - pro only
require_once('modules/TimePeriods/TimePeriod.php');
require_once('modules/Forecasts/ForecastSchedule.php');
require_once('modules/Forecasts/Forecast.php');
require_once('include/TimeDate.php');
require_once('modules/Forecasts/ForecastOpportunities.php');
require_once('modules/Forecasts/ForecastDirectReports.php');

require_once('modules/Forecasts/Common.php');


$timeperiods=array();
$arr_today = getdate();
$timedate = new TimeDate();
$timeperiod = new TimePeriod();
$timeperiod->name = "Year ".$arr_today['year'];
$timeperiod->start_date = $timedate->to_display_date($arr_today['year']."-01-01");
$timeperiod->end_date = $timedate->to_display_date($arr_today['year']."-12-31");
$timeperiod->is_fiscal_year =1;
$fiscal_year_id=$timeperiod->save();

//create a time period record for the first quarter.
$timeperiod = new TimePeriod();
$timeperiod->name = "Q1 ".$arr_today['year'];
$timeperiod->start_date = $timedate->to_display_date($arr_today['year']."-01-01");
$timeperiod->end_date = $timedate->to_display_date($arr_today['year']."-03-31");
$timeperiod->is_fiscal_year =0;
$timeperiod->parent_id=$fiscal_year_id;
$current_timeperiod_id = $timeperiod->save();
$timeperiods[$current_timeperiod_id]=$timeperiod->start_date;
//create a timeperiod record for the 2nd quarter.
$timeperiod = new TimePeriod();
$timeperiod->name = "Q2 ".$arr_today['year'];
$timeperiod->start_date = $timedate->to_display_date($arr_today['year']."-04-01");
$timeperiod->end_date = $timedate->to_display_date($arr_today['year']."-06-30");
$timeperiod->is_fiscal_year =0;
$timeperiod->parent_id=$fiscal_year_id;
$current_timeperiod_id = $timeperiod->save();
$timeperiods[$current_timeperiod_id]=$timeperiod->start_date;
//create a timeperiod record for the 3rd quarter.
$timeperiod = new TimePeriod();
$timeperiod->name = "Q3 ".$arr_today['year'];
$timeperiod->start_date = $timedate->to_display_date($arr_today['year']."-07-01");
$timeperiod->end_date = $timedate->to_display_date($arr_today['year']."-09-30");
$timeperiod->is_fiscal_year =0;
$timeperiod->parent_id=$fiscal_year_id;
$current_timeperiod_id = $timeperiod->save();
$timeperiods[$current_timeperiod_id]=$timeperiod->start_date;
//create a timeperiod record for the 4th quarter.
$timeperiod = new TimePeriod();
$timeperiod->name = "Q4 ".$arr_today['year'];
$timeperiod->start_date = $timedate->to_display_date($arr_today['year']."-10-01");
$timeperiod->end_date = $timedate->to_display_date($arr_today['year']."-12-31");
$timeperiod->is_fiscal_year =0;
$timeperiod->parent_id=$fiscal_year_id;
$current_timeperiod_id = $timeperiod->save();
$timeperiods[$current_timeperiod_id]=$timeperiod->start_date;

//build a collection of users
$query = "SELECT id from users";
$result = $db->query($query, false,"error fetching users collection:");
$timedate = new TimeDate();
$comm = new Common();
$commit_order=$comm->get_forecast_commit_order();

foreach ($timeperiods as $timeperiod_id=>$start_date) {
	foreach($commit_order as $commit_type_array) {

		//create forecast schedule for this timeperiod record and user.
		//create forecast schedule using this record becuse there will be one
		//direct entry per user, and some user will have a Rollup entry too.
		if ($commit_type_array[1] == 'Direct') {
			$fcst_schedule = new ForecastSchedule();
			$fcst_schedule->timeperiod_id=$timeperiod_id;
			$fcst_schedule->user_id=$commit_type_array[0];
			$fcst_schedule->cascade_hierarchy=0;
			$fcst_schedule->forecast_start_date=$start_date;
			$fcst_schedule->status='Active';
			$fcst_schedule->save();

			//commit a direct forecast for this user and timeperiod.
			$forecastopp = new ForecastOpportunities();
			$forecastopp->current_timeperiod_id = $timeperiod_id;
			$forecastopp->current_user_id = $commit_type_array[0];
			$opp_summary_array= $forecastopp->get_opportunity_summary();

			$forecast = new Forecast();
			$forecast->timeperiod_id=$timeperiod_id;
			$forecast->user_id =  $commit_type_array[0];
			$forecast->opp_count= $opp_summary_array['OPPORTUNITYCOUNT'];
			$forecast->opp_weigh_value=$opp_summary_array['WEIGHTEDVALUENUMBER'];
			$forecast->best_case=$opp_summary_array['WEIGHTEDVALUENUMBER'] + 500;
			$forecast->worst_case=$opp_summary_array['WEIGHTEDVALUENUMBER'] + 500;
			$forecast->likely_case=$opp_summary_array['WEIGHTEDVALUENUMBER'] + 500;			
			$forecast->forecast_type='Direct';
			$forecast->date_committed = $timedate->to_display_date_time(date("Y-m-d H:i:s", time()), true);

			$forecast->save();
		} else {

			//create where clause....
			$where  = " users.deleted=0 ";
			$where .= " AND (users.id = '$commit_type_array[0]'";
			$where .= " or users.reports_to_id = '$commit_type_array[0]')";

			//Get the forecasts created by the direct reports.
			$DirReportsFocus = new ForecastDirectReports();
			$DirReportsFocus->current_user_id=$commit_type_array[0];
			$DirReportsFocus->current_timeperiod_id=$timeperiod_id;
			$DirReportsFocus->compute_rollup_totals('',$where);

			$forecast = new Forecast();
			$forecast->timeperiod_id=$timeperiod_id;
			$forecast->user_id =  $commit_type_array[0];
			$forecast->opp_count= $DirReportsFocus->total_opp_count;
			$forecast->opp_weigh_value=$DirReportsFocus->total_weigh_value_number;
			$forecast->likely_case=$DirReportsFocus->total_weigh_value_number + 500;
			$forecast->best_case=$DirReportsFocus->total_weigh_value_number + 500;
			$forecast->worst_case=$DirReportsFocus->total_weigh_value_number + 500;
			$forecast->forecast_type='Rollup';
			$forecast->date_committed = $timedate->to_display_date_time(date("Y-m-d H:i:s", time()), true);

			$forecast->save();
		}

	}

}
//end create timeperiods, pro only.

$manufacturer = new Manufacturer;
$manufacturer->name = "TekkyWare Inc.";
$manufacturer->status = "Active";
$manufacturer->list_order = "1";
$manufacturer->save();
$tekkyware_id = $manufacturer->id;

unset($manufacturer);
$manufacturer = new Manufacturer;
$manufacturer->name = "Wally's Widget World";
$manufacturer->status = "Active";
$manufacturer->list_order = "1";
$manufacturer->save();
$widgetworld_id = $manufacturer->id;

$shipper = new Shipper;
$shipper->name = "FedEx";
$shipper->status = "Active";
$shipper->list_order = "2";
$shipper->save();
$fedex_id = $shipper->id;

unset($shipper);
$shipper = new Shipper;
$shipper->name = "USPS Ground";
$shipper->status = "Active";
$shipper->list_order = "1";
$shipper->save();
$usps_id = $shipper->id;

$category = new ProductCategory;
$category->name = "Desktops";
$category->list_order = "1";
$category->save();
$desktops_id = $category->id;

unset($category);
$category = new ProductCategory;
$category->name = "Laptops";
$category->list_order = "1";
$category->save();
$laptops_id = $category->id;

unset($category);
$category = new ProductCategory;
$category->name = "Stationary Widgets";
$category->list_order = "1";
$category->save();
$stationary_id = $category->id;

unset($category);
$category = new ProductCategory;
$category->name = "Wobbly Widgets";
$category->list_order = "2";
$category->save();
$wobbly_id = $category->id;

$type = new ProductType;
$type->name = "Widgets";
$type->list_order = "1";
$type->save();
$widgets_id = $type->id;

unset($type);
$type = new ProductType;
$type->name = "Hardware";
$type->list_order = "2";
$type->save();
$hardware_id = $type->id;

unset($type);
$type = new ProductType;
$type->name = "Support Contract";
$type->list_order = "3";
$type->save();
$support_id = $type->id;

$taxrate = new TaxRate;
$taxrate->name = "8.25 - Cupertino, CA";
$taxrate->value = 8.25;
$taxrate->status = "Active";
$taxrate->list_order = "1";
$taxrate->save();
$taxrate_id = $taxrate->id;

$currency = new Currency;
$currency->name = "Euro";
$currency->status = "Active";
$currency->conversion_rate = 0.9;
$currency->iso4217 = "EUR";
$currency->symbol = "&#8364;";
$currency->save();
$euro_id = $currency->id;

$dollar_id = '-99';

$template = new ProductTemplate;
$template->name = "TK 1000 Desktop";
$template->tax_class = "Taxable";
$template->manufacturer_id = $tekkyware_id;
$template->currency_id = $dollar_id;
$template->cost_price = 500.00;
$template->cost_usdollar = 500.00;
$template->list_price = 800.00;
$template->list_usdollar = 800.00;
$template->discount_price = 800.00;
$template->discount_usdollar = 800.00;
$template->pricing_formula = "IsList";
$template->mft_part_num = "XYZ7890122222";
$template->pricing_factor = "1";
$template->status = "Available";
$template->weight = 20.0;
$template->date_available = "2004-10-15";
$template->qty_in_stock = "72";
$template->category_id = $desktops_id;
$template->type_id = $hardware_id;
$template->save();

unset($template);
$template = new ProductTemplate;
$template->name = "TK 2000 Desktop";
$template->tax_class = "Taxable";
$template->manufacturer_id = $tekkyware_id;
$template->currency_id = $dollar_id;
$template->cost_price = 600.00;
$template->cost_usdollar = 600.00;
$template->list_price = 900.00;
$template->list_usdollar = 900.00;
$template->discount_price = 900.00;
$template->discount_usdollar = 900.00;
$template->pricing_formula = "IsList";
$template->pricing_factor = "1";
$template->mft_part_num = "XYZ7890123456";
$template->status = "Available";
$template->date_available = "2004-10-15";
$template->qty_in_stock = "65";
$template->weight = 20.0;
$template->category_id = $desktops_id;
$template->type_id = $hardware_id;
$template->save();

unset($template);
$template = new ProductTemplate;
$template->name = "TK m30 Laptop";
$template->tax_class = "Taxable";
$template->manufacturer_id = $tekkyware_id;
$template->currency_id = $dollar_id;
$template->cost_price = 1300.00;
$template->cost_usdollar = 1300.00;
$template->list_price = 1700.00;
$template->list_usdollar = 1700.00;
$template->discount_price = 1625.00;
$template->discount_usdollar = 1625.00;
$template->pricing_formula = "ProfitMargin";
$template->pricing_factor = "20";
$template->mft_part_num = "ABCD123456890";
$template->weight = 5.0;
$template->status = "Available";
$template->date_available = "2004-10-15";
$template->qty_in_stock = "12";
$template->category_id = $laptops_id;
$template->type_id = $hardware_id;
$template->save();

unset($template);
$template = new ProductTemplate;
$template->name = "Reflective Mirror Widget";
$template->tax_class = "Non-Taxable";
$template->manufacturer_id = $widgetworld_id;
$template->currency_id = $dollar_id;
$template->cost_price = 200.00;
$template->cost_usdollar = 200.00;
$template->list_price = 325.00;
$template->list_usdollar = 325.00;
$template->discount_price = 266.50;
$template->discount_usdollar = 266.50;
$template->pricing_formula = "PercentageDiscount";
$template->mft_part_num = "2.0";
$template->pricing_factor = "20";
$template->status = "Available";
$template->date_available = "2004-10-25";
$template->category_id = $wobbly_id;
$template->type_id = $widgets_id;
$template->save();

include_once('modules/TeamNotices/DefaultNotices.php');

///
/// SEED DATA FOR CONTRACTS
///

include_once('modules/Contracts/Contract.php');

$contract = new Contract();
$contract->name = 'IT Tech Support for Moon Base';
$contract->reference_code = 'EMP-9802';
$contract->status = 'signed';
$contract->account_id = $account->id;
$contract->total_contract_value = '500600.01';
$contract->team_id = 1;
$contract->assigned_user_id = 'will_id';
$contract->start_date = '2010-05-15';
$contract->end_date = '2020-05-15';
$contract->company_signed_date = '2006-03-15';
$contract->customer_signed_date = '2006-03-16';
$contract->description = 'This is a sub-contract for a very large, very hush-hush project on the moon of Endor.';
$contract->save();

$contract = new Contract();
$contract->name = 'Ion Engines for Empire';
$contract->reference_code = 'EMP-7277';
$contract->status = 'signed';
$contract->account_id = $account->id;
$contract->total_contract_value = '333444.34';
$contract->team_id = 1;
$contract->assigned_user_id = 'jim_id';
$contract->company_signed_date = '2006-03-12';
$contract->customer_signed_date = '2006-03-14';
$contract->description = 'In competition with Sienar Fleet Systems for this one.';
$contract->save();

// END SUGARCRM flav=pro ONLY 

///
/// SEED DATA FOR PROJECT AND PROJECT TASK
///

include_once('modules/Project/Project.php');
include_once('modules/ProjectTask/ProjectTask.php');

// Project: Take Over The World

$project = new Project();
$project->name = 'Take Over The World';
$project->description = 'World conquest has always been a passion of mine.';
$project->assigned_user_id = 1;
// BEGIN SUGARCRM flav=pro ONLY 
$project->team_id = 1;
// END SUGARCRM flav=pro ONLY 
$take_over_world_id = $project->save();

$project_task = new ProjectTask();
$project_task->assigned_user_id = 1;
// BEGIN SUGARCRM flav=pro ONLY 
$project_task->team_id = 1;
// END SUGARCRM flav=pro ONLY 
$project_task->name = 'Make lots of money';
$project_task->status = 'Completed';
$project_task->date_due = create_date();
$project_task->time_due = '08:00:00';
$project_task->date_start = create_past_date();
$project_task->time_start = '08:00:00';
$project_task->parent_id = $take_over_world_id;
$project_task->priority = 'High';
$project_task->description = "Need proper funding for such an ambitious undertaking.  Be sure to ask Moriarty for some cash--he owes me a favor.";
$project_task->order_number = 1;
$project_task->task_number = 101;
$project_task->estimated_effort = 40;
$project_task->actual_effort = 45;
$project_task->utilization = 100;
$project_task->percent_complete = 100;
$make_money_id = $project_task->save();

$project_task = new ProjectTask();
$project_task->assigned_user_id = 1;
// BEGIN SUGARCRM flav=pro ONLY 
$project_task->team_id = 1;
// END SUGARCRM flav=pro ONLY 
$project_task->name = 'Build fortress';
$project_task->status = 'In Progress';
$project_task->date_due = create_date();
$project_task->time_due = '07:00:00';
$project_task->date_start = create_past_date();
$project_task->time_start = '08:00:00';
$project_task->parent_id = $take_over_world_id;
$project_task->priority = 'Medium';
$project_task->description = 'Must be secluded, but close to an interstate highway exit.  Would be great if it was in a good school zone.';
$project_task->order_number = 2;
$project_task->task_number = 102;
$project_task->depends_on_id = $make_money_id;
$project_task->estimated_effort = 40;
$project_task->actual_effort = 45;
$project_task->utilization = 100;
$project_task->percent_complete = 50;
$build_fortress_id = $project_task->save();

$project_task = new ProjectTask();
$project_task->assigned_user_id = 1;
// BEGIN SUGARCRM flav=pro ONLY 
$project_task->team_id = 1;
// END SUGARCRM flav=pro ONLY 
$project_task->name = 'Purchase giant death ray gun';
$project_task->status = 'Not Started';
$project_task->date_due = create_date();
$project_task->time_due = '07:00:00';
$project_task->date_start = create_past_date();
$project_task->time_start = '08:00:00';
$project_task->parent_id = $take_over_world_id;
$project_task->priority = 'Medium';
$project_task->description = 'The bigger, the better.  Go with a known name brand.';
$project_task->order_number = 3;
$project_task->task_number = 103;
$project_task->depends_on_id = $make_money_id;
$project_task->estimated_effort = 100;
$project_task->actual_effort = 60;
$project_task->utilization = 100;
$project_task->percent_complete = 90;
$project_task->save();

$project_task = new ProjectTask();
$project_task->assigned_user_id = 1;
// BEGIN SUGARCRM flav=pro ONLY 
$project_task->team_id = 1;
// END SUGARCRM flav=pro ONLY 
$project_task->name = 'Threaten all major countries on public television';
$project_task->status = 'Not Started';
$project_task->date_due = create_date();
$project_task->time_due = '10:00:00';
$project_task->date_start = create_past_date();
$project_task->time_start = '07:00:00';
$project_task->parent_id = $take_over_world_id;
$project_task->priority = 'High';
$project_task->description = "Be sure to stick it to them with an attitude.  We all know what happened the last time they didn't take us seriously.";
$project_task->order_number = 4;
$project_task->task_number = 100;
$project_task->estimated_effort = 3;
$project_task->utilization = 104;
$project_task->percent_complete = 0;
$project_task->save();

$project_task = new ProjectTask();
$project_task->assigned_user_id = 1;
// BEGIN SUGARCRM flav=pro ONLY 
$project_task->team_id = 1;
// END SUGARCRM flav=pro ONLY 
$project_task->name = 'Pick color for new throne';
$project_task->status = 'Not Started';
$project_task->date_due = create_date();
$project_task->time_due = '10:00:00';
$project_task->date_start = create_past_date();
$project_task->time_start = '08:00:00';
$project_task->parent_id = $take_over_world_id;
$project_task->priority = 'Low';
$project_task->description = 'Not terribly urgent, yet still important.  Remember to pick a color that matches the decor of the fortress.';
$project_task->order_number = 5;
$project_task->task_number = 105;
$project_task->depends_on_id = $build_fortress_id;
$project_task->estimated_effort = 10;
$project_task->utilization = 20;
$project_task->percent_complete = 0;
$project_task->save();

// Project: Move Mountain

$project = new Project();
$project->name = 'Move Mountain';
$project->description = 'The mountain to move has yet to be determined.';
$project->assigned_user_id = 1;
// BEGIN SUGARCRM flav=pro ONLY 
$project->team_id = 1;
// END SUGARCRM flav=pro ONLY 
$move_mountain_id = $project->save();

$project_task = new ProjectTask();
$project_task->assigned_user_id = 1;
// BEGIN SUGARCRM flav=pro ONLY 
$project_task->team_id = 1;
// END SUGARCRM flav=pro ONLY 
$project_task->name = 'Purchase lots of shovels';
$project_task->status = 'Completed';
$project_task->date_due = create_date();
$project_task->time_due = '08:00:00';
$project_task->date_start = create_past_date();
$project_task->time_start = '08:00:00';
$project_task->parent_id = $move_mountain_id;
$project_task->priority = 'High';
$project_task->description = "Lots and lots of shovels.";
$project_task->order_number = 1;
$project_task->task_number = 101;
$project_task->estimated_effort = 40;
$project_task->actual_effort = 36;
$project_task->utilization = 100;
$project_task->percent_complete = 100;
$project_task->save();

$project_task = new ProjectTask();
$project_task->assigned_user_id = 1;
// BEGIN SUGARCRM flav=pro ONLY 
$project_task->team_id = 1;
// END SUGARCRM flav=pro ONLY 
$project_task->name = 'Make friends with woodland creatures who can dig';
$project_task->status = 'In Progress';
$project_task->date_due = create_date();
$project_task->time_due = '08:00:00';
$project_task->date_start = create_past_date();
$project_task->time_start = '08:00:00';
$project_task->parent_id = $move_mountain_id;
$project_task->priority = 'High';
$project_task->description = "Gophers, rabbits, and lemmings are well suited for the job.";
$project_task->order_number = 2;
$project_task->task_number = 102;
$project_task->estimated_effort = 40;
$project_task->actual_effort = 20;
$project_task->utilization = 100;
$project_task->percent_complete = 50;
$project_task->save();

$project_task = new ProjectTask();
$project_task->assigned_user_id = 1;
// BEGIN SUGARCRM flav=pro ONLY 
$project_task->team_id = 1;
// END SUGARCRM flav=pro ONLY 
$project_task->name = 'Find a very large hole to put all the dirt into';
$project_task->status = 'Not Started';
$project_task->date_due = create_date();
$project_task->time_due = '08:00:00';
$project_task->date_start = create_past_date();
$project_task->time_start = '08:00:00';
$project_task->parent_id = $move_mountain_id;
$project_task->priority = 'High';
$project_task->description = "Craters and very large valleys will do.  So will the Grand Canyon.";
$project_task->order_number = 3;
$project_task->task_number = 103;
$project_task->estimated_effort = 40;
$project_task->utilization = 100;
$project_task->percent_complete = 0;
$project_task->save();

// Project: Setup Booth At Tradeshow

$project = new Project();
$project->name = 'Setup Booth At Tradeshow';
$project->description = "The annual Widgets Tradeshow will be held in Springfield this year.  We are going to design a booth so good, it will knock the socks off of our competition.  No more fish heads thrown at us this year!";
$project->assigned_user_id = 1;
// BEGIN SUGARCRM flav=pro ONLY 
$project->team_id = 1;
// END SUGARCRM flav=pro ONLY 
$tradeshow_id = $project->save();

$project_task = new ProjectTask();
$project_task->assigned_user_id = 1;
// BEGIN SUGARCRM flav=pro ONLY 
$project_task->team_id = 1;
// END SUGARCRM flav=pro ONLY 
$project_task->name = 'Build the marketing message theme';
$project_task->status = 'Completed';
$project_task->date_due = create_date();
$project_task->time_due = '08:00:00';
$project_task->date_start = create_past_date();
$project_task->time_start = '08:00:00';
$project_task->parent_id = $tradeshow_id;
$project_task->priority = 'High';
$project_task->description = "Thinking along the lines of a post-apocalyptic world in which the human race is gone.  Where the robots have taken over the world.  Gray color palette, blinking LED lights.";
$project_task->order_number = 1;
$project_task->task_number = 111;
$project_task->estimated_effort = 32;
$project_task->actual_effort = 40;
$project_task->utilization = 100;
$project_task->percent_complete = 100;
$project_task_id = $project_task->save();

$project_task = new ProjectTask();
$project_task->assigned_user_id = 1;
// BEGIN SUGARCRM flav=pro ONLY 
$project_task->team_id = 1;
// END SUGARCRM flav=pro ONLY 
$project_task->name = 'Order tradeshow booth';
$project_task->status = 'Completed';
$project_task->date_due = create_date();
$project_task->time_due = '08:00:00';
$project_task->date_start = create_past_date();
$project_task->time_start = '08:00:00';
$project_task->parent_id = $tradeshow_id;
$project_task->priority = 'High';
$project_task->description = "";
$project_task->order_number = 2;
$project_task->task_number = 109;
$project_task->depends_on_id = $project_task_id;
$project_task->estimated_effort = 80;
$project_task->actual_effort = 70;
$project_task->utilization = 100;
$project_task->percent_complete = 100;
$project_task_id = $project_task->save();

$project_task = new ProjectTask();
$project_task->assigned_user_id = 1;
// BEGIN SUGARCRM flav=pro ONLY 
$project_task->team_id = 1;
// END SUGARCRM flav=pro ONLY 
$project_task->name = 'Order tradeshow graphics';
$project_task->status = 'Completed';
$project_task->date_due = create_date();
$project_task->time_due = '08:00:00';
$project_task->date_start = create_past_date();
$project_task->time_start = '08:00:00';
$project_task->parent_id = $tradeshow_id;
$project_task->priority = 'High';
$project_task->description = "We need a big poster of a fallen Statue of Liberty--a la Planet of the Apes.  And flying cars--we need flying cars.";
$project_task->order_number = 3;
$project_task->task_number = 110;
$project_task->depends_on_id = $project_task_id;
$project_task->estimated_effort = 40;
$project_task->actual_effort = 50;
$project_task->utilization = 100;
$project_task->percent_complete = 100;
$project_task_id = $project_task->save();

$project_task = new ProjectTask();
$project_task->assigned_user_id = 1;
// BEGIN SUGARCRM flav=pro ONLY 
$project_task->team_id = 1;
// END SUGARCRM flav=pro ONLY 
$project_task->name = 'Confirm booth number with the tradeshow';
$project_task->status = 'Completed';
$project_task->date_due = create_date();
$project_task->time_due = '17:00:00';
$project_task->date_start = create_past_date();
$project_task->time_start = '10:00:00';
$project_task->parent_id = $tradeshow_id;
$project_task->priority = 'High';
$project_task->description = "Make sure we get a good booth location near the center of the show floor.";
$project_task->order_number = 4;
$project_task->task_number = 112;
$project_task->depends_on_id = $project_task_id;
$project_task->estimated_effort = 4;
$project_task->actual_effort = 2;
$project_task->utilization = 50;
$project_task->percent_complete = 100;
$confirm_booth_id = $project_task->save();

$project_task = new ProjectTask();
$project_task->assigned_user_id = 1;
// BEGIN SUGARCRM flav=pro ONLY 
$project_task->team_id = 1;
// END SUGARCRM flav=pro ONLY 
$project_task->name = 'Organize union help';
$project_task->status = 'Completed';
$project_task->date_due = create_date();
$project_task->time_due = '10:00:00';
$project_task->date_start = create_past_date();
$project_task->time_start = '10:00:00';
$project_task->parent_id = $tradeshow_id;
$project_task->priority = 'Medium';
$project_task->description = "";
$project_task->order_number = 5;
$project_task->task_number = 108;
$project_task->depends_on_id = $confirm_booth_id;
$project_task->estimated_effort = 24;
$project_task->actual_effort = 10;
$project_task->utilization = 100;
$project_task->percent_complete = 100;
$project_task_id = $project_task->save();

$project_task = new ProjectTask();
$project_task->assigned_user_id = 1;
// BEGIN SUGARCRM flav=pro ONLY 
$project_task->team_id = 1;
// END SUGARCRM flav=pro ONLY 
$project_task->name = 'Order drayage';
$project_task->status = 'Completed';
$project_task->date_due = create_date();
$project_task->time_due = '17:00:00';
$project_task->date_start = create_past_date();
$project_task->time_start = '10:00:00';
$project_task->parent_id = $tradeshow_id;
$project_task->priority = 'High';
$project_task->description = "";
$project_task->order_number = 6;
$project_task->task_number = 107;
$project_task->depends_on_id = $project_task_id;
$project_task->estimated_effort = 40;
$project_task->actual_effort = 40;
$project_task->utilization = 100;
$project_task->percent_complete = 100;
$project_task_id = $project_task->save();

$project_task = new ProjectTask();
$project_task->assigned_user_id = 1;
// BEGIN SUGARCRM flav=pro ONLY 
$project_task->team_id = 1;
// END SUGARCRM flav=pro ONLY 
$project_task->name = 'Order chotskies';
$project_task->status = 'Completed';
$project_task->date_due = create_date();
$project_task->time_due = '17:00:00';
$project_task->date_start = create_past_date();
$project_task->time_start = '10:00:00';
$project_task->parent_id = $tradeshow_id;
$project_task->priority = 'Medium';
$project_task->description = "The edible pencils we gave our last year did well.";
$project_task->order_number = 7;
$project_task->task_number = 105;
$project_task->depends_on_id = $confirm_booth_id;
$project_task->estimated_effort = 16;
$project_task->actual_effort = 40;
$project_task->utilization = 100;
$project_task->percent_complete = 100;
$project_task_id = $project_task->save();

$project_task = new ProjectTask();
$project_task->assigned_user_id = 1;
// BEGIN SUGARCRM flav=pro ONLY 
$project_task->team_id = 1;
// END SUGARCRM flav=pro ONLY 
$project_task->name = 'Order lead capture device';
$project_task->status = 'Completed';
$project_task->date_due = create_date();
$project_task->time_due = '17:00:00';
$project_task->date_start = create_past_date();
$project_task->time_start = '10:00:00';
$project_task->parent_id = $tradeshow_id;
$project_task->priority = 'Medium';
$project_task->description = "Leads will be piped straight into SugarCRM from a swipe of the admission badge this year.";
$project_task->order_number = 8;
$project_task->task_number = 106;
$project_task->depends_on_id = $confirm_booth_id;
$project_task->estimated_effort = 4;
$project_task->actual_effort = 16;
$project_task->utilization = 100;
$project_task->percent_complete = 100;
$project_task_id = $project_task->save();

$project_task = new ProjectTask();
$project_task->assigned_user_id = 1;
// BEGIN SUGARCRM flav=pro ONLY 
$project_task->team_id = 1;
// END SUGARCRM flav=pro ONLY 
$project_task->name = 'Assign booth duty';
$project_task->status = 'Completed';
$project_task->date_due = create_date();
$project_task->time_due = '17:00:00';
$project_task->date_start = create_past_date();
$project_task->time_start = '10:00:00';
$project_task->parent_id = $tradeshow_id;
$project_task->priority = 'Medium';
$project_task->description = "No more than 3 hour shifts.  Let the employees have a look around the tradeshow floor.";
$project_task->order_number = 9;
$project_task->task_number = 103;
$project_task->depends_on_id = $confirm_booth_id;
$project_task->estimated_effort = 4;
$project_task->actual_effort = 3;
$project_task->utilization = 100;
$project_task->percent_complete = 100;
$project_task_id = $project_task->save();

$project_task = new ProjectTask();
$project_task->assigned_user_id = 1;
// BEGIN SUGARCRM flav=pro ONLY 
$project_task->team_id = 1;
// END SUGARCRM flav=pro ONLY 
$project_task->name = 'Remind booth workers to wear their uniforms';
$project_task->status = 'Not Started';
$project_task->date_due = create_date();
$project_task->time_due = '17:00:00';
$project_task->date_start = create_past_date();
$project_task->time_start = '10:00:00';
$project_task->parent_id = $tradeshow_id;
$project_task->priority = 'Low';
$project_task->description = "Be sure to suggest to dress up as a famous suppressive robot (e.g. HAL2000).";
$project_task->order_number = 10;
$project_task->task_number = 104;
$project_task->depends_on_id = $project_task_id;
$project_task->estimated_effort = 1;
$project_task->utilization = 100;
$project_task->percent_complete = 0;
$project_task_id = $project_task->save();

$project_task = new ProjectTask();
$project_task->assigned_user_id = 1;
// BEGIN SUGARCRM flav=pro ONLY 
$project_task->team_id = 1;
// END SUGARCRM flav=pro ONLY 
$project_task->name = 'Build press kits';
$project_task->status = 'In Progress';
$project_task->date_due = create_date();
$project_task->time_due = '17:00:00';
$project_task->date_start = create_past_date();
$project_task->time_start = '10:00:00';
$project_task->parent_id = $tradeshow_id;
$project_task->priority = 'High';
$project_task->description = "";
$project_task->order_number = 11;
$project_task->task_number = 101;
$project_task->depends_on_id = $confirm_booth_id;
$project_task->estimated_effort = 16;
$project_task->actual_effort = 3;
$project_task->utilization = 100;
$project_task->percent_complete = 25;
$project_task_id = $project_task->save();

$project_task = new ProjectTask();
$project_task->assigned_user_id = 1;
// BEGIN SUGARCRM flav=pro ONLY 
$project_task->team_id = 1;
// END SUGARCRM flav=pro ONLY 
$project_task->name = 'Arrange partner meetings';
$project_task->status = 'In Progress';
$project_task->date_due = create_date();
$project_task->time_due = '17:00:00';
$project_task->date_start = create_past_date();
$project_task->time_start = '10:00:00';
$project_task->parent_id = $tradeshow_id;
$project_task->priority = 'Medium';
$project_task->description = "Get the usual bunch.";
$project_task->order_number = 12;
$project_task->task_number = 102;
$project_task->depends_on_id = $project_task_id;
$project_task->estimated_effort = 40;
$project_task->actual_effort = 10;
$project_task->utilization = 100;
$project_task->percent_complete = 25;
$project_task_id = $project_task->save();

// BEGIN SUGARCRM flav=pro ONLY 
    include('install/seed_data/products_SeedData.php');

    //This is set to yes at the begininning of this file
	unset($_SESSION['disable_workflow']);
// END SUGARCRM flav=pro ONLY 



?>