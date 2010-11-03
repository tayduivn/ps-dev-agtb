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

require_once('modules/Opportunities/Opportunity.php');
require_once('modules/Accounts/Account.php');
require_once('modules/Contacts/Contact.php');
require_once('modules/Notes/Note.php');
require_once('modules/P1_Partners/P1_PartnersUtils.php');
require_once('data/Tracker.php');

/**** Quick Edit Processing ****/
if(!isset($_POST['P1_PartnersQuickEditSave']) || empty($_POST['P1_PartnersQuickEditSave'])) {
	sugar_die('Error 1305: Not a valid entry point.');
}

if(!isset($_POST['Opportunitiesid']) || empty($_POST['Opportunitiesid'])) {
        sugar_die('Error 1306: Error retrieving record.');
}

$oppData = array();
foreach($_REQUEST as $key => $value) {
	if(preg_match('/^Opportunities/',$key) && $key != 'Opportunities_divs') {
		$oppData[str_replace('Opportunities','',$key)] = $value;
	}
}

$opportunity = new Opportunity();
$opportunity->retrieve($_REQUEST['Opportunitiesid']);

$opportunity_updated = false;
foreach($oppData as $key => $value){
	if($opportunity->$key != $value){
     		$opportunity->$key = $value;
        	$opportunity_updated = true;
      	}
}

//update opportunity
if($opportunity_updated) {
	$opportunity->save(false);
}

//create note 
if(isset($_REQUEST['Notessubject']) && !empty($_REQUEST['Notessubject'])) {
     	global $current_user;
    	$note = new Note();	
       	$note->name = $_REQUEST['Notessubject'];
        $note->created_by = $current_user->id;
        $note->parent_type = 'Opportunities';
        $note->parent_id = $opportunity->id;
        $note->portal_flag = 0;
        $note->description = isset($_REQUEST['Notesdescription']) ? $_REQUEST['Notesdescription'] : "";
        $note->team_id = $current_user->default_team;
        $note->save(false);
}

//update account name if it is updated via quick edit
$account = new Account();
$account->retrieve($opportunity->account_id);
if(isset($_REQUEST['Accountsname']) && !empty($_REQUEST['Accountsname']) && $_REQUEST['Accountsname'] != $account->name) {
        $account->name = $_REQUEST['Accountsname'];
        $account->save(false);
}

//Add to tracker
//IMP NOTE: Admin -> Tracker Settings -> Tracker Actions must be enabled for this to work
P1_PartnerUtils::oppToTracker($opportunity, true);

//Redirect back to P1_Partners module list view page.
if(!isset($_POST['entryPoint'])) {
	header("Location: index.php?module=P1_Partners&action=index");
}
/**** END Quick Edit Processing ****/
?>
