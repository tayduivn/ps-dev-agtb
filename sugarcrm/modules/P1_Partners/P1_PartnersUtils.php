<?php

/**
 * Custom Class which provides generic utils for the P1_Parters module.  This class was written so that for future releases, the code can be called from the LogicHook
 * functions without the need to duplicate code.
 *
 */

class P1_PartnerUtils
{

	/**
	 * Custom function to send related PRM emails.
	 *
	 */
	static function sendPRMEmail($to_contact_id, $subject, $body, $s_attachment = "")
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
		$mail->IsHTML(true);
		
		//Setup the reply to and from name for the email object.
		$mail->AddReplyTo('no-reply@sugarcrm.com','No Reply');
		$mail->From = $admin->settings['notify_fromaddress'];
		$mail->FromName = $admin->settings['notify_fromname'];
		
		//Add the csv attachment.
		//Removing for now.
		if(isset($s_attachment) && !empty($s_attachment)) {
			$file_name_suffix = gmdate("Ymd");
			$mail->AddStringAttachment($s_attachment,"partner_output_{$file_name_suffix}.txt", 'base64', 'text/plain');
		}

		//Setup the outbound email send method.
		if ($admin->settings['mail_sendtype'] == "SMTP") 
		{
			$mail->Host = $admin->settings['mail_smtpserver'];
			$mail->Port = $admin->settings['mail_smtpport'];
			if ($admin->settings['mail_smtpauth_req']) 
			{
				$mail->SMTPAuth = TRUE;
				$mail->Username = $admin->settings['mail_smtpuser'];
				$mail->Password = $admin->settings['mail_smtppass'];
			}
			$mail->Mailer   = "smtp";
			$mail->SMTPKeepAlive = true;
		} 
		else 
		{
			$mail->mailer = 'sendmail';
		}

		//Retrieve the contact id passed in and get the email address.
		require_once('modules/Contacts/Contact.php');
		$cnt = new Contact();
		$cnt->retrieve($to_contact_id);
		if( !empty($cnt->email1) )
			//$mail->AddAddress('internalsystems@sugarcrm.com', 'Testing');
			$mail->AddAddress($cnt->email1, $cnt->full_name);
		else 
		{
			$GLOBALS['log']->fatal("Could not send PRM email, contact email empty for contact id: $to_contact_id");
			return false;
		}
				
		//Email Templates parsing does not allow user to insert signature variable so we perform this substitution manually
		//Disabling for now, Dee already added the signature on the front end.
		//$a_default_sigs = $current_user->getDefaultSignature();
		//$default_signature = "";
		//if( isset($a_default_sigs['signature_html']) )
		//	$default_signature = $a_default_sigs['signature_html'];

		//$body = preg_replace('/\$contact_user_signature/',$default_signature,$body);
		
		//Parse the body html to replace any email template variables.
		$template = new EmailTemplate();
		$macro_nv = array();
		$data = array('subject'=>$subject,'body_html'=>from_html($body),'body'=>$body);
		$template_data = @$template->parse_email_template($data, 'Contacts', $cnt, $macro_nv);

		//Set the subject and body from the parsed results.
		$mail->Body = $template_data['body_html'];
		$mail->Subject = $template_data['subject'];
		
		//Do any char set conversion necessary	
		$mail->prepForOutbound($locale->getPrecedentPreference('default_email_charset'));
		//Perform the actual send.
		if (!$mail->Send()) 
		{
			$GLOBALS['log']->fatal("Unable to send PRM email with subject: $subject");
			$GLOBALS['log']->fatal("PRM Email error message received: {$mail->ErrorInfo}");
		}
		else
		{
			$GLOBALS['log']->debug("Email with subject: send successfully.$subject ");
		}
		
		//Cleanup.
		$mail->ClearAddresses();
		$mail->ClearCCs();
		$mail->ClearBCCs();
		
		$email_object = new Email();
		$email_object->name = $mail->Subject;
		$email_object->type = "archived";
		$email_object->from_addr = $current_user->email1;
		$email_object->status = "archived";
		$email_object->intent = "pick";
		$email_object->parent_type = "Opportunities";
		$email_object->description = $mail->Body;
		$email_object->description_html = $mail->Body;
		$email_object->assigned_user_id = $current_user->id;
		$email_object->to_addrs = $cnt->email1;
		//Add the date sent, not automatically added.
		$today = gmdate($GLOBALS['timedate']->get_db_date_time_format());
		$email_object->date_start = $GLOBALS['timedate']->to_display_date($today);
		$email_object->time_start = $GLOBALS['timedate']->to_display_time($today, true);
		
		return $email_object;
		
	}
	
	/**
	 * Update an opp when assigned to a partner.
	 *
	 * @param SugarBean $bean
	 */
	static function updateOppFromPartnerWizard(&$bean)
	{
		$GLOBALS['log']->debug("Updaing Opp from partner wizard: {$bean->id}");
		//We only want to execute this portion of code if the opp is being saved from the Wizard.  Will pass in
		//either a flag into the bean itself as an instance variable or check a $_POST variable.
		global $app_list_strings;
		$new_accepted_by_partner_value =  "P"; //Pending
		$bean->partner_assigned_to_c = $_POST['P1_Partnerspartner_assigned_to_c'];
		$bean->accepted_by_partner_c =$new_accepted_by_partner_value;
		$bean->contact_id_c = $_POST['P1_Partnerscontact_id'];
		
		//Since we are hardcoding two of the values, check to see if they still exist in the app_list_strings array.
		//If they don't exist log some fatal errors.
		if(  ! ( isset($app_list_strings['partner_accepted'][$new_accepted_by_partner_value]))   )
		{
			$fatal_message = "P1_PartnerUtils - function updateOppFromPartnerWizard is trying to assign values that have been removed from app_list_strings array";
			$GLOBALS['log']->fatal($fatal_message);
		}
	}
	
	/**
	 * Create the PRM csv export that will be sent in an email to the partner.
	 *
	 * @param array Opportunity Ids
	 * @return string Contents of csv export
	 */
	static function getCsvExportForPrmEmail($a_opp_id)
	{
		$export_defs = array( 
			'Opportunities' => array(
				array('field' => 'name', 'label' => 'LBL_OPPORTUNITY_NAME',),
				array('field' => 'date_closed', 'label' => 'LBL_DATE_CLOSED',),
				array('field' => 'date_entered', 'label' => 'LBL_CSV_EXPRT_DATE_CREATED',),
				array('field' => 'users', 'label' => 'LBL_USERS',),
			//	array('field' => 'description', 'label' => 'LBL_DESCRIPTION', ),
				array('field' => 'current_solution', 'label' => 'LBL_CURRENT_SOLUTION', ),),
			'Campaigns' => array(
                                array('field' => 'name', 'label' => 'LBL_CAMPAIGN',),),
			'Accounts' => array(
				array('field' => 'name', 'label' => 'LBL_ACCOUNT_NAME',),
				array('field' => 'billing_address_street', 'label' => 'LBL_BILLING_ADDRESS_STREET', ),
				array('field' => 'billing_address_city', 'label' => 'LBL_BILLING_ADDRESS_CITY', ),
				array('field' => 'billing_address_state', 'label' => 'LBL_BILLING_ADDRESS_STATE', ),
				array('field' => 'billing_address_country', 'label' => 'LBL_BILLING_ADDRESS_COUNTRY', ),
				array('field' => 'billing_address_postalcode', 'label' => 'LBL_BILLING_ADDRESS_POSTALCODE', ),
				array('field' => 'website', 'label' => 'LBL_WEBSITE', ),),
			'Contacts' => array(
				array('field' => 'last_name', 'label' => 'LBL_LAST_NAME',),
				array('field' => 'first_name', 'label' => 'LBL_FIRST_NAME', ),
				array('field' => 'title', 'label' => 'LBL_TITLE', ),
				array('field' => 'phone_other', 'label' => 'LBL_OTHER_PHONE', ),
				array('field' => 'phone_work', 'label' => 'LBL_LIST_PHONE', ),
				array('field' => 'email1', 'label' => 'LBL_LIST_EMAIL_ADDRESS',  ),),
		);
		
		//Get the header
		$header_row = self::_getCsvHeaderForExport($export_defs);
		//Get the contents
		$bean_data = self::_getBeanDataForExport($export_defs,$a_opp_id);
		//Merge everything together
		$results = $header_row . "\r\n" . $bean_data;
		//Translate the contents
		$results_content  = $GLOBALS['locale']->translateCharset($results, 'UTF-8', $GLOBALS['locale']->getExportCharset());
		
		return $results_content;
	}
	
	/**
	 * Get the export data for all specified opportunities.
	 *
	 * @param array $export_def Metadata used which defines which fields to export 
	 * @param array $a_opp_id Array of all opportunities to export.
	 */
	static function _getBeanDataForExport($export_def,$a_opp_id)
	{
		
		if(empty($a_opp_id))
			return "";

		require_once('modules/Opportunities/Opportunity.php');
		require_once('modules/Accounts/Account.php');
		require_once('modules/Contacts/Contact.php');
		require_once('modules/Campaigns/Campaign.php');
	
		$a_results = array();
		foreach ($a_opp_id as $opp_id)
		{
			$opp_account_results = array();
			
			//Get the Opportunity related data
			$opp = new Opportunity();
			$opp->retrieve($opp_id);
			foreach ($export_def['Opportunities'] as $export_fields)
			{
				$value = $opp->{$export_fields['field']};
				$opp_account_results[] = self::_prepFieldForCsvExport($value);
			}
              //BEGIN Temporary fix : EDDY 13729 prt 1 of 2
               global $current_user;
               $temp_id = $current_user->id;
               $current_user->retrieve(1);
              //END Temporary fix : EDDY 13729
			/*
			** @author: Dtam
			** SUGARINTERNAL CUSTOMIZATION
			** ITRequest #: 17553
			** Description: csv file export needs Campaigns
			** Wiki customization page: internalwiki.sjc.sugarcrm.pvt/index.php/AddCampaignstoCSV
			*/
			
			//Get the campaign related to the opportunity
			$campaign_id = $opp->campaign_id;
			if(!empty($campaign_id)) {
				$campaign = new Campaign();
				$campaign->retrieve($campaign_id);
				//get campaign fields
				foreach ($export_def['Campaigns'] as $export_fields) {
					$value = $campaign->{$export_fields['field']};
					$opp_account_results[] = self::_prepFieldForCsvExport($value);
				}
			}
			/* END SUGARINTERNAL CUSTOMIZATION */
			//Get the Account related to the opportunity
			$account_id = $opp->account_id;
			if( !empty($account_id) )
			{
				$account = new Account();
				$account->retrieve($account_id);
				//Get all of the account data points.
				foreach ($export_def['Accounts'] as $export_fields)
				{
					$value = $account->{$export_fields['field']};
					$opp_account_results[] = self::_prepFieldForCsvExport($value);
				}
				
				//Get the contacts associated to the account.
				$account->load_relationship('contacts');
				$cnt_template = new Contact();
				$a_contact_beans = $account->contacts->getBeans($cnt_template);
				
				//Check if the account has related contacts.
				if( count($a_contact_beans) > 0)
				{
					foreach ($a_contact_beans as $single_contact)
					{
						$contact_export_field = array();
						foreach ($export_def['Contacts'] as $export_fields)
						{
							$value = $single_contact->{$export_fields['field']};
							$contact_export_field[] = self::_prepFieldForCsvExport($value);
						}
						$a_results[] = implode("\t", $opp_account_results) ."\t" . implode("\t", $contact_export_field) . "\r\n";
					}
				}
				//If no related contacts just add the account and opp data to the result array.
				else 
					$a_results[] = implode("\t", $opp_account_results) . "\r\n";
			}
			//Just Opportunity info, no account or contacts associated.
			else 
				$a_results[] = implode("\t", $opp_account_results) . "\r\n";		
		}
		//BEGIN Temporary fix 13729 : EDDY part 2 of 2
		$current_user->retrieve($temp_id);
		//END Temporary fix 13729 : EDDY	
		return implode("", $a_results);
	}
	
	
	/**
	 * Get the csv header row for the csv file that will be emailed as part of the PRM email.
	 *
	 * @param unknown_type $export_def
	 * @return string
	 */
	static function _getCsvHeaderForExport($export_def)
	{
		$a_results = array();
		foreach($export_def as $module_name => $field_def)
		{
			$module_language = return_module_language($GLOBALS['current_language'], $module_name,true);
			foreach ($field_def as $single_def)
			{
				//Remove the trailing semicolon in labels if present.
				$new_label = preg_replace('/:$/', '',  $module_language[$single_def['label']]);
				$a_results[] = self::_prepFieldForCsvExport($new_label);
			}
		}
		return implode("\t", $a_results);
	}
	
	/**
	 * For any field, prepare it for export which involes escaping doulbe quotes so the csv formatting isn't ruined.
	 *
	 * @param string $field
	 * @return string
	 */
	static function _prepFieldForCsvExport($field)
	{
		return '"' . preg_replace("/\"/","\"\"", $field) . '"';
	}
	
	/**
	 * Create a note and associate it to the email that is sent to the partners.  The note will contain the csv attachment sent.
	 *
	 * @param string $file_contents
	 * @param string $parent_id
	 */
	static function createNoteForEmailAttachment($file_contents, $parent_id)
	{
		global $current_user;
		$note_id = create_guid();
		
		$note = new Note();
		$note->id = $note_id;
		$note->new_with_id = TRUE;
		$note->parent_type = 'Emails';
		$note->parent_id = $parent_id;
		$note->created_by = $current_user->id;
		$file_name_suffix = gmdate("Ymd");
		$note->filename ="partner_output_{$file_name_suffix}.txt";
		$note->name = "Email Attachment: {$note->filename}"; //Same convention as Email module.
		$note->file_mime_type =  'text/plain';
		$note->team_id = $current_user->default_team;
		$note->save(FALSE);
		self::_writeNoteContentsToDisk($file_contents, $note_id);
		
	}
	
	/**
	 * Write the csv file to disk so that it can be access through the note attached to the Email.  The csv file is generated and stored
	 * in memory as a string so this is the first time it it stored to disk.
	 *
	 * @param unknown_type $file_contents
	 * @param unknown_type $id
	 */
	static function _writeNoteContentsToDisk($file_contents, $id)
	{
		$new_file_name = "cache/upload/$id";
		$succ = @file_put_contents($new_file_name, $file_contents);
		if(!$succ)
		{
			$GLOBALS['log']->fatal("Unable to write PRM csv file to disk for note attachment, filename: $new_file_name");
		}
	}

	/**
        * add opportunity record to tracker
        * tracker is the item that displays in the last viewed items section
        * IMP NOTE: Admin -> Tracker Settings -> Tracker Action must be enabled for this to work
	**/
        static function oppToTracker($bean_or_id, $is_bean){
        	$focus = '';
        	if($is_bean){
                	$focus = $bean_or_id;
        	}
        	else{
                	require_once('modules/Opportunities/Opportunity.php');
                	$focus = new Opportunity();
                	$focus->retrieve($bean_or_id);
        	}
        	require_once('modules/Trackers/TrackerManager.php');
        	$trackerManager = TrackerManager::getInstance();
        	$timeStamp = gmdate($GLOBALS['timedate']->get_db_date_time_format());
        	if($monitor = $trackerManager->getMonitor('tracker')){
                	$monitor->setValue('team_id', $GLOBALS['current_user']->getPrivateTeamID());
                	$monitor->setValue('action', 'detailview');
                	$monitor->setValue('user_id', $GLOBALS['current_user']->id);
                	$monitor->setValue('module_name', 'Opportunities');
                	$monitor->setValue('date_modified', $timeStamp);
                	$monitor->setValue('visible', 1);

                	if (!empty($focus->id)) {
                        	$monitor->setValue('item_id', $focus->id);
                        	$monitor->setValue('item_summary', $focus->name);
                 	}

                	//If visible is true, but there is no bean, do not track (invalid/unauthorized reference)
                	//Also, do not track save actions where there is no bean id
                	if($monitor->visible && empty($focus->id)) {
                   		$trackerManager->unsetMonitor($monitor);
               	    		return;
                	}
                	$trackerManager->saveMonitor($monitor);
        	}
        	else{
                	$GLOBALS['log']->fatal("OPPQ Error P1_PartnersHelper.php: Failed adding opportunity {$bean_or_id} to tracker");
        	}
        }

	/**
        * attach partner customer email to opportunity record
        **/
        static function attachContactEmail($contact_email_subject, $contact_email_body) {
                if(isset($contact_email_subject) && isset($contact_email_body)) {
                global $current_user;
                $email_object = new Email();
                $email_object->name = $contact_email_subject;
                $email_object->type = "draft";
                $email_object->from_name = $current_user->full_name;
                $email_object->from_addr = $current_user->email1;
                $email_object->bcc_addrs = $current_user->email1;
                $email_object->status = "draft";
                $email_object->intent = "pick";
                $email_object->parent_type = "Opportunities";
                $email_object->description = $contact_email_body;
                $email_object->description_html = $contact_email_body;
                $email_object->assigned_user_id = $current_user->id;
                $email_object->flagged = 1;
                //Add the date sent, not automatically added.
                $today = gmdate($GLOBALS['timedate']->get_db_date_time_format());
                $email_object->date_start = $GLOBALS['timedate']->to_display_date($today);
                $email_object->time_start = $GLOBALS['timedate']->to_display_time($today, true);
                return $email_object;
                }
                else
                        $GLOBALS['log']->fatal("oppQ PartnerUtils: Unable to attach draft customer email to opportunity record");
        }

	/**
        * get all contacts guids related to opportunity bean and related account bean
        * we also check for contacts that have an email address
        * this is called when pulling contacts on quick edit and when sending customer email
        **/
        static function getContacts(&$bean) {
                require_once('modules/Contacts/Contact.php');
                require_once('modules/Accounts/Account.php');

                //Get contacts related to this opportunity bean
                $opp_contact_arr = array();
                $opp_contacts = $bean->get_linked_beans('contacts', 'Contact');
                foreach($opp_contacts as $opp_contact) {
                        if(!empty($opp_contact->email1) && isset($opp_contact->email1)) {
                                $opp_contact_arr[] = $opp_contact->id;
                        }
                }

                //Get contact related to this account bean
                $acc_contact_arr = array();
                $opp_account = new Account();
                $opp_account->retrieve($bean->account_id);
                $acc_contacts = $opp_account->get_linked_beans('contacts', 'Contact');
                foreach($acc_contacts as $acc_contact) {
                        if(isset($acc_contact->email1) && !empty($acc_contact->email1)) {
                                $acc_contact_arr[] = $acc_contact->id;
                        }
                }

                //Merge the opp and account contacts
                $contact_merge_arr = array_merge($opp_contact_arr, $acc_contact_arr);

                if(empty($contact_merge_arr) || !isset($contact_merge_arr)) {
                        return false;
                }

                //De-dupe the array
                $contact_arr = array_unique($contact_merge_arr);

                return $contact_arr;
        }
}
