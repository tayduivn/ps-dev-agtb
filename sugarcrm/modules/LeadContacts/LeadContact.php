<?php
if(!defined('sugarEntry') || !sugarEntry) die('Not A Valid Entry Point');
/*********************************************************************************
 * The contents of this file are subject to the SugarCRM Enterprise Subscription
 * Agreement ("License") which can be viewed at
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
 * by SugarCRM are Copyright (C) 2004-2010 SugarCRM, Inc.; All Rights Reserved.
 ********************************************************************************/
/*********************************************************************************

 * Description:  TODO: To be written.
 * Portions created by SugarCRM are Copyright (C) SugarCRM, Inc.
 * All Rights Reserved.
 * Contributor(s): ______________________________________..
 ********************************************************************************/

require_once('include/utils.php');
require_once('include/SugarObjects/templates/person/Person.php');
require_once('modules/Opportunities/Opportunity.php');
require_once('modules/Cases/Case.php');
require_once('modules/Tasks/Task.php');
require_once('modules/Notes/Note.php');
require_once('modules/Meetings/Meeting.php');
require_once('modules/Calls/Call.php');
require_once('modules/Emails/Email.php');
require_once('modules/LeadAccounts/LeadAccount.php');

// Lead is used to store profile information for people who may become customers.
class LeadContact extends Person
{
    // fields
    public $id;
	public $name;
	public $date_entered;
	public $date_modified;
	public $modified_user_id;
	public $modified_by_name;
	public $created_by;
	public $created_by_name;
	public $description;
	public $deleted;
	public $created_by_link;
	public $modified_user_link;
	public $assigned_user_id;
	public $assigned_user_name;
	public $assigned_user_link;
	public $team_id;
	public $team_name;
	public $team_link;
	public $salutation;
	public $first_name;
	public $last_name;
	public $full_name;
	public $title;
	public $department;
	public $do_not_call;
	public $phone_home;
	public $phone_mobile;
	public $phone_work;
	public $phone_other;
	public $phone_fax;
	public $email1;
	public $email2;
	public $invalid_email;
	public $email_opt_out;
	public $primary_address_street;
	public $primary_address_street_2;
	public $primary_address_street_3;
	public $primary_address_city;
	public $primary_address_state;
	public $primary_address_postalcode;
	public $primary_address_country;
	public $alt_address_street;
	public $alt_address_street_2;
	public $alt_address_street_3;
	public $alt_address_city;
	public $alt_address_state;
	public $alt_address_postalcode;
	public $alt_address_country;
	public $assistant;
	public $assistant_phone;
	public $converted;
	public $campaign_id;
	public $campaign_name;
	public $score;
	public $leadaccount_id;
	public $leadaccount_status;
	public $leadaccount_name;
	public $leadaccounts;
	public $tasks;
	public $notes;
	public $meetings;
	public $calls;
	public $emails;
	public $campaign_leads;
	public $campaigns;
	public $prospect_lists;
	public $contact_id;
	public $status;
    
    // these two fields aren't in the vardefs, but are needed to make the vcard export not throw e_notice errors
    public $account_name;
    public $birthdate;

    // these fields aren't in the vardefs either, but are used when for the detailview header
    public $leadaccount_opportunity_name = '';
    public $leadaccount_opportunity_id = '';
    
    // object properties
    var $table_name = "leadcontacts";
    var $object_name = "LeadContact";
    var $object_names = "LeadContacts";
    var $module_dir = "LeadContacts";
    var $importable = true;
    var $new_schema = true;

	// This is used to retrieve related fields from form posts.
	var $additional_column_fields = Array('assigned_user_name', 'task_id', 'note_id', 'meeting_id', 'call_id', 'email_id');
	var $relationship_fields = Array('email_id'=>'emails');

	/**
     * Constructor
     */
	public function __construct()
    {
		parent::Person();
		global $current_user;
		global $app_list_strings;

		if (!empty($current_user))
			$this->team_id = $current_user->default_team;
		else
			$this->team_id = 1;

		// Customization by Julian
		if (!isset($app_list_strings['partner_assigned_to'])) {
			$app_list_strings['partner_assigned_to'] = get_partner_array(TRUE);
		}
		if (!isset($app_list_strings['lead_owner_options'])) {
			$app_list_strings['lead_owner_options'] = get_user_array(TRUE);
		}

		if (!isset($app_list_strings['campaign_list'])) {
			$app_list_strings['campaign_list'] = get_campaign_array(TRUE);
		}
		// End customization by Julian
	}

    /**
     * Marks the current lead contact as converted
     */
    public function markConverted()
    {
        // don't do this twice
        if ( $this->converted == '1' )
            return;
        
        $this->converted = '1';
        $this->status = 'Converted';
    }

    /**
     * Creates a new Contact from the current Lead Contact
     *
     * @param  $requestVars array changes from the current Lead Contact record to be saved in the new Contact record
     * @return string id of the newly created Account
     */
    public function createNewContactFrom(
        array $requestVars = array()
        )
    {
        $contactFocus = new Contact;
        $contactFocus->populateFromRow($this->toArray());
        $contactFocus->id = null;
        foreach ( $requestVars as $key => $value )
            if ( !is_array($value) )
                $contactFocus->$key = $value;
        if ( !isset($contactFocus->assigned_user_id) || $contactFocus->assigned_user_id == '' )
            $contactFocus->assigned_user_id = $this->assigned_user_id;
        // BEGIN SUGARCRM PRO ONLY
        if ( !isset($contactFocus->team_id) || $contactFocus->team_id == '' )
            $contactFocus->team_id = $this->team_id;
        // END SUGARCRM PRO ONLY
        $contactFocus->save(false);

        $this->contact_id = $contactFocus->id;

        if(isset($this->campaign_id) && $this->campaign_id != null){
 			require_once('modules/Campaigns/utils.php');
			campaign_log_lead_entry($this->campaign_id, $this, $contactFocus,'contact');
		}
        
        clone_record(
            array('email_addr_bean_rel'), 
            array(
                'bean_id' => $this->id,
                'bean_module' => 'LeadContacts',
                'primary_address' => '0',
                ),
            array(
                'bean_id' => $this->contact_id,
                'bean_module' => 'Contacts',
                )
            );
        
        // Add any remaining related calls, meetings, emails
        $contactFocus->load_relationship('calls');
        $this->load_relationship('calls');
        $callBeans = $this->calls->getBeans(new Call);
        foreach ( $callBeans as $callBean )
            $contactFocus->calls->add($callBean->id);
        
        $contactFocus->load_relationship('meetings');
        $this->load_relationship('meetings');
        $meetingsBeans = $this->meetings->getBeans(new Meeting);
        foreach ( $meetingsBeans as $meetingsBean )
            $contactFocus->meetings->add($meetingsBean->id);
        
        $contactFocus->load_relationship('emails');
        $this->load_relationship('emails');
        $emailsBeans = $this->emails->getBeans(new Email);
        foreach ( $emailsBeans as $emailsBean )
            $contactFocus->emails->add($emailsBean->id);
        
        // clone the entire history
        clone_history($this->db, $this->id, $this->contact_id ,'Contacts');
        
		return $contactFocus->id;
    }
    
    /**
     * Handles adding an additional note and/or appointment during lead conversion
     *
     * @param  $requestVars array changes from the current Lead Account record to be saved in the new Account record
     * @param  $createAppointment boolean true if a new appointment related to the newly created account should be created
     * @return array of bean types and id created
     */
    public function addToConvertedContact(
        array $requestVars = array(),
        $createAppointment = false
        )
    {
        if ( empty($this->contact_id) )
            return;
        
        global $timedate;
        
        $returnArray = array();
        
        // grab the lead account bean
        $leadAccountFocus = new LeadAccount;
        $leadAccountFocus->retrieve($this->leadaccount_id);
            
        // add a new appointment, if requested to do so
        if ( $createAppointment ) {
            if ( $requestVars['appointment']['appointment_type'] == 'Call' )
                $appointmentFocus = new Call;
            else
                $appointmentFocus = new Meeting;
            foreach ( $requestVars['appointment'] as $key => $value )
                if ( !is_array($value) )
                    $appointmentFocus->$key = $value;
            if ( !isset($appointmentFocus->assigned_user_id) || $appointmentFocus->assigned_user_id == '' )
                $appointmentFocus->assigned_user_id = $this->assigned_user_id;
            // BEGIN SUGARCRM PRO ONLY
            if ( !isset($appointmentFocus->team_id) || $appointmentFocus->team_id == '' )
                $appointmentFocus->team_id = $this->team_id;
            // END SUGARCRM PRO ONLY
            
            $appointmentFocus->date_start = $timedate->merge_date_time(
                $requestVars['appointment']['date_start'],
                $requestVars['appointment']['time_start']
                );
            
            // relate the call to the account by default
            if ( !empty($leadAccountFocus->account_id) ) { 
                $appointmentFocus->parent_type = 'Accounts';
                $appointmentFocus->parent_id = $leadAccountFocus->account_id;
            }
            $appointmentFocus->save(false);
            $returnArray[$appointmentFocus->object_name] = $appointmentFocus->id;
            
            // Now add the contact as an invitee
            $appointmentFocus->load_relationship('contacts');
            $appointmentFocus->contacts->add($this->contact_id);
        }
        
        if ( !empty($leadAccountFocus->opportunity_id) ) { 
            $appointmentFocus->load_relationship('opportunity');
            $appointmentFocus->opportunity->add($leadAccountFocus->opportunity_id);
            $opportunityFocus = new Opportunity;
            $opportunityFocus->retrieve($leadAccountFocus->opportunity_id);
            if ( !empty($opportunityFocus->id) ) {
                if ( !isset($opportunityFocus->assigned_user_id) || $opportunityFocus->assigned_user_id == '' )
                    $opportunityFocus->assigned_user_id = $this->assigned_user_id;
                // BEGIN SUGARCRM PRO ONLY
                if ( !isset($opportunityFocus->team_id) || $opportunityFocus->team_id == '' )
                    $opportunityFocus->team_id = $this->team_id;
                // END SUGARCRM PRO ONLY
                $opportunityFocus->save(false);
            }
        }
        
        return $returnArray;
    }

	/**
     * @see SugarBean::fill_in_additional_list_fields()
     */
	public function fill_in_additional_list_fields()
	{
		$this->fill_in_additional_detail_fields();
	}

	/**
     * @see SugarBean::fill_in_additional_detail_fields()
     */
	public function fill_in_additional_detail_fields()
	{
        $this->_create_proper_name_field();
        $this->assigned_user_name = get_assigned_user_name($this->assigned_user_id);
        $this->assigned_name = get_assigned_team_name($this->team_id);
        $this->created_by_name = get_assigned_user_name($this->created_by);
        $this->modified_by_name = get_assigned_user_name($this->modified_user_id);
        
        $leadAccountFocus = new LeadAccount;
        $leadAccountFocus->retrieve($this->leadaccount_id);
        if ( !empty($leadAccountFocus->id) ) {
            $this->leadaccount_name = $leadAccountFocus->name;
            $this->leadaccount_status = $leadAccountFocus->status;
            $opportunity = new Opportunity;
            $opportunity->retrieve($leadAccountFocus->opportunity_id);
            $opportunity->load_relationship('contacts');
            if ( !empty($opportunity->name) 
                    && in_array($this->contact_id,$opportunity->contacts->get()) ) {
                $this->leadaccount_opportunity_name = $opportunity->name;
                $this->leadaccount_opportunity_id = $opportunity->id;
            }
        }
        
        $contactFocus = new Contact;
        $contactFocus->retrieve($this->contact_id);
        if ( !empty($contactFocus->name) )
            $this->contact_name = $contactFocus->name;
	}

    /**
     * @see SugarBean::get_list_view_data()
     */
	function get_list_view_data()
    {
		global $app_list_strings;
		global $current_user;

		$this->_create_proper_name_field();
		$temp_array = $this->get_list_view_array();
		$temp_array['STATUS'] = (empty($temp_array['STATUS'])) ? '' : $temp_array['STATUS'];
		$temp_array['ENCODED_NAME']=$this->name;
		$temp_array['NAME']=$this->name;
		$temp_array['EMAIL1'] = $this->emailAddress->getPrimaryAddress($this);
		$temp_array['EMAIL1_LINK'] = $current_user->getEmailLink('email1', $this, '', '', 'ListView');
		return $temp_array;
	}

	/**
     * builds a generic search based on the query string using or
     * do not include any $this-> because this is called on without having the class instantiated
     *
     * @see SugarBean::build_generic_where_clause()
     */
	public function build_generic_where_clause (
        $the_query_string
        )
    {
        $where_clauses = Array();
        $the_query_string = $GLOBALS['db']->quote($the_query_string);

        array_push($where_clauses, "leadcontacts.last_name like '$the_query_string%'");
        array_push($where_clauses, "leadcontacts.first_name like '$the_query_string%'");
        array_push($where_clauses, "leadcontacts.email1 like '$the_query_string%'");
        array_push($where_clauses, "leadcontacts.email2 like '$the_query_string%'");
        if (is_numeric($the_query_string)) {
            array_push($where_clauses, "leadcontacts.phone_home like '%$the_query_string%'");
            array_push($where_clauses, "leadcontacts.phone_mobile like '%$the_query_string%'");
            array_push($where_clauses, "leadcontacts.phone_work like '%$the_query_string%'");
            array_push($where_clauses, "leadcontacts.phone_other like '%$the_query_string%'");
            array_push($where_clauses, "leadcontacts.phone_fax like '%$the_query_string%'");

        }

        $the_where = "";
        foreach($where_clauses as $clause)
        {
            if($the_where != "") $the_where .= " or ";
            $the_where .= $clause;
        }


        return $the_where;
	}
    
	/**
     * @see SugarBean::bean_implements()
     */
	public function bean_implements($interface){
		switch($interface){
			case 'ACL':return true;
		}
		return false;
	}

    /**
     * @see SugarBean::listviewACLHelper()
     */
	public function listviewACLHelper(){
		$array_assign = parent::listviewACLHelper();
		$is_owner = false;
		if(!empty($this->account_name)){

			if(!empty($this->account_name_owner)){
				global $current_user;
				$is_owner = $current_user->id == $this->account_name_owner;
			}
		}
			if( ACLController::checkAccess('Accounts', 'view', $is_owner)){
				$array_assign['ACCOUNT'] = 'a';
			}else{
				$array_assign['ACCOUNT'] = 'span';
			}
		$is_owner = false;
		if(!empty($this->opportunity_name)){

			if(!empty($this->opportunity_name_owner)){
				global $current_user;
				$is_owner = $current_user->id == $this->opportunity_name_owner;
			}
		}
			if( ACLController::checkAccess('Opportunities', 'view', $is_owner)){
				$array_assign['OPPORTUNITY'] = 'a';
			}else{
				$array_assign['OPPORTUNITY'] = 'span';
			}


		$is_owner = false;
		if(!empty($this->contact_name)){

			if(!empty($this->contact_name_owner)){
				global $current_user;
				$is_owner = $current_user->id == $this->contact_name_owner;
			}
		}
			if( ACLController::checkAccess('Contacts', 'view', $is_owner)){
				$array_assign['CONTACT'] = 'a';
			}else{
				$array_assign['CONTACT'] = 'span';
			}

		return $array_assign;
	}

	function get_unlinked_email_query($type=array()) {
		require_once('include/utils.php');
		return get_unlinked_email_query($type, $this);
	}

    /**
     * @see SugarBean::save()
     */
	function save(
        $check_notify = false
        )
    {
        if(empty($this->status))
            $this->status = 'New';

        // handle the prospect -> lead conversion for creating a new account
        if ( isset($_REQUEST['lead_account_radio']) ) {
            if ($_REQUEST['lead_account_radio'] == 'create') {
                $leadAccount = new LeadAccount;
                $leadAccount->name = $_REQUEST['new_leadaccount_name'];
                $leadAccount->phone_fax = $this->phone_fax;
                $leadAccount->phone_office = $this->phone_work;
                $leadAccount->phone_alternate = $this->phone_other;
                $leadAccount->email1 = $this->email1;
                $leadAccount->billing_address_street = $this->primary_address_street;
                $leadAccount->billing_address_city = $this->primary_address_city;
                $leadAccount->billing_address_state = $this->primary_address_state;
                $leadAccount->billing_address_postalcode = $this->primary_address_postalcode;
                $leadAccount->billing_address_country = $this->primary_address_country;
                $leadAccount->shipping_address_street = $this->alt_address_street;
                $leadAccount->shipping_address_city = $this->alt_address_city;
                $leadAccount->shipping_address_state = $this->alt_address_state;
                $leadAccount->shipping_address_postalcode = $this->alt_address_postalcode;
                $leadAccount->shipping_address_country = $this->alt_address_country;
                $leadAccount->save(false);
                $this->leadaccount_id = $leadAccount->id;
            }
            elseif ($_REQUEST['lead_account_radio'] == 'select') {
                $this->leadaccount_id = $_REQUEST['leadaccount_id'];
            }
        }

        // Handle scoring
		require_once('modules/Score/Score.php');
		Score::scoreBean($this);
        /*
        // No longer is used with SQL scoring
		// Handle rescoring based off of related record counts
		if ( empty($this->fetched_row['leadaccount_id']) || $this->leadaccount_id != $this->fetched_row['leadaccount_id'] ) {
			if ( !empty($this->fetched_row['leadaccount_id']) ) {
				Score::markDirty('target','LeadAccounts',$this->fetched_row['leadaccount_id']);
			}
			Score::markDirty('target','LeadAccounts',$this->leadaccount_id);
			$GLOBALS['log']->fatal('Changed lead account, from '.$this->fetched_row['leadaccount_id'].' to '.$this->leadaccount_id);
		}
		*/
		// Finished with scoring
        
        $old_lead_pass_c = $this->fetched_row['lead_pass_c'];
        $new_lead_pass_c = ( isset($this->lead_pass_c) ? $this->lead_pass_c : '0' );
        
		// IT REQUEST 8535 - DISABLE NOTIFICATIONS FOR LEAD PERSONS, LEAD COMPANIES, AND TOUCHPOINTS
        //$returnValue = parent::save($check_notify);
        $returnValue = parent::save(false);
		// IT REQUEST 8535 - DISABLE NOTIFICATIONS FOR LEAD PERSONS, LEAD COMPANIES, AND TOUCHPOINTS
        
        if ( ($old_lead_pass_c != $new_lead_pass_c ) && $new_lead_pass_c == '1' )
            SugarApplication::redirect("index.php?module=LeadAccounts&action=ConvertLead&record={$this->leadaccount_id}&uid={$this->id}");
        
        return $returnValue;
    }
    
    /**
     * Returns the interactions query parts; ready to be consumed by a subpaneldef
     */
    public function getInteractionsQuery()
    {
        $return_array['select'] = 'SELECT interactions.id ';
        $return_array['from']   = 'FROM interactions ';
        $return_array['where']  = " WHERE ( (parent_type = '{$this->module_dir}' AND parent_id = '{$this->id}') ";
        $return_array['join'] = "";
        $return_array['join_tables'][0] = '';
        
        if ( !empty($this->leadaccount_id) ) {
            $return_array['where'] .= " OR (parent_type = 'LeadAccounts' AND parent_id = '{$this->leadaccount_id}')";
            $leadAccountFocus = new LeadAccount;
            $leadAccountFocus->retrieve($this->leadaccount_id);
            if ( !empty($leadAccountFocus->account_id) )
                $return_array['where'] .= " OR (parent_type = 'Accounts' AND parent_id = '{$leadAccountFocus->account_id}')";
        }
        $return_array['where'] .= ")";
        
        return $return_array;
    }
    
    /**
     * Returns the lead contacts subpanel query parts; ready to be consumed by a subpaneldef
     */
    public function getLeadContactsQuery()
    {
        $return_array['select'] = 'SELECT leadcontacts.id ';
        $return_array['from']   = 'FROM leadcontacts ';
        $return_array['where']  = " WHERE leadaccount_id = '{$this->leadaccount_id}' AND leadcontacts.id != '{$this->id}' ";
        $return_array['join'] = '';
        $return_array['join_tables'][0] = '';
        
        return $return_array;
    }
    
    /**
     * Returns the touchpoints query parts; ready to be consumed by a subpaneldef
     */
    public function getTouchpointsQuery()
    {
        $return_array['select'] = 'SELECT touchpoints.id ';
        $return_array['from']   = ' FROM touchpoints ';
        $return_array['where']  = "";
        $return_array['join'] = " JOIN interactions on interactions.source_id = touchpoints.id 
                                    AND interactions.source_module = 'Touchpoints'
                                    AND ( (interactions.parent_type = '{$this->module_dir}' 
                                        AND interactions.parent_id = '{$this->id}') ";
        $return_array['join_tables'][0] = '';
        
        if ( !empty($this->leadaccount_id) ) {
            $return_array['join'] .= " OR (parent_type = 'LeadAccounts' AND parent_id = '{$this->leadaccount_id}')";
            $leadAccountFocus = new LeadAccount;
            $leadAccountFocus->retrieve($this->leadaccount_id);
            if ( !empty($leadAccountFocus->account_id) )
                $return_array['join'] .= " OR (parent_type = 'Accounts' AND parent_id = '{$leadAccountFocus->account_id}')";
        }
        $return_array['join'] .= ")";
        
        return $return_array;
    }

	function set_notification_body($xtpl, $leadcontact)
	{
		global $app_list_strings;

		$xtpl->assign("LEAD_NAME", $leadcontact->first_name . " " . $leadcontact->last_name);
		$xtpl->assign("LEAD_STATUS",
			      (isset($leadcontact->status)
			       && isset($app_list_strings['leadcontact_status_dom'][$leadcontact->status])
			       ? $app_list_strings['leadcontact_status_dom'][$leadcontact->status]
			       : ""));
		$xtpl->assign("LEAD_PHONE_WORK", $leadcontact->phone_work);
		$xtpl->assign("LEAD_EMAIL1", $leadcontact->email1);
		$xtpl->assign("LEAD_ACCOUNT_NAME", $leadcontact->leadaccount_name);
		$xtpl->assign("LEAD_CAMPAIGN_NAME", $leadcontact->campaign_name);
		$xtpl->assign("LEAD_DESCRIPTION", $leadcontact->description);

		return $xtpl;
	}
}

?>
