<?php
 if(!defined('sugarEntry'))define('sugarEntry', true);
/*********************************************************************************
 * The contents of this file are subject to the SugarCRM Professional End User
 * License Agreement ("License") which can be viewed at
 * http://www.sugarcrm.com/crm/products/sugar-professional-eula.html
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

 ********************************************************************************/

// Jacob
//include("scripts/log_lead_form.php");

require_once ('include/entryPoint.php');
require_once('modules/Users/User.php');
require_once('modules/Leads/LeadFormBase.php');
require_once('modules/ACL/ACLController.php');
require_once('log4php/LoggerManager.php');
require_once('config.php');
require_once('include/utils.php');
require_once ('include/modules.php');

clean_special_arguments();
require_once('include/database/DBManager.php');

$GLOBALS['log'] = LoggerManager::getLogger('leadCapture');
SYSLOG(LOG_DEBUG, "dmittalSI3: start sugarinternal lead capture");
clean_special_arguments();

//begin customizations sugarinternal - jgreen
$GLOBALS['check_notify'] ='true';
//end customizations - jgreen sugarinternal

$app_strings = return_application_language($sugar_config['default_language']);
$app_list_strings = return_app_list_strings_language($sugar_config['default_language']);
$mod_strings = return_module_language($sugar_config['default_language'], 'Leads');

$app_list_strings['record_type_module'] = array('Contact'=>'Contacts', 'Account'=>'Accounts', 'Opportunity'=>'Opportunities', 'Case'=>'Cases', 'Note'=>'Notes', 'Call'=>'Calls', 'Email'=>'Emails', 'Meeting'=>'Meetings', 'Task'=>'Tasks', 'Lead'=>'Leads','Bug'=>'Bugs',

'Report'=>'Reports',  'Quote'=>'Quotes'

);


//pluto is used by all forms on sugarcrm.com
//orbit is used by Installer Registration form
$users = array(
	'cheeto' => array('name'=>'admin', 'pass'=>'0192023a7bbd73250516f069df18b500'),
	'test' => array('name'=>'nate', 'pass'=>'d61aac88ad7b9c8981028e20308d7ba2'),
	'orbit' => array('name'=>'Leads_Nurture', 'pass'=>'c78aab0cfad330b27b43d9129a190b15'),
	'pluto' => array('name'=>'Leads_HotMktg', 'pass'=>'9139a90b1bd94dc57d8b57a5815a2353'),
	'corp' => array('name' => 'Leads_HotEntMktg', 'pass' => '6a143608bdf3bbe2530b68c2ca4f608e'),
	// We can add form elements as 'corp_original' in the future to start using Leads_HotCorpMktg
	//'corp_original' => array('name' => 'Leads_HotCorpMktg', 'pass' => 'ada15bd1a5ddf0b790ae1dcfd05a1e70'),
	'partner' => array('name'=>'Leads_Partner', 'pass'=>'6e323a7c0254589792d270f9f63f37bd'),
	'247intouch-sales' => array('name'=>'247intouch-sales', 'pass'=>'0e92c797a929ca84dbd1bdb72b9a53c6'),
	'neptune' => array('name'=>'training', 'pass'=>'fbc2a6c5c1e0771702cce0caf975974f'),
);



//These are Installer Registration form submittals
if ($_REQUEST['user'] == 'orbit') {
	unset($_REQUEST['assigned_user_id']);
	unset($_REQUEST['team_id']);

	//this is from installer forms
	if(isset($_REQUEST['company'])  && !empty($_REQUEST['company'])) {
  		$_REQUEST['account_name'] = $_REQUEST['company'];
		unset($_REQUEST['company']);
  	}
  
	if ($_REQUEST['campaign_name'] == 'Product Registration') {
		$_REQUEST['campaign_name'] = '3f5959cd-739b-2bc6-4610-43742ca4148e';
		$_REQUEST['account_name'] = $_REQUEST['company'];
		unset($_REQUEST['company']);
	}

  	// deepali - this is for new installer registeration form - 01/10/07
  	else if ($_REQUEST['campaign_name'] == 'OS' || $_REQUEST['campaign_name'] == 'CE') {
    		$_REQUEST['campaign_name'] = 'dc47492a-87aa-445f-24f8-45a433a0e344';
		/* Dee - this is to assign installer leads to new, rating A and to hotMktg if user has requested a call back */
  		if(isset($_REQUEST['Call_Back_c']) && !empty($_REQUEST['Call_Back_c'])) {
			$_REQUEST['lead_rating_c'] = 'A';
			$_REQUEST['status'] = 'New';
			$_REQUEST['user'] = 'pluto';
		}
		/* end Dee 05/15/08 */
	}
  	else if ($_REQUEST['campaign_name'] == 'PRO') {
    		$_REQUEST['campaign_name'] = 'e9e69850-a9f0-8cac-d93c-45a433781111';
  	}
  	else if ($_REQUEST['campaign_name'] == 'ENT') {
    		$_REQUEST['campaign_name'] = 'd4db3cc4-6056-f8c9-b6b9-45a433389548';
  	}
  	//end deepali
	/*
	** @author: DEE
	** SUGARINTERNAL CUSTOMIZATION
	** Description: If campaign = Installer Registration - Sugar CE 080428
	** THEN assign leads to Leads_Nurture (user = orbit)
	** If Call_Back_c = Yes OR ce_user_profile_c = 'Evaluating Sugar for Purchase' assign to Leads_HotMktg (user = pluto)
	*/
	else if($_REQUEST['campaign_name'] == "6a1a911f-5770-efcd-4476-475f5c695902"){
		$_REQUEST['user'] == 'orbit';
		if((isset($_REQUEST['Call_Back_c']) && !empty($_REQUEST['Call_Back_c'])) 
		|| (isset($_REQUEST['ce_user_profile_c']) && !empty($_REQUEST['ce_user_profile_c']) && $_REQUEST['ce_user_profile_c'] == 'Evaluating Sugar for purchase')
		) {
                        $_REQUEST['lead_rating_c'] = 'A';
                        $_REQUEST['status'] = 'New';
                        $_REQUEST['user'] = 'pluto';
                }

	}
	/* END DEE CUSTOMIZATION */
  
	//this is from sugarcrm.com forms
	else {
		$_REQUEST['user'] = 'pluto';
	}
}

// Special case for processing partner application lead
if ($_REQUEST['campaign_name'] == '4412c993-9183-68c7-b4e0-43742c518ff2') {
	$_REQUEST['description'] = "";
	$_REQUEST['description'] .= "Website: ".$_REQUEST['website']."\n\n";

	$_REQUEST['description'] .= "Which of the following best describes your company's primary business? ".join(', ',$_REQUEST['primary_business_selection'])."\n";
	$_REQUEST['description'] .= "Other: ".$_REQUEST['primary_business_other']."\n\n";

	$_REQUEST['description'] .= "What type of business do you have? ".$_REQUEST['business_type']."\n";
	$_REQUEST['description'] .= "Other: ".$_REQUEST['business_type_other']."\n\n";

	$_REQUEST['description'] .= "What year did your company commence operations? ".$_REQUEST['yr_started']."\n\n";

	$_REQUEST['description'] .= "Into which of the following countries does your company sell? ".join(', ',$_REQUEST['resell_country'])."\n";
	$_REQUEST['description'] .= "Other: ".$_REQUEST['resell_country_other']."\n\n";

	$_REQUEST['description'] .= "How many branch locations does your company have? ".$_REQUEST['branch_location_number']."\n\n";

	$_REQUEST['description'] .= "How many sales people are in your company? ".$_REQUEST['sales_number']."\n\n";

	$_REQUEST['description'] .= "How many technical/systems engineers are in your company? ".$_REQUEST['engineers_number']."\n\n";

	$_REQUEST['description'] .= "Which of the following best describes your company's primary business? ".join(', ',$_REQUEST['primary_business'])."\n";
	$_REQUEST['description'] .= "Other: ".$_REQUEST['primary_business_other']."\n\n";

	$_REQUEST['description'] .= "What level of Technical Support do you currently offer?".join(', ',$_REQUEST['technical_support'])."\n";
	$_REQUEST['description'] .= "Other: ".$_REQUEST['technical_support_other']."\n\n";

	$_REQUEST['description'] .= "Does your company have its own separate CRM practice? ".$_REQUEST['CRM_practice']."\n";
	$_REQUEST['description'] .= "CRM practice description: ".$_REQUEST['CRM_practice_description']."\n\n";

	$_REQUEST['description'] .= "Do you have a lab environment or training center for demonstrating CRM solutions? ".$_REQUEST['lab_training']."\n";
	$_REQUEST['description'] .= "Yes. Number of seats: ".$_REQUEST['lab_training_number']."\n\n";

	$_REQUEST['description'] .= "Are you interested in becoming an authorized SugarCRM training site? ".$_REQUEST['interested_in_SugarCRM_training']."\n\n";

	$_REQUEST['description'] .= "What sales revenue did your company achieve in the last full year of trading? ".$_REQUEST['sales_revenue']."\n\n";

	$_REQUEST['description'] .= "To what percentage of your overall revenue do the following items apply?"."\n";
	$_REQUEST['description'] .= "Hardware: ".$_REQUEST['revenue_hardware_percent']."\n";
	$_REQUEST['description'] .= "Software: ".$_REQUEST['revenue_software_percent']."\n";
	$_REQUEST['description'] .= "Services: ".$_REQUEST['revenue_services_percent']."\n";
	$_REQUEST['description'] .= "Other: ".$_REQUEST['revenue_other_percent']."\n\n";

	$_REQUEST['description'] .= "What percentage of your overall sales revenue is derived from selling CRM solutions? ".$_REQUEST['sales_from_crm']."\n\n";

	$_REQUEST['description'] .= "Do you have an existing install base that you plan to sell SugarCRM into? " .$_REQUEST['existing_install_base']."\n";
	$_REQUEST['description'] .= "If yes: ".$_REQUEST['existing_install_base_number']."\n\n";

	$_REQUEST['description'] .= "Which of the following methods does your company employ to sell solutions? ". join(', ',$_REQUEST['technologies'])."\n\n";

	$_REQUEST['description'] .= "Which of the following customer types does your company target? ".join(', ',$_REQUEST['customers'])."\n";
	$_REQUEST['description'] .= "Other: ".$_REQUEST['customers_other']."\n\n";

	$_REQUEST['description'] .= "Which of the following vertical markets does your company currently focus? ".join(', ',$_REQUEST['vertical_markets_selection'])."\n\n";

	$_REQUEST['description'] .= "On which of the following technical areas does your company currently focus? ".join(', ',$_REQUEST['technical_focus'])."\n\n";

	$_REQUEST['description'] .= "Which of the following vertical markets does your company currently focus? ".join(', ',$_REQUEST['vertical_markets_selection'])."\n\n";

	$_REQUEST['description'] .= "From which of the following companies does your company currently recommend or sell solutions? ".join(', ',$_REQUEST['currently_reselling'])."\n";
	$_REQUEST['description'] .= "Other: ".$_REQUEST['currently_reselling_other']."\n\n";
	$_REQUEST['description'] .= "Comments: ".$_REQUEST['comments']."\n\n";

	$_REQUEST['area_of_expertise'] = join(', ',$_REQUEST['area_of_expertise']);

	if($_REQUEST['primary_address_state_other'] != "") {
		$_REQUEST['primary_address_state'] = $_REQUEST['primary_address_state_other'];
	}

}
/***** DEE 04/28/08 *******/
if($_REQUEST['campaign_name'] == '6a1a911f-5770-efcd-4476-475f5c695902' && isset($_REQUEST['leadid']) && !empty($_REQUEST['leadid']) && isset($_REQUEST['instance_key_c']) && !empty($_REQUEST['instance_key_c'])) {

  $query = "UPDATE leads_cstm SET instance_key_c = '".$_REQUEST['instance_key_c']."' WHERE id_c = '".$_REQUEST['leadid']."'";
  $GLOBALS['db']->query($query);
}

else {
/***** end DEE 04/28/08 *******/
$assigned_user = new User();
$_REQUEST['assigned_user_id'] = $assigned_user->retrieve_user_id($users[$_REQUEST['user']]['name']);

// DEE CUSTOMIZATION
// if campaign = Partner re-sell/marketing programs then assign to Leads_Partner
/*$partner_campaign = array(
			'9e4d2191-a2dd-54f7-8aaa-4a53c3d2a9a9',
		    );
if(isset($_REQUEST['campaign_name']) && in_array($_REQUEST['campaign_name'], $partner_campaign)) {
	$_REQUEST['assigned_user_id'] = '2c780a1f-1f07-23fd-3a49-434d94d78ae5';
}*/
// END DEE CUSTOMIZATION
$submitter_user = new User();
$submitter_user->user_name = $users['cheeto']['name'];
if($submitter_user->authenticate_user($users['cheeto']['pass'])){
$fp = @fopen("/var/www/sugarinternal/logs/forms7.log", "a");
@fwrite($fp, date('Y-m-d H:i:s') . " hit auth block\n");
	$userid = $submitter_user->retrieve_user_id($users['cheeto']['name']);
	$current_user = new User();
	$current_user->retrieve($userid);
	@fwrite($fp, date('Y-m-d H:i:s') . " retrieved user\n");

	$_REQUEST['record'] ='';
	 if (isset($_REQUEST['email'])  && !empty($_REQUEST['email'])) {
	 	$_REQUEST['email1'] = $_REQUEST['email'];
	 }
	if( isset($_REQUEST['__splitName'])   && !empty($_REQUEST['__splitName'])) {

		$name = explode(' ',$_REQUEST['name']);
		if(sizeof($name) == 1) {
			$_REQUEST['first_name'] = $name[0];
			$_REQUEST['last_name'] = '(No last name provided)';
		}
		else {
			$_REQUEST['first_name'] = array_shift($name);
			$last_name = implode(' ', $name);
			$_REQUEST['last_name'] = $last_name;
		}
	 }

        // jparsons ondemand eval creation
        if( isset($_REQUEST['evalcreate']) ) {
                $dmg = isset($_REQUEST['data_migration']) ? "yes" : "no";
                $evg = isset($_REQUEST['agreement']) ? "yes" : "no";

                $description = sprintf("Data Migration: %s\nEval Agreement: %s\n", $dmg, $evg);
                $_REQUEST['description'] = $description;
        }
        // end jparsons ondemand eval creation

//$_POST = $_REQUEST;
@fwrite($fp, date('Y-m-d H:i:s') . " before touchpoint save\n");

// Jacob debug
//foreach($_REQUEST as $key=>$value){
//    $_POST[$key] = $value;
//}
// End jacob debug

	//create new touchpoint
	require_once('modules/Touchpoints/Touchpoint.php');
	$tp = new Touchpoint();

	// BEGIN jostrow customization
	if (isset($_REQUEST['trial_name']) && isset($_REQUEST['registered_eval_c']) && $_REQUEST['registered_eval_c'] == '7_day_trial') {
		$tp->trial_name_c = "http://trial.sugarcrm.com/{$_REQUEST['trial_name']}";
		$tp->trial_expiration_c = date('Y-m-d', strtotime('+9 day'));
	}
	// END jostrow customization

	//populate touchpoint with values from request
	$tp->populate_touchpoint_from_array();

	$tp_id = $tp->save();

	SYSLOG(LOG_DEBUG, "dmittalSI3: lead created {$tp_id}");
	@fwrite($fp, date('Y-m-d H:i:s') . " after handleSave, return value is ".(empty($tp_id) ? "empty value" : $tp_id )."\n\n");
	fclose($fp);

    /************** DEE 04/28/08 send lead id to sugarcrm.com  *************************/
	if($_REQUEST['campaign_name'] == '6a1a911f-5770-efcd-4476-475f5c695902') {	
		echo "Form Key: ".$tp_id."\n";
	}
	/************** end DEE 04/28/08 send lead id to sugarcrm.com  *************************/
	echo "processed";
}
else {
	echo "error";
}
/***** DEE 04/28/08 *******/
}
/***** end DEE 04/28/08 *******/
?>
