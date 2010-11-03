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
require_once('include/SugarObjects/templates/company/Company.php');
require_once('modules/Opportunities/Opportunity.php');
require_once('modules/Accounts/Account.php');
require_once('modules/LeadContacts/LeadContact.php');
require_once('modules/Notes/Note.php');
require_once('modules/Meetings/Meeting.php');
require_once('modules/Calls/Call.php');
require_once('modules/Emails/Email.php');

// Lead is used to store profile information for people who may become customers.
class LeadAccount extends Company 
{
    // table field names
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
	public $leadaccount_type;
	public $industry;
	public $annual_revenue;
	public $phone_fax;
	public $billing_address_street;
	public $billing_address_street_2;
	public $billing_address_street_3;
	public $billing_address_street_4;
	public $billing_address_city;
	public $billing_address_state;
	public $billing_address_postalcode;
	public $billing_address_country;
	public $rating;
	public $phone_office;
	public $phone_alternate;
	public $website;
	public $ownership;
	public $employees;
	public $ticker_symbol;
	public $shipping_address_street;
	public $shipping_address_street_2;
	public $shipping_address_street_3;
	public $shipping_address_street_4;
	public $shipping_address_city;
	public $shipping_address_state;
	public $shipping_address_postalcode;
	public $shipping_address_country;
	public $email1;
	public $emails;
	public $converted;
	public $referred_by;
	public $lead_source;
	public $lead_source_description;
	public $status;
	public $account_id;
	public $opportunity_id;
	public $portal_name;
	public $portal_app;
	public $last_interaction_date;
	public $conversion_date;
	public $score;
	public $leadcontact_id;
	public $leadcontact_name;
	public $leadcontact_title;
	public $leadcontact_department;
	public $leadcontacts;
	public $account_type;

    // object properties
    public $table_name = "leadaccounts";
    public $object_name = "LeadAccount";
    public $object_names = "LeadAccounts";
    public $module_dir = "LeadAccounts";
    public $importable = true;
    public $new_schema = true;

    // This is used to retrieve related fields from form posts.
    var $additional_column_fields = Array('assigned_user_name');
    
    /**
     * Constructor
     */
	public function __construct() 
    {
        parent::Company();
        
        global $current_user;
        global $app_list_strings;


        if (!empty($current_user)) {
        	$this->team_id = $current_user->default_team;
        }
        else {
        	$this->team_id = 1;
        }

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
     * Marks the current lead account as converted
     */
    public function markConverted(
        $existingAccount = false
        )
    {
        // don't do this twice
        if ( $this->converted == '1' )
            return;
        
        $this->conversion_date = gmdate($GLOBALS['timedate']->get_db_date_time_format());
        $this->converted = '1';
        if ( $existingAccount )
            $this->status = 'Converted - Existing';
        else
            $this->status = 'Converted';
    }
    
    /**
     * Creates a new Account from the current Lead Account
     *
     * @param  $requestVars array changes from the current Lead Account record to be saved in the new Account record
     * @return string id of the newly created Account
     */
    public function createNewAccountFrom(
        array $requestVars = array()
        )
    {
        $accountFocus = new Account;
        $accountFocus->populateFromRow($this->toArray());
        $accountFocus->id = null;
        foreach ( $requestVars as $key => $value )
            if ( !is_array($value) )
                $accountFocus->$key = $value;
        if ( !isset($accountFocus->assigned_user_id) || $accountFocus->assigned_user_id == '' )
            $accountFocus->assigned_user_id = $this->assigned_user_id;
        // BEGIN SUGARCRM PRO ONLY
        if ( !isset($accountFocus->team_id) || $accountFocus->team_id == '' )
            $accountFocus->team_id = $this->team_id;
        // END SUGARCRM PRO ONLY
        $accountFocus->save(false);
        
        $this->account_id = $accountFocus->id;
        
        clone_record(
            array('email_addr_bean_rel'), 
            array(
                'bean_id' => $this->id,
                'bean_module' => 'LeadAccounts',
                'primary_address' => '0',
                ),
            array(
                'bean_id' => $this->account_id,
                'bean_module' => 'Accounts',
                )
            );
        
        // Add any remaining related calls, meetings, emails
        $accountFocus->load_relationship('calls');
        $this->load_relationship('calls');
        $callBeans = $this->calls->getBeans(new Call);
        foreach ( $callBeans as $callBean )
            $accountFocus->calls->add($callBean->id);
        
        $accountFocus->load_relationship('meetings');
        $this->load_relationship('meetings');
        $meetingsBeans = $this->meetings->getBeans(new Meeting);
        foreach ( $meetingsBeans as $meetingsBean )
            $accountFocus->meetings->add($meetingsBean->id);
        
        $accountFocus->load_relationship('emails');
        $this->load_relationship('emails');
        $emailsBeans = $this->emails->getBeans(new Email);
        foreach ( $emailsBeans as $emailsBean )
            $accountFocus->emails->add($emailsBean->id);
        
        // clone the entire history
        clone_history($this->db, $this->id, $this->account_id ,'Accounts');
        
        return $accountFocus->id;
    }
    
    /**
     * Handles adding an additional note and/or appointment during lead conversion
     *
     * @param  $requestVars array changes from the current Lead Account record to be saved in the new Account record
     * @param  $createNote boolean true if a new note related to the newly created account should be created
     * @param  $createAppointment boolean true if a new appointment related to the newly created account should be created
     * @return array of bean types and id created
     */
    public function addToConvertedAccount(
        array $requestVars = array(),
        $createNote = false,
        $createAppointment = false
        )
    {
        if ( empty($this->account_id) )
            return;
        
        global $timedate;
        
        $returnArray = array();
        
        // add a new note, if requested to do so
        if ( $createNote ) {
            $noteFocus = new Note;
            foreach ( $requestVars['note'] as $key => $value )
                if ( !is_array($value) )
                    $noteFocus->$key = $value;
            if ( !isset($noteFocus->assigned_user_id) || $noteFocus->assigned_user_id == '' )
                $noteFocus->assigned_user_id = $this->assigned_user_id;
            // BEGIN SUGARCRM PRO ONLY
            if ( !isset($noteFocus->team_id) || $noteFocus->team_id == '' )
                $noteFocus->team_id = $this->team_id;
            // END SUGARCRM PRO ONLY
            $noteFocus->parent_type = 'Accounts';
            $noteFocus->parent_id = $this->account_id;
            $noteFocus->save(false);
            $returnArray['Note'] = $noteFocus->id;
        }
        
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
            $appointmentFocus->parent_type = 'Accounts';
            $appointmentFocus->parent_id = $this->account_id;
            $appointmentFocus->save(false);
            $returnArray[$appointmentFocus->object_name] = $appointmentFocus->id;
            
            if ( !empty($this->opportunity_id) ) { 
                $appointmentFocus->load_relationship('opportunity');
                $appointmentFocus->opportunity->add($this->opportunity_id);
            }
        }
        
        return $returnArray;
    }
    
    /**
     * Creates a new Opportunity from the current Lead Account
     *
     * @param  $requestVars array fields to be saved in the new Opportunity record
     * @param  $createNote boolean true if a new appointment related to the newly created account should be created
     * @return string id of the newly created Account
     */
    public function createNewOpportunityFrom(
        array $requestVars = array(),
        $createNote = false
        )
    {
        $opportunityFocus = new Opportunity;
        foreach ( $requestVars as $key => $value )
            if ( !is_array($value) )
                $opportunityFocus->$key = $value;
        if ( !isset($opportunityFocus->assigned_user_id) || $opportunityFocus->assigned_user_id == '' )
            $opportunityFocus->assigned_user_id = $this->assigned_user_id;
        // BEGIN SUGARCRM PRO ONLY
        if ( !isset($opportunityFocus->team_id) || $opportunityFocus->team_id == '' )
            $opportunityFocus->team_id = $this->team_id;
        // END SUGARCRM PRO ONLY
        $opportunityFocus->account_id = $this->account_id;
        $opportunityFocus->save(false);
        
        // add a new note, if requested to do so
        if ( isset($opportunityFocus->id)  && $createNote) {
            $noteFocus = new Note;
            foreach ( $requestVars['note'] as $key => $value )
                if ( !is_array($value) )
                    $noteFocus->$key = $value;
            if ( !isset($noteFocus->assigned_user_id) || $noteFocus->assigned_user_id == '' )
                $noteFocus->assigned_user_id = $this->assigned_user_id;
            // BEGIN SUGARCRM PRO ONLY
            if ( !isset($noteFocus->team_id) || $noteFocus->team_id == '' )
                $noteFocus->team_id = $this->team_id;
            // END SUGARCRM PRO ONLY
            $noteFocus->parent_type = 'Opportunities';
            $noteFocus->parent_id = $opportunityFocus->id;
            $noteFocus->save(false);
        }
        
        $this->opportunity_id = $opportunityFocus->id;
        
        return $opportunityFocus->id;
    }
    
	/**
     * @see SugarBean::bean_implements()
     */
    public function bean_implements(
        $interface
        )
    {
        switch($interface){
        	case 'ACL':return true;
        }
        return false;
	}
    
    /**
     * @see SugarBean::listviewACLHelper()
     */
	public function listviewACLHelper()
    {
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
    
    /**
     * @see SugarBean::save()
     */
	public function save(
        $check_notify = false
        ) 
    {
        if(empty($this->status)) 
            $this->status = "New";

        // Handle scoring
		require_once('modules/Score/Score.php');
		// No parent, there's noplace for the score to roll up to
		Score::scoreBean($this);
		// Finished with scoring        
		// IT REQUEST 8535 - DISABLE NOTIFICATIONS FOR LEAD PERSONS, LEAD COMPANIES, AND TOUCHPOINTS
        //$returnval = parent::save($check_notify);
        $returnval = parent::save(false);
		// IT REQUEST 8535 - DISABLE NOTIFICATIONS FOR LEAD PERSONS, LEAD COMPANIES, AND TOUCHPOINTS
        
        // When importing, handle building a contact if we specify one
        if ( !empty($this->leadcontact_id) ) {
            $leadContactFocus = new LeadContact;
            if ( !is_null($leadContactFocus->retrieve($this->leadcontact_id)) ) {
                $leadContactFocus->leadaccount_id = $this->id;
                if ( isset($this->leadcontact_title) )
                    $leadContactFocus->title = $this->leadcontact_title;
                if ( isset($this->leadcontact_department) )
                    $leadContactFocus->department = $this->leadcontact_department;
                $leadContactFocus->save();
            }
        }
        if ( !empty($this->leadcontact_name) ) {
            $leadContactFocus = new LeadContact;
            $leadContactNameParts = explode(' ',$this->leadcontact_name);
            $first_name = array_shift($leadContactNameParts);
            $last_name = array_shift($leadContactNameParts);
            if ( is_null($leadContactFocus->retrieve_by_string_fields(
                    array(
                        'first_name' => $first_name,
                        'last_name' => $last_name,
                        'leadaccount_id' => $this->id,
                    ))) ) {
                $leadContactFocus->first_name = $first_name;
                $leadContactFocus->last_name = $last_name;
                $leadContactFocus->leadaccount_id = $this->id;
                if ( isset($this->leadcontact_title) )
                    $leadContactFocus->title = $this->leadcontact_title;
                if ( isset($this->leadcontact_department) )
                    $leadContactFocus->department = $this->leadcontact_department;
                $leadContactFocus->save();
            }
        }
        
        return $returnval;
    }
    
    /**
     * @see SugarBean::fill_in_additional_detail_fields()
     */
	public function fill_in_additional_detail_fields()
	{
        $account = new Account;
        $account->retrieve($this->account_id);
        if ( !empty($account->name) )
            $this->account_name = $account->name;
        
        $opportunity = new Opportunity;
        $opportunity->retrieve($this->opportunity_id);
        if ( !empty($opportunity->name) )
            $this->opportunity_name = $opportunity->name;
        
        if ( !isset($this->decision_date_c) || is_null($this->decision_date_c) )
			$this->decision_date_c = $GLOBALS['timedate']->to_display_date(gmdate($GLOBALS['timedate']->get_db_date_time_format()));
    }
    
    function get_unlinked_email_query($type=array()) {
		require_once('include/utils.php');
		return get_unlinked_email_query($type, $this);
	}
    
    /**
     * Returns the interactions query parts; ready to be consumed by a subpaneldef
     */
    public function getInteractionsQuery()
    {
        $return_array['select'] = 'SELECT interactions.id ';
        $return_array['from']   = 'FROM interactions ';
        $return_array['where']  = " WHERE ( (parent_type = '{$this->module_dir}' AND parent_id = '{$this->id}')";
        $return_array['join'] = "";
        $return_array['join_tables'][0] = '';
        
        $this->load_relationship('leadcontacts');
        foreach ( $this->build_related_list($this->leadcontacts->getQuery(), new LeadContact) as $leadcontact) {
            $return_array['where'] .= " OR (parent_type = 'LeadContacts' AND parent_id = '{$leadcontact->id}')";
            if ( !empty($leadcontact->contact_id) )
                $return_array['where'] .= " OR (parent_type = 'Contacts' AND parent_id = '{$leadcontact->contact_id}')";
        }
        
        if ( !empty($this->account_id) )
            $return_array['where'] .= " OR (parent_type = 'Accounts' AND parent_id = '{$this->account_id}')";

        $return_array['where'] .= ")";
        
        return $return_array;
    }
    
    /**
     * Returns the touchpoints query parts; ready to be consumed by a subpaneldef
     */
    public function getTouchpointsQuery()
    {
        $return_array['select'] = 'SELECT touchpoints.id ';
        $return_array['from']   = 'FROM touchpoints ';
        $return_array['where']  = "";
        $return_array['join'] = " JOIN interactions on interactions.source_id = touchpoints.id 
                                    AND interactions.source_module = 'Touchpoints'
                                    AND ( (interactions.parent_type = '{$this->module_dir}' 
                                        AND interactions.parent_id = '{$this->id}') ";
        $return_array['join_tables'][0] = '';
        
        $this->load_relationship('leadcontacts');
        foreach ( $this->build_related_list($this->leadcontacts->getQuery(), new LeadContact) as $leadcontact) {
            $return_array['join'] .= " OR (parent_type = 'LeadContacts' AND parent_id = '{$leadcontact->id}')";
            if ( !empty($leadcontact->contact_id) )
                $return_array['join'] .= " OR (parent_type = 'Contacts' AND parent_id = '{$leadcontact->contact_id}')";
        }
        
        if ( !empty($this->account_id) )
            $return_array['join'] .= " OR (parent_type = 'Accounts' AND parent_id = '{$this->account_id}')";

        $return_array['join'] .= ")";
        
        return $return_array;
    }
	
	function set_notification_body($xtpl, $leadaccount)
	{
		global $app_list_strings;

		$xtpl->assign("LEAD_NAME", $leadaccount->name);
		$xtpl->assign("LEAD_CAMPAIGN_NAME", $leadaccount->campaign_name);
		$xtpl->assign("LEAD_DESCRIPTION", $leadaccount->description);

		return $xtpl;
	}
}

?>
