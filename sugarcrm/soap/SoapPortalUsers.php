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
 *  (i) the "Powered by SugarCRM" logo and
 *  (ii) the SugarCRM copyright notice
 * in the same form as they appear in the distribution.  See full license for requirements.
 *Your Warranty, Limitations of liability and Indemnity are expressly stated in the License.  Please refer
 *to the License for the specific language governing these rights and limitations under the License.
 *Portions created by SugarCRM are Copyright (C) 2004 SugarCRM, Inc.; All Rights Reserved.
 ********************************************************************************/

require_once('soap/SoapHelperFunctions.php');
require_once('soap/SoapTypes.php');


require_once('soap/SoapPortalHelper.php');

require_once('modules/KBDocuments/KBDocument.php');
require_once('modules/Administration/SessionManager.php');
//require_once ('log4php/LoggerManager.php');
//$GLOBALS['log'] = LoggerManager::getLogger('SugarCRM');

if(!empty($GLOBALS['beanList']['KBDocuments'])) {
require_once('modules/KBDocuments/KBDocument.php');
}


/*******************************************************************************
* DEEPALI - Partner Portal Customization
* This is for partner portal
* Checking if the logged in user has the following attributes
* portal name = sugarcrm.com username
* portal active = 1
* account type = Partner, Parter-Pro, Partner-Ent
* support service level = sugarcrm partner
* returns 1 if true else returns -2
*******************************************************************************/
$server->register(
        'partner_portal_check',
        array('portal_name'=>'xsd:string'),
        array('return'=>'xsd:int'),
        $NAMESPACE);

function partner_portal_check($portal_name)
{
	$accountquery = "SELECT distinct accounts.name, accounts_cstm.Partner_Type_c
			FROM accounts_contacts
			LEFT JOIN contacts ON contacts.id = accounts_contacts.contact_id AND contacts.deleted = '0'
			LEFT JOIN accounts ON accounts.id = accounts_contacts.account_id AND accounts.deleted = '0'
			LEFT JOIN accounts_cstm ON accounts.id = accounts_cstm.id_c
			WHERE contacts.portal_name = \"$portal_name\" 
			AND contacts.portal_active = '1' 
			AND accounts.account_type in ('Partner', 'Partner-Pro', 'Partner-Ent') 
			AND accounts_contacts.deleted = '0'";	
	$response = $GLOBALS['db']->query($accountquery); 
	$account = $GLOBALS['db']->fetchByAssoc($response);
//	$response = mysql_query($accountquery);
//	$account = mysql_fetch_assoc($response);

       if(empty($account))
	{
       	return -2;

       }

	return 1;
}
// END DEEPALI Customization



/*******************************************************************************
* LAM - Partner Portal Customization - PRM
* Makes sure the session is still valid
* returns -1 if not valid, 1 if is valid
*******************************************************************************/

$server->register(
        'portal_check_authentication',
        array('session'=>'xsd:string'),
        array('return'=>'xsd:int'),
        $NAMESPACE);
       

function portal_check_authentication($session){
	if(! portal_validate_authenticated($session)){
	 	return -1;
	}
 	return 1;
}
// END Lam Customization


/*******************************************************************************
* LAM - Partner Portal Customization - PRM
* Grabs contacts associated with the opportunities account
* returns array of contacts
*******************************************************************************/
$server->register(
        'partner_portal_get_contacts',
        array('portal_name'=>'xsd:string','accountid'=>'xsd:string','oppid'=>'xsd:string','session'=>'xsd:string'),
        array('return'=>'tns:get_entry_list_result'),
//        array('return'=>'tns:get_opportunities_list_result'),
        $NAMESPACE);

function partner_portal_get_contacts($portal_name,$accountid,$oppid,$session)
{
	$error = new SoapError();

	if(! portal_validate_authenticated($session)){
		$error->set_error('invalid_session');
		return array('field_list'=>-1, 'entry_list'=>array(), 'error'=>$error->get_soap_array());
	}
	
	if(!empty($opp_id)) {
		$where = "AND opportunities.id='{$oppid}'";
	}
	
	$accountquery = "SELECT DISTINCT contacts.first_name, contacts.last_name, contacts.title, contacts.phone_work, contacts.phone_mobile,contacts.id,email_addresses.email_address, contacts_cstm.*, accounts_cstm.Support_Service_Level_c,
		contacts.primary_address_street, contacts.primary_address_city, contacts.primary_address_state, contacts.primary_address_postalcode, contacts.primary_address_country,
		contacts.alt_address_street, contacts.alt_address_city, contacts.alt_address_state, contacts.alt_address_postalcode, contacts.alt_address_country
	
FROM  contacts
LEFT JOIN contacts_cstm ON contacts.id = contacts_cstm.id_c
LEFT JOIN accounts_contacts ON contacts.id=accounts_contacts.contact_id AND accounts_contacts.deleted=0
LEFT JOIN accounts_opportunities jtl0 ON accounts_contacts.account_id=jtl0.account_id  AND jtl0.deleted=0
LEFT JOIN opportunities ON opportunities.id = jtl0.opportunity_id AND opportunities.deleted=0
LEFT JOIN opportunities_cstm ON opportunities.id = opportunities_cstm.id_c 
LEFT JOIN accounts ON accounts.id=jtl0.account_id AND accounts.deleted=0
LEFT JOIN accounts_cstm ON accounts.id = accounts_cstm.id_c
LEFT JOIN accounts as partner_account ON partner_account.id=opportunities_cstm.partner_assigned_to_c AND partner_account.deleted=0
LEFT JOIN accounts_contacts as partner_account_contacts ON partner_account_contacts.account_id=partner_account.id AND partner_account_contacts.deleted=0
LEFT JOIN contacts as partner_contact ON partner_contact.id = partner_account_contacts.contact_id AND partner_contact.deleted =0
LEFT JOIN contacts_cstm as partner_contact_cstm ON partner_contact.id = partner_contact_cstm.id_c
LEFT JOIN email_addr_bean_rel ON email_addr_bean_rel.bean_id = contacts.id AND email_addr_bean_rel.deleted=0
LEFT JOIN email_addresses ON email_addr_bean_rel.email_address_id=email_addresses.id AND email_addresses.deleted = 0


WHERE partner_contact.portal_name = '{$portal_name}' 
AND partner_contact.portal_active = 1 
AND partner_contact.deleted = 0
AND (partner_contact.id = opportunities_cstm.contact_id_c OR partner_contact_cstm.oppq_active_c = 1)
AND contacts.deleted = 0
AND partner_account.account_type IN ('Partner', 'Partner-Pro', 'Partner-Ent')
AND accounts.id = '{$accountid}'
AND contacts.id = accounts_contacts.contact_id
{$where}"
/*AND opportunities_cstm.accepted_by_partner_c IN ('Y','R','P')
AND opportunities.sales_stage NOT IN ('Closed Won', 'Closed Lost', 'Finance Closed')
"*/;	
	$response = $GLOBALS['db']->query($accountquery); 
	$fields = $GLOBALS['db']->getFieldsArray($response);
	
	$field_list = array();
	foreach ($fields as $value) {
		$field_list[] = array("name"=>$value);
	}

	$output_list = array();
	while ($row = $GLOBALS['db']->fetchByAssoc($response)) {

		$name_value_list = array();
		foreach ($row as $field => $value) {
			$name_value_list[$field] = array("name"=>$field,"value"=>$value);
		}
		
		$output_list[] = array(
				'id'=>$row['id'],
				'module_name'=> "Contacts",
				'name_value_list'=> $name_value_list,				
				
			);
	}
	 
	 
	return array('field_list'=>$field_list, 'entry_list'=>$output_list, 'error'=>$error->get_soap_array());

	
}
// END LAM Customization


/*******************************************************************************
* LAM - Partner Portal Customization - PRM
* Grabs opportunities assigned to the logged in portal user for the purpose of accepting or rejecting opportunities
* returns array of opportunities
*******************************************************************************/

$server->register(
        'partner_portal_get_opportunities',
        array('portal_name'=>'xsd:string','id'=>'xsd:string','session'=>'xsd:string','filter'=>'xsd:string'),
        array('return'=>'tns:get_entry_list_result'),
//        array('return'=>'tns:get_opportunities_list_result'),
        $NAMESPACE);

function partner_portal_get_opportunities($portal_name,$id,$session,$filter)
{
	require_once("custom/include/language/en_us.lang.php");
	$error = new SoapError();

	if(! portal_validate_authenticated($session)){
		$error->set_error('invalid_session');
		return array('field_list'=>array(), 'entry_list'=>array(), 'error'=>$error->get_soap_array());
	}
	
	$filter = unserialize($filter);
	$idSql = ($id) ?$idSql = "AND opportunities.id='{$id}' " : ""; 
	$filterSql = "";
	$filterSql .= ($filter['opp_name']) ? "AND opportunities.name LIKE '%".addslashes($filter['opp_name'])."%' " : "";
	$filterSql .= ($filter['status'] && $filter['status'] != "A") ? "AND opportunities_cstm.accepted_by_partner_c = '".addslashes($filter['status'])."' " : ""; 
	$filterSql .= ($filter['sales_stage'] && $filter['sales_stage'] != "A") ? "AND opportunities.sales_stage = '".addslashes($filter['sales_stage'])."' " : ""; 
	$filterSql .= ($filter['location']) ? "AND (accounts.billing_address_country LIKE '%".addslashes($filter['location'])."%' OR accounts.billing_address_state LIKE '%".addslashes($filter['location'])."%')" : ""; 

	/**
	 * @author Jim Bartek
	 * @project moofcart
	 * @tasknum 99
	 * Adding a filter to get only opps for a specific accounts.id
	*/

	$filterSql .= ($filter['account_id']) ? "AND accounts.id = '{$filter['account_id']}'" : "";

	/* END Jim Bartek customization */
	
	$page = addslashes($filter['page']);
	$view = 25;
	$start=  (($page-1) * $view);
	$filterSql .= " ORDER BY decision ASC";
	if (!$id && $filter['account_id'] == false) $filterSql .= " LIMIT $start,$view";


	$accountquery = "SELECT DISTINCT opportunities.id, opportunities.name,opportunities.description,accounts.name account_name, jtl0.account_id account_id , opportunities.amount_usdollar, opportunities.amount, opportunities_cstm.accepted_by_partner_c,accounts.account_type,DATE_FORMAT(opportunities.date_entered,'%m/%d/%y') as created, opportunities_cstm.opportunity_type type,DATE_FORMAT(opportunities.date_closed,'%m/%d/%y') as decision,opportunities_cstm.users subscriptions,opportunities_cstm.current_solution current_solution, accounts.billing_address_street street, accounts.billing_address_city city, accounts.billing_address_state state, accounts.billing_address_country country, accounts.website, opportunities.sales_stage,  opportunities.next_step, DATE_FORMAT(opportunities_cstm.next_step_due_date,'%m/%d/%y') as next_step_due_date, opportunities_cstm.competitor_1, opportunities.description,opportunities_cstm.closed_lost_reason_c, opportunities_cstm.closed_lost_reason_detail_c, opportunities_cstm.primary_reason_competitor_c, opportunities_cstm.closed_lost_description, campaigns.name as campaign_name
FROM opportunities
LEFT JOIN opportunities_cstm ON opportunities.id = opportunities_cstm.id_c 
LEFT JOIN accounts_opportunities jtl0 ON opportunities.id=jtl0.opportunity_id  AND jtl0.deleted=0 
LEFT JOIN accounts ON accounts.id=jtl0.account_id AND accounts.deleted=0
LEFT JOIN accounts as partner_account ON partner_account.id=opportunities_cstm.partner_assigned_to_c AND partner_account.deleted=0
LEFT JOIN accounts_contacts ON partner_account.id=accounts_contacts.account_id AND accounts_contacts.deleted=0
LEFT JOIN contacts ON contacts.id = accounts_contacts.contact_id
LEFT JOIN contacts_cstm ON contacts.id = contacts_cstm.id_c
LEFT JOIN campaigns ON opportunities.campaign_id = campaigns.id

WHERE 
contacts.portal_name = '{$portal_name}' 
AND contacts.portal_active = 1 
AND (contacts.id = opportunities_cstm.contact_id_c OR contacts_cstm.oppq_active_c = 1)
AND contacts.deleted = 0 
AND partner_account.account_type IN ('Partner', 'Partner-Pro', 'Partner-Ent')
AND opportunities_cstm.accepted_by_partner_c IN ('Y','R','P')
AND opportunities.sales_stage NOT IN ('Closed Won', 'Closed Lost', 'Finance Closed')
AND opportunities.deleted =0
$idSql
$filterSql";	

	//mail("jbartek@sugarcrm.com","sql",$accountquery,"From: Jim <jbartek@sugarcrm.com>");
	$response = $GLOBALS['db']->query($accountquery); 
	$fields = $GLOBALS['db']->getFieldsArray($response);
	
	$field_list = array();
	foreach ($fields as $value) {
		$field_list[] = array("name"=>$value);
	}

	$output_list = array();
	while ($row = $GLOBALS['db']->fetchByAssoc($response)) {

		$name_value_list = array();
		foreach ($row as $field => $value) {
			if($field == "type") {
				$value = $GLOBALS['app_list_strings']['opportunity_type_dom'][$value];
			}
			$name_value_list[$field] = array("name"=>$field,"value"=>$value);
		}
		
		$output_list[] = array(
				'id'=>$row['id'],
				'module_name'=> "Opportunities",
				'name_value_list'=> $name_value_list,				
				
			);
	}
	 
	 
	return array('field_list'=>$field_list, 'entry_list'=>$output_list, 'error'=>$error->get_soap_array());

	
}

// END LAM Customization
/*******************************************************************************
* LAM - Partner Portal Customization - PRM
* Gets total number of opps
* returns total number of opps
*******************************************************************************/
$server->register(
        'partner_portal_get_total_opportunities',
        array('portal_name'=>'xsd:string','id'=>'xsd:string','session'=>'xsd:string','filter'=>'xsd:string'),
        array('return'=>'tns:set_entry_result'),
        $NAMESPACE);

function partner_portal_get_total_opportunities($portal_name,$id,$session,$filter)
{
	require_once("custom/include/language/en_us.lang.php");
	$error = new SoapError();

	if(! portal_validate_authenticated($session)){
		$error->set_error('invalid_session');
		return array('id'=>-1, 'error'=>$error->get_soap_array());
	}
	
	$filter = unserialize($filter);
	$idSql = ($id) ?$idSql = "AND opportunities.id='{$id}' " : ""; 
	$filterSql = "";
	$filterSql .= ($filter['status'] && $filter['status'] != "A") ? "AND opportunities_cstm.accepted_by_partner_c = '".addslashes($filter['status'])."' " : ""; 

	$filterSql .= ($filter['account_id'] ) ? "AND accounts.id = '".addslashes($filter['account_id'])."' " : ""; 

	$filterSql .= ($filter['sales_stage'] && $filter['sales_stage'] != "A") ? "AND opportunities.sales_stage = '".addslashes($filter['sales_stage'])."' " : ""; 
	$filterSql .= ($filter['location']) ? "AND (accounts.billing_address_country LIKE '%".addslashes($filter['location'])."%' OR accounts.billing_address_state LIKE '%".addslashes($filter['location'])."%')" : ""; 

	$accountquery = "SELECT DISTINCT  count(*) as total
	
FROM opportunities
LEFT JOIN opportunities_cstm ON opportunities.id = opportunities_cstm.id_c 
LEFT JOIN accounts_opportunities jtl0 ON opportunities.id=jtl0.opportunity_id  AND jtl0.deleted=0 
LEFT JOIN accounts ON accounts.id=jtl0.account_id AND accounts.deleted=0
LEFT JOIN accounts as partner_account ON partner_account.id=opportunities_cstm.partner_assigned_to_c AND partner_account.deleted=0
LEFT JOIN accounts_contacts ON partner_account.id=accounts_contacts.account_id AND accounts_contacts.deleted=0
LEFT JOIN contacts ON contacts.id = accounts_contacts.contact_id
LEFT JOIN contacts_cstm ON contacts.id = contacts_cstm.id_c

WHERE 
contacts.portal_name = '{$portal_name}' 
AND contacts.portal_active = 1 
AND (contacts.id = opportunities_cstm.contact_id_c OR contacts_cstm.oppq_active_c = 1)
AND contacts.deleted = 0 
AND partner_account.account_type IN ('Partner', 'Partner-Pro', 'Partner-Ent')
AND opportunities_cstm.accepted_by_partner_c IN ('Y','R','P')
AND opportunities.sales_stage NOT IN ('Closed Won', 'Closed Lost', 'Finance Closed')
AND opportunities.deleted =0
$idSql
$filterSql";	


	$response = $GLOBALS['db']->query($accountquery); 
	$result = $GLOBALS['db']->fetchByAssoc($response);
	 
	if($response) {
	 	return array('id'=>$result['total'], 'error'=>$error->get_soap_array());
	 } else {
        $GLOBALS['log']->warn('partner_portal_update_opportunities error: no rows updated');
	 	return array('id'=>-1, 'error'=>$error->get_soap_array());	
	 }

	
}
// END LAM Customization


/*******************************************************************************
* LAM - Partner Portal Customization - PRM
* Updates opportunities
* returns array id 1 success (-1 if error), error if any 
*******************************************************************************/
$server->register(
        'partner_portal_update_opportunities',
        array('name_value_list'=>'tns:name_value_list','session'=>'xsd:string'),
        array('return'=>'tns:set_entry_result'),
        $NAMESPACE);

function partner_portal_update_opportunities($name_value_list,$session)
{
	require_once('modules/Tasks/Task.php');
	require_once('modules/Opportunities/Opportunity.php');
	global $beanFiles;
	$error = new SoapError();

	if(! portal_validate_authenticated($session)){
		$error->set_error('invalid_session');
        $GLOBALS['log']->warn('partner_portal_update_opportunities error: invalid session');
		return array('id'=>-1, 'error'=>$error->get_soap_array());
	}

	$sqlFieldsC = array();
	$sqlFieldsO = array();
	$sqlFieldsA = array();
	$customFields = array(
	"users",
	"current_solution",
	"competitor_1",
	"primary_reason_competitor_c",
	"next_step_due_date",
	"closed_lost_description",
	"closed_lost_reason_c",
	"closed_lost_reason_detail_c",
	"accepted_by_partner_c"
	);
	
	$auditFields = array(
	"users_before" => "int",
	"sales_stage_before" => "varchar",
	"amount_before" => "double",
	"date_closed_before" => "date"
	);
	

	$auditAfterValue = array();
	foreach ($auditFields as $field => $type) {
		foreach ($name_value_list as $value) {
			if(str_replace("_before","",$field) == $value['name']) {
				$auditAfterValue[$value['name']] = $value['value'];
			}
		}
	}

	foreach ($name_value_list as $value) {
		
		if($value['name'] == 'id') {
			$opp = new Opportunity();
			$opp->disable_row_level_security = TRUE;
			$opp->retrieve($value['value'],false);
			
			$sqlWhere = "opportunities.id = '{$value['value']}'";
			$sqlWhere .= " AND id_c = '{$value['value']}'";
			$parentId = $value['value'];
		} else {
		    	
			if(in_array($value['name'],$customFields)) {
				$sqlFieldsC[] = "opportunities_cstm.{$value['name']} = '{$value['value']}'";
			} elseif(!array_key_exists($value['name'],$auditFields)) {
                $value['value'] = addslashes($value['value']);
				$sqlFieldsO[]= "opportunities.{$value['name']} = '{$value['value']}'";
			}
		}
		
		if($value['name'] == 'accepted_by_partner_c' && $value['value'] == "R" && $opp->accepted_by_partner_c != "R") {
				
				$oppTask = new Task();
				$oppTask->assigned_user_id = $opp->assigned_user_id;
				$oppTask->name = "Rejected: Opportunity ".$opp->name.".";
				$oppTask->status = "Not Started";
				$oppTask->priority = "High";
				$oppTask->description = "Please reassign this Opportunity";
				$oppTask->save();
				
				$opp->load_relationship('tasks');
				$opp->tasks->add($oppTask->id);
		}

//BEGIN SUGARCRM flav=pro ONLY

	}
	$sqlFields = array_merge($sqlFieldsO,$sqlFieldsC);
	$sqlFields = join(",",$sqlFields);
	$query = "UPDATE opportunities,opportunities_cstm SET $sqlFields WHERE $sqlWhere AND opportunities.id = opportunities_cstm.id_c";	
	$response = $GLOBALS['db']->query($query); 
	
	foreach ($name_value_list as $value) {
		if(array_key_exists($value['name'],$auditFields)) {
		$auditId = create_guid();
		$fieldname= str_replace("_before","",$value['name']);
		$query = "INSERT INTO opportunities_audit (id,field_name,parent_id,created_by,data_type,before_value_string,after_value_string,date_created) VALUES ('$auditId','$fieldname','$parentId','92ae4188-00d7-870b-6583-4c102f014eb1','".$auditFields[$value['name']]."','".$value['value']."','".$auditAfterValue[$fieldname]."',NOW())";
		$response_audit = $GLOBALS['db']->query($query);
		}
	
	}
	
	 


	 if($response) {
	 	return array('id'=>1, 'error'=>$error->get_soap_array());
	 } else {
        $GLOBALS['log']->warn('partner_portal_update_opportunities error: no rows updated');
	 	return array('id'=>-1, 'error'=>$error->get_soap_array());	
	 }
	 
}
// END LAM Customization

//END SUGARCRM flav=pro ONLY


/*************************************************************************************

THIS IS FOR PORTAL USERS


*************************************************************************************/


/*
this authenticates a user as a portal user and returns the session id or it returns false otherwise;
*/
$server->register(
        'portal_login',
        array('portal_auth'=>'tns:user_auth','user_name'=>'xsd:string', 'application_name'=>'xsd:string'),
        array('return'=>'tns:set_entry_result'),
        $NAMESPACE); 
        
function portal_login($portal_auth, $user_name, $application_name){
	$error = new SoapError();
	$contact = new Contact();
	$result = login_user($portal_auth);

    if($result == 'fail' || $result == 'sessions_exceeded'){
        if($result == 'sessions_exceeded') {
            $error->set_error('sessions_exceeded');
        }
        else {
            $error->set_error('no_portal');
        }
        return array('id'=>-1, 'error'=>$error->get_soap_array());
    }
    global $current_user;
    //BEGIN SUGARCRM flav=ent ONLY
    $sessionManager = new SessionManager();
    //END SUGARCRM flav=ent ONLY

	if($user_name == 'lead'){
		session_start();
		$_SESSION['is_valid_session']= true;
		$_SESSION['ip_address'] = $_SERVER['REMOTE_ADDR'];
		$_SESSION['portal_id'] = $current_user->id;
		$_SESSION['type'] = 'lead';
        //BEGIN SUGARCRM flav=ent ONLY
        $sessionManager->session_type = 'lead';
        $sessionManager->last_request_time = gmdate($GLOBALS['timedate']->get_db_date_time_format());
        $sessionManager->session_id = session_id();
        $sessionManager->save();
        //END SUGARCRM flav=ent ONLY
		login_success();
		return array('id'=>session_id(), 'error'=>$error->get_soap_array());	
	}else if($user_name == 'portal'){
		session_start();
		$_SESSION['is_valid_session']= true;
		$_SESSION['ip_address'] = $_SERVER['REMOTE_ADDR'];
		$_SESSION['portal_id'] = $current_user->id;
		$_SESSION['type'] = 'portal';
        //BEGIN SUGARCRM flav=ent ONLY
        $sessionManager->session_type = 'portal';
        $sessionManager->last_request_time = gmdate($GLOBALS['timedate']->get_db_date_time_format());
        $sessionManager->session_id = session_id();
        $sessionManager->save();
        //END SUGARCRM flav=ent ONLY
        $GLOBALS['log']->debug("Saving new session");
		login_success();
		return array('id'=>session_id(), 'error'=>$error->get_soap_array());	
	}
//BEGIN Sugar Internal customizations
	elseif ($portal_auth['user_name'] == "bug_portal" || $portal_auth['user_name'] == "campaign_portal" || $portal_auth['user_name'] == "lead_portal" || $portal_auth['user_name'] == "portal") {
                session_start();

                $_SESSION['is_valid_session']= true;
                $_SESSION['ip_address'] = $_SERVER['REMOTE_ADDR'];
                $_SESSION['user_id'] = $contact->id;
                $_SESSION['portal_id'] = $user->id;
                $_SESSION['type'] = 'contact';
                $_SESSION['team_id'] = $user->default_team;
                $_SESSION['assigned_user_id'] = $user->id;
		$_SESSION['portal_username'] = $user_name;
		$_SESSION['user'] = $portal_auth['user_name'];

                login_success();

	$contact = $contact->retrieve_by_string_fields(array('portal_name'=>$user_name, 'portal_active'=>'1', 'deleted'=>0) );
	if($contact != null){
	                build_relationship_tree($contact);
		}

                return array('id'=>session_id(), 'error'=>$error->get_soap_array());
	}
// END Internal Sugar customizations
	else{
		$contact = $contact->retrieve_by_string_fields(array('portal_name'=>$user_name, 'portal_active'=>'1', 'deleted'=>0) );
		if ($contact != null){
		session_start();

		$_SESSION['is_valid_session']= true;
		$_SESSION['ip_address'] = $_SERVER['REMOTE_ADDR'];
		$_SESSION['user_id'] = $contact->id;
		$_SESSION['portal_id'] = $current_user->id;

		$_SESSION['type'] = 'contact';
		//BEGIN SUGARCRM flav=pro ONLY
		$_SESSION['team_id'] = $contact->team_id;
		$_SESSION['team_set_id'] = $contact->team_set_id;
		//END SUGARCRM flav=pro ONLY
		$_SESSION['assigned_user_id'] = $contact->assigned_user_id;
        //BEGIN SUGARCRM flav=ent ONLY
        $sessionManager->session_type = 'contact';
        $sessionManager->last_request_time = gmdate($GLOBALS['timedate']->get_db_date_time_format());
        $sessionManager->session_id = session_id();
        $sessionManager->save();
        //END SUGARCRM flav=ent ONLY
		login_success();
		build_relationship_tree($contact);
	
		return array('id'=>session_id(), 'error'=>$error->get_soap_array());	
	}
	}

	$error->set_error('invalid_login');
	return array('id'=>-1, 'error'=>$error->get_soap_array());
}

//BEGIN SUGARCRM flav=pro ONLY
$server->register(
        'account_by_contact_search',
        array('portal_name'=>'xsd:string'),
        array('return'=>'xsd:int'),
        $NAMESPACE);


/* returns:
 *    1 : They are allowed to create a case
 *   -1 : No contact with that portal_name
 *   -2 : No account associated with that contact
 *   -3 : The customer has no cases remaining in their sugar network subscription
 *   -4 : The customer's remaining_support_cases_c is set to NULL in the DB - This shouldn't happen 
 *   -5 : Express customer with no remaining cases, but allowed to buy more cases // NASSI
 *   -6 : The account is set to NO SUPPORT PROVIDED // NASSI
 */
function account_by_contact_search($portal_name)
{
	$contactquery = "SELECT contacts.*, 
                accounts.name as account_name, 
                accounts.id as account_id, 
                accounts.assigned_user_id account_id_owner, 
                contacts_cstm.* 
                FROM contacts 
                            LEFT JOIN accounts_contacts 
                            ON contacts.id=accounts_contacts.contact_id and accounts_contacts.deleted=0   
                            LEFT JOIN accounts 
                            ON accounts_contacts.account_id=accounts.id
			    LEFT JOIN contacts_cstm ON contacts.id = contacts_cstm.id_c
		where ( portal_name = \"$portal_name\" and portal_active = \"1\" )
		  AND contacts.deleted=0  AND (accounts.deleted is NULL or accounts.deleted=0)
		  AND accounts_contacts.deleted=0
		order by accounts_contacts.date_modified desc";
	
	$response = $GLOBALS['db']->query($contactquery); 
	$contact = $GLOBALS['db']->fetchByAssoc($response);
	
	if(empty($contact)){
                return -1;
        }
	
	$account_id = $contact['account_id'];
	
	$accountquery = "SELECT accounts.* , accounts_cstm.*
			FROM  accounts
				LEFT JOIN accounts_cstm ON accounts.id = accounts_cstm.id_c
			where accounts.id = \"$account_id\" AND accounts.deleted=0";
	
	$response = $GLOBALS['db']->query($accountquery);
	$account = $GLOBALS['db']->fetchByAssoc($response);
	
        if(empty($account)){
                return -2;
        }

	if($account['Support_Service_Level_c'] == 'no_support'){
		return -6;
	}


	if($account['Support_Service_Level_c'] == 'sugar_network'){
		if($account['remaining_support_cases_c'] != NULL && $account['remaining_support_cases_c'] > 0){
			return 1;
		}
		else{
			if($account['remaining_support_cases_c'] != NULL){
				return -3;
			}
			else{
				return -4;
			}
		}
	}
	
// NASSI
        if($account['Support_Service_Level_c'] == 'Sugar Express'){
                if($account['remaining_support_cases_c'] != NULL && $account['remaining_support_cases_c'] > 0){
                        return 1;
                }
                else{
                        if($account['remaining_support_cases_c'] != NULL){
                                return -5;
                        }
                        else{
                                return -4;
                        }
                }
        }
// NASSI	
	return 1;
}
// SADEK: END OSSC CUSTOMIZATION


// SADEK: BEGIN TC CUSTOMIZATION
$server->register(
        'get_available_credits',
        array('portal_name'=>'xsd:string'),
        array('return'=>'xsd:int'),
        $NAMESPACE);


/* returns:
 *  +num: The number of available training credits. 
 *   -1 : No contact with that portal_name
 *   -2 : Expiration has passed
 *   -3 : Not authorized to spend credits
 */
function get_available_credits($portal_name)
{
    /* jmullan: IT Request 5550 */
    $contactquery = "
SELECT
    accounts_cstm.remaining_training_credits_c as remaining_tc,
    training_credits_exp_date_c as expiration_tc,
    contacts_cstm.university_enabled_c
FROM contacts
INNER JOIN accounts_contacts 
    ON contacts.id=accounts_contacts.contact_id and accounts_contacts.deleted=0
INNER JOIN accounts 
    ON accounts_contacts.account_id=accounts.id
INNER JOIN accounts_cstm
    ON accounts.id = accounts_cstm.id_c
INNER JOIN contacts_cstm
    ON contacts.id = contacts_cstm.id_c
WHERE
    portal_name = \"$portal_name\" and portal_active = \"1\"
    AND contacts.deleted=0
    AND (accounts.deleted is NULL or accounts.deleted=0)
    AND accounts_contacts.deleted=0
    AND accounts_cstm.remaining_training_credits_c > 0
ORDER BY
    accounts_contacts.date_modified DESC";

        $response = mysql_query($contactquery);
        $account_cstm = mysql_fetch_assoc($response);
        if(empty($account_cstm)){
                return -1;
        }
	if (!$account_cstm['university_enabled_c']) {
	    return -3;
	}
        $remaining_tc = $account_cstm['remaining_tc'];
        $expiration_tc = $account_cstm['expiration_tc'];

        $datearr = explode("-", $expiration_tc);
        $exp_time = mktime(0, 0, 0, $datearr[1], $datearr[2], $datearr[0]);
        $now_time = strtotime("-1 day");

        if($now_time <= $exp_time){
                return $remaining_tc;
        }
        else{
                return -2;
        }
        return array('id'=>-1, 'error'=>$error->get_soap_array());
    }
    global $current_user;
    //BEGIN SUGARCRM flav=ent ONLY
    $sessionManager = new SessionManager();
    //END SUGARCRM flav=ent ONLY
    $contact = $contact->retrieve_by_string_fields(array('portal_name'=>$contact_portal_auth['user_name'], 'portal_password' => $contact_portal_auth['password'], 'portal_active'=>'1', 'deleted'=>0) );

		$response = mysql_query($accountquery);
		$account = mysql_fetch_assoc($response);

		if(empty($account)){
				return -1;
		}
		$account_id = $account['account_id'];
		$remaining_tc = $account['remaining_tc'];
		$expiration_tc = $account['expiration_tc'];

		$datearr = explode("-", $expiration_tc);
		$exp_time = mktime(0, 0, 0, $datearr[1], $datearr[2], $datearr[0]);
		$now_time = strtotime("-1 day");
		
		if($now_time <= $exp_time){
			$updatequery = "UPDATE accounts_cstm
                            SET remaining_training_credits_c = remaining_training_credits_c - $decrement_by
                            WHERE accounts_cstm.id_c = '$account_id'";
			$success = mysql_query($updatequery);

        $_SESSION['team_id'] = $contact->team_id;
        $_SESSION['team_set_id'] = $contact->team_set_id;
        
        $_SESSION['assigned_user_id'] = $contact->assigned_user_id;
        //BEGIN SUGARCRM flav=ent ONLY
        $sessionManager->session_type = 'contact';
        $sessionManager->last_request_time = gmdate($GLOBALS['timedate']->get_db_date_time_format());
        $sessionManager->session_id = session_id();
        $sessionManager->save();
        //END SUGARCRM flav=ent ONLY
        login_success();
        build_relationship_tree($contact);
        return array('id'=>session_id(), 'error'=>$error->get_soap_array());
    }
    else{
        $error->set_error('invalid_login');
        return array('id'=>-1, 'error'=>$error->get_soap_array());
    }
}
//END SUGARCRM flav=pro ONLY
/*
this validates the session and starts the session;
*/
function portal_validate_authenticated($session_id){
	$old_error_reporting = error_reporting(0);
	session_id($session_id);
	// This little construct checks to see if the session validated
	if(session_start()) {
        $valid_session = true;
        //BEGIN SUGARCRM flav=pro ONLY
        $valid_session = SessionManager::getValidSession($session_id);
        //END SUGARCRM flav=pro ONLY
		if(!empty($_SESSION['is_valid_session']) && $_SESSION['ip_address'] == query_client_ip() && $valid_session != null && ($_SESSION['type'] == 'contact' || $_SESSION['type'] == 'lead' || $_SESSION['type'] == 'portal')){
			global $current_user;
			//BEGIN SUGARCRM flav=pro ONLY
            $valid_session->last_request_time = gmdate($GLOBALS['timedate']->get_db_date_time_format());
            $valid_session->save();
            //END SUGARCRM flav=pro ONLY
			$current_user = new User();
			$current_user->retrieve($_SESSION['portal_id']);
			login_success();
			error_reporting($old_error_reporting);
			return true;	
		}
	}
	session_destroy();
	$GLOBALS['log']->fatal('SECURITY: The session ID is invalid');
	error_reporting($old_error_reporting);
	return false;
}


$server->register(
        'portal_logout',
        array('session'=>'xsd:string'),
        array('return'=>'tns:error_value'),
        $NAMESPACE); 
function portal_logout($session){
	$error = new SoapError();
	if(portal_validate_authenticated($session)){
        //BEGIN SUGARCRM flav=ent ONLY
        $sessionManager = new SessionManager();
        $sessionManager->archiveSession($session);
        //END SUGARCRM flav=ent ONLY
		session_destroy();
		return $error->get_soap_array();
	}
	$error->set_error('invalid_session');
	return $error->get_soap_array();
}

$server->register(
        'portal_get_sugar_id',
        array('session'=>'xsd:string'),
        array('return'=>'tns:set_entry_result'),
        $NAMESPACE);
function portal_get_sugar_id($session){
	$error = new SoapError();
	if(portal_validate_authenticated($session)){
		return array('id'=>$_SESSION['portal_id'], 'error'=>$error->get_soap_array());
	}
	$error->set_error('invalid_session');
	return array('id'=>-1, 'error'=>$error->get_soap_array());
	
}

$server->register(
    'portal_get_partner_entry_list',
    array('session'=>'xsd:string', 'module_name'=>'xsd:string','where'=>'xsd:string', 'order_by'=>'xsd:string', 'select_fields'=>'tns:select_fields'),
    array('return'=>'tns:get_entry_list_result'),
    $NAMESPACE);

function portal_get_partner_entry_list($session, $module_name,$where, $order_by, $select_fields){
	global  $beanList, $beanFiles, $portal_modules;
	$error = new SoapError();
	if(! portal_validate_authenticated($session)){
		$error->set_error('invalid_session');	
		return array('result_count'=>-1, 'entry_list'=>array(), 'error'=>$error->get_soap_array());
	}
	if($_SESSION['type'] == 'lead' ){
		$error->set_error('no_access');	
		return array('result_count'=>-1, 'entry_list'=>array(), 'error'=>$error->get_soap_array());	
	}
	if(empty($beanList[$module_name])){
		$error->set_error('no_module');	
		return array('result_count'=>-1, 'entry_list'=>array(), 'error'=>$error->get_soap_array());
	}

	if ($module_name == 'Leads' || $module_name == 'Opportunities') {
	        $templateclass = $beanList[$module_name];
	        $sugar = new $templateclass();
		$sugar->disable_row_level_security = TRUE;

	        if(!isset($_SESSION['viewable'][$module_name])){
			require_once("include/database/PearDatabase.php");
			$db = & PearDatabase :: getInstance();

			$accounts_in = "(\"" . implode(get_module_from_cache("Accounts"), '","') . "\")";
			$q = "SELECT id_c FROM {$sugar->table_name}_cstm WHERE partner_assigned_to_c IN {$accounts_in}";
			$res = $db->query($q);

			$records = array();
			while ($row = $db->fetchByAssoc($res)) {
				$records[] = $row['id_c'];
			}

			$records_in = "(\"" . implode($records, '","') . "\")";

			set_module_in(array("list" => $records, "in" => $records_in), $module_name);
	        }

	        $someIN = get_module_in($module_name);
	        $list = get_related_list(get_module_in($module_name), $sugar, $where, $order_by);

	}
	else{
		$error->set_error('no_module_support');	
		return array('result_count'=>-1, 'entry_list'=>array(), 'error'=>$error->get_soap_array());
	}
			
	$output_list = Array();
	$field_list = array();

	foreach($list as $value)
	{
		$output_list[] = get_return_value($value, $module_name);
		$_SESSION['viewable'][$module_name][$value->id] = $value->id;
		if(empty($field_list)){
			$field_list = get_field_list($value);	
		}
	}

	$output_list = filter_return_list($output_list, $select_fields, $module_name);
	$field_list = filter_field_list($field_list,$select_fields, $module_name);

	$out = array('result_count'=>sizeof($output_list), 'next_offset'=>0,'field_list'=>$field_list, 'entry_list'=>$output_list, 'error'=>$error->get_soap_array());

	return $out;
}

$server->register(
    'portal_get_entry_list',
    array('session'=>'xsd:string', 'module_name'=>'xsd:string','where'=>'xsd:string', 'order_by'=>'xsd:string', 'select_fields'=>'tns:select_fields'),
    array('return'=>'tns:get_entry_list_result'),
    $NAMESPACE);

function portal_get_entry_list($session, $module_name,$where, $order_by, $select_fields){
	global  $beanList, $beanFiles, $portal_modules;
	$error = new SoapError();

	if(! portal_validate_authenticated($session)){
		$error->set_error('invalid_session');	
		return array('result_count'=>-1, 'entry_list'=>array(), 'error'=>$error->get_soap_array());
	}

	if($_SESSION['type'] == 'lead' || ($module_name == 'KBDocuments' && empty($GLOBALS['beanList']['KBDocuments']))){
		$error->set_error('no_access');	
		return array('result_count'=>-1, 'entry_list'=>array(), 'error'=>$error->get_soap_array());	
	}
	if(empty($beanList[$module_name])){
		$error->set_error('no_module');	
		return array('result_count'=>-1, 'entry_list'=>array(), 'error'=>$error->get_soap_array());
	}
	if($module_name == 'Cases'){
		if(!isset($_SESSION['viewable'][$module_name])){
			get_cases_in_contacts(get_contacts_in());
			get_cases_in_accounts(get_accounts_in());
		}

		$sugar = new aCase();	
		// BEGIN Internal Sugar customization
		$list =  get_related_list(get_module_in($module_name), new aCase(), $where,$order_by);

	}else if($module_name == 'Contacts'){
			$sugar = new Contact();
			$list =  get_related_list(get_module_in($module_name), new Contact(), $where,$order_by);
	}else if($module_name == 'Accounts'){
			$sugar = new Account();
			$list =  get_related_list(get_module_in($module_name), new Account(), $where,$order_by);	
	}else if($module_name == 'Bugs'){
			if(!isset($_SESSION['viewable'][$module_name])){
				get_bugs_in_contacts(get_contacts_in());
				// Begin Bug Portal modification by Julian
				//get_bugs_in_accounts(get_accounts_in());
				// End Bug Portal modification by Julian
			}

				$pre_query = "SELECT id FROM bugs LEFT JOIN bugs_cstm ON (bugs.id = bugs_cstm.id_c) WHERE {$where}";
				$list = get_related_list("", new Bug(), "",$order_by, 0, "", $pre_query);
			
	}
	// BEGIN Internal Sugar customization
        else if($module_name == 'Campaigns'){
                require_once("modules/Campaigns/Campaign.php");
                $sugar = new Campaign();
                $sugar->disable_row_level_security = TRUE;
			
                $response = $sugar->get_list($order_by, $where, 0, -99, -99);
                $list = $response['list'];
	// END Internal Sugar customization
	} else if($module_name == 'KBDocuments' || $module_name == 'FAQ') {
		$sugar = new KBDocument();
	}
	else{
		$error->set_error('no_module_support');	
		return array('result_count'=>-1, 'entry_list'=>array(), 'error'=>$error->get_soap_array());
		
	}
			
	$output_list = Array();
	$field_list = array();

	// Begin Internal Sugar customization -- jostrow 2006-03-30
	$portal_username = isset($_SESSION['portal_username']) ? $_SESSION['portal_username'] : "";
	// END Internal Sugar customization

	foreach($list as $value)
	{
                // BEGIN Internal Sugar customization -- jostrow 2006-03-30
                if ($module_name == "Bugs") {
                        if (
                                $value->display_in_portal_c == "no" ||
                                ($value->display_in_portal_c == "submitter_only" && $value->portal_name_c != $portal_username)
                        ) {
				continue;
                        }
                }
                // END Internal Sugar customization
		
		$output_list[] = get_return_value($value, $module_name);

		$_SESSION['viewable'][$module_name][$value->id] = $value->id;
		if(empty($field_list)){
			$field_list = get_field_list($value);	
		}

	}

	$output_list = filter_return_list($output_list, $select_fields, $module_name);
	$field_list = filter_field_list($field_list,$select_fields, $module_name);

	$out = array('result_count'=>sizeof($output_list), 'next_offset'=>0,'field_list'=>$field_list, 'entry_list'=>$output_list, 'error'=>$error->get_soap_array());

	return $out;
}

/*
 * Acts like a normal get_entry_list except it will build the where clause based on the name_value pairs passed
 * Here we assume 'AND'
 */
$server->register(
    'portal_get_entry_list_filter',
    array('session'=>'xsd:string', 'module_name'=>'xsd:string', 'order_by'=>'xsd:string', 'select_fields'=>'tns:select_fields', 'row_offset' => 'xsd:int', 'limit'=>'xsd:int', 'filter' =>'tns:name_value_operator_list'),
    array('return'=>'tns:get_entry_list_result'),
    $NAMESPACE);
    

function portal_get_entry_list_filter($session, $module_name, $order_by, $select_fields, $row_offset, $limit, $filter){
    global  $beanList, $beanFiles, $portal_modules;

    $error = new SoapError();
    if(! portal_validate_authenticated($session)){
        $error->set_error('invalid_session');
        return array('result_count'=>-1, 'entry_list'=>array(), 'error'=>$error->get_soap_array());
    }

    if($_SESSION['type'] == 'lead'){
        $error->set_error('no_access');
        return array('result_count'=>-1, 'entry_list'=>array(), 'error'=>$error->get_soap_array());
    }
    if(empty($beanList[$module_name])){
        $error->set_error('no_module');
        return array('result_count'=>-1, 'entry_list'=>array(), 'error'=>$error->get_soap_array());
    }
    
    //build the where clause
    
    $sugar = null;
    if($module_name == 'Cases'){
        $sugar = new aCase();
    }else if($module_name == 'Contacts'){
        $sugar = new Contact();
    }else if($module_name == 'Accounts'){
        $sugar = new Account(); 
    //BEGIN SUGARCRM flav!=sales ONLY
    } else if($module_name == 'Bugs'){
        $sugar = new Bug();
    } else if($module_name == 'KBDocuments' || $module_name == 'FAQ') {
    	$sugar = new KBDocument();
    //END SUGARCRM flav!=sales ONLY
    } else {
        $error->set_error('no_module_support');
        return array('result_count'=>-1, 'entry_list'=>array(), 'error'=>$error->get_soap_array());
    }

    if($sugar != null){
        if(isset($filter) && is_array($filter)){
            $where = "";
            foreach($filter as $nvOp){
                $name = $nvOp['name'];
                $value = $nvOp['value'];
                $value_array = $nvOp['value_array'];
                $operator = $nvOp['operator'];
                //do nothing if all three values are not set
                if(isset($name) && (isset($value) || isset($value_array)) && isset($operator)){
                    if(!empty($where)){
                        $where .= ' AND ';   
                    }
                    if(isset($sugar->field_defs[$name])){
						// BEGIN SUGARINTERNAL CUSTOMIZATION - SUPPORT FOR CUSTOM FIELDS IN PORTAL - WILL GO INTO PRODUCT - REMOVE WHEN COMPLETE
                        // MFH - Added Support For Custom Fields in Searches
                        if (isset($sugar->field_defs[$name]['source']) && $sugar->field_defs[$name]['source'] == 'custom_fields'){
                            $cstm = '_cstm';    
                        } else {
                            $cstm = '';
                        }
                        $where .=  "$sugar->table_name$cstm.$name $operator ";
						// END SUGARINTERNAL CUSTOMIZATION - SUPPORT FOR CUSTOM FIELDS IN PORTAL - WILL GO INTO PRODUCT - REMOVE WHEN COMPLETE
                        if($sugar->field_defs['name']['type'] == 'datetime'){
                            $where .= db_convert("'$value'", 'datetime');
                        }else{
                            if(empty($value)) {
                                $tmp = array();
                                foreach($value_array as $v) {
                                    $tmp[] = $GLOBALS['db']->quote(from_html($v));
                                }
                                $where .= "('" . implode("', '", $tmp) . "')";                                
                            } else {
                                $where .= "'".$GLOBALS['db']->quote(from_html($value))."'";
                            }   
                        }   
                    }     
                }    
            }
        }      

        $GLOBALS['log']->debug("Portal where clause: ".$where);
        return portal_get_entry_list_limited($session, $module_name, $where, $order_by, $select_fields, $row_offset, $limit);          
    }else{
        $error->set_error('no_module_support');
        return array('result_count'=>-1, 'entry_list'=>array(), 'error'=>$error->get_soap_array());  
    }
}


$server->register(
    'portal_get_entry',
    array('session'=>'xsd:string', 'module_name'=>'xsd:string', 'id'=>'xsd:string', 'select_fields'=>'tns:select_fields'),
    array('return'=>'tns:get_entry_result'),
    $NAMESPACE);

function portal_get_entry($session, $module_name, $id,$select_fields ){
	global  $beanList, $beanFiles;
	$error = new SoapError();
	if(! portal_validate_authenticated($session)){
		$error->set_error('invalid_session');	
		return array('result_count'=>-1, 'entry_list'=>array(), 'error'=>$error->get_soap_array());
	}
	if($_SESSION['type'] == 'lead' || ($module_name == 'KBDocuments' && empty($GLOBALS['beanList']['KBDocuments']))){
		$error->set_error('no_access');	
		return array('result_count'=>-1, 'entry_list'=>array(), 'error'=>$error->get_soap_array());	
	}
	if(empty($beanList[$module_name])){
		$error->set_error('no_module');	
		return array('result_count'=>-1, 'entry_list'=>array(), 'error'=>$error->get_soap_array());
	}
	
	if(empty($_SESSION['viewable'][$module_name][$id]) && $_SESSION['user'] != "lead_portal"){
		$error->set_error('no_access');	
		return array('result_count'=>-1, 'entry_list'=>array(), 'error'=>$error->get_soap_array());	
	} 
	
	$class_name = $beanList[$module_name];
	require_once($beanFiles[$class_name]);
	$seed = new $class_name();
	//BEGIN SUGARCRM flav=pro ONLY
	$seed->disable_row_level_security = true;
	//END SUGARCRM flav=pro ONLY
	$seed->retrieve($id);

	if($module_name == 'KBDocuments') {
		$body = $seed->get_kbdoc_body($id);
		$seed->description = $body;
	}	
	
	$output_list = Array();
	

		
		//$loga->fatal("Adding another account to the list");
	$output_list[] = get_return_value($seed, $module_name);
	
	$output_list = filter_return_list($output_list, $select_fields, $module_name);
	$field_list = array();
	if(empty($field_list)){
			$field_list = get_field_list($seed);
	}
	$output_list = filter_return_list($output_list, $select_fields, $module_name);
	$field_list = filter_field_list($field_list,$select_fields, $module_name);

	return array('field_list'=>$field_list, 'entry_list'=>$output_list, 'error'=>$error->get_soap_array());
}
 
$server->register(
        'portal_login_contact',
        array('portal_auth'=>'tns:user_auth','contact_portal_auth'=>'tns:user_auth', 'application_name'=>'xsd:string'),
        array('return'=>'tns:set_entry_result'),
        $NAMESPACE);

function portal_login_contact($portal_auth, $contact_portal_auth, $application_name){
//$GLOBALS['log']->fatal('login');
    $error = new SoapError();
    $contact = new Contact();
    $result = login_user($portal_auth);
    
    if($result == 'fail' || $result == 'sessions_exceeded'){
        if($result == 'sessions_exceeded') {
            $error->set_error('sessions_exceeded');
        }
        else {
            $error->set_error('no_portal');
        }
        return array('id'=>-1, 'error'=>$error->get_soap_array());
    }
    global $current_user;
    $sessionManager = new SessionManager();
    $contact = $contact->retrieve_by_string_fields(array('portal_name'=>$contact_portal_auth['user_name'], 'portal_password' => $contact_portal_auth['password'], 'portal_active'=>'1', 'deleted'=>0) );
//    $GLOBALS['log']->fatal($contact);
    if($contact != null){
        session_start();
        $_SESSION['is_valid_session']= true;
        $_SESSION['ip_address'] = $_SERVER['REMOTE_ADDR'];
        $_SESSION['user_id'] = $contact->id;
        $_SESSION['portal_id'] = $current_user->id;

        $_SESSION['type'] = 'contact';

        $_SESSION['team_id'] = $contact->team_id;

        $_SESSION['assigned_user_id'] = $contact->assigned_user_id;
        $sessionManager->session_type = 'contact';
        $sessionManager->last_request_time = gmdate("Y-m-d H:i:s");
        $sessionManager->session_id = session_id();
        $sessionManager->save();
        login_success();
        build_relationship_tree($contact);
        return array('id'=>session_id(), 'error'=>$error->get_soap_array());
    }
    else{
        $error->set_error('invalid_login');
        return array('id'=>-1, 'error'=>$error->get_soap_array());
    }
}
 
$server->register(
    'portal_set_entry',
    array('session'=>'xsd:string', 'module_name'=>'xsd:string',  'name_value_list'=>'tns:name_value_list'),
    array('return'=>'tns:set_entry_result'),
    $NAMESPACE);
    
function portal_set_entry($session,$module_name, $name_value_list){
	global  $beanList, $beanFiles, $valid_modules_for_contact;

	$error = new SoapError();
	if(!portal_validate_authenticated($session)){
		$error->set_error('invalid_session');	
		return array('id'=>-1,  'error'=>$error->get_soap_array());
	}
	if(empty($beanList[$module_name])){
		$error->set_error('no_module');	
		return array('id'=>-1, 'error'=>$error->get_soap_array());
	}
	if($_SESSION['type'] == 'lead' && $module_name != 'Leads'){
		$error->set_error('no_access');	
		return array('id'=>-1, 'error'=>$error->get_soap_array());	
	}
	
	if($_SESSION['type'] == 'contact' && !key_exists($module_name, $valid_modules_for_contact) ){
		$error->set_error('no_access');	
		return array('id'=>-1, 'error'=>$error->get_soap_array());	
	}
	
	$class_name = $beanList[$module_name];
	require_once($beanFiles[$class_name]);
	$seed = new $class_name();
	$is_update = false;
	$values_set = array();
	
	foreach($name_value_list as $value){
		if($value['name'] == 'id' && !empty($value['value'])) {
			$seed->disable_row_level_security = true;
			$seed->retrieve($value['value']);
			$is_update = true;
			break;
		}
		$values_set[$value['name']] = $value['value'];
		$seed->$value['name'] = $value['value'];
	}

	// If it was an update, we have to set the values again
	if($is_update) {
		foreach($name_value_list as $value){
			$seed->$value['name'] = $value['value'];
		}
	}

	if(!isset($_SESSION['viewable'][$module_name])){
		$_SESSION['viewable'][$module_name] = array();
	}
	
	if(!$is_update){

	if(!$is_update){
	//BEGIN SUGARCRM flav=pro ONLY
		if(!key_exists('team_id', $values_set) && isset($_SESSION['team_id'])){
			$seed->team_id = $_SESSION['team_id'];
		}	

		if(!key_exists('team_set_id', $values_set) && isset($_SESSION['team_set_id'])){
			$seed->team_set_id = $_SESSION['team_set_id'];
		}				
	//END SUGARCRM flav=pro ONLY
		if(isset($_SESSION['assigned_user_id']) && (!key_exists('assigned_user_id', $values_set) || empty($values_set['assigned_user_id']))){
			$seed->assigned_user_id = $_SESSION['assigned_user_id'];
		}	
		if(isset($_SESSION['account_id']) && (!key_exists('account_id', $values_set) || empty($values_set['account_id']))){
			// BEGIN Internal Sugar customization -- jostrow
			// 'Account Name' should not be updated for Leads
			if ($module_name != 'Leads') {
			require_once("modules/Accounts/Account.php");
			$seed_account = new Account();
			$seed_account->disable_row_level_security = TRUE;
			$seed_account->retrieve($_SESSION['account_id']);

			$seed->account_id = $_SESSION['account_id'];	
			$seed->account_name = $seed_account->name;

			unset($seed_account);
			}
			// END Internal Sugar customization
		}
		$seed->portal_flag = 1;
	    $seed->portal_viewable = true;
	}
	//BEGIN SUGARCRM flav=pro ONLY
	$seed->disable_row_level_security = true;
	//END SUGARCRM flav=pro ONLY
	$id = $seed->save();
	//BEGIN SUGARCRM flav=pro ONLY
	$seed->disable_row_level_security = true;
	//END SUGARCRM flav=pro ONLY
	set_module_in(array('in'=>"('$id')", 'list'=>array($id)), $module_name);
	if($_SESSION['type'] == 'contact' && $module_name != 'Contacts' && !$is_update){
		if($module_name == 'Notes'){
			$seed->contact_id = $_SESSION['user_id'];
			if(isset( $_SESSION['account_id'])){
				$seed->parent_type = 'Accounts';
				$seed->parent_id = $_SESSION['account_id'];

	//BEGIN Sugar Internal customizations
	$check_notify = TRUE;
	if ($_SESSION['type'] == 'contact' && $module_name != 'Contacts' && !$is_update) {
		$seed->contact_id = $_SESSION['user_id'];

		if($module_name != 'Notes') {
			$seed->contact_id = $_SESSION['user_id'];

			if(isset( $_SESSION['account_id'])){
				$seed->account_id = $_SESSION['account_id'];
			}

			$seed->save_relationship_changes(false);
		}

		if ($module_name == "Cases") {
			global $app_list_strings;

			require_once("modules/Accounts/Account.php");
			$parent_obj = new Account();
			$parent_obj->disable_row_level_security = TRUE;
			$parent_obj->retrieve($seed->account_id);

			$seed->Support_Service_Level_c = $app_list_strings['Support Service Level'][$parent_obj->Support_Service_Level_c];
		}
	}

	if ($module_name == 'Leads') {
		$seed->partner_assigned_to_c = $_SESSION['account_id'];
	}
	// END Internal Sugar customization

	$id = $seed->save($check_notify);
// SADEK BEGIN DEBUG CODE
if(!empty($id) && $id == '4fbeee0a-d7c0-9c6e-d2ee-4418ce6a48a2'){
	//$fp = fopen('zzzzz', 'a');
}
// SADEK END DEBUG CODE
	set_module_in(array('in'=>"('$id')", 'list'=>array($id)), $module_name);
	//END Sugar Internal customizations


	return array('id'=>$id, 'error'=>$error->get_soap_array());
}

/*

NOTE SPECIFIC CODE
*/
$server->register(
        'portal_set_note_attachment',
        array('session'=>'xsd:string','note'=>'tns:note_attachment'),
        array('return'=>'tns:set_entry_result'),
        $NAMESPACE);  

function portal_set_note_attachment($session,$note)
{
	$error = new SoapError();
	if(!portal_validate_authenticated($session)){
		$error->set_error('invalid_session');
		return array('id'=>'-1', 'error'=>$error->get_soap_array());
	}
	if($_SESSION['type'] == 'lead' || !isset($_SESSION['viewable']['Notes'][$note['id']])){
		$error->set_error('no_access');	
		return array('id'=>-1, 'error'=>$error->get_soap_array());	
	}
	require_once('modules/Notes/NoteSoap.php');
	$ns = new NoteSoap();
	$id = $ns->saveFile($note, true);
	return array('id'=>$id, 'error'=>$error->get_soap_array());

}

$server->register(
    'portal_remove_note_attachment',
    array('session'=>'xsd:string', 'id'=>'xsd:string'),
    array('return'=>'tns:error_value'),
    $NAMESPACE);

function portal_remove_note_attachment($session, $id)
{
    $error = new SoapError();
    if(! portal_validate_authenticated($session)){
        $error->set_error('invalid_session');
        return array('result_count'=>-1, 'entry_list'=>array(), 'error'=>$error->get_soap_array());
    }
    if($_SESSION['type'] == 'lead' || !isset($_SESSION['viewable']['Notes'][$id])){
        $error->set_error('no_access');
        return array('result_count'=>-1, 'entry_list'=>array(), 'error'=>$error->get_soap_array());
    }
    
    $focus = new Note();
    //BEGIN SUGARCRM flav=pro ONLY
    $focus->disable_row_level_security = true;
    //END SUGARCRM flav=pro ONLY
    $focus->retrieve($id);
    $result = $focus->deleteAttachment();

    return $error->get_soap_array();
}

$server->register(
    'portal_get_note_attachment',
    array('session'=>'xsd:string', 'id'=>'xsd:string'),
    array('return'=>'tns:return_note_attachment'),
    $NAMESPACE);

function portal_get_note_attachment($session,$id)
{
	
	$error = new SoapError();
	if(! portal_validate_authenticated($session)){
		$error->set_error('invalid_session');	
		return array('result_count'=>-1, 'entry_list'=>array(), 'error'=>$error->get_soap_array());
	}
	if($_SESSION['type'] == 'lead' || !isset($_SESSION['viewable']['Notes'][$id])){
		$error->set_error('no_access');	
		return array('result_count'=>-1, 'entry_list'=>array(), 'error'=>$error->get_soap_array());	
	}
	$current_user = $seed_user;
	
	$note = new Note();
	//BEGIN SUGARCRM flav=pro ONLY
	$note->disable_row_level_security = true;
	//END SUGARCRM flav=pro ONLY
	$note->retrieve($id);
	require_once('modules/Notes/NoteSoap.php');
	$ns = new NoteSoap();
	if(!isset($note->filename)){
		$note->filename = '';
	}
	$file= $ns->retrieveFile($id,$note->filename);
	if($file == -1){
		$error->set_error('no_file');
		$file = '';
	}

	return array('note_attachment'=>array('id'=>$id, 'filename'=>$note->filename, 'file'=>$file), 'error'=>$error->get_soap_array());

}
$server->register(
    'portal_relate_note_to_module',
    array('session'=>'xsd:string', 'note_id'=>'xsd:string', 'module_name'=>'xsd:string', 'module_id'=>'xsd:string'),
    array('return'=>'tns:error_value'),
    $NAMESPACE);

function portal_relate_note_to_module($session,$note_id, $module_name, $module_id){
	global  $beanList, $beanFiles, $current_user;
	
	$error = new SoapError();
	if(! portal_validate_authenticated($session)){
		$error->set_error('invalid_session');	
		return $error->get_soap_array();
		}
	if($_SESSION['type'] == 'lead' || !isset($_SESSION['viewable']['Notes'][$note_id]) || !isset($_SESSION['viewable'][$module_name][$module_id])){
		$error->set_error('no_access');	
		return $error->get_soap_array();
		}
	if(empty($beanList[$module_name])){
		$error->set_error('no_module');	
		return $error->get_soap_array();
	}
	
	$class_name = $beanList[$module_name];
	require_once($beanFiles[$class_name]);

	$seed = new $class_name();
	//BEGIN SUGARCRM flav=pro ONLY
	$seed->disable_row_level_security = true;
	//END SUGARCRM flav=pro ONLY
	$seed->retrieve($module_id);
	if($module_name == 'Cases' || $module_name == 'Bugs') {
		$seed->note_id =  $note_id;
		$seed->save(false);
	}
	// BEGIN Internal Sugar customization
	elseif ($module_name == 'Leads' || $module_name == 'Opportunities') {
		$note_seed = new Note();
		$note_seed->retrieve($note_id);
		$note_seed->parent_type = $module_name;
		$note_seed->parent_id = $module_id;
		$note_seed->save(FALSE);
	}
	// END Internal Sugar customization
	else {
		$error->set_error('no_module_support');	
		$error->description .= ': '. $module_name;
	}
	return $error->get_soap_array();
	
}
$server->register(
    'portal_get_related_notes',
    array('session'=>'xsd:string', 'module_name'=>'xsd:string', 'module_id'=>'xsd:string',  'select_fields'=>'tns:select_fields'),
    array('return'=>'tns:get_entry_result'),
    $NAMESPACE);
    
function portal_get_related_notes($session,$module_name, $module_id, $select_fields){
	global  $beanList, $beanFiles;
	$error = new SoapError();
	if(! portal_validate_authenticated($session)){
		$error->set_error('invalid_session');	
		return array('result_count'=>-1, 'entry_list'=>array(), 'error'=>$error->get_soap_array());
	}
	if($_SESSION['type'] == 'lead' ){
		$error->set_error('no_access');	
		return array('result_count'=>-1, 'entry_list'=>array(), 'error'=>$error->get_soap_array());	
	}
	if(empty($beanList[$module_name])){
		$error->set_error('no_module');	
		return array('result_count'=>-1, 'entry_list'=>array(), 'error'=>$error->get_soap_array());
	}

	// BEGIN jostrow customization
	// See ITRequest #6967: bugs.sugarcrm.com not displaying notes for bugs
	// Since we're now using the SoapSugarUsers API for retrieving the list of Bugs, the $_SESSION['viewable'] array
	// ... is no longer being filled in.  Access control is being handled on the Bug Portal side, so we can safely comment
	// ... this section out.

	//if(empty($_SESSION['viewable'][$module_name][$module_id])){
	//	$error->set_error('no_module');	
	//	return array('result_count'=>-1, 'entry_list'=>array(), 'error'=>$error->get_soap_array());	
	//}

	// END jostrow customization
	
	if($module_name =='Contacts'){
		if($_SESSION['user_id'] != $module_id){
			$error->set_error('no_access');	
			return array('result_count'=>-1, 'entry_list'=>array(), 'error'=>$error->get_soap_array());	
		}
		$list = get_notes_in_contacts("('$module_id')");
	}else{
		// BEGIN jostrow customization
		// See ITRequest #7307: My Bugs - Notes not sorted by date?

		$list = get_notes_in_module("('$module_id')", $module_name, 'notes.date_entered');

		// END jostrow customization
	}

	
	
	$output_list = Array();
	$field_list = Array();
	foreach($list as $value)
	{
		
		//$loga->fatal("Adding another account to the list");
		$output_list[] = get_return_value($value, 'Notes');
		$_SESSION['viewable']['Notes'][$value->id] = $value->id;
	if(empty($field_list)){
			$field_list = get_field_list($value);	
		}
	}
	$output_list = filter_return_list($output_list, $select_fields, $module_name);
	$field_list = filter_field_list($field_list,$select_fields, $module_name);
	

	return array('result_count'=>sizeof($output_list), 'next_offset'=>0,'field_list'=>$field_list, 'entry_list'=>$output_list, 'error'=>$error->get_soap_array());
}

$server->register(
    'portal_get_module_fields',
    array('session'=>'xsd:string', 'module_name'=>'xsd:string'),
    array('return'=>'tns:module_fields'),
    $NAMESPACE);

function portal_get_module_fields($session, $module_name){
	global  $beanList, $beanFiles, $portal_modules, $valid_modules_for_contact;
	$error = new SoapError();
	$module_fields = array();
	if(! portal_validate_authenticated($session)){
		$error->set_error('invalid_session');	
		$error->description .=$session;
		return array('module_name'=>$module_name, 'module_fields'=>$module_fields, 'error'=>$error->get_soap_array());
	}
	if($_SESSION['type'] == 'lead'  && $module_name != 'Leads'){
		$error->set_error('no_access');	
		return array('module_name'=>$module_name, 'module_fields'=>$module_fields, 'error'=>$error->get_soap_array());
	}
	if(empty($beanList[$module_name])){
		$error->set_error('no_module');	
		return array('module_name'=>$module_name, 'module_fields'=>$module_fields, 'error'=>$error->get_soap_array());
	}
	if(($_SESSION['type'] == 'portal'||$_SESSION['type'] == 'contact') &&  !key_exists($module_name, $valid_modules_for_contact)){
		$error->set_error('no_module');	
		return array('module_name'=>$module_name, 'module_fields'=>$module_fields, 'error'=>$error->get_soap_array());
	}
	$class_name = $beanList[$module_name];
    require_once($beanFiles[$class_name]);
	$seed = new $class_name();
	$seed->fill_in_additional_detail_fields();
	$returnFields = get_return_module_fields($seed, $module_name, $error->get_soap_array());

	if(is_subclass_of($seed, 'Person')) {
	   $returnFields['module_fields']['email1'] = array('name'=>'email1', 'type'=>'email', 'required'=>0, 'label'=>translate('LBL_EMAIL_ADDRESS', $seed->module_dir));
	   $returnFields['module_fields']['email_opt_out'] = array('name'=>'email_opt_out', 'type'=>'bool', 'required'=>0, 'label'=>translate('LBL_EMAIL_OPT_OUT', $seed->module_dir), 'options'=>array()); 
	} //if
	
	return $returnFields;
}

$server->register(
	'portal_get_related_list',
	array('session'=>'xsd:string', 'module_name'=>'xsd:string', 'rel_module'=>'xsd:string', 'module_id'=>'xsd:string',  'select_fields'=>'tns:select_fields', 'order_by'=>'xsd:string', 'offset' => 'xsd:int', 'limit' => 'xsd:int'),
	array('return'=>'tns:get_entry_result'),
	$NAMESPACE);

function portal_get_related_list($session, $module_name, $rel_module, $module_id, $select_fields, $order_by, $offset, $limit){
	global  $beanList, $beanFiles;
	$error = new SoapError();
	if(! portal_validate_authenticated($session)){
		$error->set_error('invalid_session');
		return array('result_count'=>-1, 'entry_list'=>array(), 'error'=>$error->get_soap_array());
	}
	if($_SESSION['type'] == 'lead' ){
		$error->set_error('no_access');
		return array('result_count'=>-1, 'entry_list'=>array(), 'error'=>$error->get_soap_array());
	}
	if(empty($beanList[$module_name])){
		$error->set_error('no_module');
		return array('result_count'=>-1, 'entry_list'=>array(), 'error'=>$error->get_soap_array());
	}
	if(empty($_SESSION['viewable'][$module_name][$module_id])){
		$error->set_error('no_access');
		return array('result_count'=>-1, 'entry_list'=>array(), 'error'=>$error->get_soap_array());
	}

	$list = get_related_in_module("('$module_id')", $module_name, $rel_module, $order_by, $offset, $limit);

	$output_list = Array();
	$field_list = Array();
	foreach($list as $value)
	{
	    /* BEGIN SUGARINTERNAL CUSTOMIZATION */
	    if (!empty($value->id)) {
		//$loga->fatal("Adding another account to the list");
		$output_list[] = get_return_value($value, $rel_module);
		$_SESSION['viewable'][$rel_module][$value->id] = $value->id;
		if(empty($field_list)){
			$field_list = get_field_list($value);
		}
	    }
	    /* END SUGARINTERNAL CUSTOMIZATION */
	}
	$output_list = filter_return_list($output_list, $select_fields, $module_name);
	$field_list = filter_field_list($field_list,$select_fields, $module_name);


	return array('result_count'=>$list['result_count'], 'next_offset'=>0,'field_list'=>$field_list, 'entry_list'=>$output_list, 'error'=>$error->get_soap_array());
}
//BEGIN SUGARCRM flav!=sales ONLY
$server->register(
	'portal_get_subscription_lists',
	array('session'=>'xsd:string'),
	array('return'=>'tns:get_subscription_lists_result'),
	$NAMESPACE);

function portal_get_subscription_lists($session){
	global  $beanList, $beanFiles;

	$error = new SoapError();
	if(! portal_validate_authenticated($session)){
		$error->set_error('invalid_session');
		return array('result_count'=>-1, 'entry_list'=>array(), 'error'=>$error->get_soap_array());
	}

	require_once('modules/Campaigns/utils.php');

	$contact = new Contact();

	$contact->disable_row_level_security = true;

    $contact = new Contact();
    //BEGIN SUGARCRM flav=pro ONLY
    $contact->disable_row_level_security = true;
    //END SUGARCRM flav=pro ONLY
    $contact->retrieve($_SESSION['user_id']);

	if(!empty($contact->id)) {
		$result = get_subscription_lists_keyed($contact, true);
	}


	$return_results = array('unsubscribed' => array(), 'subscribed' => array());

	foreach($result['unsubscribed'] as $newsletter_name => $data) {
		$return_results['unsubscribed'][] = array('name' => $newsletter_name, 'prospect_list_id' => $data['prospect_list_id'],
												  'campaign_id' => $data['campaign_id'], 'description' => $data['description'],
												  'frequency' => $data['frequency']);
	}
	foreach($result['subscribed'] as $newsletter_name => $data) {
		$return_results['subscribed'][] = array('name' => $newsletter_name, 'prospect_list_id' => $data['prospect_list_id'],
												'campaign_id' => $data['campaign_id'], 'description' => $data['description'],
												'frequency' => $data['frequency']);
	}

	return array('unsubscribed'=>$return_results['unsubscribed'], 'subscribed' => $return_results['subscribed'], 'error'=>$error->get_soap_array());
}

$server->register(
	'portal_set_newsletters',
	array('session'=>'xsd:string', 'subscribe_ids' => 'tns:select_fields', 'unsubscribe_ids' => 'tns:select_fields'),
	array('return'=>'tns:error_value'),
	$NAMESPACE);

function portal_set_newsletters($session, $subscribe_ids, $unsubscribe_ids){
	global  $beanList, $beanFiles;

	$error = new SoapError();
	if(! portal_validate_authenticated($session)){
		$error->set_error('invalid_session');
		return array('result_count'=>-1, 'entry_list'=>array(), 'error'=>$error->get_soap_array());
	}

	require_once('modules/Campaigns/utils.php');

	$contact = new Contact();

	$contact->disable_row_level_security = true;

    $contact = new Contact();
    //BEGIN SUGARCRM flav=pro ONLY
    $contact->disable_row_level_security = true;
    //END SUGARCRM flav=pro ONLY
    $contact->retrieve($_SESSION['user_id']);

	if(!empty($contact->id)) {
		foreach($subscribe_ids as $campaign_id) {
			subscribe($campaign_id, null, $contact, true);
		}
		foreach($unsubscribe_ids as $campaign_id) {
			unsubscribe($campaign_id, $contact);
		}
	}

	return $error->get_soap_array();
}

//END SUGARCRM flav!=sales ONLY
//BEGIN SUGARCRM flav=pro ONLY

$server->register(
	'portal_get_child_tags',
	array('session'=>'xsd:string', 'tag'=>'xsd:string'),
	array('return'=>'tns:kbtag_list'),
	$NAMESPACE);

function portal_get_child_tags($session, $tag) {
	return portal_get_child_tags_query($session, $tag);
}

if (!empty($GLOBALS['beanList']['KBDocuments'])) {
$server->register(
	'portal_get_tag_docs',
	array('session'=>'xsd:string', 'tag'=>'xsd:string'),
	array('return'=>'tns:kbtag_docs_list'),
	$NAMESPACE);
}
function portal_get_tag_docs($session, $tag) {
	return portal_get_tag_docs_query($session, $tag);
}

if (!empty($GLOBALS['beanList']['KBDocuments'])) {
$server->register(
	'portal_get_kbdocument_attachment',
	array('session'=>'xsd:string', 'id'=>'xsd:string'),
	array('return'=>'tns:return_note_attachment'),
	$NAMESPACE);
}
function portal_get_kbdocument_attachment($session, $id)
{
	$error = new SoapError();
	if(!portal_validate_authenticated($session)){
	   $error->set_error('invalid_session');
	   return array('result_count'=>-1, 'entry_list'=>array(), 'error'=>$error->get_soap_array());
	}
	require_once('modules/KBDocuments/KBDocumentSoap.php');
	$ns = new KBDocumentSoap($id);
	$file= $ns->retrieveFile($id);
	if($file == -1){
	   $error->set_error('no_file');
	   $file = '';
	}
	return array('note_attachment'=>array('id'=>$id, 'filename'=>$ns->retrieveFileName($id), 'file'=>$file), 'error'=>$error->get_soap_array());
}

if (!empty($GLOBALS['beanList']['KBDocuments'])) {
$server->register(
	'portal_get_kbdocument_body',
	array('session'=>'xsd:string', 'id'=>'xsd:string'),
	array('return'=>'xsd:string'),
	$NAMESPACE);
}
function portal_get_kbdocument_body($session, $id) {
	return portal_get_kbdocument_body_query($session, $id);
}

$server->register(
        'portal_get_sugar_contact_id',
        array('session'=>'xsd:string'),
        array('return'=>'tns:set_entry_result'),
        $NAMESPACE);
function portal_get_sugar_contact_id($session){
    $error = new SoapError();
    if(portal_validate_authenticated($session)){
        return array('id'=>$_SESSION['user_id'], 'error'=>$error->get_soap_array());
    }
    $error->set_error('invalid_session');
    return array('id'=>-1, 'error'=>$error->get_soap_array());

}

// BEGIN JOSTROW CUSTOMIZATION
// copy of portal_login_contact() to get new Case Portal working
$server->register(
        'portal_login_contact_nopass',
        array('portal_auth'=>'tns:user_auth','contact_portal_auth'=>'tns:user_auth', 'application_name'=>'xsd:string'),
        array('return'=>'tns:set_entry_result'),
        $NAMESPACE);

function portal_login_contact_nopass($portal_auth, $contact_portal_auth, $application_name){
//$GLOBALS['log']->fatal('login');
    $error = new SoapError();
    $contact = new Contact();
    $result = login_user($portal_auth);
    
    if($result == 'fail' || $result == 'sessions_exceeded'){
        if($result == 'sessions_exceeded') {
            $error->set_error('sessions_exceeded');
        }
        else {
            $error->set_error('no_portal');
        }
        return array('id'=>-1, 'error'=>$error->get_soap_array());
    }
    global $current_user;
    $sessionManager = new SessionManager();

	// BEGIN jostrow customization
	// After the MoofCart launch, we began requiring the the 'Support Authorized Contact' checkbox is checked in Contacts, to gain access to the public Case Tracker

	syslog(LOG_DEBUG, "jostrow: application_name: {$application_name}");

	if ($application_name == 'CaseTracker') {
	    $contact = $contact->retrieve_by_string_fields(array('portal_name'=>$contact_portal_auth['user_name'], 'portal_active'=>'1', 'support_authorized_c' => 1, 'deleted'=>0) );
	}
	else {
	    $contact = $contact->retrieve_by_string_fields(array('portal_name'=>$contact_portal_auth['user_name'], 'portal_active'=>'1', 'deleted'=>0) );
	}

	// END jostrow customization

//    $GLOBALS['log']->fatal($contact);
    if($contact != null){
        session_start();
        $_SESSION['is_valid_session']= true;
        $_SESSION['ip_address'] = $_SERVER['REMOTE_ADDR'];
        $_SESSION['user_id'] = $contact->id;
        $_SESSION['portal_id'] = $current_user->id;

        $_SESSION['type'] = 'contact';

        $_SESSION['team_id'] = $contact->team_id;

        $_SESSION['assigned_user_id'] = $contact->assigned_user_id;
        $sessionManager->session_type = 'contact';
        $sessionManager->last_request_time = gmdate("Y-m-d H:i:s");
        $sessionManager->session_id = session_id();
        $sessionManager->save();
        login_success();
        build_relationship_tree($contact);
        return array('id'=>session_id(), 'error'=>$error->get_soap_array());
    }
    else{
        $error->set_error('invalid_login');
        return array('id'=>-1, 'error'=>$error->get_soap_array());
    }
}
// END JOSTROW CUSTOMIZATIONS
/*
** @author: DTam
** SUGARINTERNAL CUSTOMIZATION
** ITRequest #: 16752
** Description: Add Demo Enviroment link to partner portal sales page.
** Wiki customization page: 
*/
$server->register(
        'portal_get_demo_url',
        array('session'=>'xsd:string','portal_id'=>'xsd:string'),
        array('return'=>'xsd:string'),
        $NAMESPACE);
		
function portal_get_demo_url($session,$portal_id){
	$error = new SoapError();
	if(portal_validate_authenticated($session)){
	// get and return demo env url only if portal name is active
		$urlQuery = "SELECT accounts_cstm.demo_enviroment_url_c FROM accounts_contacts LEFT JOIN contacts ON contacts.id = accounts_contacts.contact_id AND contacts.deleted = '0' LEFT JOIN accounts ON accounts.id = accounts_contacts.account_id AND accounts.deleted = '0' LEFT JOIN accounts_cstm ON accounts.id = accounts_cstm.id_c WHERE contacts.portal_name = \"$portal_id\" AND contacts.portal_active = '1' AND accounts_contacts.deleted = '0' limit 1";
		$response = $GLOBALS['db']->query($urlQuery); 
		$url = $GLOBALS['db']->fetchByAssoc($response);
		return $url['demo_enviroment_url_c'];
	}
	$error->set_error('invalid_session');
	return 'Invalid session, cannot return demo enviroment URL.';
}
//END SUGARCRM flav=pro ONLY
?>