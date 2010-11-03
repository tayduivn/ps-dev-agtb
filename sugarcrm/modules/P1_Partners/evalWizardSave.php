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

require_once('modules/Subscriptions/Subscription.php');
require_once('modules/Opportunities/Opportunity.php');
require_once('modules/Accounts/Account.php');
require_once('modules/Contacts/Contact.php');
require_once('modules/Notes/Note.php');
require_once('modules/P1_Partners/P1_PartnersUtils.php');
require_once('modules/P1_Partners/30dayeval_email.php');
require_once('data/Tracker.php');

function generate_password() {
        srand(rand(time(),1));

        $sid = '';
        $sets = "ABCDEFGHJKMNPQRSTWXYZ";
        $sets .= "acefghjkmnorstwxyz";
        $sets .= "23456789";

        for($index = 0; $index < 10; $index++)
        {
                $sid .= substr($sets,(rand()%(strlen($sets))), 1);
        }

        return $sid;
}

global $timedate;

/* Common Vars
	$_POST['Opportunitiesid'];
	$_POST['instance_name'];
	$_POST['flavor'];
	$_POST['eval_end_date'];
	$_POST['data_center'];
	$_POST['OpportunitiesSaleStage'];
	$_POST['opp_account_id'];
*/

/*
** @author: DTam
** SUGARINTERNAL CUSTOMIZATION
** ITRequest #: 
** Description: Save for automated creation of evals
** Wiki customization page: 
*/
/* END SUGARINTERNAL CUSTOMIZATION */

// check entry
if(!isset($_POST['Opportunitiesid']) || empty($_POST['Opportunitiesid'])) {
        sugar_die('Error 1306: Error retrieving record.');
		
}
if(!isset($_POST['P1_PartnersEvalWizardSave']) || empty($_POST['P1_PartnersEvalWizardSave'])) {
	sugar_die('Error 1305: Not a valid entry point.');
}
	$debug = "";
if ($_POST['eval_update'] == 'true') {
	//load opp for eval url to get sub
	$opp = new Opportunity();
	$opp->retrieve($_POST['Opportunitiesid']);
	$opp->eval_extend_count_c = $opp->eval_extend_count_c + 1;
	$opp->Evaluation_Close_Date_c = $timedate->to_db_date($_POST['eval_end_date']);
	$opp->save(false);
	$sub_id = md5($opp->eval_url_c);
	// get sub and update date
	$result = $GLOBALS['db']->query("Select * from subscriptions where subscription_id = '{$sub_id }' and deleted = '0'");
	$row = $GLOBALS['db']->fetchByAssoc($result);
	// check if relationship exists
	if ($row) {
		$Sub = new Subscription();
		$Sub->retrieve($row['id']);
		$Sub->expiration_date = $timedate->to_db_date($_POST['eval_end_date']);
		$Sub->save(false);
	} else {
	$debug .= 'Problem with sub retrieval';
	}	
	
	// NEED ION UPDATE
	
	
	// update confirm for IE
	if(isset($_POST['IE']) && !empty($_POST['IE'])) {
	$confirmation = 'Eval updated. New end date: '.$_POST['eval_end_date'];
	} else {
	// update confirm for all others 
	$confirmation = '<script type="text/javascript">
	alert("Eval updated. New end date: '.$_POST['eval_end_date'].'");
	window.location = "index.php?module=P1_Partners&view=evalWizardSave";
	</script>'; 
	}
} else {


	// create eval url
	if ($_POST['data_center'] == 'us') {
		$eval_domain = '.sugarondemand.com';
	} else {
		$eval_domain = '.sugaropencloud.eu';
	}
	$eval_url = "http://" . $_POST['instance_name'] . $eval_domain;

	// Create Subscription
	$newSub = new Subscription();
	$newSub->subscription_id = md5($eval_url);
	$newSub->expiration_date = $timedate->to_db_date($_POST['eval_end_date']);
	$newSub->status = 'enabled';
	$newSub->account_id = $_POST['opp_account_id'];
	$newSub->team_set_id = '1';
	$newSub->team_id = '1';
	$newSub->created_by = '1';
	$newSub->assigned_user_id = '1';
	$newSub->save(false);
	
	// set distribution group
	$newSub->load_relationship('distgroups');
	if ( $_POST['flavor']=='ent') {
		$newSub->distgroups->add('cdc48a26-030e-0d37-e443-46e1d33c7af1');
		$distID = 'cdc48a26-030e-0d37-e443-46e1d33c7af1';
	} elseif ( $_POST['flavor']=='pro') {
		$newSub->distgroups->add('a08c5a21-1108-76e9-e5be-46e1d37f69a2');
		$distID = 'a08c5a21-1108-76e9-e5be-46e1d37f69a2';
	}
	$newSub->save(false);

	// save eval url in opp for eval extensions later
	$opp = new Opportunity();
	$opp->retrieve($_POST['Opportunitiesid']);
	$opp->eval_url_c = $eval_url;
	$opp->evaluation_start_date = $timedate->get_gmt_db_date();
	$opp->Evaluation_Close_Date_c = $timedate->to_db_date($_POST['eval_end_date']);
	$opp->evaluation = '1';
	$opp->save(false);
	
	// update sub quantity
	$result = $GLOBALS['db']->query("Select * from subscriptions_distgroups where subscription_id = '{$newSub->id}' and distgroup_id ='{$distID}'");
	$row = $GLOBALS['db']->fetchByAssoc($result);
	
	// associate sub to account
	if ($row) {
		$result = $GLOBALS['db']->query("UPDATE subscriptions_distgroups SET quantity='10' where subscription_id = '{$newSub->id}' and distgroup_id ='{$distID}'");
		$result = $GLOBALS['db']->query("Select * from subscriptions_distgroups where subscription_id = '{$newSub->id}' and distgroup_id ='{$distID}'");
		$row = $GLOBALS['db']->fetchByAssoc($result);
	} else {
		$debug .= 'Problem with sub creation. ';
	}
	
	// load opp account
	$account = new Account();
	$account->disable_row_level_security = true;
	$account->retrieve($_POST['opp_account_id']);
	
	// if account exists
	if(!empty($account->id)){
		$sub_query = "select id from subscriptions where subscription_id = '{$newSub->id}' and deleted = 0";
		$res = $GLOBALS['db']->query($sub_query);
		$row = $GLOBALS['db']->fetchByAssoc($res);
		// found the subscription in the database
		if($row){
			require_once('modules/Subscriptions/Subscription.php');
			$subscription = new Subscription();
			$subscription->disable_row_level_security = true;
			$subscription->retrieve($row['id']);
			// associate this subscription with the account
			if(!empty($subscription->id)){
				$account->load_relationship('subscriptions');
				$account->subscriptions->add($subscription->id);
				$account->update_date_modified = false;
				$account->update_modified_by = false;
				if(!empty($account->description)){
					$account->description .= "\n\n";
				}
				$account->description .= "Script: Automatically added subscription {$subscription->subscription_id} to this account based on order number from opportunity";
				$account->save(FALSE);
			}
		}
	} else {
		$debug .= 'Problem with account sub assocation. ';
	}

	// Send notification email
	// set correct address to send it to if a account contact is chosen use it
	if (isset($_POST['P1_Partnerscontact_id'])) {
		$opp_contact = new Contact(); 
		$opp_contact=$opp_contact->retrieve($_POST['P1_Partnerscontact_id']);
		$sendTo = $opp_contact->emailAddress->getPrimaryAddress($opp_contact);
	} else { // otherwise default to primary email on account
		$opp_account = new Account(); 
		$opp_account=$opp_account->retrieve($_POST['opp_account_id']);
		$sendTo = $opp_account->emailAddress->getPrimaryAddress($opp_account);
	}
	$admin_pw = generate_password();
	if (!isset($sendTo)) {
		echo 'error no account email set';
	} else {
		// Send eval email
		$email_object = trials_send_welcome_email($eval_url, $_POST['eval_end_date'], $sendTo, $admin_pw);	
		// Link the email sent to the opportunity.
		$relate_email = clone $email_object;
		$relate_email->parent_id = $opp->id;
		$relate_email->save(FALSE);
		$relate_email->load_relationship('opportunities');
		$relate_email->opportunities->add($opp->id);
		//var_dump($relate_email);
	}
	
	// ION PROVISIONING 

$server = 7; // set default to us
if($_POST['data_center'] == 'emea') {
  $server = 10;
}

$cleanEndDate=$timedate->to_db_date($_POST['eval_end_date']);
$iondb = mysql_connect("admin4", "ion3", "flip5!m00");
mysql_select_db("ion3", $iondb);
$sql = sprintf("INSERT INTO log_instances(instance,password,admin_password, license_users, license_expire, license_key, internal_record, edition, server, user_id, owner, first_name, done, autogen, portal_user, date_created, order_id, evaluation) VALUES('%s', '%s','%s','%s','%s','%s','%s','%s',%d,'%s','%s','%s',%d,%d, '%s', NOW(), '%s', %d)",
                      strtolower($_POST['instance_name']),
                      generate_password(),
                      $admin_pw,
                      10,
                      $cleanEndDate,
                      $newSub->subscription_id,
                      $_POST['opp_account_id'],
                      strtolower($_POST['flavor']), $server, $sendTo,
                      'ion@sugarcrm.com', 'Evaluation User', 0, 1, 'No Portal', 'EVAL', 1);
syslog(LOG_DEBUG, "joey livedebug log_instance eval: insert sql " . $sql);
mysql_query($sql, $iondb);

$sql = sprintf("INSERT INTO accounts(instance, account_type, account_name, internal_id) VALUES ('%s','%s','%s','%s')", strtolower($_POST['instance_name']), 'account', $_POST['opp_account_id'], $_POST['opp_account_id']);
syslog(LOG_DEBUG, "joey livedebug account eval: insert sql " . $sql);
mysql_query($sql, $iondb);
	
	// set confirm
	// for IE post is done directly in modal so update modal
	if(isset($_POST['IE']) && !empty($_POST['IE'])) {
		$confirmation = 'New eval created<br>URL: ' . $eval_url .'<br>End Date: ' . $_POST['eval_end_date'].'<br>Flavor: ' . $_POST['flavor'] .'<br>Notification email sent to '. $sendTo;
	} else {
	// for all others post is to the eval wiz save so redirect back to oppQ
	$confirmation = '<script type="text/javascript">
	alert("New eval created\nURL: ' . $eval_url .'\nEnd Date: ' . $_POST['eval_end_date'].'\nFlavor: ' . $_POST['flavor'] .'\nNotification email sent to '. $sendTo.'");
	window.location = "index.php?module=P1_Partners&view=evalWizardSave";
	</script>';
	}

}
// post confirm and debug
 echo $confirmation . $debug;
?>
