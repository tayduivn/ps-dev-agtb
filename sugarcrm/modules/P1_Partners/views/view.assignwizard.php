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
require_once('modules/LeadContacts/LeadContact.php');

class P1_PartnersViewAssignWizard extends SugarView
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
		if(empty($_POST['mass']) || !isset($_POST['mass'])) {
			sugar_die('Error 1201: No opportunities are selected. Please select the opportunities from the PRM module list view');
		}

		global $mod_strings, $app_strings, $app_list_strings, $timedate;
	
		/**** Title ****/
		$this->ss->assign('MODULE_TITLE', get_module_title($mod_strings['LBL_MODULE_NAME'], $mod_strings['LBL_ASSIGN_WIZARD_TITLE'], true));
		
		/**** Language Def ****/
		$this->ss->assign('MOD_OPPORTUNITIES', return_module_language($GLOBALS['current_language'],'Opportunities'));
	
		$this->ss->assign('MOD_ASSIGN_TO_OPPS', $mod_strings['LBL_ASSIGN_TO_OPPS']);	
		$this->ss->assign('MOD_ASSIGN_PARTNER_ASSIGNED_TO', $mod_strings['LBL_ASSIGN_PARTNER_ASSIGNED_TO']);
		$this->ss->assign('MOD_ASSIGN_PARTNER_ACCOUNT_CONTACTS', $mod_strings['LBL_ASSIGN_PARTNER_ACCOUNT_CONTACTS']);
		$this->ss->assign('MOD_ASSIGN_PARTNER_SUBJECT', $mod_strings['LBL_ASSIGN_PARTNER_SUBJECT']);
		$this->ss->assign('MOD_ASSIGN_PARTNER_EMAIL', $mod_strings['LBL_ASSIGN_PARTNER_EMAIL']);
	
		/**** JSON ****/
		$json = getJSONobj();
	
		/**** Get Partner Assigned To, Account Name and OPP size ****/
		
		$this->ss->assign('opp_ids', $_POST['mass']);

		$opp = new Opportunity();
		$opp_acc = new Account();
		$partner_acc = new Account();	
	
		$oppfieldDefs = $opp->field_defs;
		
		foreach ($oppfieldDefs as $key => $values ) {
                        if(isset($oppfieldDefs[$key]['options']) && isset($app_list_strings[$oppfieldDefs[$key]['options']])){
                                $oppfieldDefs[$key]['options'] = $app_list_strings[$oppfieldDefs[$key]['options']];
                        }
            		if ( isset($opp->$key) )
                		$oppfieldDefs[$key]['value'] = $opp->$key;
        	}

		$this->ss->assign('opp_fields', $oppfieldDefs);


		$oppAccounts = array();
		$opportunity_ids = array();

		foreach($_POST['mass'] as $opp_id) {
			$opportunity_ids[] = $opp_id;
			$opp->retrieve($opp_id);
			$opp_acc->retrieve($opp->account_id);
			$partner_acc->retrieve($opp->partner_assigned_to_c);
			$oppAccounts[$opp->id] = array (
				'account_id' => $opp_acc->id,
				'account_name' => $opp_acc->name,
				'opp_amount' => $opp->amount,
				'partner_account_name' => $partner_acc->name,
			);
		}	
		$opportunity_id = implode(",", $opportunity_ids);
		$this->ss->assign('opp_id', $opportunity_id);	
		$this->ss->assign('oppAccounts', $oppAccounts);
		
		/**** Quick search ****/
		/*require_once('include/QuickSearchDefaults.php');
                $qsd = new QuickSearchDefaults();
		$qsd->setFormName('MassUpdate');
                $sqs_objects = array(
                        'accounts_opportunities_1_name' => $qsd->getQSAccount('accounts_opportunities_1_name', 'accounts_o2772ccounts_ida'),
                );
		$quicksearch_js = '<script type="text/javascript">sqs_objects = ' . $json->encode($sqs_objects) . '</script>';
                $this->ss->assign('QSJAVASCRIPT', $quicksearch_js);
		*/
		
                global $current_user;
		$this->ss->assign('P1_Partnersuser_email', $current_user->email1);
	
		require_once('modules/P1_Partners/partneremail.php');

                if(isset($body_html) && !empty($body_html)) {
                        $this->ss->assign('BODY_HTML', $body_html);
                } else {
                        $this->ss->assign('BODY_HTML', "");
                }

                require_once('modules/P1_Partners/customeremail.php');

                if(isset($contactemail_body_html) && !empty($contactemail_body_html)) {
                        $this->ss->assign('CONTACTEMAIL_BODY_HTML', $contactemail_body_html);
                } else {
                        $this->ss->assign('CONTACTEMAIL_BODY_HTML', "");
                }
	
		/**** TINYMCE ****/
		require_once("include/SugarTinyMCE.php");
        	$tiny = new SugarTinyMCE();
        	$tiny->defaultConfig['width'] = '100px';
		$tiny->defaultConfig['height'] = '350px';
		$tinyHtml = $tiny->getInstance('P1_Partnersbody_html');
        	$this->ss->assign('tiny', $tinyHtml);

		$toggleScript = "<script>
	function toggleId(id) {
	if(document.getElementById(id).style.display == 'none') {
		document.getElementById(id).style.display = 'block';
		document.getElementById(id+'Link').innerHTML = 'Hide Email';
	} else {
		document.getElementById(id).style.display = 'none';
		document.getElementById(id+'Link').innerHTML = 'Show Email';
	}
	}
	</script>";
		$this->ss->assign('TOGGLE_SCRIPT', $toggleScript);
		/**** Render Custom View ****/
		$this->ss->display('modules/P1_Partners/tpls/assignwizard.tpl');	
	}
}
?>
