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

********************************************************************************/
/*********************************************************************************

 * Description: view handler for last step of the import process
 * Portions created by SugarCRM are Copyright (C) SugarCRM, Inc.
 * All Rights Reserved.
 ********************************************************************************/
require_once('include/MVC/View/SugarView.php');
require_once('modules/Opportunities/Opportunity.php');
require_once('modules/Accounts/Account.php');
require_once('modules/Contacts/Contact.php');
require_once('modules/Tasks/Task.php');
require_once('modules/LeadContacts/LeadContact.php');
                
class OpportunitiesViewOpportunitywizard extends SugarView 
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
		if(empty($_REQUEST['record'])){
			sugar_die('No record ID passed.');
		}
		
        global $mod_strings, $app_strings, $app_list_strings, $timedate;
        
        $this->ss->assign("MODULE_TITLE", 
                    get_module_title(
                        $mod_strings['LBL_MODULE_NAME'], 
                        $mod_strings['LBL_MODULE_NAME'] . ': Opportunity Wizard', 
                        true
                        )
                    );
        
        $focus = new Opportunity();
        $focus->retrieve($_REQUEST['record']);
		
		if(empty($focus->id)){
			sugar_die("Could not retrieve record with ID passed.");
		}
        
		if(empty($focus->account_id)){
			sugar_die("The Opportunity must have an account selected");
		}
		
		$focusAccount = new Account();
        $focusAccount->retrieve($focus->account_id);
		
        $this->ss->assign('APP', $app_strings);
        $this->ss->assign('MOD', $mod_strings);
        $this->ss->assign('MOD_ACCOUNTS', return_module_language($GLOBALS['current_language'],'Accounts'));
        $accountFieldDefs = $focusAccount->field_defs;
        foreach ($accountFieldDefs as $key => $values ) {
            if(isset($accountFieldDefs[$key]['options']) && isset($app_list_strings[$accountFieldDefs[$key]['options']]))
                $accountFieldDefs[$key]['options'] = $app_list_strings[$accountFieldDefs[$key]['options']];
            if ( isset($focusAccount->$key) )
                $accountFieldDefs[$key]['value'] = $focusAccount->$key;
        }
        
        // handle the opportunites fields
        $this->ss->assign('MOD_OPPORTUNITIES', return_module_language($GLOBALS['current_language'],'Opportunities'));
        $opp_focus = new Opportunity;
        $oppfieldDefs = $opp_focus->field_defs;
        foreach ($oppfieldDefs as $key => $values ) {
			if(isset($oppfieldDefs[$key]['options']) && isset($app_list_strings[$oppfieldDefs[$key]['options']])){
				// BEGIN SUGARINTERNAL CUSTOMIZATION - SADEK - PREVENT NON SALES OPS FROM BEING ABLE TO ACCESS SALES STAGES
				unset($app_list_strings['sales_stage_dom']['Finance Closed']);
				unset($app_list_strings['sales_stage_dom']['Sales Ops Closed']);
				// END SUGARINTERNAL CUSTOMIZATION - SADEK - PREVENT NON SALES OPS FROM BEING ABLE TO ACCESS SALES STAGES
				$oppfieldDefs[$key]['options'] = $app_list_strings[$oppfieldDefs[$key]['options']];
			}
            if ( isset($focus->$key) )
                $oppfieldDefs[$key]['value'] = $focus->$key;
        }
        // begin jwhitcraft customization ITR: 13820 set the default sales stage to Closed Won (98%)
        $oppfieldDefs['sales_stage']['value'] = 'Closed Won';
        // end jwhitcraft customization ITR: 13820
        $this->ss->assign('opp_fields',$oppfieldDefs);
        $json = getJSONobj();
        $this->ss->assign('PROB_ARRAY',$json->encode($app_list_strings['sales_probability_dom']));
        
        // handles fields from other modules
        $this->ss->assign('MOD_NOTES', return_module_language($GLOBALS['current_language'],'Notes'));
        $this->ss->assign('MOD_CALLS', return_module_language($GLOBALS['current_language'],'Calls'));
        
        $mod_strings_contacts = return_module_language($GLOBALS['current_language'],'Contacts');
        $Contact = new Contact();
        
        // get a list of lead contacts attached to this lead account
        $focusAccount->load_relationship('contacts');
        $contacts = array();
        foreach ( $focusAccount->build_related_list($focusAccount->contacts->getQuery(), new Contact) as $contact) {
            $contacts[$contact->id] = array(
		'name' => $contact->name, 
		'portal_name' => $contact->portal_name, 
		'portal_active' => $contact->portal_active,
		'support_authorized_c' => $contact->support_authorized_c,
		'billing_contact_c' => $contact->billing_contact_c,
        'primary_business_c' => $contact->primary_business_c,       // ITR:13820 jwhitcraft - Added Primay Business Contact to the array for usage on the view
		'email1' => $contact->email1,
	    );
        }
	$this->ss->assign('contacts',$contacts);
		
        $this->ss->assign('account_fields',$accountFieldDefs);
        
        $this->ss->assign('CAL_DATEFORMAT',$timedate->get_cal_date_format());
        $this->ss->assign('USER_DATEFORMAT', $timedate->get_user_date_format());
        $this->ss->assign('USER_TIMEFORMAT', $timedate->get_user_time_format());
        $date = gmdate($GLOBALS['timedate']->get_db_date_time_format());
        $this->ss->assign('USER_DATEDEFAULT', $timedate->to_display_date($date));
        $this->ss->assign('USER_TIMEDEFAULT', $timedate->to_display_time($date,true));
		// BEGIN SUGARINTERNAL CUSTOMIZATION - IT REQUEST 7987 - Expected Close Date should be that of the decision date of the Lead Company
        $this->ss->assign('Opportunitiesdate_closed', !empty($focus->decision_date_c) ? $timedate->to_display_date($focus->decision_date_c) : $timedate->to_display_date($date));
		// END SUGARINTERNAL CUSTOMIZATION - IT REQUEST 7987 - Expected Close Date should be that of the decision date of the Lead Company
		
		require_once('include/QuickSearchDefaults.php');
		$qsd = new QuickSearchDefaults();
		echo $qsd->getQSScripts();
		
		$sqs_objects = array(
			'account_name' => $qsd->getQSAccount('account_name', 'account_id'),
		);
		$quicksearch_js = '<script type="text/javascript">sqs_objects = ' . $json->encode($sqs_objects) . '</script>';
		$this->ss->assign('QSJAVASCRIPT', $quicksearch_js);
/*
** @author: Dtam
** SUGARINTERNAL CUSTOMIZATION
** ITRequest #: 14692
** Description: make partner assigned to mandatory for channel sales managers
** Wiki customization page: internalwiki.sjc.sugarcrm.pvt/index.php?title=Closed_won_wizard&action=edit&redlink=1
*/
	$AssintedUserId= $focus->assigned_user_id;
	$AssignedUser = new User();
	$AssignedUser->retrieve($AssintedUserId);
	if (!($AssignedUser->check_role_membership('Channel Sales Manager'))) {
		$deptFlag = "false";
	} else { 
		$deptFlag = "true";
	}
	$this->ss->assign('DEPTFLAG', $deptFlag);
/* END SUGARINTERNAL CUSTOMIZATION */

       $this->ss->display('custom/modules/Opportunities/tpls/OpportunityWizard.tpl');
    }
}
?>
