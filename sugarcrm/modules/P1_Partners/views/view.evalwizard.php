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

class P1_PartnersViewEvalWizard extends SugarView
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
			$this->ss->assign('opp_account_id', $opp_acc->id);
        }
		$account_email=$opp_acc->emailAddress->getPrimaryAddress($opp_acc);
		if(isset($account_email) && !empty($account_email)) {
			$this->ss->assign('account_email', $account_email);
		}
		/**** Check if updateable and set update flag ****/
		$ionURL = "http://www.sugarcrm.com/sugarshop/ion3-tools/display.php?arid=" . $opp_acc->id;
		$ionResult = file_get_contents($ionURL);
		//var_dump($ionResult); 
		//if ($ionResult != 'None exists') {
		if(isset($opp->eval_url_c) && !empty($opp->eval_url_c)) {
			$updateflag = 'true';
			//echo('Eval URL:<br>');
			//echo($ionResult);
		} else {
			$updateflag = 'false';
		}
		
		$this->ss->assign('updateflag', $updateflag);
		
		/**** Render Custom View ****/
		if ($updateflag == 'true') {
		$this->ss->assign('exp_date', $opp->Evaluation_Close_Date_c);
		$this->ss->assign('eval_url', $opp->eval_url_c);
		$this->ss->display('modules/P1_Partners/tpls/evalwizardupdate.tpl');	
		} else {
		$this->ss->display('modules/P1_Partners/tpls/evalwizard.tpl');	
		}
	}
}
?>
