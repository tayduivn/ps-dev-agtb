<?php
if (!defined('sugarEntry') || !sugarEntry) die('Not A Valid Entry Point');
//require_once('modules/Leads/Lead.php');
require_once('modules/Touchpoints/Touchpoint.php');
require_once('modules/Contacts/Contact.php');
require_once('modules/Accounts/Account.php');
//require_once('modules/Leads/leadqual_utils.php');

require_once('modules/LeadContacts/LeadContact.php');
require_once('modules/LeadAccounts/LeadAccount.php');
require_once("include/JSON.php");

global $unknownAssignedUserParents;
$unknownAssignedUserParents = array(
    "Leads_HotEntMktg" => "29b83a70-5c2d-12b0-1683-469be8330a75",
    "Leads_HotCorpMktg" => "ebdd06a4-6794-f03a-c0f8-4460e9bde0d8",
    "Leads_HotMktg" => "c15afb6d-a403-b92a-f388-4342a492003e",
    "Leads_Installer" => "cef7c0a7-4ab0-ae95-2200-4342a4f55812",
);

global $statusesToReset;
$statusesToReset = array(
    'Nurture',
    'Dead',
    'Recycled',
);

global $leadSourceToCheckForContactAcct;
$leadSourceToCheckForContactAcct = array(
    'Training' => 'Training',
);

class ScrubHelper {
    var $fields_to_copy = array();
    var $override_data = array();
    var $LeadAccount_id; //id of related created lead_Account
    var $LeadContact_id; //id of related created lead_contact
    var $scrubResultAction = 'no_match';
    var $scrubResultConflict = false;
    var $scrubRelationBean = 'touchpoint';
    var $new_leadaccount_id = ''; //id of newly created lead_Account
    var $new_leadcontact_id = ''; //id of newly created lead_contact
    var $touchpoint_id = '';
    var $discrepancy_array = array();
    var $discrepancy_text = '';
    var $json;
    var $ignore_discrepancy_fields = array(
        'id',
        'full_name',
        'date_created',
        'date_entered',
        'date_modified',
        'modified_user_id',
        'modified_by_name',
        'created_by',
        'created_by_link',
        'created_by_name',
        'team_id',
        'team_name',
        'campaign_id',
        'description',
        'prospect_id_c',
        'remote_ip_address_c',
    );

    public function ScrubHelper() {
        //constructor
        $this->json = new JSON(JSON_LOOSE_TYPE);

    }

    //initiate auto scrub process
    public function autoScrub($id, $debugMode = false) {

        // This function is fired from post touchpoint save
        //cannot proceed without an id
        if (empty($id) && empty($this->touchpoint_id)) {
            //cannot proceed without an id
            $this->scrubLog("Touchpoint id not passed in, please investigate");
            return false;
        }

        //set id info, to be used to retrieve touchpoint as needed
        if (empty($id)) {
            $id = $this->touchpoint_id;
        } elseif (empty($this->touchpoint_id)) {
            $this->touchpoint_id = $id;
        }

        //Confirm we are not running populating seed data
        if (isset($_SESSION['disable_workflow'])) {
            //no need to continue scrub process
            return false;
        }

        //set global user if not already set
        global $current_user;

        if (empty($current_user->id)) {
            require_once('modules/Users/User.php');
            $current_user = new User();
            $current_user->retrieve('1');
        }

        //retrieve the touchpoint
        $touch_point = new Touchpoint();
        $touch_point->retrieve($id);

        if ($touch_point->scrubbed === 1) {
            //only proceed if the scrubbed flag is set to 0.
            //This touchpoint does not need to be scrubbed again
            $this->scrubLog("Skipping Touchpoint '{$touch_point->id}', this touchpoint has already been scrubbed");
            return false;
        }

        // HERE ARE ALL THE EXCEPTIONS - IF THESE MATCH, WE RETURN FROM THIS FUNCTION
        // assigned_user_id is leads_partner
        if (isset($touch_point->assigned_user_id) && !empty($touch_point->assigned_user_id) && $touch_point->assigned_user_id == '2c780a1f-1f07-23fd-3a49-434d94d78ae5') {
            return;
        }
        // END EXCEPTIONS


        //check name against portal name
        $foundParentLead = false;

        //check name against portal name
        if (!empty($touch_point->portal_name)) {
            $foundParentLead = $this->matchLeadByPortalName($touch_point);
        }

        //make sure emails are there
        if (!$foundParentLead) {
            $foundParentLead = $this->matchLeadByEmail($touch_point);
        }

        //check against actual leads and contacts table, in case no conversion has ocurred yet.
        if (!$foundParentLead) {
            $foundParentLead = $this->matchLeadToContact($touch_point);
        }

        //perform all needed linking
        if ($foundParentLead && !$debugMode) {
            $this->performScrubActions($touch_point->id);
        }

        //populate results array
        $scrub_results['scrubResultAction'] = $this->scrubResultAction;
        $scrub_results['LeadAccount_id'] = $this->LeadAccount_id;
        $scrub_results['LeadContact_id'] = $this->LeadContact_id;
        $scrub_results['RelationBean'] = $this->scrubRelationBean;
        $scrub_results['newLeadAccountId'] = $this->new_leadaccount_id;
        $scrub_results['newLeadContactId'] = $this->new_leadcontact_id;

        // Sadek Pardot 2009-12-07 :: We need to be setting the lead account and lead contact id for previously existing records as well
        // The reason for this is that we need to be creating interactions from activities that are generated from pardot in pardot, and
        //    we need to know which related records to link them to
        if (empty($scrub_results['newLeadContactId']) && !empty($scrub_results['LeadContact_id'])) {
            $scrub_results['newLeadContactId'] = $scrub_results['LeadContact_id'];
        }
        if (empty($scrub_results['newLeadAccountId']) && !empty($scrub_results['LeadAccount_id'])) {
            $scrub_results['newLeadAccountId'] = $scrub_results['LeadAccount_id'];
        }

        require_once('custom/si_custom_files/custom_functions.php');
        $campaign_ids_for_opp_create = getCampaignIdsForSixtyMinOpp();
        require('custom/si_custom_files/meta/touchpointsToOppMap.php');
        if (empty($touch_point->potential_users_c) || !isset($touch_point->potential_users_c)) {
            $touch_point->potential_users_c = 'Unknown';
        }

        if ($foundParentLead && $touch_point->campaign_id == '27c5bb36-a021-0835-7d82-43742c76164d') {
            require_once('custom/si_custom_files/custom_functions.php');
            createOppTaskFromTouchpoint($touch_point->id, $this->LeadContact_id);
        }

        if (!$foundParentLead && isSixtyMinuteOpp($touch_point) && !empty($touchpointsToOppMap[$touch_point->potential_users_c])) {
            // IT REQUEST 12294 - Save before creating other records so round robin runs and assignment happens correctly
            require_once('custom/si_custom_files/custom_functions.php');
            $return_vals = siGetSalesAssignmentMap($touch_point);
            if (!empty($return_vals)) {
                $touch_point->assigned_user_id = $return_vals['assigned_user_id'];
            }
            require_once('custom/si_logic_hooks/Leads/LeadQualAutomation.php');
            $lq_hook_class = new LeadQualAutomation();
            $lq_hook_class->leadQualRoundRobin($touch_point, 'before_save', "");

            $leadAcc = new LeadAccount();
            if (!empty($touch_point->company_name)) {
                $leadAcc->name = $touch_point->company_name;
            }
            else {
                if (!empty($touch_point->first_name)) {
                    $leadAcc->name .= $touch_point->first_name . " ";
                }
                $leadAcc->name .= $touch_point->last_name;
            }
            $leadAcc_id = $this->copyBeanFields($touch_point, $leadAcc, false, true);
            $this->new_leadaccount_id = $leadAcc_id;

            $leadCon = new LeadContact();
            $leadCon->leadaccount_id = $leadAcc->id;
            $leadCon->leadaccount_name = $leadAcc->name;
            $leadCon_id = $this->copyBeanFields($touch_point, $leadCon, false, true);
            $this->new_leadcontact_id = $leadCon_id;

            $account = new Account();
            $account->assigned_user_id = $leadCon->assigned_user_id;
            $account_id = $this->copyBeanFields($touch_point, $account, false, false);

            $contact = new Contact();
            $contact_id = $this->copyBeanFields($touch_point, $contact, false, false);
            $contact->load_relationship('accounts');
            $contact->accounts->add($account_id);
            $contact->assigned_user_id = $leadCon->assigned_user_id;
            $contact->save(false);

            $opportunity = new Opportunity();
            $opportunity->name = $touch_point->company_name . " " . $touchpointsToOppMap[$touch_point->potential_users_c]['users'] . " Pro";
            $opportunity->users = $touchpointsToOppMap[$touch_point->potential_users_c]['users'];
            $opportunity->amount = $touchpointsToOppMap[$touch_point->potential_users_c]['amount'];
            $opportunity->amount_usdollar = $touchpointsToOppMap[$touch_point->potential_users_c]['amount_usdollar'];
            $opportunity->opportunity_type = 'sugar_pro_converge';
            $opportunity->Term_c = 'Annual';
            $opportunity->Revenue_Type_c = 'New';
            $opportunity->sales_stage = 'Initial_Opportunity';
            require_once('include/TimeDate.php');
            $timedate = new TimeDate();
            $opportunity->campaign_id = $touch_point->campaign_id;
            $opportunity->account_id = $account_id;
            $opportunity->allow_override_date_closed = true;
            $opportunity->date_closed = $timedate->to_display_date_time(gmdate("Y-m-d", 60 * 86400 + time()));
            $opportunity->assigned_user_id = $leadCon->assigned_user_id;
            $opportunity_id = $this->copyBeanFields($touch_point, $opportunity, false, false);
            $opportunity->load_relationship('contacts');
            $opportunity->contacts->add($contact_id);
            $opportunity->date_closed = $timedate->to_display_date_time(gmdate("Y-m-d", 60 * 86400 + time()));
            $opportunity->sixtymin_opp_c = '1';
            $opportunity->sixtymin_opp_pass_c = '1';
            //** BEGIN CUSTOMIZATION EDDY IT TIX 13018 - rolling partner assigned to data into opportunity
            if (!empty($touchpoint->partner_assigned_to_c) && empty($opportunity->partner_assigned_to_c)) {
                $opportunity->partner_assigned_to_c = $touchpoint->partner_assigned_to_c;
            }
            //** END CUSTOMIZATION EDDY IT TIX 13018
	    if(isset($touchpoint->lead_source_description) && !empty($touchpoint->lead_source_description)) {
	   	$opportunity->notes_c .= $touchpoint->lead_source_description; 
	    }
            $opportunity->save(false);

            $leadConCopy = new LeadContact();
            $leadConCopy->retrieve($leadCon->id);
            $leadConCopy->contact_id = $contact_id;
            $leadConCopy->converted = '1';
            $leadConCopy->save(false);

            $leadAccCopy = new LeadAccount();
            $leadAccCopy->retrieve($leadAcc->id);
            $leadAccCopy->account_id = $account_id;
            $leadAccCopy->opportunity_id = $opportunity_id;
            $leadAccCopy->converted = '1';
            $leadAccCopy->save(false);

            $touch_point->new_leadaccount_id = $this->new_leadaccount_id;
            $touch_point->new_leadcontact_id = $this->new_leadcontact_id;
            $scrub_results['newLeadAccountId'] = $this->new_leadaccount_id;
            $scrub_results['newLeadContactId'] = $this->new_leadcontact_id;
            $scrub_results['RelationBean'] = 'opportunity';
            $scrub_results['scrubResultAction'] = 'auto_create';
            $touch_point->scrub_result = 'auto_create';
            $touch_point->scrubbed = 1;
            $touch_point->save(false, false);

            $this->createInteraction($touch_point);
        }


        //return scrub results array
        return $scrub_results;

    }

    function manualScrub($touchpoint_id, $scrubResultAction, $parent_id = '', $override_data = array(), $rescrub = false) {
        // scrubResultAction should be one of the following when calling manual_scrub
        // * manual_found_is_parent - this means you are setting the touchpoint as a lead contact
        // * manual_found_lead - this means you found a parent lead contact
        // * manual_found_leadaccount - this means you found a lead account to match it up with
        // * manual_found_contact - this means you found a contact you can associate it with
        // * manual_found_account - this means you found an account they should be associated with
        // ** If you found another touchpoint, we convert the other touchpoint to a lead contact, and associate this one as an interaction under that one
        $this->scrubResultAction = $scrubResultAction;
        $this->override_data = $override_data;

        //retrieve the touchpoint
        $touchpoint_bean = new Touchpoint();
        $touchpoint_bean->retrieve($touchpoint_id);

        if (empty($touchpoint_bean->id)) {
            // We could not retrieve the touchpoint
            $this->scrubLog("Manual Scrub: Invalid Touchpoint ID: '{$touchpoint_bean->id}'");
            return false;
        }

        if ($rescrub) {
            $delete_interaction_query = "update interactions set deleted = 1 where source_id = '{$touchpoint_id}'";
            $GLOBALS['db']->query($delete_interaction_query);
            if (!empty($touchpoint_bean->new_leadaccount_id) || !empty($touchpoint_bean->new_leadcontact_id)) {
                $touchpoint_bean->new_leadaccount_id = '';
                $touchpoint_bean->new_leadcontact_id = '';
                $touchpoint_bean->save(false, false);
            }
        }

        $this->touchpoint_id = $touchpoint_id;

        //set global user if not already set
        global $current_user;

        //only proceed if the scrubbed flag is set to 0.
        if ($touchpoint_bean->scrubbed === 1) {
            //This touchpoint does not need to be scrubbed again
            $this->scrubLog("Manual Scrub: Skipping Touchpoint '{$touchpoint_bean->id}', this touchpoint has already been scrubbed");
            return false;
        }
        $skipFields = array('date_entered', 'date_created', 'date_modified', 'created_by', 'id', 'deleted', 'score');
        foreach ($this->override_data as $key => $value) {
            // remove the temp strings set by in the dropdowns
            $value = str_replace(array('eq_qe1','eq_qe2','eq_qe3'), '', $value);

            $touchpoint_bean->$key = $value;
            //syslog(LOG_DEBUG, 'ScrubRouter - Override Data `' . $key . '` -- Value: `' . $value . '`');
        }
        $performResult = $this->performManualScrubActions($touchpoint_bean, $parent_id);

        //populate results array
        $scrub_results['scrubResultAction'] = $this->scrubResultAction;
        $scrub_results['LeadAccount_id'] = $this->LeadAccount_id;
        $scrub_results['LeadContact_id'] = $this->LeadContact_id;
        //return scrub results array
        return $scrub_results;

    }

    function performManualScrubActions($touchpoint_bean, $parent_id = '') {
        //sugar_die($this->scrubResultAction . "::" . $touchpoint_bean->id . "::" . $parent_id."<BR>");

        if (!isset($this->scrubResultAction) || empty($this->scrubResultAction)) {
            //we cannot proceed without scrubResultAction
            $this->scrubLog("Manual Scrub: performManualScrubActions(): touchpoint {$touchpoint_bean->id} -- No action was set.");
            return false;
        }

        $success = false;
        if ($this->scrubResultAction == 'manual_found_is_parent') {

            // IT REQUEST 12294 - Save before creating other records so round robin runs and assignment happens correctly
            require_once('custom/si_logic_hooks/Leads/LeadQualAutomation.php');
            $lq_hook_class = new LeadQualAutomation();
            $lq_hook_class->leadQualRoundRobin($touchpoint_bean, 'before_save', "");

            // We found no record, so we 1) Create a new Lead Account, 2) Create a new Lead Contact
            $leadAcc = new LeadAccount();
            if (!empty($touchpoint_bean->company_name)) {
                $leadAcc->name = $touchpoint_bean->company_name;
            }
            else {
                if (!empty($touchpoint_bean->first_name)) {
                    $leadAcc->name .= $touchpoint_bean->first_name . " ";
                }
                $leadAcc->name .= $touchpoint_bean->last_name;
            }
            $touchpoint_bean->scrub_relation_type = 'leadaccount';
            // DEE CUSTOMIZATION
            $leadAcc->assigned_user_id = $touchpoint_bean->assigned_user_id;
            // END DEE CUSTOMIZATION
            $leadAcc_id = $this->copyBeanFields($touchpoint_bean, $leadAcc, false, true);
            $this->new_leadaccount_id = $leadAcc_id;

            $leadCon = new LeadContact();
            //if dealing with Leads_Partner touchpoints, never round-robin.
            if ($touchpoint_bean->assigned_user_id != '2c780a1f-1f07-23fd-3a49-434d94d78ae5') {
                $leadCon->scrub_flag = true;
            }
            $leadCon->leadaccount_id = $leadAcc->id;
            $leadCon->leadaccount_name = $leadAcc->name;
            // DEE CUSTOMIZATION
            $leadCon->assigned_user_id = $touchpoint_bean->assigned_user_id;
            // END DEE CUSTOMIZATION
            $leadCon_id = $this->copyBeanFields($touchpoint_bean, $leadCon, false, true);
            $this->new_leadcontact_id = $leadCon_id;

            // BEGIN jostrow customization
            require_once('custom/si_custom_files/custom_functions.php');
            // END jostrow customization
            require('custom/si_custom_files/meta/touchpointsToOppMap.php');
            if (empty($touch_point->potential_users_c) || !isset($touch_point->potential_users_c)) {
                $touch_point->potential_users_c = 'Unknown';
            }
            if (!empty($touchpointsToOppMap[$touchpoint_bean->potential_users_c])) {
                $account = new Account();
                $account->assigned_user_id = $leadCon->assigned_user_id;
                $account_id = $this->copyBeanFields($touchpoint_bean, $account, false, false);

                $contact = new Contact();
                $contact_id = $this->copyBeanFields($touchpoint_bean, $contact, false, false);
                $contact->load_relationship('accounts');
                $contact->accounts->add($account_id);
                $contact->assigned_user_id = $leadCon->assigned_user_id;
                $contact->save(false);

                // SugarIntenral Customization - jwhitcraft - moved the opportunity creation code into it's
                // own method for reusability see createOpportunity Method (line ~837)
                $opportunity_id = $this->createOpportunity($touchpoint_bean, $account_id, $contact_id, $leadCon->assigned_user_id);
                SYSLOG(LOG_DEBUG, 'DEEITR19415: ' . $opportunity_id . 'created for touchpoint ' . $touchpoint_bean->id);
		// end SugarIntenral Customization

                $leadConCopy = new LeadContact();
                $leadConCopy->retrieve($leadCon->id);
                $leadConCopy->contact_id = $contact_id;
                $leadConCopy->converted = '1';
                $leadConCopy->save(false);

                $leadAccCopy = new LeadAccount();
                $leadAccCopy->retrieve($leadAcc->id);
                $leadAccCopy->account_id = $account_id;
                $leadAccCopy->opportunity_id = $opportunity_id;
                $leadAccCopy->converted = '1';
                $leadAccCopy->save(false);
            }

            //create new interaction (commented out, can bring back if necessary)
            $this->createInteraction($touchpoint_bean);
            $success = true;
        }
        else if ($this->scrubResultAction == 'manual_found_lead') {
            // We found a lead contact, so we 1) create an interaction associate with that lead contact
            $this->new_leadcontact_id = $parent_id;
            $this->resetStatus($parent_id);
            $touchpoint_bean->scrub_relation_type = 'interaction';
            $this->createInteraction($touchpoint_bean);


            //IT Request#10083 - Lead Person - Email Addresses not rolling up
            if (!empty($_POST['email1'])) {
                //Currently opt out is not carrying through, will file bug.
                //$is_opt_out = (isset($_POST['email_opt_out']) && $_POST['email_opt_out'] == 1) ? TRUE : FALSE;
                $tmp_lead_contact = new LeadContact();
                $t_addresses = $tmp_lead_contact->emailAddress->getAddressesByGUID($parent_id, $tmp_lead_contact->module_dir);
                $tmp_lead_contact->emailAddress->addresses = $t_addresses;
                $tmp_lead_contact->emailAddress->addAddress($_POST['email1']);
                $tmp_lead_contact->emailAddress->save($parent_id, $tmp_lead_contact->module_dir);

                //Set this flag as otherwise the scrub save method will remove emails from the LeadContact bean if there are more
                //than two.
                $_REQUEST['useEmailWidget'] = 1;
            }

            $success = true;
        }
        else if ($this->scrubResultAction == 'manual_found_leadaccount') {
            // We found a lead account, so we create a lead contact, associate it with the lead account, and create an interaction
            $this->new_leadaccount_id = $parent_id;
            $touchpoint_bean->scrub_relation_type = 'leadcontact';
            $leadCon = new LeadContact();
            //if dealing with Leads_Partner touchpoints, never round-robin.
            if ($touchpoint_bean->assigned_user_id != '2c780a1f-1f07-23fd-3a49-434d94d78ae5') {
                $leadCon->scrub_flag = true;
            }
            $leadCon->leadaccount_id = $parent_id;
            $leadCon_id = $this->copyBeanFields($touchpoint_bean, $leadCon, false, true);
            $this->new_leadcontact_id = $leadCon_id;

            $this->createInteraction($touchpoint_bean);
            $success = true;
        }
        else if ($this->scrubResultAction == 'manual_found_contact') {
            $contact = new Contact();
            $contact->retrieve($parent_id);

            // We found a contact, so we 1) create a lead account, 2) create a new lead contact, flagged as converted to that contact, 3) create an interaction
            $touchpoint_bean->scrub_relation_type = 'leadaccount';
            $leadAcc = new LeadAccount();
            //echo "<PRE>"; echo "::".$parent_id."<BR>"; print_r($contact); sugar_die('');
            $leadAcc->account_id = $contact->account_id;
            $leadAcc_id = $this->copyBeanFields($touchpoint_bean, $leadAcc, false, true);

            $this->new_leadaccount_id = $leadAcc_id;
            $leadCon = new LeadContact();
            $leadCon->converted = 1;
            $leadCon->leadaccount_id = $leadAcc_id;
            $leadCon->contact_id = $parent_id;
            $leadCon_id = $this->copyBeanFields($touchpoint_bean, $leadCon, false, true);
            $this->new_leadcontact_id = $leadCon_id;

            //create new interaction (commented out, can bring back if necessary)
            $this->createInteraction($touchpoint_bean);
            $success = true;
        }
        else if ($this->scrubResultAction == 'manual_found_account') {
            // TODO: We found a parent account, so we: 1) push data to the account, 2) create a new contact, 3) create a lead contact, 4) associate an interaction with the lead contact
            $account = new Account();
            $account->retrieve($parent_id);
            $this->copyBeanFields($touchpoint_bean, $account, false, false);

            $touchpoint_bean->scrub_relation_type = 'contact';
            $contact = new Contact();
            $contact->account_id = $parent_id;
            $contact_id = $this->copyBeanFields($touchpoint_bean, $contact, false, true);

            // try and find a lead account to associate to the touchpoint before we create one
            $leadAcc = new LeadAccount();
            $leadAcc->retrieve_by_string_fields(array(
                'account_id' => $parent_id
            ));

            if (empty($leadAcc->id)) {
                // lead account is empty so we create it now.
                $leadAcc->account_id = $parent_id;
                $leadAcc->id = $this->copyBeanFields($touchpoint_bean, $leadAcc, false, true);
            }
            // lead account is not empty so we assign it to the touchpoint.
            $this->new_leadaccount_id = $leadAcc->id;

            $leadCon = new LeadContact();
            $leadCon->leadaccount_id = $leadAcc->id;
            $leadCon->leadaccount_name = $leadAcc->name;
            $leadCon->converted = 1;
            $leadCon->contact_id = $contact->id;
            $leadCon->account_id = $parent_id;
            $leadCon_id = $this->copyBeanFields($touchpoint_bean, $leadCon, false, true);
            $this->new_leadcontact_id = $leadCon_id;

            //create new interaction
            $this->createInteraction($touchpoint_bean, $contact_id, "Contacts");
            $success = true;
        }
        else {
            $this->scrubLog("Manual Scrub: performManualScrubActions(): touchpoint {$touchpoint_bean->id} -- Action '{$this->scrubResultAction}' was not found in the acceptable list of actions.");
            $success = false;
        }

        $touchpoint_bean->scrub_result = 'manual';
        if ($success) {
            $touchpoint_bean->scrubbed = 1;
            if (!empty($this->discrepancy_text)) {
                $touchpoint_bean->discrepancies = $this->discrepancy_text;
            }
        }
        else {
            $touchpoint_bean->scrub_relation_type = 'touchpoint';
        }

        $touchpoint_bean->new_leadaccount_id = $this->new_leadaccount_id;
        $touchpoint_bean->new_leadcontact_id = $this->new_leadcontact_id;

        //DEE CUSTOMIZATION: Hack to remove the discrepancy prefix value from touchpoint bean values
        foreach ($_REQUEST as $field => $value) {
            if (isset($touchpoint_bean->$field) && !empty($touchpoint_bean->$field) && $field != "assigned_user_id") {
                $touchpoint_bean->$field = $_REQUEST[$field];
            }
        }
        //END DEE CUSTOMIZATION

        $touchpoint_bean->save(false, false);

        return $success;
    }

    //given a touchpoint, look for matching Lead Accounts by Portal Name
    public function matchLeadByPortalName($touch_point) {
        $foundParentLead = false;

        // Look for parent or unknown leads that have the same portal name
        $leadCon = new LeadContact();
        $parentSearchQueryPortal =
                "select leadcontacts.id id, leadcontacts.assigned_user_id assigned_user_id \n" .
                        "from leadcontacts leadcontacts \n";
        $leadCon->add_team_security_where_clause($parentSearchQueryPortal);
        $parentSearchQueryPortal .=
                "where leadcontacts.deleted = 0\n" .
                        "  and leadcontacts.portal_name = '{$touch_point->portal_name}'\n";

        if (!empty($touch_point->id)) {
            $parentSearchQueryPortal .= "  and leadcontacts.id != '{$touch_point->id}'\n";
        }
        $parentSearchQueryPortal .=
                "order by date_entered asc\n ";

        $result = $GLOBALS['db']->query($parentSearchQueryPortal);
        if (!$result) {
            $GLOBALS['log']->fatal("Error in parent/child lead automation query. Please investigate.");
            return;
        }
        $row = $GLOBALS['db']->fetchByAssoc($result);
        // We've found one, start linking

        if ($row) {
            $foundParentLead = true;

            $foundParentLead = true;
            //account exists already, set values for later linking

            $this->LeadAccount_id = '';
            $this->LeadContact_id = $row['id'];
            $this->scrubResultAction = 'portal_found';
            //reset status of leadAccount
            $this->resetStatus($row['id']);

        } // end first if($row)
        else {
            // No Parents found
        }
        return $foundParentLead;

    }


    //given a touchpoint, look for matching Leads by Email addresses
    public function matchLeadByEmail($touch_point) {
        $foundParentLead = false;

        // Don't try to auto scrub if it's not an email address
        if (!isset($touch_point->email1) || empty($touch_point->email1) || strpos($touch_point->email1, "@") === false) {
            $fp = fopen('/var/www/sugarinternal/logs/lead_scrub_automation_log.log', 'a');
            fwrite($fp, '"' . date('Y-m-d H:i:s') . "\",\"incoming Touchpoint\",\"{$touch_point->id}\",\"\",\"No valid email\"\n");
            fclose($fp);
            return;
        }

        // Look for leads or lead contacts with matching emails
        $leadCon = new LeadContact();

        $parentSearchQuery =
                "(\n" .
                        "select leadcontacts.id id, leadcontacts.assigned_user_id assigned_user_id, leadcontacts.alt_address_state status, leadcontacts.date_entered date_entered, eabr.bean_module module\n" .
                        "from leadcontacts leadcontacts inner join email_addr_bean_rel eabr on leadcontacts.id = eabr.bean_id and eabr.deleted = 0\n" .
                        "inner join email_addresses ea on eabr.email_address_id = ea.id and ea.deleted = 0\n";

        $leadCon->add_team_security_where_clause($parentSearchQuery);
        $parentSearchQuery .=
                "where leadcontacts.deleted = 0 \n" .
                        " and ea.email_address = '{$touch_point->email1}'\n";

        $parentSearchQuery .=
                ")\n" .
                        "order by date_entered asc\n";

        $result = $GLOBALS['db']->query($parentSearchQuery);

        if (!$result) {
            $GLOBALS['log']->fatal("Error in parent/child lead automation query. Please investigate.");
            return;
        }

        $row = $GLOBALS['db']->fetchByAssoc($result);

        // We've found one, start linking
        if ($row) {
            // If the first parent lead is unknown, make sure it is assigned to a group user, defined at the top of this file

            // If the row is still valid, set the values

            $foundParentLead = true;

            //Found contact, create interaction
            //LeadContact exists already, just set values for later linking
            $this->LeadAccount_id = '';
            $this->LeadContact_id = $row['id'];
            $this->scrubResultAction = 'email_found';
            //reset status of leadAccount
            $this->resetStatus($row['id']);
        } // end first if($row)
        else {
            // No Parents found
        }
        return $foundParentLead;
    }


    //given a touchpoint, look for matching Contact
    public function matchLeadToContact($touch_point) {
        $foundParentContact = false;
        $createParentAccount = false;

        if (empty($touch_point->email1)) {
            //cannot proceed without email or last name
            return $foundParentContact;
        }

        // Look for contacts that have the same email address as this touchpoint
        $c_bean = new Contact();
        $contactSearchQuery =
                "select a_c.account_id acc_id, contacts.id id, contacts.assigned_user_id assigned_user_id, contacts.date_entered date_entered \n" .
                        "from contacts inner join email_addr_bean_rel on contacts.id = email_addr_bean_rel.bean_id and email_addr_bean_rel.deleted = 0\n" .
                        "           inner join email_addresses on email_addr_bean_rel.email_address_id = email_addresses.id and email_addresses.deleted = 0\n" .
                        "           inner join accounts_contacts a_c on a_c.contact_id = contacts.id and a_c.deleted = 0\n";
        $c_bean->add_team_security_where_clause($contactSearchQuery);
        $contactSearchQuery .=
                "where contacts.deleted = 0\n";

        //set email filter
        $contactSearchQuery .= "  and email_addresses.email_address = '{$touch_point->email1}'\n";

        $contactSearchQuery .= "order by date_entered desc\n";

        // Continue here - just like process above for lead_contact
        $result = $GLOBALS['db']->query($contactSearchQuery);
        if (!$result) {
            $GLOBALS['log']->fatal("Error in parent/child lead automation query for finding contacts. Please investigate.");
            return;
        }

        // We've found one, start linking
        $row = $GLOBALS['db']->fetchByAssoc($result);
        if ($row) {
            $foundParentContact = true;
            //set lead account value for later linking
            $this->LeadAccount_id = '';

            $la_id = '';
            //grab related lead account

            if (!empty($row['acc_id'])) {
                //get lead account based on account id from contact
                if ($this->findParentLeadAccount($row['acc_id'])) {
                    $la_id = $this->LeadAccount_id;
                }
            }

            //if la_id is still empty (findparentleadaccount() function did not find an account,
            //or account id was not found from contact query) then create a new leadaccount record
            if (empty($la_id)) {
                //no lead_account = create new lead_account
                $la_id = $this->createLeadBeanFromContact($row['id']);

                //set scrub relation info
                $this->scrubRelationBean = 'leadaccount';
                $this->new_leadaccount_id = $la_id;
                $this->LeadAccount_id = $la_id;
                $createParentAccount = true;
            }

            //create a new leadcontact record based on found contact record, and attached to new/found leadaccount
            $lc_id = $this->createLeadContactFromContact($row['id'], $la_id);
            if (!$createParentAccount) {
                //set scrub relation info if no account was created
                $this->scrubRelationBean = 'leadcontact';
                $this->new_leadcontact_id = $lc_id;
                $this->LeadContact_id = $lc_id;
            }

            //set lead contact values for later linking, lead account should have already been set if found
            $this->LeadContact_id = $lc_id;
            $this->LeadAccount_id = $la_id;
            $this->scrubResultAction = 'contact_found';

        }
        else {
            // 	No Contacts found
        }


        return $foundParentContact;

    }

    //given an account id, search for any matching Lead Account
    public function findParentLeadAccount($account_id) {
        $foundParentLeadAccount = false;

        //search for lead account based on email address
        $leadAcc = new LeadAccount();
        $LeadParentSearchQuery =
                "select leadaccounts.id id \n" .
                        "from leadaccounts leadaccounts ";

        $leadAcc->add_team_security_where_clause($LeadParentSearchQuery);
        $LeadParentSearchQuery .=
                "where leadaccounts.deleted = 0 \n" .
                        " and leadaccounts.account_id = '$account_id'\n";
        //execute query
        $result = $GLOBALS['db']->query($LeadParentSearchQuery);
        if (!$result) {
            $GLOBALS['log']->fatal("Error in parent/child lead automation query for finding Lead Accounts from contacts. Please investigate.");
            return;
        }

        // We've found one, start linking
        $row = $GLOBALS['db']->fetchByAssoc($result);
        if ($row && !empty($row['id'])) {
            //set values for later linking
            $this->LeadAccount_id = $row['id'];
            $foundParentLeadAccount = true;
        }

        return $foundParentLeadAccount;

    }

    //given a touchpoint, create an interaction
    public function createInteraction($touchpoint_ref, $parent_id = '', $parent_type = '') {
        // BEGIN IT REQUEST 12850 - DISABLE CREATION OF INTERACTIONS FROM WITHIN SUGARINTERNAL NOW THAT PARDOT CREATES THEM
        return false;
        // END IT REQUEST 12850 - DISABLE CREATION OF INTERACTIONS FROM WITHIN SUGARINTERNAL NOW THAT PARDOT CREATES THEM
        require_once('modules/Interactions/Interaction.php');
        $interaction = new Interaction();

        // You can pass in an id or an actual touchpoint bean. The next nine lines are for checking and using the correct info
        if (is_string($touchpoint_ref)) {
            $touchpoint = new Touchpoint();
            $touchpoint->retrieve($touchpoint_ref);
            $touchpoint_id = $touchpoint_ref;
        }
        else {
            $touchpoint = $touchpoint_ref;
            $touchpoint_id = $touchpoint_ref->id;
        }

        global $timedate;
        $interaction->name = $touchpoint->get_summary_text();

        //new contact id is not set, use related lead contact if set
        if (empty($parent_id)) {
            //grab the newly created lead contact id first if it is set
            if (isset($this->new_leadcontact_id) && !empty($this->new_leadcontact_id)) {
                $parent_id = $this->new_leadcontact_id;
            } elseif (isset($this->LeadContact_id) && !empty($this->LeadContact_id)) {
                $parent_id = $this->LeadContact_id;
            }
        }
        //new related id is not set, default to related lead contact if set
        if (empty($parent_type)) {
            $parent_type = "LeadContacts";
        }
        $interaction->parent_id = $parent_id;
        $interaction->parent_type = $parent_type;
        $interaction->scrub_complete_date = $timedate->to_display_date(gmdate('Y-m-d H:i:s'));
        $interaction->source_id = $touchpoint_id;
        $interaction->source_module = "Touchpoints";
        $interaction->start_date = $timedate->to_display_date(gmdate('Y-m-d H:i:s'));
        $interaction->end_date = $timedate->to_display_date(gmdate('Y-m-d H:i:s'));
        $interaction_id = $interaction->save(false);

        //set scrub relation info to interaction, if relation bean is touchpoint
        if ($this->scrubRelationBean == 'touchpoint')
            $this->scrubRelationBean = 'interaction';

        return $interaction_id;
    }

    /**
     * SugarInternal Customization jwhitcraft
     * Centeralized Location for Creating a new Opportunity
     *
     * @param Touchpoint $touchpoint_bean
     * @param string $account_id
     * @param bool|string $contact_id
     * @param bool|string $assigned_user_id
     * @return
     */
    public function createOpportunity($touchpoint_bean, $account_id, $contact_id = false, $assigned_user_id = false) {
        require('custom/si_custom_files/meta/touchpointsToOppMap.php');
        $opportunity = new Opportunity();
        $opportunity->name = $touchpoint_bean->company_name . " " . $touchpointsToOppMap[$touchpoint_bean->potential_users_c]['users'] . " Pro";
        $opportunity->users = $touchpointsToOppMap[$touchpoint_bean->potential_users_c]['users'];
        $opportunity->amount = $touchpointsToOppMap[$touchpoint_bean->potential_users_c]['amount'];
        $opportunity->amount_usdollar = $touchpointsToOppMap[$touchpoint_bean->potential_users_c]['amount_usdollar'];
        $opportunity->opportunity_type = 'sugar_pro_converge';

        // BEGIN jostrow customization
        $opportunity->trial_name_c = $touchpoint_bean->trial_name_c;
        $opportunity->trial_expiration_c = $touchpoint_bean->trial_expiration_c;
        // END jostrow customization

        $opportunity->Term_c = 'Annual';
        $opportunity->Revenue_Type_c = 'New';
        $opportunity->sales_stage = 'Initial_Opportunity';
        require_once('include/TimeDate.php');
        $timedate = new TimeDate();
        $opportunity->campaign_id = $touchpoint_bean->campaign_id;
        $opportunity->account_id = $account_id;
        $opportunity->allow_override_date_closed = true;
        $opportunity->date_closed = $timedate->to_display_date_time(gmdate("Y-m-d", 60 * 86400 + time()));
        if ($assigned_user_id === false) {
            $opportunity->assigned_user_id = $touchpoint_bean->assigned_user_id;
        } else {
            $opportunity->assigned_user_id = $assigned_user_id;
        }
        syslog(LOG_DEBUG, 'ScrubRouter - Opportunity Assigned To Id (' . $opportunity->assigned_user_id . ')');

        $opportunity_id = $this->copyBeanFields($touchpoint_bean, $opportunity, false, false);
        if ($contact_id !== false) {
            $opportunity->load_relationship('contacts');
            $opportunity->contacts->add($contact_id);
        /**
         * SUGARINTERNAL CUSTOMIZATION
         * @author jwhitcraft
         * @ITR 17633
         * @description if the contact_id is not set try and find the contact by the
         * email address and the account
         */
        } else if(false !== ($contact_id = $this->getAccountContactIdByEmail($touchpoint_bean->email1, $account_id))) {
            $opportunity->load_relationship('contacts');
            $opportunity->contacts->add($contact_id);
        /**
         * END SUGARINTERNAL CUSTOMIZATION
         */
        }
        if (isSixtyMinuteOpp_manual($touchpoint_bean)) {
            $opportunity->sixtymin_opp_c = '1';
            $opportunity->sixtymin_opp_pass_c = '1';
        }

        // BEGIN jostrow customization
        // scrub() wasn't playing nice with date fields when users are configured with a non-standard date format
        // the date would always end up as 2000-01-01; I suspect this is because multiple saves are occuring on the same page
        // to fix this, we're converting trial_expiration_c to the display date version

        $opportunity->trial_expiration_c = $touchpoint_bean->trial_expiration_c;

        // END jostrow customization

        //** BEGIN CUSTOMIZATION EDDY IT TIX 13018 - rolling partner assigned to data into opportunity
        if (!empty($touchpoint_bean->partner_assigned_to_c) && empty($opportunity->partner_assigned_to_c)) {
            $opportunity->partner_assigned_to_c = $touchpoint_bean->partner_assigned_to_c;
            $opportunity->accepted_by_partner_c = 'Y';
        }
        //** END CUSTOMIZATION EDDY IT TIX 13018

	if(isset($touchpoint_bean->lead_source_description) && !empty($touchpoint_bean->lead_source_description)) {
                $opportunity->notes_c .= $touchpoint_bean->lead_source_description;
        }
	
        $opportunity_id = $opportunity->save(false);

        return $opportunity_id;
    }

    protected function getAccountContactIdByEmail($email, $account_id)
    {
        if (empty($email)) {
            //cannot proceed without email or last name
            $this->log('info', 'ScrubRouter - Email address was empty! Failing Out');
            return false;
        }

        // Look for contacts that have the same email address as this touchpoint
        $c_bean = new Contact();
        $contactSearchQuery =
                "select contacts.id as contact_id \n" .
                        "from contacts inner join email_addr_bean_rel on contacts.id = email_addr_bean_rel.bean_id and email_addr_bean_rel.deleted = 0\n" .
                        "           inner join email_addresses on email_addr_bean_rel.email_address_id = email_addresses.id and email_addresses.deleted = 0\n" .
                        "           inner join accounts_contacts a_c on a_c.contact_id = contacts.id and a_c.deleted = 0\n";
        $c_bean->add_team_security_where_clause($contactSearchQuery);
        $contactSearchQuery .=
                "where contacts.deleted = 0\n";

        //set email filter
        $contactSearchQuery .= "  and email_addresses.email_address = '{$email}'\n";
        $contactSearchQuery .= "  and a_c.account_id = '{$account_id}'\n";

        $contactSearchQuery .= "order by date_entered desc\n";

        // Continue here - just like process above for lead_contact
        $result = $GLOBALS['db']->query($contactSearchQuery);
        if (!$result) {
            $GLOBALS['log']->fatal("Error in parent/child lead automation query for finding contacts. Please investigate.");
            return;
        }

        // We've found one, start linking
        $row = $GLOBALS['db']->fetchByAssoc($result);
        if ($row) {
            return $row['contact_id'];
        }

        return false;
    }

    // end SugarInternal Customization

    //Copy and save common Fields between beans, or from raw_to_lead.
    //give options to disallow overwrite, remove id to create new bean, and pass in fixed values through a field array
    public function copyBeanFields($copy_from_bean, $copy_to_bean, $overwrite = false, $new = true, $field_arr = '') {

        require('custom/si_custom_files/meta/touchpointsScrubMap.php');

        $c_bean_array = $copy_from_bean->toArray();

        //account for copying name value across leadaccount and leadcontact
        if (isset($c_bean_array['name']) && !isset($c_bean_array['last_name'])) {
            $c_bean_array['last_name'] = $c_bean_array['name'];
        } else if (isset($c_bean_array['last_name']) && !isset($c_bean_array['name'])) {
            if (isset($c_bean_array['first_name'])) {
                $c_bean_array['first_name'] . ' ' . $c_bean_array['last_name'];
            } else {
                $c_bean_array['name'] = $c_bean_array['last_name'];
            }
        }

        //name is empty or not set but first and last name are set, then concatenate
        if (
                (!isset($c_bean_array['name']) || empty($c_bean_array['name']))
                && isset($c_bean_array['first_name']) && isset($c_bean_array['last_name'])) {
            $c_bean_array['name'] = $c_bean_array['first_name'] . ' ' . $c_bean_array['last_name'];
        }

        $to_module_class = get_class($copy_to_bean);
        //copy values for all similar fields
        foreach ($touchpointsScrubMap as $c_key => $map_array) {
            if (in_array($c_key, $this->ignore_discrepancy_fields) && $c_key != 'prospect_id_c') {
                continue;
            }

            // There is no mapping to the other field
            if (!isset($map_array[$to_module_class]) || empty($map_array[$to_module_class])) {
                continue;
            }

            //check for discrepancies
            //if values are set, but do not match AND this is not a new bean,
            //AND neither value is blank, then we have a conflict
            if (
                    isset($copy_to_bean->$map_array[$to_module_class])
                    && isset($c_bean_array[$c_key])
                    && (!empty($c_bean_array[$c_key]) && !empty($copy_to_bean->$map_array[$to_module_class]))
                    && !$overwrite
                    && !$new
                    && trim($copy_to_bean->$map_array[$to_module_class]) !== trim($c_bean_array[$c_key])
            ) {
                //mark the conflict and continue processing
                $this->discrepancy_text .= "\nfield: $c_key,  incoming value: '" . $c_bean_array[$c_key] . "' -- current value: '" . $copy_to_bean->$map_array[$to_module_class] . "' ";
                $this->scrubResultConflict = true;
            }

            if (isset($c_bean_array[$c_key]) && !empty($c_bean_array[$c_key])) {
                //decide whether to overwrite or fill in blanks
                if ($overwrite || $new) {
                    //always write in new value
                    $copy_to_bean->$map_array[$to_module_class] = $c_bean_array[$c_key];
                } else {
                    //write in new value only if field is empty
                    if (empty($copy_to_bean->$map_array[$to_module_class]) && isset($c_bean_array[$c_key])) {
                        $copy_to_bean->$map_array[$to_module_class] = $c_bean_array[$c_key];
                    }
                }
            }
        }

        if ($new) {
            //blank out id if creating new bean
            $copy_to_bean->id = '';

            // IT REQUEST 8979 - MAKE SURE THAT DATE ENTERED ISN'T SET IF IT'S A NEW RECORD
            unset($copy_to_bean->date_entered);
        }
        else {
            // IT REQUEST 8979 - MAKE SURE WE DON'T UPDATE THE DATE ENTERED
            $copy_to_bean->update_date_entered = false;
        }


        //if field => value array has been passed in, overwrite values
        if (is_array($field_arr) && !empty($field_arr)) {
            foreach ($field_arr as $fa_key => $fa_val) {
                $copy_to_bean->$fa_key = $fa_val;
            }
        }

        $copy_to_bean->save(false);
        return $copy_to_bean->id;

    }


    //given a contact id, and a bean type, create the bean type and copy over values from passed in contact id
    public function createLeadBeanFromContact($contact_id, $bean_type = 'leadaccount') {

        //retrieve contact
        $c_bean = new Contact();
        $c_bean->retrieve($contact_id);
        $field_arr = '';

        //convert contact to array
        $c_bean_array = $c_bean->toArray();

        //create right bean type
        $bean = '';
        if (strtolower($bean_type) == 'leadaccount') {
            //create leadaccount
            $bean = new LeadAccount();
            //create lead account using info from found contact
            return $this->copyBeanFields($c_bean, $bean, true, $field_arr);
        } else {
            //create leadcontact
            $bean = new LeadContact();
            //create field array so contact id is added to copy function, and the lead contact is marked as converted
            $field_arr['contact_id'] = $contact_id;
            $field_arr['converted'] = 1;
            $field_arr['status'] = 'New';
            //create lead contact using info from raw lead
            return $this->copyBeanFields($c_bean, $bean, true, $field_arr);

        }
    }


    //given a contact id, create a new LeadContact and link to lead_account if provided
    public function createLeadContactFromContact($contact_id, $lead_account_id = '') {
        //call function that creates lead contact bean
        $lead_contact_id = $this->createLeadBeanFromContact($contact_id, 'leadcontact');

        //if lead_account is passed in, then link lead_account to lead_id
        if (!empty($lead_account_id)) {
            $leadCon = new LeadContact();
            $leadCon->retrieve($lead_contact_id);
            $leadCon->leadaccount_id = $lead_account_id;
            $leadCon->contact_id = $contact_id;
            $leadCon->save(false);
        }
        return $lead_contact_id;
    }


    //perform any linkage or creation of objects that may be needed after scrubbing has ocurred
    //3 possible actions are 'portal_found', 'email_found', 'contact_found'
    public function performScrubActions($touchpoint_id) {

        if (!isset($this->scrubResultAction) || empty($this->scrubResultAction)) {
            //we cannot proceed without scrubResultAction
            $this->scrubLog("could not process touchpoint {$touchpoint_id} -- No action was set.");
            return false;
        }

        $touchpoint = new Touchpoint();
        $touchpoint->retrieve($touchpoint_id);

        // We found no touchpoint with this id. return
        if (empty($touchpoint->id)) {
            $GLOBALS['log']->fatal("Touchpoint scrub performScrubActions passed in id {$touchpoint_id} doesn't exist");
            return false;
        }

        //found in email, or portal.
        if ($this->scrubResultAction == 'email_found' || $this->scrubResultAction == 'portal_found') {
            //LeadContact exists already, just create interaction and update any missing fields
            //update any missing fields from raw lead
            $leadCon = new LeadContact();
            $leadCon->retrieve($this->LeadContact_id);
            $this->copyBeanFields($touchpoint, $leadCon, false, false);
            //set account id for linking to interaction
            $this->LeadAccount_id = $leadCon->leadaccount_id;
            //create new interaction
            $this->createInteraction($touchpoint_id);
        }

        //found in contact.
        if ($this->scrubResultAction == 'contact_found') {
            //both lead_account and lead_contact should have been created already
            //so just create new interaction, update any missing fields from raw lead
            $leadCon = new LeadContact();
            $leadCon->retrieve($this->LeadContact_id);
            $this->copyBeanFields($touchpoint, $leadCon, false, false);

            //create new interaction
            $this->createInteraction($touchpoint_id);

        }

        //if conflict exists, set the result action to conflict, and populate touchpoint with discrepancies
        if ($this->scrubResultConflict == true) {
            //conflict exists, we cannot proceed
            $this->scrubResultAction = 'conflict';
            $tp = new Touchpoint();
            $tp->retrieve($this->touchpoint_id);
            $tp->discrepancies = $this->discrepancy_text;
            $tp->scrub_result = 'conflict';
            $tp->save(false, false);
            $this->scrubLog("could not process touchpoint {$touchpoint_id} -- Conflict was found.");

            return false;
        }

        //log results
        $this->scrubLog("processed touchpoint {$touchpoint_id} with action {$this->scrubResultAction}, leadcontact {$this->LeadContact_id} and leadaccount {$this->LeadAccount_id}.");
    }


    //function for logging scrub messages
    public function resetStatus($contact_id) {
        global $statusesToReset, $unknownAssignedUserParents;

        if (empty($contact_id)) return false;

        //get the lead account bean
        $leadCon = new LeadContact();
        $leadCon->retrieve($contact_id);

        $leadAcc = new LeadAccount();
        $leadAcc->retrieve($leadCon->leadaccount_id);
        $saveLead = false;

        //check to see if assigned user should be reset
        if (($leadCon->assigned_user_id != "cef7c0a7-4ab0-ae95-2200-4342a4f55812" && $leadAcc->assigned_user_id != "cef7c0a7-4ab0-ae95-2200-4342a4f55812")) {
            $leadCon->assigned_user_id = $leadAcc->assigned_user_id;
        }

        //check to see if status needs to be reset
        if (in_array($leadCon->status, $statusesToReset)) {
            // if the parent and child are assigned to Leads_Installer and child status is recycled, don't touch the parent status
            if (!($leadCon->assigned_user_id == "cef7c0a7-4ab0-ae95-2200-4342a4f55812" &&
                    isset($leadCon->status) && $leadCon->status == 'Recycled')
            ) {
                //the leadContact is not assigned to leads installer and status set to recycled, so continue

                //if leadContact is assigned to unknown user parents, then set leadAccount status to new
                if (in_array($leadCon->assigned_user_id, $unknownAssignedUserParents)) {
                    //$leadAcc->status = 'New';
                    $leadCon->status = 'New';
                    $saveLead = true;
                }
                else {
                    //if leadContact is NOT assigned to unknown user parents but lead contact status is set to nurture
                    //then set leadAccount status to requalify
                    if ($leadCon->status == 'Nurture') {
                        //$leadAcc->status = 'Requalify';
                        $leadCon->status = 'Requalify';
                        $saveLead = true;
                    }
                    else {
                        //if leadContact is NOT assigned to unknown user parents lead account status is NOT set to nurture
                        //so set leadAccount status to Assigned
                        //$leadAcc->status = 'Assigned';
                        $leadCon->status = 'Assigned';
                        $saveLead = true;
                    }
                }
                //end check for unknown user parents
            }
            //end check for lead not being recycled
        }
        //end check for statuses to reset

        //save lead account and contact and if needed
        if ($saveLead) {
            //$leadAcc->save();
            $leadCon->save(false);
        }
    }

    public function getDiscrepancyArray($touchpoint_id, $parent_id, $parent_type, $override = array()) {
        $touchpoint_bean = new Touchpoint();
        $touchpoint_bean->retrieve($touchpoint_id);
        if (empty($touchpoint_bean->id)) {
            return false;
        }

        $other_bean = new $parent_type();
        $other_bean->retrieve($parent_id);

        if (empty($other_bean->id)) {
            return false;
        }

        require('custom/si_custom_files/meta/touchpointsScrubMap.php');

        $discrepancy_array = array();
        $to_module_class = $parent_type;
        foreach ($touchpointsScrubMap as $touchpoint_key => $map_array) {
            if (in_array($touchpoint_key, $this->ignore_discrepancy_fields)) {
                continue;
            }

            // There is no mapping to the other field
            if (!isset($map_array[$to_module_class]) || empty($map_array[$to_module_class])) {
                continue;
            }

            //check for discrepancies
            if (isset($touchpoint_bean->$touchpoint_key) && !empty($touchpoint_bean->$touchpoint_key)) {
                if (
                        (isset($other_bean->$map_array[$to_module_class]) && !empty($other_bean->$map_array[$to_module_class]) && trim($other_bean->$map_array[$to_module_class]) !== trim($touchpoint_bean->$touchpoint_key))
                        ||
                        (isset($override[$touchpoint_key]) && !empty($override[$touchpoint_key]) && trim($override[$touchpoint_key]) !== trim($touchpoint_bean->$touchpoint_key))
                )
                    $discrepancy_array[$touchpoint_key]['touchpoint'] = $touchpoint_bean->$touchpoint_key;
                $discrepancy_array[$touchpoint_key]['parent'] = '';
                if (isset($other_bean->$map_array[$to_module_class]))
                    $discrepancy_array[$touchpoint_key]['parent'] = $other_bean->$map_array[$to_module_class];
                if (!empty($override[$touchpoint_key])) {
                    $discrepancy_array[$touchpoint_key]['override'] = $override[$touchpoint_key];
                }
            }
        }

        if (isset($discrepancy_array['assigned_user_id'])) {
            $user = new User();
            if (isset($discrepancy_array['assigned_user_id']['touchpoint'])) {
                $user->retrieve($discrepancy_array['assigned_user_id']['touchpoint']);
                $discrepancy_array['assigned_user_name']['touchpoint'] = $user->user_name;
            }
            if (isset($discrepancy_array['assigned_user_id']['parent'])) {
                $user->retrieve($discrepancy_array['assigned_user_id']['parent']);
                $discrepancy_array['assigned_user_name']['parent'] = $user->user_name;
            }
            if (isset($discrepancy_array['assigned_user_id']['override'])) {
                $user->retrieve($discrepancy_array['assigned_user_id']['override']);
                $discrepancy_array['assigned_user_name']['override'] = $user->user_name;
            }
            //unset($discrepancy_array['assigned_user_id']);
        }
        $this->discrepancy_array = $discrepancy_array;
        return $discrepancy_array;
    }

    //function for logging scrub messages
    public function scrubLog($msg) {

        // BEGIN LOGGING
        $fp = fopen('/var/www/sugarinternal/logs/lead_scrub_automation_log.log', 'a');
        fwrite($fp, '"' . date('Y-m-d H:i:s') . ' ' . $msg . " \n");
        fclose($fp);
        // END LOGGING
    }


}

