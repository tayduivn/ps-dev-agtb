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

 * Description: Controller for the Import module
 * Portions created by SugarCRM are Copyright (C) SugarCRM, Inc.
 * All Rights Reserved.
 ********************************************************************************/
require_once("modules/Accounts/AccountFormBase.php");
 
class LeadAccountsViewConvertleadsave extends SugarView 
{	
    /**
     * Constructor
     */
 	public function __construct()
    {
 		parent::SugarView();
    }
    
 	/** 
     * @see SugarView::display()
     */
    public function display()
    {
        global $mod_strings;
        
        // parse the request vars into subarrays that will be passed to the various bean methods
        $formdata = array('account'=>array(),'opportunity'=>array());
        foreach ( $_REQUEST as $key => $value ) {
            if ( preg_match('/^AccountNotes/',$key) )
                $formdata['account']['note'][str_replace('AccountNotes','',$key)] = $value;
            if ( preg_match('/^OpportunityNotes/',$key) )
                $formdata['opportunity']['note'][str_replace('OpportunityNotes','',$key)] = $value;
            if ( preg_match('/^Accounts/',$key) )
                $formdata['account'][str_replace('Accounts','',$key)] = $value;
            if ( preg_match('/^Opportunities/',$key) )
                $formdata['opportunity'][str_replace('Opportunities','',$key)] = $value;
            if ( preg_match('/^Appointments/',$key) ) {
                if ( isset($_REQUEST['Appointmentsparent_id']) && $_REQUEST['Appointmentsparent_id'] != 'account' ) {
                    $formdata['contact']['appointment'][str_replace('Appointments','',$key)] = $value;
                }
                else
                    $formdata['account']['appointment'][str_replace('Appointments','',$key)] = $value;
            }
        }
        
        // Handle populating the list of lead contacts to convert with any lead contact we selected to be related to
        // a new opportunity or appointment
        if ( !isset($_REQUEST['mass']) )
            $_REQUEST['mass'] = array();
        // push lead person's assignee, team, and address onto the newly created items by default
        elseif ( is_array($_REQUEST['mass']) ) {
            $leadContactFocus = new LeadContact;
            $leadContactFocus->retrieve($_REQUEST['mass'][0]);
            if ( !empty($leadContactFocus->id) ) {
                $formdata['account']['note']['assigned_user_id'] = $leadContactFocus->assigned_user_id;
                $formdata['account']['note']['team_id'] = $leadContactFocus->team_id;
                $formdata['opportunity']['note']['assigned_user_id'] = $leadContactFocus->assigned_user_id;
                $formdata['opportunity']['note']['team_id'] = $leadContactFocus->team_id;
                $formdata['account']['assigned_user_id'] = $leadContactFocus->assigned_user_id;
                $formdata['account']['team_id'] = $leadContactFocus->team_id;
                $formdata['opportunity']['assigned_user_id'] = $leadContactFocus->assigned_user_id;
                $formdata['opportunity']['team_id'] = $leadContactFocus->team_id;
                $formdata['opportunity']['next_step'] = $leadContactFocus->next_step_c;
                $formdata['opportunity']['next_step_due_date'] = $leadContactFocus->next_step_due_date_c;
                $formdata['opportunity']['partner_assigned_to_c'] = $leadContactFocus->partner_assigned_to_c;
                $formdata['account']['appointment']['assigned_user_id'] = $leadContactFocus->assigned_user_id;
                $formdata['account']['appointment']['team_id'] = $leadContactFocus->team_id;
            }
        }
        if ( isset($_REQUEST['Appointmentsparent_id']) 
                && $_REQUEST['Appointmentsparent_id'] != 'account' 
                && !in_array($_REQUEST['Appointmentsparent_id'],(array) $_REQUEST['mass']) )
            $_REQUEST['mass'][] = $_REQUEST['Appointmentsparent_id'];
        if ( isset($_REQUEST['Opportunitiescontact_id']) 
                && $_REQUEST['Opportunitiescontact_id'] != 'account' 
                && !in_array($_REQUEST['Opportunitiescontact_id'],(array) $_REQUEST['mass']) )
            $_REQUEST['mass'][] = $_REQUEST['Opportunitiescontact_id'];
        
        // first convert the lead account into an account if we need to
        $leadAccountFocus = new LeadAccount;
        $leadAccountFocus->retrieve($_REQUEST['record']);
        if ( !empty($leadAccountFocus->id) ) {
            $existingAccount = false;
            $createdAccountObjects = array();
            // Associate with an existing account
            if ( isset($_REQUEST['create_account']) && $_REQUEST['create_account'] == 'no' && isset($_REQUEST['account_id']) ) {
                $leadAccountFocus->account_id = $_REQUEST['account_id'];
                $account_id = $leadAccountFocus->account_id;
                $existingAccount = true;
                clone_history($leadAccountFocus->db, $leadAccountFocus->id, $leadAccountFocus->account_id ,'Accounts');
                // Bug 28749 - creating a emails_beans record for created contact
                $this->cloneEmailRelationship('bean_id', $leadAccountFocus->id, 'LeadAccounts', $leadAccountFocus->account_id, 'Accounts');
            }
            // Create a new account
            elseif ( isset($_REQUEST['create_account']) && $_REQUEST['create_account'] == 'yes' ) {
                // check for duplicate account record
                if (empty($_POST['dup_checked'])) {
                    $duplicateAccounts = AccountFormBase::checkForDuplicates('Accounts');
                    if(isset($duplicateAccounts)){
                        $location='module=Accounts&action=ShowDuplicates';
                        $get = '';
                        // add all of the post fields to redirect get string
                        foreach ( $_POST as $key => $field )
                            $get .= "&$key=".urlencode($field);
                        
                        //create list of suspected duplicate account id's in redirect get string
                        $i=0;
                        foreach ($duplicateAccounts as $account)
                        {
                            $get .= "&duplicate[$i]=".$account['id'];
                            $i++;
                        }
            
                        //now redirect the post to modules/Accounts/ShowDuplicates.php
                        $_SESSION['SHOW_DUPLICATES'] = $get;
                        header("Location: index.php?$location");
                    }
                }
                $account_id = $leadAccountFocus->createNewAccountFrom(
                    $formdata['account']
                    );
                
                // Bug 28749 - creating a emails_beans record for created contact
                $this->cloneEmailRelationship('bean_id', $leadAccountFocus->id, 'LeadAccounts', $account_id, 'Accounts');                    
            }
            // Already converted; just add a new appointment if requested
            elseif ( isset($leadAccountFocus->account_id) ) {
                $account_id = $leadAccountFocus->account_id;
                $existingAccount = true;
            }
            // Add a new opportunity to the account if requested
            $opportunity_id = null;
            if ( isset($_REQUEST['newopportunity']) )
                $opportunity_id = $leadAccountFocus->createNewOpportunityFrom(
                    $formdata['opportunity'],
                    isset($_REQUEST['newoppnote'])
                    );
            // Add any remaining objects
            $createdAccountObjects = $leadAccountFocus->addToConvertedAccount(
                    $formdata['account'],
                    isset($_REQUEST['newaccountnote']),
                    isset($_REQUEST['newmeeting']) && ( $_REQUEST['Appointmentsparent_id'] == 'account' )
                    );
            $leadAccountFocus->markConverted($existingAccount);
            $leadAccountFocus->save(false);
        }
        
        // Next convert the selected lead contacts into contacts
        $contact_ids = array();
        $createdContactObjects = array();
        if ( isset($_REQUEST['mass']) ) {
            foreach ( $_REQUEST['mass'] as $lead_contact_id ) {
                $leadContactFocus = new LeadContact;
                $leadContactFocus->retrieve($lead_contact_id);
                if ( !empty($leadContactFocus->id) ) {
                    $formdata['contact']['account_id'] = $account_id;
                    $contact_ids[] = $leadContactFocus->createNewContactFrom($formdata['contact']);
                    $leadContactFocus->markConverted();
                    $leadContactFocus->save(false);
                    if ( !is_null($opportunity_id) && $formdata['opportunity']['contact_id'] == $lead_contact_id ) {
                        $opp = new Opportunity;
                        $opp->retrieve($opportunity_id);
                        $opp->load_relationship('contacts');
                        $opp->contacts->add($leadContactFocus->contact_id);
                    }
                    if ( $_REQUEST['Appointmentsparent_id'] == $lead_contact_id ) {
                        $createdContactObjects = $leadContactFocus->addToConvertedContact(
                            $formdata['contact'],
                            true
                            );
                    }
                    // Bug 28749 - creating a emails_beans record for created contact
                    clone_relationship($leadContactFocus->db, array('emails_beans'), 'bean_id', $lead_contact_id, $leadContactFocus->contact_id); 
                }
            }
        }
        
        // Now handle displaying the results screen
        echo get_module_title(
            $mod_strings['LBL_MODULE_NAME'], 
            $mod_strings['LBL_MODULE_NAME'] . ': ' . $mod_strings['LBL_CONVERTLEAD'], 
            true
            );
        
        echo "<ul><li>";
        if ( $existingAccount ) 
            echo $mod_strings['LBL_EXISTING_ACCOUNT'];
        else
            echo $mod_strings['LBL_CREATED_ACCOUNT'];
        $account = new Account;
        $account->retrieve($account_id);
		// BEGIN SUGARINTERNAL CUSTOMIZATION - IT REQUEST 8508 - INCLUDE A NOTE AS TO WHO IT'S ASSIGNED TO
        echo " - <a href='index.php?action=DetailView&module=Accounts&record=".$account->id."'>".$account->name."</a>, assigned to {$account->assigned_user_name}</li>";
		// END SUGARINTERNAL CUSTOMIZATION - IT REQUEST 8508 - INCLUDE A NOTE AS TO WHO IT'S ASSIGNED TO
        
        foreach ( $contact_ids as $contact_id ) {
            $contact = new Contact;
            $contact->retrieve($contact_id);
			// BEGIN SUGARINTERNAL CUSTOMIZATION - IT REQUEST 8508 - INCLUDE A NOTE AS TO WHO IT'S ASSIGNED TO
            echo "<li>".$mod_strings['LBL_CREATED_CONTACT']." - <a href='index.php?action=DetailView&module=Contacts&record=".$contact->id."'>".$contact->first_name ." ".$contact->last_name."</a>, assigned to {$contact->assigned_user_name}</li>";
			// END SUGARINTERNAL CUSTOMIZATION - IT REQUEST 8508 - INCLUDE A NOTE AS TO WHO IT'S ASSIGNED TO
        }
        
        if ( !is_null($opportunity_id) ) {
            $opportunity = new Opportunity;
            $opportunity->retrieve($opportunity_id);
			// BEGIN SUGARINTERNAL CUSTOMIZATION - IT REQUEST 8508 - INCLUDE A NOTE AS TO WHO IT'S ASSIGNED TO
            echo "<li>".$mod_strings['LBL_CREATED_OPPORTUNITY']. " - <a href='index.php?action=DetailView&module=Opportunities&record=".$opportunity->id."'>".$opportunity->name."</a>, assigned to {$opportunity->assigned_user_name}</li>";
			// END SUGARINTERNAL CUSTOMIZATION - IT REQUEST 8508 - INCLUDE A NOTE AS TO WHO IT'S ASSIGNED TO
        }
        
        if ( isset($createdAccountObjects['Call']) || isset($createdAccountObjects['Meeting']) ) {
            echo "<li>";
            if ( isset($createdAccountObjects['Call']) ) {
                $call = new Call;
                $call->retrieve($createdAccountObjects['Call']);
				// BEGIN SUGARINTERNAL CUSTOMIZATION - IT REQUEST 8508 - INCLUDE A NOTE AS TO WHO IT'S ASSIGNED TO
                echo $mod_strings['LBL_CREATED_CALL']. " - <a href='index.php?action=DetailView&module=Calls&record=".$call->id."'>".$call->name."</a>, assigned to {$call->assigned_user_name}";
				// END SUGARINTERNAL CUSTOMIZATION - IT REQUEST 8508 - INCLUDE A NOTE AS TO WHO IT'S ASSIGNED TO
            }
            else {
                $meeting = new Meeting;
                $meeting->retrieve($createdAccountObjects['Meeting']);
				// BEGIN SUGARINTERNAL CUSTOMIZATION - IT REQUEST 8508 - INCLUDE A NOTE AS TO WHO IT'S ASSIGNED TO
                echo $mod_strings['LBL_CREATED_MEETING']. " - <a href='index.php?action=DetailView&module=Meetings&record=".$meeting->id."'>".$meeting->name."</a>, assigned to {$meeting->assigned_user_name}";
				// END SUGARINTERNAL CUSTOMIZATION - IT REQUEST 8508 - INCLUDE A NOTE AS TO WHO IT'S ASSIGNED TO
            }
            echo "</li>";
        }
        
        if ( isset($createdContactObjects['Call']) || isset($createdContactObjects['Meeting']) ) {
            echo "<li>";
            if ( isset($createdContactObjects['Call']) ) {
                $call = new Call;
                $call->retrieve($createdContactObjects['Call']);
				// BEGIN SUGARINTERNAL CUSTOMIZATION - IT REQUEST 8508 - INCLUDE A NOTE AS TO WHO IT'S ASSIGNED TO
                echo $mod_strings['LBL_CREATED_CALL']. " - <a href='index.php?action=DetailView&module=Calls&record=".$call->id."'>".$call->name."</a>, assigned to {$call->assigned_user_name}";
				// END SUGARINTERNAL CUSTOMIZATION - IT REQUEST 8508 - INCLUDE A NOTE AS TO WHO IT'S ASSIGNED TO
            }
            else {
                $meeting = new Meeting;
                $meeting->retrieve($createdContactObjects['Meeting']);
				// BEGIN SUGARINTERNAL CUSTOMIZATION - IT REQUEST 8508 - INCLUDE A NOTE AS TO WHO IT'S ASSIGNED TO
                echo $mod_strings['LBL_CREATED_MEETING']. " - <a href='index.php?action=DetailView&module=Meetings&record=".$meeting->id."'>".$meeting->name."</a>, assigned to {$meeting->assigned_user_name}";
				// END SUGARINTERNAL CUSTOMIZATION - IT REQUEST 8508 - INCLUDE A NOTE AS TO WHO IT'S ASSIGNED TO
            }
            echo "</li>";
        }
        
        echo "</ul>";
        echo "<a href='index.php?module=Leads&action=index'>{$mod_strings['LBL_BACKTOLEADS']}</a>";
    }

	// helper function to convert emails from LeadContacts and LeadAccounts to Contacts and Accounts, respectively    
    private function cloneEmailRelationship($from_column, $from_id, $from_module, $to_id, $to_module){
    	$query = "SELECT * FROM emails_beans WHERE $from_column='$from_id' AND bean_module='$from_module'";

    	$results = $GLOBALS['db']->query($query);
    	while($row = $GLOBALS['db']->fetchByAssoc($results)){
    		$insert_query = "INSERT INTO emails_beans ";
    		$names = '';
    		$values = '';
    		$row[$from_column] = $to_id;
			$row['id'] = create_guid();
			$row['bean_module'] = $to_module;
		
			foreach($row as $name=>$value){
				if(empty($names)){
					$names .= $name;
					$values .= "'$value'";
				} 
				else{
					$names .= ', '. $name;
					$values .= ", '$value'";
				}
			}
			$insert_query .= "($names) VALUES ($values)";
			$GLOBALS['db']->query($insert_query);
    	}
    } 
}
?>
