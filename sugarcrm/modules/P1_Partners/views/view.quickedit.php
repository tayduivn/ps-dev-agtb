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

 * Description:  TODO To be written.
 * Portions created by SugarCRM are Copyright (C) SugarCRM, Inc.
 * All Rights Reserved.
 * Contributor(s): ______________________________________..
 ********************************************************************************/

require_once('include/MVC/View/SugarView.php');
require_once('modules/Opportunities/Opportunity.php');
require_once('modules/Accounts/Account.php');
require_once('modules/Contacts/Contact.php');
require_once('modules/Tasks/Task.php');

class P1_PartnersViewQuickEdit extends SugarView
{
    /**
     * Constructor
     */
        public function __construct()
    	{
               parent::SugarView();
    	}
	
	function process() {
                $this->display();
        }

	function display()
    	{
		global $theme;
		$this->ss->assign('THEME', $theme);
		if(empty($_REQUEST['record']) || !isset($_REQUEST['record'])) {
			sugar_die('Error 1300: No opportunity record has been selected. Please select an opportunity to continue.');
		}

		$opp = new Opportunity();
        	$opp_acc = new Account();
		$campaign = new Campaign();

 	 	$opp->retrieve($_REQUEST['record']);
		if(empty($opp->id) || !isset($opp->id)) {
			sugar_die('Error 1301: Could not retrieve opportunity record for the specified id.');
		}

		global $mod_strings, $app_strings, $app_list_strings, $timedate;
	
		/**** Title ****/
		$view_title = $mod_strings['LBL_QUICK_EDIT_TITLE'];
		$this->ss->assign('MODULE_TITLE', $view_title);
		
		/**** Language Def ****/
		$this->ss->assign('MOD_OPPORTUNITIES', return_module_language($GLOBALS['current_language'],'Opportunities'));	
	
		/**** JSON ****/
		$json = getJSONobj();	

		/**** DROP DOWN DEFS ****/
		$oppfieldDefs = $opp->field_defs;
		
		foreach ($oppfieldDefs as $key => $values ) {
                        if(isset($oppfieldDefs[$key]['options']) && isset($app_list_strings[$oppfieldDefs[$key]['options']])){
				unset($app_list_strings['sales_stage_dom']['Finance Closed']);
                                unset($app_list_strings['sales_stage_dom']['Sales Ops Closed']);
		                unset($app_list_strings['sales_stage_dom']['Closed Won']);
				$oppfieldDefs[$key]['options'] = $app_list_strings[$oppfieldDefs[$key]['options']];
                        }
            		if ( isset($opp->$key) )
                		$oppfieldDefs[$key]['value'] = $opp->$key;
        	}
		$this->ss->assign('opp_fields', $oppfieldDefs);

		/**** DATE TIME ***/
		$this->ss->assign('CAL_DATEFORMAT',$timedate->get_cal_date_format());
        	$this->ss->assign('USER_DATEFORMAT', $timedate->get_user_date_format());
        	$this->ss->assign('USER_TIMEFORMAT', $timedate->get_user_time_format());
        	$date = gmdate($GLOBALS['timedate']->get_db_date_time_format());
       	 	$this->ss->assign('USER_DATEDEFAULT', $timedate->to_display_date($date));
        	$this->ss->assign('USER_TIMEDEFAULT', $timedate->to_display_time($date,true));
	
		/**** GET CONTACTS RELATED TO OPP ACCOUNT ****/
                $opp_acc->retrieve($opp->account_id);
                $opp_acc->load_relationship('contacts');
                $contacts = array();
                foreach ($opp_acc->build_related_list($opp_acc->contacts->getQuery(), new Contact) as $contact) {
                	if(isset($contact->phone_work)) {
				$trim_phone_work = trim($contact->phone_work);
				$trimmed_phone_work = preg_replace('/(\W*)/', '', $trim_phone_work);
			}
		        $contacts[$contact->id] = array('name' => $contact->name, 'phone_work' => $contact->phone_work, 'email1' => $contact->email1, 'trimmed_phone_work' => $trimmed_phone_work);
                }
                $this->ss->assign('contacts',$contacts);
		
		/**** Get campaign associated to opportunity ****/
		if(isset($opp->campaign_id) && !empty($opp->campaign_id)) {
			$campaign->retrieve($opp->campaign_id);	
			$this->ss->assign('campaign_id', $campaign->id);
			$this->ss->assign('campaign_name', $campaign->name);
		}

		/**** Get account name associated to opportunity****/
                if(isset($opp_acc->name) && !empty($opp_acc->name)) {
                        $this->ss->assign('account_name', $opp_acc->name);
                }

		/**** Get logged in user's office phone # ****/
		global $current_user;
		$trim_user_phone_work = trim($GLOBALS['current_user']->phone_work);
                $trimmed_user_phone_work = preg_replace('/(\W*)/', '', $trim_user_phone_work);
		$this->ss->assign('user_office_phone', $trimmed_user_phone_work);
		
		/**** HISTORY ITEMS ****/
//** BEGIN CUSTOMIZATION EDDY :: ITTix 12567
//hiding history panel for now, and using subpanel instead
/*
                $opp_notes = array();
                $note_query = "select date_modified, name, id from notes where parent_type = 'Opportunities' and parent_id = '{$opp->id}' and deleted = 0 order by date_modified DESC limit 0,5";
                $result = $GLOBALS['db']->query($note_query);
                $count = 0;
                if(!$result) {
                        $GLOBALS['log']->fatal("DEEOPPQ QUICKEDIT VIEW NOTES QUERY: Could not connect to the SugarInternal DB");
                }
                else {
                        while ($row = $GLOBALS['db']->fetchByAssoc($result)) {
                                $opp_notes[$count][$opp->id]['name'] = $row['name'];
                                $opp_notes[$count][$opp->id]['id'] = $row['id'];
                                $date_modified = $timedate->to_display_date($row['date_modified'])." ".$timedate->to_display_time($row['date_modified'], true);
                                $opp_notes[$count][$opp->id]['date_modified'] = $date_modified;
                                $count++;
                        }
                }
                $this->ss->assign('opp_notes',$opp_notes);

                $opp_emails = array();

				// See ITRequest #11612
				// The original query to pull Email records was not using the new emails table structure, and therefore always retrieved 0 rows
				// This new query is based on what the History subpanel uses-- except I took out the team security joins, since it seems like OppQ doesn't care about team security
				$email_query = "SELECT
						emails.id, emails.name, emails.status, emails.date_modified
					FROM emails
					INNER JOIN emails_beans
						ON (emails.id = emails_beans.email_id AND emails_beans.bean_id = '{$opp->id}' AND bean_module='Opportunities')
					WHERE
						(emails_beans.deleted = 0 AND emails.deleted = 0) AND emails.deleted = 0
					ORDER BY emails.date_modified DESC LIMIT 0,5";

                $email_result = $GLOBALS['db']->query($email_query);
                $email_count = 0;
                if(!$email_result) {
                        $GLOBALS['log']->fatal("DEEOPPQ QUICKEDIT VIEW EMAIL QUERY: Could not connect to the SugarInternal DB");
                }
                else {
                        while ($email_row = $GLOBALS['db']->fetchByAssoc($email_result)) {
                                $opp_emails[$email_count][$opp->id]['name'] = $email_row['name'];
                                $opp_emails[$email_count][$opp->id]['id'] = $email_row['id'];
                                $opp_emails[$email_count][$opp->id]['status'] = $email_row['status'];
                                $email_date_modified = $timedate->to_display_date($email_row['date_modified'])." ".$timedate->to_display_time($email_row['date_modified'], true);
                                $opp_emails[$email_count][$opp->id]['date_modified'] = $email_date_modified;
                                $email_count++;
                        }
                }
                $this->ss->assign('opp_emails',$opp_emails);
*/
//** END CUSTOMIZATION EDDY :: ITTix 12567

		//** BEGIN CUSTOMIZATION EDDY :: ITTix 12405
	        //retrieve max score stratight from the bean
        	$this->ss->assign('MAX_SCORE', $opp->score_c);
                

		//BEGIN EDDY  ITTix13077
		$this->ss->assign("CLOSEDREASONS", get_select_options_with_id($app_list_strings['closed_lost_dependant_1_dom'], $opp->closed_lost_reason_c));
		$this->ss->assign("CLOSEDDETAILSABANDON", get_select_options_with_id($app_list_strings['closed_lost_dependant_2_dom'], $opp->closed_lost_reason_detail_c));
		$this->ss->assign("CLOSEDDETAILSCOMPETTIOR", get_select_options_with_id($app_list_strings['closed_lost_dependant_2_dom'], $opp->closed_lost_reason_detail_c));
		$this->ss->assign("CLOSEDDETAILSUNABLE", get_select_options_with_id($app_list_strings['closed_lost_dependant_2_dom'], $opp->closed_lost_reason_detail_c));
		//END EDDY  ITTix13077

		/**** Render Custom View ****/
		$this->ss->display('modules/P1_Partners/tpls/quickedit.tpl');	

		//** BEGIN CUSTOMIZATION EDDY :: ITTix 12220
		/**** RELATED INTERACTIONS  ****/
		//populate subpanels manually, since this is not a detail form
		require_once('include/SubPanel/SubPanelTiles.php');
		$subpanel = new SubPanelTiles($opp);
		//get list of available tabs
		$alltabs=$subpanel->subpanel_definitions->get_available_tabs();
		if (!empty($alltabs)) {
	            //only show history and interaction tabs for this opportunity
		    foreach ($alltabs as $name) {
		        if ($name == 'history_for_oppq' || $name =='for_opp_q') {
				continue;
			}else{
		            $subpanel->subpanel_definitions->exclude_tab($name);            
		        }   
		    }
		}
		//print out subpanels
		echo $subpanel->display();
		//** END CUSTOMIZATION EDDY :: ITTix 12220

	}
}
?>
