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
require_once('modules/P1_Partners/P1_PartnersUtils.php');
require_once('modules/Notes/Note.php');
/**** Assign Wizard Processing ****/

$a_opp_ids = isset($_POST['P1_Partnersopp_ids']) ? explode(",",$_POST['P1_Partnersopp_ids']) : "";
$contact_id = isset($_POST['P1_Partnerscontact_id']) ? $_POST['P1_Partnerscontact_id'] : "";
$email_body = isset($_POST['P1_Partnersbody_html']) ? $_POST['P1_Partnersbody_html'] : "";
$email_subject =isset($_POST['P1_Partnersemail_subject']) ? $_POST['P1_Partnersemail_subject'] : "";
$check_contact_email = isset($_POST['P1_Partners_check_send_contact_mail']) ? $_POST['P1_Partners_check_send_contact_mail'] : "";
$contact_email_subject = isset($_POST['P1_Partners_contactmail_subject']) ? $_POST['P1_Partners_contactmail_subject'] : "";
$contact_email_body = isset($_POST['P1_Partners_contactmail_body_html']) ? $_POST['P1_Partners_contactmail_body_html'] : "";

/*
** Get this contacts information
*/
if(!empty($contact_email_body)) {
        $partner_contact = new Contact();
        $partner_contact->retrieve($contact_id);
        $partner_account = new Account();
        $partner_account->retrieve($partner_contact->account_id);

        $replace_contact_info = array(
                'partner_assigned_to_first_name' => isset($partner_contact->first_name) ? $partner_contact->first_name : ""
                ,'partner_assigned_to_last_name'  => isset($partner_contact->last_name) ? $partner_contact->last_name : ""
                ,'partner_assigned_to_title' => isset($partner_contact->title) ? $partner_contact->title : ""
                ,'partner_assigned_to_email' => isset($partner_contact->email1) ? $partner_contact->email1 : ""
                ,'partner_assigned_to_phone_work' => isset($partner_contact->phone_work) ? $partner_contact->phone_work : ""
                ,'partner_assigned_to_account_name' => isset($partner_account->name) ? $partner_account->name : ""
        );
        foreach($replace_contact_info as $key => $value) {
                $key = '$'.$key;
                $contact_email_body = str_replace($key, $value, $contact_email_body);
        }
}

//Handle Sending Of PRM Email.
if( empty($contact_id) || empty($email_body) )
	$GLOBALS['log']->fatal("Unable to send PRM email, missing contact id or email body.");
else 
{
	$csv_contents = P1_PartnerUtils::getCsvExportForPrmEmail($a_opp_ids);
	$email_object = P1_PartnerUtils::sendPRMEmail($contact_id, $email_subject, $email_body,$csv_contents);	
}

//Handle Updating Of Opportunities
foreach ($a_opp_ids as $single_opp_id)
{
	$opp = new Opportunity();
	$opp->retrieve($single_opp_id);
	P1_PartnerUtils::updateOppFromPartnerWizard($opp);
	$opp->save();
	
	//Link the email sent to the opportunity.
	$relate_email = clone $email_object;
	$relate_email->parent_id = $single_opp_id;
	$relate_email->save(FALSE);
	$relate_email->load_relationship('opportunities');
	$relate_email->opportunities->add($single_opp_id);
	
	//Create a note and relate it to the attached email with the contents of the CSV file.
	P1_PartnerUtils::createNoteForEmailAttachment($csv_contents, $relate_email->id);

	//if checkbox to send customer email was checked attach email as draft to the opportunity record
        if(isset($check_contact_email) && !empty($check_contact_email)) {
                $contact_email_object = P1_PartnerUtils::attachContactEmail($contact_email_subject, $contact_email_body);
                if(isset($contact_email_object) && !empty($contact_email_object)) {
                        $contact_relate_email = clone $contact_email_object;
                        $contact_relate_email->parent_id = $single_opp_id;
                        $contact_relate_email->save(FALSE);
                        $contact_relate_email->load_relationship('opportunities');
                        $contact_relate_email->opportunities->add($single_opp_id);
                }
        }	
}


//Redirect back to P1_Partners module list view page.
if(!isset($_POST['entryPoint'])) {
	header("Location: index.php?module=P1_Partners&action=index");
}

