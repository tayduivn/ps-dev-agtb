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
require_once('include/MVC/View/SugarView.php');
require_once('modules/LeadContacts/LeadContact.php');
require_once('modules/LeadAccounts/LeadAccount.php');
                
class LeadAccountsViewConvertlead extends SugarView 
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
        global $mod_strings, $app_strings, $app_list_strings, $timedate;
        
        $this->ss->assign("MODULE_TITLE", 
                    get_module_title(
                        $mod_strings['LBL_MODULE_NAME'], 
                        $mod_strings['LBL_MODULE_NAME'] . ': ' . $mod_strings['LBL_CONVERTLEAD'], 
                        true
                        )
                    );
        
        $focus = new LeadAccount;
        $focus->retrieve($_REQUEST['record']);
        
        $this->ss->assign('APP', $app_strings);
        $this->ss->assign('MOD', $mod_strings);
        $fieldDefs = $focus->field_defs;
        foreach ($fieldDefs as $key => $values ) {
            if(isset($fieldDefs[$key]['options']) && isset($app_list_strings[$fieldDefs[$key]['options']]))
                $fieldDefs[$key]['options'] = $app_list_strings[$fieldDefs[$key]['options']];
            if ( isset($focus->$key) )
                $fieldDefs[$key]['value'] = $focus->$key;
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
        $this->ss->assign('opp_fields',$oppfieldDefs);
        $json = getJSONobj();
        $this->ss->assign('PROB_ARRAY',$json->encode($app_list_strings['sales_probability_dom']));
        
        // handles fields from other modules
        $this->ss->assign('MOD_NOTES', return_module_language($GLOBALS['current_language'],'Notes'));
        $this->ss->assign('MOD_CALLS', return_module_language($GLOBALS['current_language'],'Calls'));
        
        $mod_strings_leadcontacts = return_module_language($GLOBALS['current_language'],'LeadContacts');
        $leadContact = new LeadContact;
        
        // get a list of lead contacts attached to this lead account
        $focus->load_relationship('leadcontacts');
        $leadcontacts = array('account' => $app_strings['LBL_NONE']);
        foreach ( $focus->build_related_list($focus->leadcontacts->getQuery(), new LeadContact) as $leadcontact)
            if ( $leadcontact->converted != '1' )
                $leadcontacts[$leadcontact->id] = $leadcontact->name;
        $this->ss->assign('leadcontacts',$leadcontacts);
        unset($leadcontacts['account']);
        
        // Validate passed lead contact id
        if ( isset($_REQUEST['uid']) && !isset($leadcontacts[$_REQUEST['uid']]) )
            unset($_REQUEST['uid']);
        else {
            $leadContactFocus = new LeadContact;
            $leadContactFocus->retrieve($_REQUEST['uid']);
            $this->ss->assign('Opportunitiesdescription',$leadContactFocus->description);
            $this->ss->assign('Opportunitiescompetitor_1',$leadContactFocus->competitor_1_c);
            $this->ss->assign('Opportunitiescurrent_solution',$leadContactFocus->current_solution_c);
            $this->ss->assign('Opportunitiesusers',$leadContactFocus->initial_subscriptions_c);
            $fieldDefs['billing_address_street']['value'] = $leadContactFocus->primary_address_street;
            $fieldDefs['billing_address_city']['value'] = $leadContactFocus->primary_address_city;
            $fieldDefs['billing_address_state']['value'] = $leadContactFocus->primary_address_state;
            $fieldDefs['billing_address_postalcode']['value'] = $leadContactFocus->primary_address_postalcode;
            $fieldDefs['billing_address_country']['value'] = $leadContactFocus->primary_address_country;
        }
        
        $this->ss->assign('fields',$fieldDefs);
        
        // Build Lead Contacts listview
        $_REQUEST['select_entire_list'] = 0;
        if ( !isset($_REQUEST['uid']) )
            $_REQUEST['uid'] = implode(',',array_keys($leadcontacts));
        
        require_once('include/ListView/ListViewFacade.php');
        $lvf = new ListViewFacade($leadContact, $leadContact->module_dir, 0);
    
        $params = array();
        if(!empty($_REQUEST['LeadContacts2_LEADCONTACT_ORDER_BY'])) {
            $params['orderBy'] = $_REQUEST['LeadContacts2_LEADCONTACT_ORDER_BY'];
            $params['overrideOrder'] = true;
            if(!empty($_REQUEST['lvso'])) $params['sortOrder'] = $_REQUEST['lvso'];
        }
        $params['custom_where'] = " AND leadcontacts.converted != '1' AND leadcontacts.leadaccount_id = '{$focus->id}'";
        $lvf->lv->mergeduplicates = false;
        $lvf->mod_strings = $mod_strings;
        $lvf->lv->export = false;
        $lvf->lv->delete = false;
        $lvf->lv->select = true;
        $lvf->lv->mailMerge = false;
        $lvf->lv->multiSelect = true;
        $lvf->lv->quickViewLinks = false;
        $lvf->lv->setup($lvf->focus, $lvf->template, '', $params, 0, -1,  array(), 'id');
        $this->ss->assign('LEAD_CONTACTS_LISTVIEW',
            get_form_header($mod_strings['LBL_SELECT_CONTACTS_TO_CONVERT'], '', false).$lvf->lv->display());
		
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
		
        if ( $focus->converted == '1' && count($leadcontacts) <= 0 )
            echo "Nothing to convert!";
        else
            $this->ss->display('modules/LeadAccounts/tpls/ConvertLead.tpl');
    }
}
?>
