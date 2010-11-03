<?php
/*
** @author: Jesse Mullan, Julian Ostrow, Sadek Baroudi
** SUGARINTERNAL CUSTOMIZATION
** ITRequest #: 9615, 9620
** Description: designed to e-mail customers when their Subscription renewal is due
** Wiki customization page: http://internalwiki.sjc.sugarcrm.pvt/index.php/Moofcart/SiFindOpenOpps.php
*/

ob_start();
chdir('../..');
define('sugarEntry', true);
define('give_me_a_pdf', 1);

$renewal_contact_fieldname = 'contact_id3_c';

require_once('/var/www/sugarinternal/sugarinternal_lib/SI.class.php');

$do_log = true;
$dry_run = false;


if ($do_log) {
# For all your logging needs
    require_once('/var/www/sugarinternal/sugarinternal_lib/SILog.class.php');
    $logfile = 'siFindOpenOpps.php.log';
}

require_once('include/entryPoint.php');
require_once('modules/EmailTemplates/EmailTemplate.php');
require_once('include/SugarPHPMailer.php');
require_once("modules/Administration/Administration.php");
require_once('modules/Emails/Email.php');
require_once('modules/Accounts/Account.php');
require_once('modules/Users/User.php');
require_once('modules/Quotes/Quote.php');
require_once('modules/Quotes/EmailPDF.php');
require_once('modules/Quotes/Layouts.php');
require_once('custom/si_custom_files/MoofCartHelper.php');

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

/******

$customer_subject = 'Your SugarCRM Account Renewal';
$customer_template = "
Dear CUSTOMER_NAME,

Thank you for your continued interest in SugarCRM products and services. We appreciate your business.

This is your renewal notice. <strong>Your OPPORTUNITY_TYPE subscription is set to expire on EXPIRATION_DATE. Of course, you can renew ahead of time to avoid a service interruption.

<strong>Please ask me about our special offers for multi-year renewals.</strong>

The attached quotation is for a one-year renewal. You can renew right away by following these simple steps:

1. Login to http://www.sugarcrm.com/sugarshop/ using the SugarCRM account credentials you established when you first purchased Sugar.

2. Once you have logged in, initiate your renewal by following the instructions in the 'Your Subscription' area.

3. Proceed with checkout and submit payment

4. When payment is confirmed we will update your account and follow up with you

DISCOUNT

When payment is confirmed we will extend your subscription and you will receive a confirmation email.

Please let me know if you have any questions and I will be happy to help.


Best Regards,
ASSIGNED_REP_NAME
ASSIGNED_REP_TITLE
ASSIGNED_REP_EMAIL
ASSIGNED_REP_OFFICE_PHONE
Fax: 408-877-1802
";

$customer_template_html = '
<table style="border-right: #ccc 1px solid; border-left: #ccc 1px solid; border-bottom: #ccc 1px solid; font-size: 12px; font-family: arial, verdana, helvetica, sans-serif; line-height: 16px; width: 600px" cellspacing="0" cellpadding="0" width="600" align="center" border="0">
<tr>
<td><a href="http://www.sugarcrm.com" style="color: #9D0C0B;"><img src="http://media.sugarcrm.com/newsletter/SugarCRMheader.jpg" width="600" height="200" alt="SugarCRM"  border="0" /></a></td>
</tr>
<tr>
<td style="padding: 20px 30px 40px 60px;">
<p>Dear CUSTOMER_NAME,</p>
<p>Thank you for your continued interest in SugarCRM products and services. We appreciate your business.</p>
<p>This is your renewal notice. <strong>Your OPPORTUNITY_TYPE subscription is set to expire on EXPIRATION_DATE.</strong> Of course, you can renew ahead of time to avoid a service interruption.</p>
<p><strong>Please ask me about our special offers for multi-year renewals.</strong></p>
<p>The attached quotation is for a one-year renewal. You can renew right away by following these simple steps:</p>
<ol>
<li style="margin-bottom: 10px;"><a href="http://www.sugarcrm.com/sugarshop/" style="color: #9D0C0B;">Login to www.sugarcrm.com</a> using the SugarCRM account credentials you established when you first purchased Sugar.</li>
<li style="margin-bottom: 10px;">Once you have logged in, initiate your renewal by following the instructions in the 'Your Subscription' area.<br /><br />
If you are interested in extended or premium support, please contact me so we can go over your options.</li>
<li style="margin-bottom: 10px;">Proceed with checkout and submit payment.</li>
</ol>
<p>DISCOUNT</p>
<p>When payment is confirmed we will extend your subscription and you will receive a confirmation email.</p>
<p>Please let me know if you have any questions and I will be happy to help.</p>
<br />
<p>Best regards,</p>
<p><strong>ASSIGNED_REP_NAME</strong><br />
ASSIGNED_REP_TITLE<br />
<a href="mailto:ASSIGNED_REP_EMAIL" style="color: #9D0C0B;">ASSIGNED_REP_EMAIL</a><br />
1.408.454.6900</p>
</td>
</tr>
</table>
<table border="0" cellspacing="0" cellpadding="0" width="600" align="center">
<tbody>
<tr>
<td style="padding: 10px 0pt 0pt 60px; font-size: 11px; color: #666666; font-family: Arial,Verdana,Helvetica,sans-serif">&copy; SugarCRM Inc. If you choose not to receive SugarCRM sent emails, you can <A style="color: #666666" href="{optout}">unsubscribe</A> or email <A style="color: #666666" href="mailto:news@sugarcrm.com">news@sugarcrm.com</A> or mail 10050 N. Wolfe Rd. SW2-130, Cupertino CA 95014, USA or call +1 408.454.6900.</td>
</tr>
</tbody>
</table>
';

$partner_subject = 'SugarCRM Account Renewal';
$partner_template = "
MISSING_PARTNER_NOTICE
Dear PARTNER_NAME,

The OPPORTUNITY_TYPE subscription for CUSTOMER_NAME is nearing renewal and will expire on EXPIRATION_DATE.  Of course, you can renew ahead of time to avoid a service interruption, but please renew at least one week prior to the expiration.  If you notice a change in the number of users quoted, this is based on the customer's current usage patterns.  When you are ready to renew, please follow the steps below:

1. Login to http://www.sugarcrm.com/ (if you do not have an account, call me and I will help you create one)

2. Navigate to the Sales area of the Partner Portal:

http://www.sugarcrm.com/crm/partners/partner_portal/sales

3. Locate your customer on the My Customers page and initiate their renewal from the 'Subscriptions' area

4. When payment is confirmed we will extend your customer's subscription and you will receive a confirmation email

Please let me know if you have any questions and I will be happy to help.

DISCOUNT

Best Regards,
ASSIGNED_REP_NAME
ASSIGNED_REP_TITLE
ASSIGNED_REP_EMAIL
ASSIGNED_REP_OFFICE_PHONE
Fax: 408-877-1802

";

$customer_template_discount = "

A discount has been applied to your order. Please see your sales representive for terms and conditions of this discount. Discounts may expire before the attached quote.

";

$missing_partner_notice = '
We could not find a Primary Business Contact for this Account (PARTNER_ACCOUNT_NAME).

Please forward this e-mail to the proper Contact at PARTNER_ACCOUNT_NAME. To avoid this message in the future, please update the Account and choose at least one Contact as the Primary Business Contact.

LINK_TO_PARTNER_ACCOUNT
';
******/

/*
 * TODO: Change logging level before going into production, but probably after
 * testing.
 */

$GLOBALS['log']->fatal("----->siFindOpenOpps.php started");
	
$ranges = array(array(0, 5),
                array(5, 15),
                array(15, 30),
                array(30, 60),
                array(60, 61));
foreach ($ranges as $range) {
    $start = $range[0];
    $end = $range[1];
    /*
     * TODO: Create renewal_notified_c field in opps module to hold number of last
     * notification sent.  The mailing process will add in the end value so opps
     * are not notified more than once for any given period.
     *
     * Studio appears to be down on moofcart-si.
     *
     * This query is as yet untested.
     */
    $queries[] = "
SELECT
    `opportunities`.`id` AS `opportunities_id`,
    '$end' AS `label`
FROM
    `opportunities`
LEFT JOIN
    `opportunities_cstm` ON `opportunities`.`id` = `opportunities_cstm`.`id_c`
LEFT JOIN
	`accounts_opportunities` ON `opportunities`.`id` = `accounts_opportunities`.`opportunity_id`
LEFT JOIN
	`accounts_cstm` ON `accounts_opportunities`.`account_id` = `accounts_cstm`.`id_c`
WHERE (
    `opportunities`.`date_closed` >= DATE_ADD(CURDATE(), INTERVAL $start DAY)
    AND `opportunities`.`date_closed` < DATE_ADD(CURDATE(), INTERVAL $end DAY)
    AND `opportunities_cstm`.`Revenue_Type_c` = 'Renewal'
	AND `opportunities_cstm`.`Term_c` = 'Annual'
    AND `opportunities`.`sales_stage` IN('Qualified', 'Interested_Prospect', 'Proposal_Delivered', 'Negotiation', 'Verbal')
	AND `opportunities_cstm`.`opportunity_type` IN ('sugar_ent_converge','sugar_pro_converge','Sugar Enterprise','Sugar Professional','Sugar Enterprise On-Demand','Sugar OnDemand')
    AND `opportunities`.`deleted` = 0
    AND (`opportunities_cstm`.`renewal_notified_c` IS NULL
         OR `opportunities_cstm`.`renewal_notified_c` > $end)
    AND `accounts_opportunities`.`deleted` = 0
    AND `accounts_cstm`.`auto_send_renewal_emails_c` = 1
)
";
}

$opps = array();
foreach ($queries as $query) {
    $res = $GLOBALS['db']->query($query);
    if (!$res) {
        /*
         * If there is a problem with the query, quick early and make a lot of
         * noise in the logs.
         */
        $GLOBALS['db']->checkError('Error with query');
        $GLOBALS['log']->fatal("----->siFindOpenOpps.php query failed: " . $query);
        exit;
    } else {
        /*
         * The query did not bomb, so put all of the opportunity ids into an
         * array for future processing.
         */
        while ($row = $GLOBALS['db']->fetchByAssoc($res)) {
            $opps[$row['opportunities_id']] = $row['label'];
        }
    }
}
if (false && $do_log) { SILog::appendStringToFile($logfile, count($opps) . ' opps found'); }

/*
 * Each opportunity is now loaded as a SugarBean so it can be poked and prodded
 * to retrieve the attached contacts and partners.
 */
foreach ($opps as $opp_id => $time_period) {
    
    $opportunity = new Opportunity();
    $opportunity->retrieve($opp_id);
    
    $opportunity->load_relationship('accounts');
    $accounts = $opportunity->get_linked_beans('accounts', 'Account');

    $opportunity_type = 'SugarCRM';
    if (!empty($GLOBALS['app_list_strings']['opportunity_type_dom'][$opportunity->opportunity_type])) {
	$opportunity_type = $GLOBALS['app_list_strings']['opportunity_type_dom'][$opportunity->opportunity_type];
    }

    $assigned_user = null;
    if (!empty($opportunity->assigned_user_id)) {
	$assigned_user = new User();
	$assigned_user->retrieve($opportunity->assigned_user_id);
    }
    if (!$assigned_user) {
	if (false && $do_log) {
	    SILog::appendStringToFile($logfile, "OPP $opp_id NO ASSIGNED USER");
        }
	continue;
    }
    
    $contact = null;
    $contacts = array();
    $account_id = null;
    if ($accounts) {
	foreach ($accounts as $account) {
	    if ($account->$renewal_contact_fieldname) {
		$account_id = $account->id;
		$contact = new Contact();
		$contact->retrieve($account->$renewal_contact_fieldname);
		$contacts[] = $contact;
		break;
	    }
	}
    }
    if (!$contacts) {
	$opportunity->load_relationship('contacts');
	$contacts = $opportunity->get_linked_beans('contacts', 'Contact');
    }
    if (!$contacts && !$opportunity->partner_assigned_to_c) {
	if ($do_log) {
	    SILog::appendStringToFile($logfile, "OPP $opp_id NO CONTACTS AND NO ASSIGNED PARTNER");
	}
	continue;
    }
    $partner = null;
    $partner_contacts = array();
    $send_to_sugar_instead = false;
    if (!empty($opportunity->partner_assigned_to_c)) {
#echo "Found partner " . $opportunity->partner_assigned_to_c . "\n";
	$partner = new Account();
	$partner->retrieve($opportunity->partner_assigned_to_c);
	$partner->load_relationship('contacts');
	$possible_contacts = $partner->get_linked_beans('contacts', 'Contact');
	foreach ($possible_contacts as $contact) {
	    if (1 == $contact->primary_business_c || 1 == count($possible_contacts)) {
		$contact->load_relationship('contactemail_addresses');
		$emails = $contact->get_linked_beans('email_addresses', 'EmailAddress');
		foreach ($emails as $email) {
		    if (1 != $email->invalid_email && 1 != $email->opt_out) {
			/*
			 * We only check to see if there is a valid and
			 * non-opted out email address.
			 */
			$partner_contacts[] = $contact;
			break;
		    }
		}
		break;
	    }
	}
	if (!$partner_contacts) {
	    $send_to_sugar_instead = true;
	}
    }
    
    if (!$dry_run) {
	$quote = new Quote();
	$success = MoofCartHelper::createQuoteForRenewal($opportunity, $quote);
	if (!$success || empty($quote->id)) {
	    if (false && $do_log) {
		SILog::appendStringToFile($logfile, "OPP $opp_id NO QUOTE");
	    }
	    continue;
	}
	$quote->assigned_user_id = $assigned_user->id;
	$quote->new_with_id = false;
	$quote->save();
	$quote_id = $quote->id;
	
	/* Retrieve a pristine copy of the quote */
	$quote = new Quote();
	$quote->retrieve($quote_id);
	
	$opportunity->load_relationship('quotes');
	$opportunity->quotes->add($quote_id);
    }
    
    $admin = new Administration();
    $admin->retrieveSettings();

    if ($partner_contacts || $send_to_sugar_instead) {
	if ($send_to_sugar_instead) {
	    $replacements = array('MISSING_PARTNER_NOTICE' => $missing_partner_notice,
				  'PARTNER_ACCOUNT_NAME' => $partner->name,

				// BEGIN jostrow customization
				// See ITRequest #10854
				// Instead of displaying the Opportunity Name, we're displaying only the Partner Name and (Customer) Account Name

				  'CUSTOMER_NAME' => $partner->name . ' - ' . $account->name,

				// END jostrow customization

				  'PARTNER_NAME' => 'NO PARTNER CONTACT',
				  'OPPORTUNITY_TYPE' => $opportunity_type,
				  'DISCOUNT' => (!empty($opportunity->discount_code_c)
						 ? $customer_template_discount : ''),
				  'EXPIRATION_DATE' => date('Y-m-d', strtotime($opportunity->date_closed)),
				  'ASSIGNED_REP_NAME' => $assigned_user->name,
				  'ASSIGNED_REP_TITLE' => $assigned_user->title,
				  'ASSIGNED_REP_EMAIL' => $assigned_user->email1,
				  'ASSIGNED_REP_OFFICE_PHONE' => $assigned_user->phone_work,
				  'LINK_TO_PARTNER_ACCOUNT'
				  => (!empty($opportunity->partner_assigned_to_c)
				      ? 'https://sugarinternal.sugarondemand.com/index.php?module=Accounts&action=DetailView&record='
				      . $opportunity->partner_assigned_to_c : '')

				  );
	    $body = MoofCartHelper::getRenewalEmail($replacements, 'partner');
	    
	    $customer_template_data = array('subject' => MoofCartHelper::$partner_subject,
					    'body' => $body);
#echo "partner email\n";
	    if (!$dry_run) {
		$email_bean = new Email();
		$email_bean->id = create_guid();
		$email_bean->new_with_id = true;
		$email_bean->parent_type = 'Opportunities';
		$email_bean->parent_id = $opportunity->id;
	    
		$email_bean->name = $customer_template_data['subject'];
	    
		$email_bean->from_addr = $assigned_user->email1;
		$email_bean->from_name = $assigned_user->first_name . " " . $assigned_user->last_name;
		$email_bean->to_addrs_arr = array();
		$email_bean->bcc_addrs_arr = array();
		$email_bean->to_addrs_arr[] = array('email' => $assigned_user->email1,
						    'name' => $assigned_user->first_name . " " . $assigned_user->last_name);
		$email_bean->to_addrs = $assigned_user->email1;
		$email_bean->cc_addrs_arr = array();
		$email_bean->description = $customer_template_data['body'];
	    
		$email_bean->date_sent = date('Y-m-d h:i:s');
		$email_bean->assigned_user_id = $assigned_user->id;
	    
		$email_bean->save(false);
		$email_bean->new_with_id = false;	
	    
#echo "adding contact\n";
		#$email_bean->load_relationship('contacts');
		#$email_bean->contacts->add($partner_contact->id);
	    
		$email_bean->load_relationship('users');
		$email_bean->users->add($assigned_user->id);
	    
		$email_bean->load_relationship('opportunities');
		$email_bean->opportunities->add($opportunity->id);
	    
		$email_bean->load_relationship('quotes');
		$email_bean->quotes->add($quote_id);
	    
		$partner->load_relationship('emails');
		$partner->emails->add($email_bean->id);
		$partner->save();
#echo "saving partner\n";
	    
		$email_bean->saved_attachments = array();
#echo "saving email\n";
	    
		global $focus;
		global $mod_strings;
		global $current_language;
	    
		$focus = $quote;
		$mod_strings = return_module_language($current_language, 'Quotes');
		$file_name = get_quote_pdf('Proposal_Terms');

		$note = new Note();
		$note->filename = $file_name;
		$note->team_id = "";
		$note->file_mime_type = "application/pdf";
		$note->name = $mod_strings['LBL_EMAIL_ATTACHMENT'] . $file_name;
	    
		//save the pdf attachment note
		$note->parent_id = $email_bean->id;
		$note->parent_type = "Emails";
		$note->save(false);
	    
#echo "email " . $email_bean->id . "\n";
	    
		$email_bean->saved_attachments[] = $note;
	    
		$source = $GLOBALS['sugar_config']['upload_dir'] . $file_name;
		$destination = $GLOBALS['sugar_config']['upload_dir'] . $note->id;
	    
		if (!rename($source, $destination)) {
		    $msg = str_replace('$destination', $destination, $mod_strings['LBL_RENAME_ERROR']);
		    if ($do_log) {
			SILog::appendStringToFile($logfile, "MOVE FILE FAIL\n\tOpp $opp_id\n\y$source $destination");
		    }
		    $GLOBALS['log']->fatal("siFindOpenOpps.php $msg");
		    echo "Error: $msg;";
		    break;
		}
	    
		$email_bean->load_relationship('notes');
		$email_bean->notes->add($note->id);
		$email_bean->save(false);
	    
		$success = $email_bean->Send();
		$email_bean->save(false);	    
	    }	    
	    if ($do_log) {
		SILog::appendStringToFile($logfile,
					  ($send_to_sugar_instead ? 'Sugar' : 'Partner')
					  . "\n\tOpp $opp_id"
					  . "\n\tAcc $account_id"
					  . ($send_to_sugar_instead ? "\n\tSugar" : "\n\tContact")
					  . "\n\tAssigned " . $assigned_user->user_name
					  . "\n\tEmail" . $customer_template_data['subject']
					  . "\n\t" . $customer_template_data['body']
					  . "\n\n");
	    }
	} else {
	    foreach ($partner_contacts as $partner_contact) {
		$replacements = array(
                // BEGIN jostrow customization
                // See ITRequest #10854
                // Instead of displaying the Opportunity Name, we're displaying only the Partner Name and (Customer) Account Name

                  'CUSTOMER_NAME' => $partner->name . ' - ' . $account->name,

                // END jostrow customization

				      'PARTNER_NAME' => $partner_contact->name,
				      'OPPORTUNITY_TYPE' => $opportunity_type,
				      'DISCOUNT' => (!empty($opportunity->discount_code_c)
						     ? $customer_template_discount : ''),
				      'EXPIRATION_DATE' => date('Y-m-d', strtotime($opportunity->date_closed)),
				      'ASSIGNED_REP_NAME' => $assigned_user->name,
				      'ASSIGNED_REP_TITLE' => $assigned_user->title,
				      'ASSIGNED_REP_EMAIL' => $assigned_user->email1,
				      'ASSIGNED_REP_OFFICE_PHONE' => $assigned_user->phone_work,
				      'MISSING_PARTNER_NOTICE' => $send_to_sugar_instead ? $missing_partner_notice : '',
				      'LINK_TO_PARTNER_ACCOUNT' => ($send_to_sugar_instead && $opportunity->partner_assigned_to_c)
				      ? 'https://sugarinternal.sugarondemand.com/index.php?module=Accounts&action=DetailView&record='
				      . $opportunity->partner_assigned_to_c : ''
				      
				      );

		$body = MoofCartHelper::getRenewalEmail($replacements,'partner');

		$customer_template_data = array('subject' => MoofCartHelper::$partner_subject,
						'body' => $body);
#echo "partner email\n";
		if (!$dry_run) {
		    $email_bean = new Email();
		    $email_bean->id = create_guid();
		    $email_bean->new_with_id = true;
		    $email_bean->parent_type = 'Opportunities';
		    $email_bean->parent_id = $opportunity->id;
	
		    $email_bean->name = $customer_template_data['subject'];
	
		    $email_bean->from_addr = $assigned_user->email1;
		    $email_bean->from_name = $assigned_user->first_name . " " . $assigned_user->last_name;
		    $email_bean->to_addrs_arr = array();
		    $email_bean->bcc_addrs_arr = array();
		    if ($send_to_sugar_instead) {
			$email_bean->to_addrs_arr[] = array('email' => $assigned_user->email1,
							    'name' => $assigned_user->first_name . " " . $assigned_user->last_name);
			$email_bean->to_addrs = $assigned_user->email1;
		    } else {
			$email_bean->to_addrs_arr[] = array('email' => $partner_contact->email1,
							    'name' => $partner_contact->first_name . " " . $partner_contact->last_name);
			$email_bean->to_addrs = $partner_contact->email1;
		
			$email_bean->bcc_addrs_arr[] = array('email' => $assigned_user->email1,
							     'name' => $assigned_user->first_name . " " . $assigned_user->last_name);
			$email_bean->bcc_addrs = $assigned_user->email1;
		    }
		    $email_bean->cc_addrs_arr = array();
		    $email_bean->description = $customer_template_data['body'];

		    $email_bean->date_sent = date('Y-m-d h:i:s');
		    $email_bean->assigned_user_id = $assigned_user->id;

		    $email_bean->save(false);
		    $email_bean->new_with_id = false;	

#echo "adding contact\n";
		    $email_bean->load_relationship('contacts');
		    $email_bean->contacts->add($partner_contact->id);

		    $email_bean->load_relationship('users');
		    $email_bean->users->add($assigned_user->id);

		    $email_bean->load_relationship('opportunities');
		    $email_bean->opportunities->add($opportunity->id);

		    $email_bean->load_relationship('quotes');
		    $email_bean->quotes->add($quote_id);

		    $partner->load_relationship('emails');
		    $partner->emails->add($email_bean->id);
		    $partner->save();
#echo "saving partner\n";
	
		    $email_bean->saved_attachments = array();
#echo "saving email\n";
	
		    global $focus;
		    global $mod_strings;
		    global $current_language;
	
		    $focus = $quote;
		    $mod_strings = return_module_language($current_language, 'Quotes');
		    $file_name = get_quote_pdf('Proposal_Terms');
	
		    $note = new Note();
		    $note->filename = $file_name;
		    $note->team_id = "";
		    $note->file_mime_type = "application/pdf";
		    $note->name = $mod_strings['LBL_EMAIL_ATTACHMENT'] . $file_name;

		    //save the pdf attachment note
		    $note->parent_id = $email_bean->id;
		    $note->parent_type = "Emails";
		    $note->save(false);

#echo "email " . $email_bean->id . "\n";
	
		    $email_bean->saved_attachments[] = $note;

		    $source = $GLOBALS['sugar_config']['upload_dir'] . $file_name;
		    $destination = $GLOBALS['sugar_config']['upload_dir'] . $note->id;
	
		    if (!rename($source, $destination)) {
			$msg = str_replace('$destination', $destination, $mod_strings['LBL_RENAME_ERROR']);
			if ($do_log) {
			    SILog::appendStringToFile($logfile, "MOVE FILE FAIL\n\tOpp $opp_id\n\y$source $destination");
			}
			$GLOBALS['log']->fatal("siFindOpenOpps.php $msg");
			echo "Error: $msg;";
			break;
		    }
	
		    $email_bean->load_relationship('notes');
		    $email_bean->notes->add($note->id);
		    $email_bean->save(false);
	
		    $success = $email_bean->Send();
		    $email_bean->save(false);
		}
		if ($do_log) {
		    SILog::appendStringToFile($logfile,
					      ($send_to_sugar_instead ? 'Sugar' : 'Partner ' . $partner_contact->name)
					      . "\n\tOpp $opp_id"
					      . "\n\tAcc $account_id"
					      . ($send_to_sugar_instead ? "\n\tSugar" : "\n\tContact " . $partner_contact->id)
					      . "\n\tAssigned " . $assigned_user->user_name
					      . "\n\tEmail" . $customer_template_data['subject']
					      . "\n\t" . $customer_template_data['body']
					      . "\n");
		}
	    }
	}
    } else {
	foreach ($contacts as $contact) {
	    $replacements = array('CUSTOMER_NAME' => $contact->name,
				  'OPPORTUNITY_TYPE' => $opportunity_type,
				  'DISCOUNT' => (!empty($opportunity->discount_code_c)
						 ? $customer_template_discount : ''),
				  'EXPIRATION_DATE' => date('Y-m-d', strtotime($opportunity->date_closed)),
				  'ASSIGNED_REP_NAME' => $assigned_user->name,
				  'ASSIGNED_REP_TITLE' => $assigned_user->title,
				  'ASSIGNED_REP_EMAIL' => $assigned_user->email1,
				  'PARTNER_NAME' => '',
				  'ASSIGNED_REP_OFFICE_PHONE' => $assigned_user->phone_work,
				  'MISSING_PARTNER_NOTICE' => '',
				  'LINK_TO_PARTNER_ACCOUNT' => '');
	    
	    $html = true;
	    if ($html) {
			$body = MoofCartHelper::getRenewalEmail($replacements,'html');
	    } else {
			$body = MoofCartHelper::getRenewalEmail($replacements,'txt');
	    }
	    

	    
	    $customer_template_data = array('subject' => MoofCartHelper::$customer_subject,
					    'body' => $body);
	    if (!$dry_run) {
		$email_bean = new Email();
		$email_bean->id = create_guid();
		$email_bean->new_with_id = true;
		$email_bean->parent_type = 'Opportunities';
		$email_bean->parent_id = $opportunity->id;
	
		$email_bean->name = $customer_template_data['subject'];
	
		$email_bean->from_addr = $assigned_user->email1;
		$email_bean->from_name = $assigned_user->first_name . " " . $assigned_user->last_name;
		$email_bean->to_addrs_arr = array();
		$email_bean->to_addrs_arr[] = array('email' => $contact->email1,
						    'name' => $contact->first_name . " " . $contact->last_name);
		$email_bean->to_addrs = $contact->email1;
	
		$email_bean->bcc_addrs_arr = array();
		$email_bean->bcc_addrs_arr[] = array('email' => $assigned_user->email1,
						     'name' => $assigned_user->first_name . " " . $assigned_user->last_name);
		$email_bean->bcc_addrs = $assigned_user->email1;
	
		$email_bean->cc_addrs_arr = array();
		$email_bean->description_html = $customer_template_data['body'];

		$email_bean->date_sent = date('Y-m-d h:i:s');
		$email_bean->assigned_user_id = $assigned_user->id;

		$email_bean->save(false);
		$email_bean->new_with_id = false;

		$email_bean->load_relationship('contacts');
		$email_bean->contacts->add($contact->id);

		$email_bean->load_relationship('users');
		$email_bean->users->add($assigned_user->id);

		$email_bean->load_relationship('opportunities');
		$email_bean->opportunities->add($opportunity->id);

		$email_bean->load_relationship('quotes');
		$email_bean->quotes->add($quote_id);
	
		$email_bean->save(false);
		$email_bean->saved_attachments = array();
	    
		global $focus;
		global $mod_strings;
		global $current_language;
	
		$focus = $quote;
		$mod_strings = return_module_language($current_language, 'Quotes');
		$file_name = get_quote_pdf('Proposal_Terms');
	
		$note = new Note();
		$note->filename = $file_name;
		$note->team_id = "";
		$note->file_mime_type = "application/pdf";
		$note->name = $mod_strings['LBL_EMAIL_ATTACHMENT'] . $file_name;

		//save the pdf attachment note
		$note->parent_id = $email_bean->id;
		$note->parent_type = "Emails";
		$note->save(false);

		$email_bean->saved_attachments[] = $note;

		$source = $GLOBALS['sugar_config']['upload_dir'] . $file_name;
		$destination = $GLOBALS['sugar_config']['upload_dir'] . $note->id;
	
		if (!rename($source, $destination)) {
		    $msg = str_replace('$destination', $destination, $mod_strings['LBL_RENAME_ERROR']);
		    $GLOBALS['log']->fatal("siFindOpenOpps.php $msg");
		    if ($do_log) {
			SILog::appendStringToFile($logfile, "MOVE FILE FAIL\n\tOpp $opp_id\n\y$source $destination");
		    }
		    break;
		}

		$email_bean->load_relationship('notes');
		$email_bean->notes->add($note->id);
		$email_bean->save(false);
		$success = $email_bean->Send();
		$email_bean->save(false);
	    }
	    
	    if ($do_log) {
		SILog::appendStringToFile($logfile,
					  $contact->name
					  . "\n\tOpp $opp_id"
					  . "\n\tAcc $account_id"
					  . "\n\tContact " . $contact->id
					  . "\n\tAssigned " . $assigned_user->user_name
					  . "\n\tEmail" . $customer_template_data['subject']
					  . "\n\t" . $customer_template_data['body']
					  . "\n");
	    }
	}
    }
#echo "\n\t";
#echo $opp_id;
#echo "\n";

    if (!$dry_run) {
	$opportunity->renewal_notified_c = $time_period;
	$opportunity->save();
    }

    if ($contacts) {
#exit;
    }
    
    if ($partner) {
#exit;
    }
}

$GLOBALS['log']->fatal("----->siFindOpenOpps.php finished");

/* Suck up any weird SI output */
$whatevs = trim(ob_get_clean());
if ($whatevs) {
    echo $whatevs;
}
