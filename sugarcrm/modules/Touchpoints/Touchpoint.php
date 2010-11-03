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

require_once('include/SugarObjects/templates/basic/Basic.php');
require_once('modules/Touchpoints/Interactions/TouchpointsInteraction.php');
require_once('modules/Campaigns/Campaign.php');
require_once('modules/LeadAccounts/LeadAccount.php');
require_once('modules/LeadContacts/LeadContact.php');

class Touchpoint extends Basic 
{
    /**
     * table fields 
     */
    public $id;
    public $first_name;
    public $last_name;
    public $full_name;
    public $primary_address_country;
    public $company_name;
    public $title;
    public $raw_data;
    public $source_type;
    public $score;
    public $scrubbed;
    public $scrub_result;
    public $scrub_relation_type;
    public $interactions_interactions_cstm_id;
    public $created_by;
    public $modified_user_id;
    public $discrepancies;
    public $new_leadaccount_id;
    public $new_leadaccount_name;
    public $new_leadcontact_id;
    public $new_leadcontact_name;
    
    /**
     * properties 
     */
    public $table_name = "touchpoints";
    public $object_name = "Touchpoint";
    public $object_names = "Touchpoints";
    public $module_dir = "Touchpoints";
    public $importable = true;
    public $new_schema = true;
    
    /**
     * $raw_data parsed into an array
     */
    public $raw_data_array = array();
    
    /**
     * Constructor
     */
	public function __construct() 
    {
    	global $sugar_config, $app_list_strings;
		parent::SugarBean();
        
        $tmpClass = new TouchpointsInteraction();
        $tmpClass->installHook(
            'modules/Touchpoints/Interactions/TouchpointsInteraction.php',
            'TouchpointsInteraction'
            );
        
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
     * @see SugarBean::populateFromRow()
     */
    public function populateFromRow($row)
	{
        parent::populateFromRow($row);
        $this->_parseRawData();
        $this->_create_proper_name_field();
    }
    
    /**
     * Parses the $raw_data into an array
     */
    private function _parseRawData()
    {
        $json = getJSONobj();
        $this->raw_data_array = $json->decode(from_html($this->raw_data));
    }
    
    /**
	 * Generate the name field from the first_name and last_name fields.
	 */
	private function _create_proper_name_field() 
    {
		global $locale;

		if($this->bean_implements('ACL') && !ACLField::hasAccess('first_name', $this->module_dir, $GLOBALS['current_user']->id, $this->isOwner($GLOBALS['current_user']->id))){
			$full_name = $this->last_name;
		}
        else {
			$full_name = $locale->getLocaleFormattedName($this->first_name, $this->last_name, '', $this->title);
		}

		$this->full_name = $full_name;
	}

    /**
     * @see SugarBean::save()
     */
    public function save(
        $check_notify = false, 
        $scrub = true
        )
    {
    	$this->_create_proper_name_field();
		
		// IT REQUEST 12214 - Check to update touchpoint with prospect activities
		$unscrubbed_before = false;
		if($this->scrubbed == 0){
			$unscrubbed_before = true;
		}
		
		// IT REQUEST 8535 - DISABLE NOTIFICATIONS FOR LEAD PERSONS, LEAD COMPANIES, AND TOUCHPOINTS
    	//$tp_id = parent::save($check_notify);

        /*
        ** @author: jwhitcraft
        ** SUGARINTERNAL CUSTOMIZATION
        ** ITRequest 15142
        ** Description: Default the trial expiration custom field so it doesn't throw a notice on import if it's empty
        */
        if(!isset($this->trial_expiration_c)) {
            $this->trial_expiration_c = ""; 
        }
        // END SUGARINTERNAL CUSTOMIZATION

		// BEGIN jostrow customization
		// See ITRequest #13803
		// And these notes from another part of this file (same issue occuring):
        // scrub() wasn't playing nice with date fields when users are configured with a non-standard date format
        // the date would always end up as 2000-01-01; I suspect this is because multiple saves are occuring on the same page
        // to fix this, we're converting trial_expiration_c to the display date version

		$old_trial_expiration_c = $this->trial_expiration_c;

    	$tp_id = parent::save(false);

		$this->trial_expiration_c = $old_trial_expiration_c;

		// END jostrow customization

		// IT REQUEST 8535 - DISABLE NOTIFICATIONS FOR LEAD PERSONS, LEAD COMPANIES, AND TOUCHPOINTS
    	if($scrub){
    		//call scrubbing function
    		$this->scrub($tp_id);
    	}
		
		// IT REQUEST 12214 - Check to update touchpoint with prospect activities
		/*if($unscrubbed_before && $this->scrubbed != 0){
			require_once('scripts/pardot/PardotHelper.php');
			PardotHelper::updateProspectActivities($this->id);
		}*/
		
		return $tp_id;
    }
    
    /**
     * @see SugarBean::fill_in_additional_detail_fields()
     */
	public function fill_in_additional_detail_fields()
	{
        if ( !isset($this->decision_date_c) || is_null($this->decision_date_c) )
			$this->decision_date_c = $GLOBALS['timedate']->to_display_date(gmdate($GLOBALS['timedate']->get_db_date_time_format()));
        
        $leadAccountFocus = new LeadAccount;
        $leadAccountFocus->retrieve($this->new_leadaccount_id);
        if ( !empty($leadAccountFocus->id) )
            $this->new_leadaccount_name = $leadAccountFocus->name;
        
        $leadContactFocus = new LeadContact;
        $leadContactFocus->retrieve($this->new_leadcontact_id);
        if ( !empty($leadContactFocus->id) )
            $this->new_leadcontact_name = $leadContactFocus->name;
    }
    
    /**
     * @see SugarBean::get_list_view_data()
     */
    public function get_list_view_data() 
    {
        global $app_list_strings;
        
        $the_array = parent::get_list_view_data();
        
        $the_array['SCRUBBED_STYLE'] = 
            ( $the_array['SCRUB_RESULT'] == 'conflict'
                ? 'style="color: red; font-weight: bold;"'
                : '' );
        
        return $the_array;
    }
    
    /**
     * Handles the touchpoint scrubbing
     *
     * @param string $id record to scrub
     */
    public function scrub(
        $id = ''
        )
    {
    	if(empty($this->id) && empty($id)){
	    	//do not scrub, the toucpoint should be saved first.
	    	return false;
    	}

    	if(empty($id)){
	    	//get id from touchpoint
	    	$id = $this->id;
    	}
    	
		$scrub_results = array();
        if ( $this->scrubbed != 1 ) {
            //call scrub helper for auto scrub  
            require_once('modules/Touchpoints/ScrubHelper.php');
            $sh = new ScrubHelper();
            $scrub_results = $sh->autoScrub($id);
    
            //set touchpoint fields from scrub result
            $this->scrub_result = $scrub_results['scrubResultAction'];
            $this->scrub_relation_type = $scrub_results['RelationBean'];
            $this->new_leadcontact_id = $scrub_results['newLeadContactId'];
            $this->new_leadaccount_id = $scrub_results['newLeadAccountId'];
                    
            //mark as scrubbed if result is not conflict or none
            if(!empty($this->scrub_result) && $this->scrub_result !='conflict' && $this->scrub_result != 'no_match') 
            { 
                $this->scrubbed = 1;
            } elseif ($this->scrub_result =='conflict') {
                $this->scrubbed=2;  //conflicts.
            } else {	
                $this->scrubbed = 0;
            }
        }

		// For scoring
		// Only score unmatched elements, no need to waste CPU on items that were scored automatically
		if ( true || $this->scrubbed == 1 ) {
			require_once('modules/Score/Score.php');
			// No parents because the touchpoint did not pass the scoring, and has no relations
			Score::scoreBean($this);
		}
		// End For scoring
    	
		
		//set description
		if (!empty($scrub_results['scrubResultAction'])){
			$this->description .="\n".date('Y-m-d H:i:s')." -- action result was ".$scrub_results['scrubResultAction'];

			if(!empty($scrub_results['LeadContact_id'])){
				$this->description .=", with leadcontact= ".$scrub_results['LeadContact_id'];	
			}

			if(!empty($scrub_results['LeadAccount_id'])){
				$this->description .=", with leadaccount= ".$scrub_results['LeadAccount_id'];	
			}
		}

        // BEGIN jostrow customization
        // scrub() wasn't playing nice with date fields when users are configured with a non-standard date format
        // the date would always end up as 2000-01-01; I suspect this is because multiple saves are occuring on the same page
        // to fix this, we're converting trial_expiration_c to the display date version

        $td = new TimeDate;
		if(isset($this->trial_expiration_c)){
			$this->trial_expiration_c = $td->to_display_date($this->trial_expiration_c, false);
		}
        // END jostrow customization

		//save again, with scrub flag set to false, otherwise will fall into infinite loop
		$this->save(false, false);
		
		return $scrub_results;
    }
    
    /**
     * Returns the id of the highest record to be scrubbed
     */
    public function getHighestRecordToScrub()
    {
        $id = $this->db->getOne("select id from touchpoints 
                                    where assigned_user_id in ( 
                                            select id from users 
                                                where user_name = 'Leads_HotMktg' )
                                        and scrubbed = 0
                                    order by score desc");
        if ( $id ) {
            $this->retrieve($id);
            return $id;
        }
        
        return false;
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
     * @see SugarBean::get_summary_text()
     */
    public function get_summary_text()
	{
        global $app_list_strings;
        
        $this->_create_proper_name_field();
        $returnVal = trim($this->full_name);
        if ( isset($this->campaign_id) && isset($app_list_strings['campaign_list'][$this->campaign_id]) )
            $returnVal .= '_Campaign:' . trim($app_list_strings['campaign_list'][$this->campaign_id]);
        
        return $returnVal;
	}

	public function populate_touchpoint_from_array($copy_vals=''){
		
		//if no array was passed in, use request object
		if(empty($copy_vals) || !is_array($copy_vals)){
			$copy_vals = $_REQUEST;
		}
		//save request information into raw_data for later processing
		$json = new JSON(JSON_LOOSE_TYPE);
		/* BEGIN SUGARINTERNAL CUSTOMIZATION */
		/*
		 * We want to encode the actual raw data that we are using, not
		 * just whatever is in the request
		 */
		$this->raw_data = $json->encode(from_html($copy_vals));
		/* END SUGARINTERNAL CUSTOMIZATION */
		//iterate through copy array and use map to populate touchpoint
		foreach($copy_vals as $k=>$v){
			$k = strtolower($k);
			$this->$k = $v;
		}

		//prepend touchpoint name to description if empty
		if (empty($this->description)){
			$this->description = 'new touchpoint for '.$this->title . ' '.$this->first_name . ' '. $this->last_name ;
		}
		
		//map annual revenue to gross annual sales
		if(isset($copy_vals['annual_revenue2_c'])){
			$this->annual_revenue = $copy_vals['annual_revenue2_c']; 	
		} 
		
		
		if(isset($copy_vals['purchasing_timeline_c'])){
			$this->replace_timeline = $copy_vals['purchasing_timeline_c']; 	
		} 		
		
		//map annual campaign_name to campaign_id, as it is an id field
		if(isset($copy_vals['campaign_name']) && empty($this->campaign_id)){
					$this->campaign_id = $copy_vals['campaign_name']; 	
		}		

		//map annual account_name to company_name
		if(isset($copy_vals['account_name']) && empty($this->company_name)){
					$this->company_name = $copy_vals['account_name']; 	
		}		
		
		//map employee_qty_c to employees
		if(isset($copy_vals['employee_qty_c']) && empty($this->employees)){
					$this->employees = $copy_vals['employee_qty_c']; 	
		}		
		
		return ;
	}
}
