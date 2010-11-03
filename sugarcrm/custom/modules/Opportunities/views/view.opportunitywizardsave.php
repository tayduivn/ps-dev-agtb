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
 * by SugarCRM are Copyright (C) 2004-2007 SugarCRM, Inc.; All Rights Reserved.
 ********************************************************************************/
/*********************************************************************************

 * Description: Controller for the Import module
 * Portions created by SugarCRM are Copyright (C) SugarCRM, Inc.
 * All Rights Reserved.
 ********************************************************************************/
require_once("modules/Accounts/AccountFormBase.php");
require_once('modules/Opportunities/Opportunity.php');
require_once('modules/Accounts/Account.php');
require_once('modules/Contacts/Contact.php');
require_once('modules/Tasks/Task.php');

 
class OpportunitiesViewOpportunitywizardsave extends SugarView 
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
		
        // Now handle displaying the results screen
        echo get_module_title(
            $mod_strings['LBL_MODULE_NAME'], 
            $mod_strings['LBL_MODULE_NAME'] . ': Opportunity Wizard Save', 
            true
            );
        
		
        
        // parse the request vars into subarrays that will be passed to the various bean methods
        $formdata = array('account'=>array(),'opportunity'=>array());
        foreach ( $_REQUEST as $key => $value ) {
            if ( preg_match('/^Accounts/',$key) && $key != 'Accounts_divs' && $key != 'Accountsshipping_checkbox')
                $formdata['account'][str_replace('Accounts','',$key)] = $value;
            if ( preg_match('/^Opportunities/',$key) && $key != 'Opportunities_divs' )
                $formdata['opportunity'][str_replace('Opportunities','',$key)] = $value;
            if ( preg_match('/^portal_name/',$key) )
                $formdata['contacts'][str_replace('portal_name_','',$key)]['portal_name'] = $value;
            if ( preg_match('/^portal_active/',$key) )
                $formdata['contacts'][str_replace('portal_active_','',$key)]['portal_active'] = 1;
            if ( preg_match('/^support_authorized_c/',$key) )
                $formdata['contacts'][str_replace('support_authorized_c_','',$key)]['support_authorized_c'] = 1;
	        if ( preg_match('/^billing_contact_c/',$key) )
                $formdata['contacts'][str_replace('billing_contact_c_','',$key)]['billing_contact_c'] = 1;
            // begin customization - jwhitcraft - ITR:13820 : Save if they are the primary business contact for the company
            if ( preg_match('/^primary_business_c/',$key) )
                $formdata['contacts'][str_replace('primary_business_c_','',$key)]['primary_business_c'] = 1;
            // end customization - jwhitcraft - ITR:13820
	}

        // Handle populating the list of lead contacts to convert with any lead contact we selected to be related to
        // a new opportunity or appointment
        if ( !isset($_REQUEST['mass']) )
            $_REQUEST['mass'] = array();
		
		$opportunity = new Opportunity();
		$opportunity->retrieve($_REQUEST['record']);
		
		$opportunity_updated = false;
		foreach($formdata['opportunity'] as $key => $value){
			if($opportunity->$key != $value){
				$opportunity->$key = $value;
				$opportunity_updated = true;
			}
		}
		/*
		** @author: dtam
		** SUGARINTERNAL CUSTOMIZATION
		** ITRequest #: 18765
		** Description: create logic that sets the close date to today for opps where the date is set in the past or future.
		** Wiki customization page: internalwiki.sjc.sugarcrm.pvt/index.php/View.opportunitywizardsave.php
		*/
		global $timedate;
		$formCloseDate = $formdata ["opportunity"]['date_closed'];
		$cleanCloseDate=$timedate->to_db_date($formCloseDate);
		$cleanToday = date('Y-m-d');
		if ($cleanCloseDate!=$cleanToday) {
		  global $current_user;
		  SYSLOG(LOG_DEBUG, "dtam opportunities debug user:".$current_user->user_name." date:".date('Y-m-d')." clean date:" . $cleanToday);
		  $opportunity->date_closed = $cleanToday;
		  $opportunity_updated = true;
		}
		/* END SUGARINTERNAL CUSTOMIZATION */
		echo "<ul>";
		

		if($opportunity_updated)
			$opportunity->save(false);
		
		echo "<li>";
		echo "Updated Opportunity - <a href='index.php?action=DetailView&module=Opportunities&record=".$opportunity->id."'>".$opportunity->name."</a>, assigned to {$opportunity->assigned_user_name}";
		echo "</li>";

		$account = new Account();
		$account->retrieve($_REQUEST['account_id']);
        
		$account_updated = false;
		foreach($formdata['account'] as $key => $value){
			if($account->$key != $value){
				$account->$key = $value;
				$account_updated = true;
			}
		}
		
		if($account_updated)
			$account->save(false);
		
		echo "<li>";
		echo "Updated Account - <a href='index.php?action=DetailView&module=Accounts&record=".$account->id."'>".$account->name."</a>, assigned to {$account->assigned_user_name}";
		echo "</li>";
		
		foreach($formdata['contacts'] as $contact_id => $contact_array){
			$contact = new Contact();
			$contact->retrieve($contact_id);
			if(!empty($contact->id)){
				$contact->portal_name = $contact_array['portal_name'];
				if(isset($contact_array['portal_active']) && $contact_array['portal_active'] == 1){
					$contact->portal_active = 1;
				}
				if(isset($contact_array['support_authorized_c']) && $contact_array['support_authorized_c'] == 1){
                                        $contact->support_authorized_c = 1;
                                }
				if(isset($contact_array['billing_contact_c']) && $contact_array['billing_contact_c'] == 1){
                                        $contact->billing_contact_c = 1;
                                }
                if(isset($contact_array['primary_business_c']) && $contact_array['primary_business_c'] == 1){
                    $contact->primary_business_c = 1;
                } 
				$contact->save(false);
				
				echo "<li>";
				echo "Updated Contact - <a href='index.php?action=DetailView&module=Contacts&record=".$contact->id."'>".$contact->name."</a>, assigned to {$contact->assigned_user_name}";
				echo "</li>";	
			}
		}

        // begin customization - jwhitcraft - ITR: 13820 - Make this automaticly redirect back to the opptunity when done
        echo "<script type='text/javascript'>setTimeout(function() { document.location.href='index.php?action=DetailView&module=Opportunities&record=".$opportunity->id."'  }, 5000);</script>";
        echo "<br /><strong>After 5 Seconds you will be redirected back to the Opportunity</strong>";
		// end customization - jwhitcraft - ITR: 13820
    }

}
?>
