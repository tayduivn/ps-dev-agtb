<?php
if (!defined('sugarEntry') || !sugarEntry) die('Not A Valid Entry Point');
/*
** @author: jwhitcraft
** SUGARINTERNAL CUSTOMIZATION
** Description: Partner Lead Conflict Project
** Wiki customization page: http://internalwiki.sjc.sugarcrm.pvt/index.php/Partner_Lead_Conflict_Project
*/
class ScrubRouting
{

    /**
     * Enable and Disable SysLog Output
     *
     * @param boolean
     */
    protected $_enableSysLog = false;

    /**
     * The string that contain the log message to be saved to the touchpoint
     *
     * @param string
     */
    protected $_logMessage = "";

    /**
     * The Account for the Touchpoint
     *
     * @param Account SugarBean
     */
    protected $account;

    /**
     * @param Touchpoint SugarBean
     */
    protected $touchpoint;

    /**
     * SugarIntenral Users that belong to Inside Sales
     *
     * @param array
     */
    protected $_insideSalesTeam = array();

    /**
     * Valid Campaigns for which this should run
     *
     * @param array
     */
    protected $_validCampaigns = array(
        '4411141c-950c-02dc-f4fc-43742c76ec5a', //Partner Lead Form
        '4901abce-e530-4ae6-0475-4c094d6db5ea', //WebCast: Cold Calling Hot Leads for ApexTwo
        '6f2a5f2c-bbfa-0399-1a5e-4c4e2f7992cd', //Webcast: LimaCRM
    	'c6b1a63e-e2d4-035f-be96-4c619866d907', //Webcast LPS Consulting Aug 2010
    	'3ae0c9de-8843-d677-79c5-4c5a6ea7b037', //CRM Accel Chicago Dec 2009
	'3a3408c0-5336-d40e-7945-4c5a6e5d4c2f', //CRM Accel Singapore Dec 200
	'5951e4c6-ccda-9e03-8156-4c5a6e1d413e', //CRM Accel Sydney Feb 2010
	'4c7a6187-01e8-dfcc-1ce1-4c5a6e8d611c', //CRM Accel Melbourne Feb 2010
	'1703997c-2a1f-2466-7b34-4c5a6ebed100', //CRM Accel Mumbai Feb 2010
	'73490952-af53-6928-5d28-4c5a6f5ed1fc', //CRM Accel Bangalore Feb 2010
	'f38a7ecc-8a31-2bc6-76d7-4c5a6f2681de', //CRM Accel Atlanta Mar 2010
	'b20cbbcc-eccf-06de-b1fc-4c5a6ffe115f', //CRM Accel Boston Mar 2010
	'3e802ec4-4c83-a6ae-5bf0-4c5a6fec312a', //CRM Accel New York May 2010
	'a6601bb9-3130-7f77-569b-4c5a6f5771bc', //CRM Accel Los Angeles May 2010
	'e0070928-948a-1077-084e-4c5a6f4d5b07', //CRM Accel Malaysia Jun 2010
	'546e4e4c-b337-e509-a739-4c5a6f2ce651', //CRM Accel Singapore Jun 2010
	'714761ad-7a87-b6b9-f008-4c5a6f71b818', //CRM Accel Hong Kong Jun 2010
	'b59a1eae-7085-8526-4f28-4c5a6f455546', //CRM Accel DC Jun 2010
	'630915b2-1f56-93c7-7717-4c5a6fa5014b', //CRM Accel Dallas Jun 2010
	'4fad454b-1fd5-bd79-10dd-4c5a6fbbc20b', //CRM Accel Montreal Jun 2010
	'5e79e91a-b215-54c3-2cf0-4c5a706e5b8c', //CRM Accel Toronto Jun 2010
	'dfb00ca8-70af-9972-0273-4c5a70352024', //CRM Accel Oslo May 2010
	'5384e4eb-4ea5-2c55-1366-4c5a70aed0d3', //CRM Accel Paris May 201
	'afbeb6b1-6af0-ba9a-aa45-4c5a70655162', //CRM Accel London May 2010
	'961a3aea-e723-dc8d-4c27-4c5a708517c1', //CRM Accel Lisbon May 2010
	'97e0bbab-8e04-3e78-86ba-4c5a702e5c45', //CRM Accel Madrid May 2010
	'c38eeb41-4e5b-6c0a-3b2f-4c5a70de5a9e', //CRM Accel Dublin May 2010
	'344064d5-90e6-665a-fb95-4c5a702c142c', //CRM Accel Amsterdam Jun 2010
	'e2cfb130-8331-86f2-aa6b-4c5a704769d0', //Webcast Sugar Refinery Mar 2010
	'85bd50a2-7a0b-9456-64f4-4c5a701cd0ba', //Webcast Levementum Apr 2010
	'21dd66b1-8887-e539-0f5d-4c5a706c6238', //Webcast inet Process Apr 2010
	'7cc474bc-d828-cbd2-8d10-4c5a71f0dc24', //Webcast Insightful Jun 2010
	'a5df863f-0a61-2185-9f65-4c5a71bb74df', //Webcast Apex Two Jun 2010
	'61125df6-858f-06a9-b0cf-4c5a7166c5e6', //Webcast Concentrix Jun 2010
	'15fdf3d7-a8b5-1e95-763d-4c5a71782d08', //Webcast LimaCRM Jul 2010
   	'd47cf195-77ef-38de-a959-4c6c4c725aca', //CRM Acceleration London October 2010
	'a199a280-83f3-9cfb-af07-4c6c514b2523', //Webcast BrainSell Aug 2010
    );

    /**
     * Invalid Sales Stages that shouldn't be processed
     *
     * @param array
     */
    protected $_invalidSalesStages = array(
        'Closed Lost',
        'Finance Closed',
        'Closed Won',
        'Sales Ops Closed'
    );

    /**
     * ScrubHelper Instance
     *
     * @param ScrubHelper
     */
    protected $_scrubHelper;

    /**
     * @param  $focus
     * @param  $event
     * @param  $arguments
     * @return bool
     */
    public function startRouting($touchpoint_id, $event, $arguments = array())
    {
        if ($event == "after_scrub") {


            // save the touchpoint for use in other methods
            if ($touchpoint_id instanceof Touchpoint) {
                $this->log('info', 'ScrubRouter - Using Touchpoint Object (' . $touchpoint_id->id . ')');
                // if the touchpiont is already a touchpoint object save it
                $this->touchpoint = $touchpoint_id;
            } else {
                $this->log('info', 'ScrubRouter - Creating Touchpoint Object (' . $touchpoint_id . ')');
                // it's not a touchpoint object so create a new instance and load it up.
                $this->touchpoint = new Touchpoint();
                $this->touchpoint->retrieve($touchpoint_id);
            }

            if ($this->touchpoint->scrubbed == 0) {
                $this->log('info', 'ScrubRouter - Touchpoint is not scrubbed. Exit Out (' . $touchpoint_id . ')');
                $this->_saveLogToTouchpoint();
                return false;
            }

            // this will eval to false since the ignorePartnerCampaign flag is only set in the Interaction.php FILE where
            // then it would eval to true when the touchpoing campaign_id is a valid PLC campaign.
            if (isset($arguments['ignorePartnerCampaign'])
                    && $arguments['ignorePartnerCampaign'] === true
                    && in_array($this->touchpoint->campaign_id, $this->_validCampaigns)) {
                $this->log('info', 'ScrubRouter - Touchpoint is not a valid campaign when the ignorePartnerCampaigns flag is set to true. Exit Out (' . $touchpoint_id . ')');
                $this->_saveLogToTouchpoint();
                return false;
            }

            // verify and set the touchpoint to the assigned_user_id in the $_POST params
            if (isset($_POST['assigned_user_id'])) {
                $tp_assignedTo = str_replace(array('eq_qe1', 'eq_qe2', 'eq_qe3'), '', $_POST['assigned_user_id']);
                $this->log('info', 'ScrubRouter - Setting Touchpoint Assigned To; New Value (' . $tp_assignedTo . ') Old Value: (' . $this->touchpoint->assigned_user_id . ')');
                $this->touchpoint->assigned_user_id = $tp_assignedTo;
            }

            // load all the people that belong to the team of Inside Sales and Customer Advocate
            $this->loadInternalUsers();

            require_once('modules/Touchpoints/ScrubHelper.php');
            $this->_scrubHelper = new ScrubHelper();

            $account_id = false;
            // check to see if the account exist.  If it doesn't then just exit out
            //get new_leadaccounts_id to retrieve leadaccounts
            if (isset($this->touchpoint->new_leadaccount_id) && !empty($this->touchpoint->new_leadaccount_id)) {
                $this->log('info', 'ScrubRouter - Loading Lead Account (' . $this->touchpoint->new_leadaccount_id . ')');
                $lead = $this->retrieve_bean_fp('LeadAccount', 'modules/LeadAccounts/LeadAccount.php', $this->touchpoint->new_leadaccount_id);
                $account_id = $lead->account_id;
            } else {
                // with no lead account lets try for the lead contact instead to pull the account id from it
                if (isset($this->touchpoint->new_leadaccount_id) && !empty($this->touchpoint->new_leadaccount_id)) {
                    $this->log('info', 'ScrubRouter - Loading Lead Contact (' . $this->touchpoint->new_leadcontact_id . ')');
                    $lead = $this->retrieve_bean_fp('LeadContact', 'modules/LeadContacts/LeadContact.php', $this->touchpoint->new_leadcontact_id);
                    $account_id = $lead->account_id;
                } else {
                    // we don't have a lead account or lead contact exit
                    $this->log('info', 'ScrubRouter - No Lead account or Lead Contact Found - Using passed in account');
                }
            }
            if (empty($account_id)) {
                // we have the only type of scrub that doesn't associate with a leadaccout or a leadcontact to the id is passed in
                if (isset($arguments['parent_id']) && !empty($arguments['parent_id'])) {
                    $this->log('info', 'ScrubRouter - Using passed in parentid (' . $arguments['parent_id'] . ')');
                    $account_id = $arguments['parent_id'];
                } else {
                    // just incase the parent_id is not set or empty we don't want to run
                    // this workflow so exit out
                    $export = var_export($arguments, true);
                    $this->log('info', 'ScrubRouter - No Account ID Found so we exit Out. Arguments Dump: ' . $export);
                    $this->_saveLogToTouchpoint();
                    return false;
                }
            }
            //get accounts_id from new_leadaccounts_id to retrieve accounts
            if (!empty($account_id)) {
                $this->log('info', 'ScrubRouter - Valid Account Id: ' . $account_id);
                $this->account = $this->retrieve_bean_fp('Account', 'modules/Accounts/Account.php', $account_id);
            } else {
                // we don't have an account so we can exit
                $this->_saveLogToTouchpoint();
                return false;
            }

            // create a new interaction that we need for every web form submitted only if
            // the campaign is part a valid PLC campaign.
            if (in_array($this->touchpoint->campaign_id, $this->_validCampaigns)) {
                $this->createInteraction();
            }

            // if there are no open opps then we need to run the following code
            $this->log('info', 'ScrubRouter - Start Testing Account for Any Open Opportunities');
            if (!$this->testAccountForOpenOpportunities()) {
                // test if the account is owned by inside sales and if it is then send the email;
                $this->log('info', 'ScrubRouter - No Open Opportunties Found');

                // create the new opportunity
                // if the account user is not inside sales set the touchpoint assigned to users = to the account
                // assigned user so the new opp has the correct assigned to
                $assigned_user_id = false; // default to false as that will let it pull the assign_user_id from the touchpoint
                if (in_array($this->account->assigned_user_id, $this->_insideSalesTeam) === false) {
                    // set the assigned_user_id for the newly created opp to the owner of the account;
                    $assigned_user_id = $this->account->assigned_user_id;
                }

                $this->log('info', 'ScrubRouter - Create a new Opportunity from the Touchpoint');
                // always create a new opportunity if one doesn't exist
                $opp_id = $this->_scrubHelper->createOpportunity($this->touchpoint, $account_id, false, $assigned_user_id);

                if (in_array($this->account->assigned_user_id, $this->_insideSalesTeam) === true
                        && in_array($this->touchpoint->campaign_id, $this->_validCampaigns)) {
                    $this->log('info', 'ScrubRouter - Account is part of inside sales.  Send Them a conflict email');

                    // resassign the account to the partner that submitted the touchpoint
                    $this->log('info', 'ScrubRouter - Change who the Account is assigned to to be that of the touchpoint (' . $this->touchpoint->assigned_user_id . '), account assigned to (' . $this->account->assigned_user_id . ')');
                    $this->account->assigned_user_id = $this->touchpoint->assigned_user_id;
                    $this->account->save(false);
                    $this->log('info', 'ScrubRouter - The Account Is Now Assigned User Id (' . $this->account->assigned_user_id . ')');

                    $subject = "Account Reassigned to Channel";
                    $arrTo[] = $this->getReportsToEmail($this->account->assigned_user_id);


                    $link_to_account = "http://sugarinternal.sugarondemand.com/index.php?module=Accounts&action=DetailView&record=" . $this->account->id;
                    $link_to_opportunity = "http://sugarinternal.sugarondemand.com/index.php?module=Opportunities&action=DetailView&record=" . $opp_id;
                    $account_name = $this->account->name;
                    $inside_sales_assigned_user_name = $this->account->assigned_user_name;
                    $new_assigned_to_user_name = $this->touchpoint->assigned_user_name;

                    require_once('custom/modules/Touchpoints/templates/conflict_directSales.php');

                    $email = $this->sendConflictEmail($arrTo, $subject, $email_body);

                    $this->log('info', 'ScrubRouter - Save the email that was sent above to the newly created opportunity (' . $opp_id . ')');
                    // if an email was sent then attached it to the newly created opportunity
                    $email->assigned_user_id = $this->touchpoint->assigned_user_id;
                    $email->parent_id = $opp_id;
                    $email->save(false);
                    $email->load_relationship('opportunities');
                    $email->opportunities->add($opp_id);

                }
            }

            // save the log message to the touchpoint description field
            $this->_saveLogToTouchpoint();
        }
    }

    /**
     * object Destructor method that calls the save log to touchpoint just in-case it didn't happen.
     *
     * @return void
     */
    public function __destruct()
    {
        $this->_saveLogToTouchpoint();
    }

    /**
     * Save the Log to the Touchpoint Description Field
     * @return void
     */
    protected function _saveLogToTouchpoint()
    {
        if (!empty($this->_logMessage)) {
            $this->log('info', 'ScrubRouter - Saving Log Message to Touchpoint (' . $this->touchpoint->id . ')');
            $this->touchpoint->description .= PHP_EOL . PHP_EOL . $this->_logMessage;
            $this->touchpoint->save(false, false);

            $this->_logMessage = '';
        }
    }


    /**
     * Load all the SugarInternal Users that belong to the group of Inside Sales into an array
     * for easier testing and only having to run this query once instead of for each opportunity
     *
     * @return void
     */
    protected function loadInternalUsers()
    {
        $query = "select DISTINCT u.id FROM team_memberships tm
                    INNER JOIN users u ON u.id = tm.user_id and u.status = 'Active' and u.deleted = 0
                    WHERE team_id in ('519912f6-177e-3cb2-ad13-43d9142d7f0f', '1d2a5770-8ac1-f5db-5618-4a0c3ad0f856');";
        $db = DBManagerFactory::getInstance();
        $result = $db->query($query, TRUE, "Error filling in partner array: ");

        while ($row = $db->fetchByAssoc($result)) {
            $this->_insideSalesTeam[] = $row['id'];
        }
    }


    /**
     * Load the accounts Opportunities and loop through them all
     *
     * @return bool
     */
    protected function testAccountForOpenOpportunities()
    {
        require_once('modules/Opportunities/Opportunity.php');
        $this->account->load_relationship('opportunities');
        $opp_beans = $this->account->opportunities->getBeans(new Opportunity());

        $hasOpen = false;
        foreach ($opp_beans as $opp) {
            if (!in_array($opp->sales_stage, $this->_invalidSalesStages)) {
                $this->log('info', 'ScrubRouter - Found An Open Opportunity (' . $opp->id . ') - (' . $opp->sales_stage . ')');
                $hasOpen = true;
                // with an open one we need to process the opp if it has a valid campaign_id
                if (in_array($this->touchpoint->campaign_id, $this->_validCampaigns)) {
                    // it's not a valid campaign so exit out;
                    $this->log('info', 'ScrubRouter - Valid Campaign - Processing Opporutunity (' . $opp->id . ')');
                    $this->processOpenOpportunity($opp);
                }
            }
        }

        return $hasOpen;
    }


    /**
     * Create a new interaction based on the touchpoint and assign it to the submitting contact
     * if found.  if the contact is not found then we create the new contact
     *
     * @return guid The Created Interaction ID
     */
    protected function createInteraction()
    {
        $this->log('info', 'ScrubRouter - Creating Interaction since this was a webform submit and the interaction is always eneed');
        require_once('modules/Interactions/Interaction.php');
        $interaction = new Interaction();

        global $timedate;
        $contact_id = $this->getAccountContactIdByEmail();
        if ($contact_id == false) {
            // we found no-one with this email so create the contact and assign it to the account
            $this->log('info', 'ScrubRouter - No Contact found by the email address on the touchpoint');
            $contact = new Contact();
            $contact->first_name = $this->touchpoint->first_name;
            $contact->last_name = $this->touchpoint->last_name;
            $contact->email1 = $this->touchpoint->email1;
            $contact->assigned_user_id = $this->touchpoint->assigned_user_id;
            $contact_id = $contact->save(false);
            $contact->load_relationship('accounts');
            $contact->accounts->add($this->account->id);
        }
        $interaction->name = $this->touchpoint->get_summary_text();
        $interaction->parent_id = $contact_id;
        $interaction->parent_type = 'Contacts';
        $interaction->scrub_complete_date = $timedate->to_display_date(gmdate('Y-m-d H:i:s'));
        $interaction->source_id = $this->touchpoint->id;
        $interaction->source_module = "Touchpoints";
        $interaction->start_date = $timedate->to_display_date(gmdate('Y-m-d H:i:s'));
        $interaction->end_date = $timedate->to_display_date(gmdate('Y-m-d H:i:s'));
        $interaction->campaign_id = $this->touchpoint->campaign_id;
        $interaction_id = $interaction->save(false);

        $this->log('info', 'ScrubRouter - Created new Interaction on the Account :: Interaction Id (' . $interaction_id . ') :: Account Id (' . $this->account->id . ') :: contact_id (' . $contact_id . ')');

        return $interaction_id;
    }

    /**
     * Run the work flow for the passed in opportunity and mark the opp as a conflict
     *
     * @param Opportunity $opportunity
     * @return void
     */
    protected function processOpenOpportunity(&$opportunity)
    {
        // check to see if the opportunity is assigned to the same partner as the touch point
        $this->log('info', 'ScrubRouter - Opportunity Partner Assigned To (' . $opportunity->partner_assigned_to_c . ') Touchpoint Partner Assigned To (' . $this->touchpoint->partner_assigned_to_c . ')');
        if ($opportunity->partner_assigned_to_c == $this->touchpoint->partner_assigned_to_c) {
            $this->log('info', 'ScrubRouter - Opportunity is assign to the same parter as the touchpoint; exit out for this opportunity');
            // exit out of the processing for this opp as we dont need to do anything else;
            return;
        }

        $emailSubject = "Lead Submission Conflict";

        // get the name of the partner for the email
        $_partner = new Account();
        $_partner->retrieve($this->touchpoint->partner_assigned_to_c);
        $name_of_partner = $_partner->name;
        $name_of_prospect = $this->touchpoint->company_name;
        unset($_partner);

        // get the email from the include file.
        // always require it as it's needed but we want a fresh copy for every loop
        require('custom/modules/Touchpoints/templates/conflict_partners.php');

        $emailTo = array();
        // flag to see if we need to get the partner email
        $getPartnerEmail = false;

        // now we test to see if the opportunity is assigned to inside sales
        if (in_array($opportunity->assigned_user_id, $this->_insideSalesTeam) === true) {
            $this->log('info', 'ScrubRouter - Opportunity is assigned to the inside sales team by user_id (' . $opportunity->assigned_user_id . ')');
            $this->log('info', 'ScrubRouter - SendEmail Assigned To Inside Sales');
            $this->log('info', 'ScrubRouter - Set Conflict Type to Inside Direct Sales');
            // yes it's assigned to inside sales.
            // send email to the manager of who the opportunity is assigned to
            $opportunity->conflict_type_c = "Inside Direct Sales";
            $emailTo[] = $this->getReportsToEmail($opportunity->assigned_user_id);
        } elseif (empty($opportunity->partner_assigned_to_c)) {
            // email template 4
            // Set the opp partner_assigned_to_c = touchpoint submitting partner then email all channel reps and submitting partner
            $this->log('info', 'ScrubRouter - opportunity does not have an assiged to partner');
            $this->log('info', 'ScrubRouter - SendEmail Not Assigned To Inside Sales and Partner Is Empty');
            $this->log('info', 'ScrubRouter - Set Conflict Type to Channel Direct Sales');
            //$opportunity->partner_assigned_to_c = $this->touchpoint->partner_assigned_to_c;
            // set the flag to get the partner email
            // with this we need to get the owner of the partner
            // Email rep that owns the partner is touchpoint->partner_assigned_to_c->assigned_user_id
            $opportunity->conflict_type_c = "Channel Direct Sales";
            $emailTo[] = $this->getPartnerAssignedToEmail($this->touchpoint->partner_assigned_to_c);
            $emailTo[] = $this->getUserEmail($opportunity->assigned_user_id);

        } elseif (!empty($opportunity->partner_assigned_to_c)) {
            // don't have to do anything special here except set the flag to fetch the partner email
            $opportunity->conflict_type_c = "Multiple Partners";
            $this->log('info', 'ScrubRouter - Set Conflict Type to Multiple Partners');
            $this->log('info', 'ScrubRouter - Opportunity is assigned to (' . $opportunity->partner_assigned_to_c . ')');
            $this->log('info', 'ScrubRouter - SendEmail Not Assigned To Inside Sales and Partner Was Not Empty');
            $getPartnerEmail = true;
        }

        if ($getPartnerEmail === true) {
            $partnerEmail = false;
            if (!empty($this->touchpoint->lead_submitter_c)) {
                $partnerEmail = $this->getEmailByPortalName($this->touchpoint->lead_submitter_c);
                $emailTo = $emailTo + $partnerEmail;
            }

            if ($partnerEmail === false) {
                $this->log('info', 'ScrubRouter - No Partner Found by portal name so we select all the contacts in the account');
                $a = new Account();
                $a->retrieve($opportunity->partner_assigned_to_c);
                $a->load_relationship('contacts');

                $contact_bean = new Contact();
                $a_contact_beans = $a->contacts->getBeans($contact_bean);

                foreach ($a_contact_beans as $contact) {
                    $emailTo[] = array($contact->email1, $contact->name);
                    unset($contact);
                }
            }

        }

        // everyone one of these emails needs to go to the channel@ alias
        $emailTo[] = array('channel@sugarcrm.com', 'SugarCRM Channel Reps');

        // mark the opportunity as a conflict and save
        $this->log('info', 'ScrubRouter - Set the conflict flag for the opportunity and save');
        $opportunity->conflict_c = 1;
        $opportunity->save(false);

        // now send the conflict email and associate it to the opportunity
        $this->log('info', 'ScrubRouter - Send the conflict email');
        $email = $this->sendConflictEmail($emailTo, $emailSubject, $email_body);

        $this->log('info', 'ScrubRouter - link the email to the opportunity');
        $email->assigned_user_id = $opportunity->assigned_user_id;
        $email->parent_id = $opportunity->id;
        $email->save(false);
        $email->load_relationship('opportunities');
        $email->opportunities->add($opportunity->id);
    }

    protected function sendConflictEmail(array $toList, $subject, $body)
    {
        global $locale, $current_user;

        //Basic setup.
        require_once('include/SugarPHPMailer.php');
        require_once("modules/Administration/Administration.php");
        require_once('modules/Emails/Email.php');
        require_once('include/workflow/alert_utils.php');
        $mail = new SugarPHPMailer();
        $admin = new Administration();
        $admin->retrieveSettings();

        //Setup the reply to and from name for the email object.
        $mail->AddReplyTo('no-reply@sugarcrm.com', 'No Reply');
        $mail->From = $admin->settings['notify_fromaddress'];
        $mail->FromName = $admin->settings['notify_fromname'];

        //Setup the outbound email send method.
        if ($admin->settings['mail_sendtype'] == "SMTP") {
            $mail->Host = $admin->settings['mail_smtpserver'];
            $mail->Port = $admin->settings['mail_smtpport'];
            if ($admin->settings['mail_smtpauth_req']) {
                $mail->SMTPAuth = TRUE;
                $mail->Username = $admin->settings['mail_smtpuser'];
                $mail->Password = $admin->settings['mail_smtppass'];
            }
            $mail->Mailer = "smtp";
            $mail->SMTPKeepAlive = true;
        }
        else
        {
            $mail->mailer = 'sendmail';
        }


        // Comment out the following 6 lines to complete ITR: 16383
        //foreach ($toList as $to) {
        //    $mail->AddAddress($to[0], $to[1]);
        //}

        // All emails should be cc'd to salesop@sugarcrm.com
        //$mail->AddCC('salesops@sugarcrm.com', 'Sales Ops');
        // end comment out

        // uncomment this following to complete ITR: 16383
        $mail->AddAddress('rmeeker@sugarcrm.com', 'Ryan Meeker');
        $mail->AddAddress('bhurwitz@sugarcrm.com', 'Bonnie Hurwitz');
	// end uncomment


        // TODO: for launch only send emails to ryan meeker until they get everyone trained.

        //Set the subject and body from the parsed results.
        $mail->IsHTML(true);
        $mail->Body = $body;
        $mail->Subject = $subject;

        //Do any char set conversion necessary
        $mail->prepForOutbound($locale->getPrecedentPreference('default_email_charset'));
        //Perform the actual send.
        if (!$mail->Send()) {
            $GLOBALS['log']->fatal("Unable to send Partner Conflict Email with subject: $subject");
            $GLOBALS['log']->fatal("Partner Conflict Email error message received:  {$mail->ErrorInfo}");
        }
        else
        {
            $GLOBALS['log']->debug("Partner Conflict Email with subject: send successfully.$subject ");
        }

        //Cleanup.
        //$mail->ClearAddresses();
        //$mail->ClearCCs();

        $email_object = new Email();
        $email_object->name = $mail->Subject;
        $email_object->type = "archived";
        $email_object->from_addr = $admin->settings['notify_fromname'];
        $email_object->status = "archived";
        $email_object->intent = "pick";
        $email_object->parent_type = "Opportunities";
        $email_object->description = $mail->Body;
        $email_object->description_html = $mail->Body;
        // Get the first user to set it it was too
        //$firstUser = array_shift($toList);
        foreach ($toList as $to) {
            $email_object->to_addrs .= $to[1] . ' <' . $to[0] . '>; ';
        }
        //Add the date sent, not automatically added.
        $today = gmdate($GLOBALS['timedate']->get_db_date_time_format());
        $email_object->date_start = $GLOBALS['timedate']->to_display_date($today);
        $email_object->time_start = $GLOBALS['timedate']->to_display_time($today, true);

        return $email_object;
    }

    protected function getAccountContactIdByEmail()
    {
        $this->log('info', 'ScrubRouter - Finding an account contact by their email address on the touchpoint');
        if (empty($this->touchpoint->email1)) {
            //cannot proceed without email or last name
            $this->log('info', 'ScrubRouter - Email address was empty! Failing Out');
            return false;
        }

        // Look for contacts that have the same email address as this touchpoint
        $c_bean = new Contact();
        $contactSearchQuery =
                "select a_c.contact_id as contact_id \n" .
                        "from contacts inner join email_addr_bean_rel on contacts.id = email_addr_bean_rel.bean_id and email_addr_bean_rel.deleted = 0\n" .
                        "           inner join email_addresses on email_addr_bean_rel.email_address_id = email_addresses.id and email_addresses.deleted = 0\n" .
                        "           inner join accounts_contacts a_c on a_c.contact_id = contacts.id and a_c.deleted = 0\n";
        $c_bean->add_team_security_where_clause($contactSearchQuery);
        $contactSearchQuery .=
                "where contacts.deleted = 0\n";

        //set email filter
        $contactSearchQuery .= "  and email_addresses.email_address = '{$this->touchpoint->email1}'\n";
        $contactSearchQuery .= "  and a_c.account_id = '{$this->account->id}'\n";

        $contactSearchQuery .= "order by date_entered desc\n";

        // Continue here - just like process above for lead_contact
        $result = $GLOBALS['db']->query($contactSearchQuery);
        if (!$result) {
            $GLOBALS['log']->fatal("Error in parent/child lead automation query for finding contacts. Please investigate.");
            return;
        }

        // We've found one, start linking
        $row = $GLOBALS['db']->fetchByAssoc($result);
        if ($row) {
            return $row['contact_id'];
        }

        return false;
    }


    /**
     * Get the the contact id for a given account by the portal name
     *
     * @param  $portal_name
     * @return bool|guid
     */
    protected function getAccountContactIdByPortalName($portal_name)
    {
        $this->log('info', 'ScrubRouter - Selecting Account/Contact Id From Portal Name (' . $portal_name . ')');
        $query = "SELECT ac.id from accounts_contacts ac
                    JOIN contacts c ON c.id = ac.contact_id and c.portal_name = '" . $portal_name . "' and c.deleted = '0'
                    WHERE ac.account_id = '" . $this->account->id . "'";
        $db = DBManagerFactory::getInstance();
        $result = $db->query($query, TRUE, "Error selecting account contact id by protal name: ");
        while ($row = $db->fetchByAssoc($result)) {
            return $row['id'];
            break;
        }

        return false;
    }

    /**
     * Return an email and name of everyone with the portal name
     * @param  $portal_name
     * @return array|bool
     */
    protected function getEmailByPortalName($portal_name)
    {
        $this->log('info', 'ScrubRouter - Selecting Contact By Portal Name (' . $portal_name . ')');
        $query = "SELECT id FROM contacts WHERE portal_name = '" . $portal_name . "' and deleted = '0';";
        $db = DBManagerFactory::getInstance();
        $result = $db->query($query, TRUE, "Error selecting contact id by protal name: ");

        $return = array();
        $contact = new Contact();
        while ($row = $db->fetchByAssoc($result)) {
            $contact->retrieve($row['id']);
            $return[] = array($contact->email1, $contact->first_name . ' ' . $contact->last_name);
        }

        if (empty($return)) {
            return false;
        }

        return $return;
    }

    /**
     * Fetch a users email address and name
     *
     * @param  $user_id
     * @return array
     */
    protected function getUserEmail($user_id)
    {
        require_once("modules/Users/User.php");
        $user = new User();
        $user->retrieve($user_id);
        return array($user->email1, $user->first_name . ' ' . $user->last_name);
    }

    /**
     * Fetches who the user reports to and returns their email and name
     * if the user doesn't have a reports to it returns their name and email
     *
     * @param  $user_id
     * @return array
     */
    protected function getReportsToEmail($user_id)
    {
        $user = new User();
        $user->retrieve($user_id);

        if (!empty($user->reports_to_id)) {
            $reportsTo = new User();
            $reportsTo->retrieve($user->reports_to_id);

            $ret = array($reportsTo->email1, $reportsTo->first_name . ' ' . $reportsTo->last_name);
        } else {
            $ret = array($user->email1, $user->first_name . ' ' . $user->last_name);
        }

        unset($user, $reportsTo);

        return $ret;
    }

    /**
     * Take the partner and return the owner of the partners email address
     * and name
     *
     * @param  $partner_assigned_to
     * @return array
     */
    protected function getPartnerAssignedToEmail($partner_assigned_to)
    {
        $account = new Account();
        $account->retrieve($partner_assigned_to);

        return $this->getUserEmail($account->assigned_user_id);
    }


    /**
     * Used to retrieve a new bean instance
     * @param string $bean_type
     * @param string $bean_file
     * @param string $bean_id
     * @return SugarBeen
     */
    protected function retrieve_bean_fp($bean_type = '', $bean_file = '', $bean_id = '')
    {
        //if any of the parameters are invalid then return false
        if (empty($bean_type) || empty($bean_file) || !is_file($bean_file)) return false;
        require_once($bean_file);

        //create new bean
        $bean = new $bean_type();
        //return retrieved bean
        return $bean->retrieve($bean_id);
    }

    /**
     * Log A Message to the Scrub Log
     *
     * @param string $type
     * @param string $msg
     * @return void
     */
    protected function log($type, $msg)
    {
        $output = strtoupper($type) . " :: " . $msg;
        if ($this->_enableSysLog) {
            syslog(LOG_ERR, $output);
        }
        global $timedate;
        $this->_logMessage .= $timedate->to_display_date_time(gmdate('Y-m-d H:i:s')) . ' -- ' . $output . PHP_EOL;
    }
}
