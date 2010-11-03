<?php
ob_start();
chdir('../..');
define('sugarEntry', true);
require_once('include/entryPoint.php');
require_once('modules/Opportunities/Opportunity.php');
require_once('modules/Accounts/Account.php');
require_once('modules/Contacts/Contact.php');
require_once('modules/Tasks/Task.php');
require_once('modules/LeadContacts/LeadContact.php');
require_once('modules/P1_Partners/P1_PartnersUtils.php');

// BEGIN jostrow customization
// Temporarily log memory consumption
require_once('scripts/jostrow_log_memory_usage.php');
// END jostrow customization

global $current_user;
$current_user = new User();
$current_user->getSystemUser();

global $current_language;

/* if the language is not set yet, then set it to the default language. */
if (isset($_SESSION['authenticated_user_language']) && $_SESSION['authenticated_user_language'] != '') {
    $current_language = $_SESSION['authenticated_user_language'];
} else {
    $current_language = $sugar_config['default_language'];
}

/* set module and application string arrays based upon selected language */
$app_strings = return_application_language($current_language);
if (!isset($modListHeader)) {
    if (isset($current_user)) {
        $modListHeader = query_module_access_list($current_user);
    }
}

/**** 
** THIS SCRIPT SENDS OUT A REMINDER EMAIL TO OPPORTUNITIES PARTNER CONTACT
** IF OPPORTUNITIES:ACCEPTED_BY_PARTNER_C = 'P' FOR MORE THAN 24 HOURS FROM THE TIME IT WAS SET
** THEN SEND OUT A REMINDER TO PARTNER CONTACT
** DEE CUSTOMIZATION
****/

$GLOBALS['log']->fatal("DEEPRMLOG: Starting siPartnerOppsSendReminder");

/****
** GET ALL PARTNER_CONTACT_ID'S FROM OPPORTUNITIES 
** WHERE ACCEPTED_BY_PARTNER_C = P FOR MORE THAN 24 HOURS FROM THE TIME IT WAS SET
** WE CHECK THE OPPORTUNITIES_AUDIT TABLE TO GET THE MOST RECENT DATE OF THE AUDIT ENTRY FOR WHEN ACCEPTED_BY_PARTNER_C IS SET TO P
** GET ONLY THOSE RECORDS WHERE PARTNER_CONTACT AND PARTNER_ASSIGNED_TO_C IS NOT EMPTY
** TO AVOID SENDING THE NOTIFICATION EVERY 24 HRS WE CHECK IF PARNTER_CONTACT_NOTIFIED IS SET
** PARNTER_CONTACT_NOTIFIED_C IS SET ONCE THE EMAIL IS SENT
****/

$query = "
SELECT DISTINCT opportunities_cstm.contact_id_c as opp_partner_contact 

FROM opportunities

INNER JOIN opportunities_cstm ON opportunities.id = opportunities_cstm.id_c
INNER JOIN opportunities_audit ON opportunities.id = opportunities_audit.parent_id
INNER JOIN contacts ON contacts.id = opportunities_cstm.contact_id_c

WHERE opportunities_cstm.accepted_by_partner_c = 'P'
AND (opportunities_cstm.partner_contact_notified_c IS NULL OR opportunities_cstm.partner_contact_notified_c = '')
AND opportunities_cstm.contact_id_c != ''
AND opportunities_cstm.partner_assigned_to_c !=  ''
AND opportunities_audit.field_name = 'accepted_by_partner_c'
AND opportunities_audit.after_value_string = 'P'
AND opportunities.sales_stage NOT IN ('Closed Lost', 'Closed Won', 'Finance Closed')
AND opportunities.deleted = 0
AND contacts.deleted = 0

GROUP BY opportunities.name

HAVING NOW() >= DATE_ADD(MAX(opportunities_audit.date_created), INTERVAL 1 DAY);
";

$opp_partner_contacts = array();
$partner_contacts = array();

/**** GET DB RESULTS FOR $query ****/
$result = $GLOBALS['db']->query($query);
if(!$result) {
	$GLOBALS['log']->fatal("DEEPRMLOG Error 100: Could not connect to the SugarInternal DB");
} else {
	while ($row = $GLOBALS['db']->fetchByAssoc($result)) {
		$opp_partner_contacts[] = $row['opp_partner_contact'];
	}
}

if(isset($opp_partner_contacts) && !empty($opp_partner_contacts)) {
/**** FOR EACH CONTACT THAT THE REMINDER SHOULD BE SENT TO ****/
foreach($opp_partner_contacts as $opp_partner_contact) {
	
	$thisContact = new Contact();
	$thisContact->retrieve($opp_partner_contact);
	$contact_opps = array();

	if(empty($thisContact->email1)) {
		$GLOBALS['log']->fatal("DEEPRMLOG Error 105: Cannot send PRM reminder email, missing contact email address for contact id {$thisContact->id}");
		continue;
	}

    $GLOBALS['log']->info("DEEPRMLOG: Get All Pending Opportunities Associated to the Partnet Contact " . $opp_partner_contact);
	/**** GET ALL PENDING OPPORTUNITIES ASSOCIATED TO THE PARTNER CONTACT. 
	** ALSO MAKE SURE PARTNER_ASSIGNED_TO_C IS SET TO MAKE SURE THIS OPP WAS ASSIGNED
	****/
	$contact_query = "
	SELECT opportunities.id as contact_opp
	,opportunities.name

	FROM opportunities

	INNER JOIN opportunities_cstm ON opportunities.id = opportunities_cstm.id_c

	WHERE opportunities_cstm.accepted_by_partner_c = 'P'
	AND opportunities_cstm.contact_id_c = '".$opp_partner_contact."'
	AND opportunities_cstm.partner_assigned_to_c !=  ''
	AND opportunities.deleted = 0;
	";
	
	/**** GET DB RESULTS FOR $contact_query ****/
	$contact_result = $GLOBALS['db']->query($contact_query);
	if(!$contact_result) {
        	$GLOBALS['log']->fatal("DEEPRMLOG Error 101: Could not connect to the SugarInternal DB");
	} else {
        	while ($contact_row = $GLOBALS['db']->fetchByAssoc($contact_result)) {
                	$contact_opps[] = $contact_row['contact_opp'];
        	}
	}

	/****
	** EMAIL VARIABLES
	** TODO: PULL EMAIL OBJ ASSOCIATED TO OPP 
	****/
	$email_subject = "Reminder: Please review your Open Opportunities";

	$email_body = '
		<table style="border-left: 1px solid #cccccc; border-right: 1px solid #cccccc; border-bottom: 1px solid #cccccc; font-size: 12px; font-family: arial,verdana,helvetica,sans-serif; line-height: 16px; width: 600px;" border="0" cellspacing="0" cellpadding="0" align="center"><tbody><tr><td><a style="color: #9D0C0B;" href="http://www.sugarcrm.com"><img src="http://media.sugarcrm.com/newsletter/SugarCRMheader.jpg" border="0" alt="SugarCRM" width="600" height="200" /></a></td></tr><tr><td style="padding: 20px 30px 40px 60px;" width="600">
	';
	$email_body .= '
		<p>Dear '.$thisContact->first_name.',</p>
<p>This is to remind you that your opportunities are pending review. </p>
<p>You can review immediately by following these simple steps:</p>
<ol>
<li style="margin-bottom: 10px;"><a style="color: #9D0C0B;" href="http://www.sugarcrm.com/crm/partners/partner_portal">Login to the Partner Portal</a> using your SugarCRM.com account credentials. </li>
<li style="margin-bottom: 10px;">Once logged in, click on the "Sales" link.</li>
<li style="margin-bottom: 10px;">If you are already logged into the SugarCRM Partner Portal in your browser, you can simply <a style="color: #9D0C0B;" href="http://www.sugarcrm.com/crm/partners/partner_portal/sales">follow this link</a> to review your opportunities.</li>
<li style="margin-bottom: 10px;">If the above link does not work, please copy and paste the following URL in your browser - <a style="color: #9D0C0B;" href="http://www.sugarcrm.com/crm/partners/partner_portal/sales">http://www.sugarcrm.com/crm/partners/partner_portal/sales</a>.</li>
</ol>
<p>Best regards, </p>
<p>- SugarCRM</p>
	';
	$email_body .= '</td></tr></tbody></table>';

    	$GLOBALS['log']->info("DEEPRMLOG: Running P1_PartnerUtils::FetchCVSExportForPrmEmail with " . var_export($contact_opps, true));
	$csv_contents = P1_PartnerUtils::getCsvExportForPrmEmail($contact_opps);
    	$GLOBALS['log']->info("DEEPRMLOG: Running P1_PartnerUtils::sendPRMEmail with " . $opp_partner_contact); 
	$email_object = P1_PartnerUtils::sendPRMEmail($opp_partner_contact, $email_subject, $email_body, $csv_contents);
	$GLOBALS['log']->info("DEEPRMLOG: Sent email to contact id - {$opp_partner_contact}");
	foreach($contact_opps as $contact_opp) {
		$opp_update_query = "UPDATE opportunities_cstm SET partner_contact_notified_c = '1' where id_c = '{$contact_opp}'";
                $GLOBALS['db']->query($opp_update_query);
		
		//Link the email sent to the opportunity.
        	$relate_email = clone $email_object;
       		$relate_email->parent_id = $contact_opp;
        	$relate_email->save(FALSE);
        	$relate_email->load_relationship('opportunities');
        	$relate_email->opportunities->add($contact_opp);
		$GLOBALS['log']->info("DEEPRMLOG: Updated opportunity id - {$contact_opp}");
        	unset($relate_email, $contact_opp);
	}
    	unset($contact_opps, $thisContact);
    	unset($email_subject, $email_body, $csv_contents, $email_object);
}

$GLOBALS['log']->info("DEEPRMLOG: Ending siPartnerOppsSendReminder - Memory Used: " . memory_get_usage());
}
$c_output = trim(ob_get_clean());
if ($c_output) {
    echo $c_output;
}

