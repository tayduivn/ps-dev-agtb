<?php
if(!defined('sugarEntry') || !sugarEntry) die('Not A Valid Entry Point');

class BugCustomPortal {
	var $fields_to_track = array(
		'priority',
		'status',
		'type',
		'fixed_in_release',
		'resolution'
	);

	var $changed_fields = array();

	//DEE CUSTOMIZATION - ITREQUEST 8131
	var $send_update_support_rep;
	//END DEE CUSTOMIZATION

	function sendUpdates($bean, $event, $arguments) {
		// if an existing bug is being saved, as opposed to a new bug...
		if (!empty($bean->fetched_row)) {
			require_once("modules/Administration/Administration.php");
			$admin = new Administration();
			$admin->retrieveSettings();
			
			//DEE CUSTOMIZATION - ITREQUEST 8131
			global $current_user;
			require_once('modules/Users/User.php');
			$temp_user = new User();
 			$temp_user->retrieve($bean->created_by);
			if(isset($temp_user->department) && $temp_user->department == "Customer Support" && isset($temp_user->id) && $current_user->id != $temp_user->id){
				$this->send_update_support_rep = $temp_user->email1;	
			}
			//END DEE CUSTOMIZATION

			if ($admin->settings['notify_on']) {
				foreach ($this->fields_to_track as $field) {
					if ($bean->$field != $bean->fetched_row[$field]) {
						$this->changed_fields[] = $field;
					}
				}
				// if some tracked fields changed during this save...
				if (!empty($this->changed_fields)) {
					/*
					** @author: Jim Bartek
					** SUGARINTERNAL CUSTOMIZATION
					** ITRequest #: 17511
					** Description: Bug Updates being sent to internal users does not include a link to sugar internal.  I changed the functions so that it creates an internal template/mail object and an external template/mail object.  It also builds two arrays of users to send to, internal and external and uses the appropriate mail object to send the email
					*/
					$external_mail = $this->create_updated_email($bean, true);
					$internal_mail = $this->create_updated_email( $bean, false );
					$success = $this->send_update_email($external_mail, $internal_mail, $bean, $admin);
					/* END SUGARINTERNAL CUSTOMIZATION */
				}
			}
		}
	}
	/*
	** @author: Jim Bartek
	** SUGARINTERNAL CUSTOMIZATION
	** ITRequest #: 17511
	** Description: Bug Updates being sent to internal users does not include a link to sugar internal.  I changed the functions so that it creates an internal template/mail object and an external template/mail object.  It also builds two arrays of users to send to, internal and external and uses the appropriate mail object to send the email
	*/
	function create_updated_email($bean, $external = true) {
		global $sugar_config, $app_list_strings, $current_user;

		require_once("include/phpmailer/class.phpmailer.php");
		require_once("XTemplate/xtpl.php");

		$current_language = empty($_SESSION['authenticated_user_language']) ? $sugar_config['default_language'] : $_SESSION['authenticated_user_language'];

		$xtpl = new XTemplate("include/language/{$current_language}.notify_template.html");
		$mail = new PHPMailer();

		$template_name = "BugUpdated";

		if (in_array('priority', $this->changed_fields)) $xtpl->assign("PRIORITY_CHANGED", "*");
		if (in_array('status', $this->changed_fields)) $xtpl->assign("STATUS_CHANGED", "*");
		if (in_array('type', $this->changed_fields)) $xtpl->assign("TYPE_CHANGED", "*");
		if (in_array('fixed_in_release', $this->changed_fields)) $xtpl->assign("FIXED_IN_RELEASE_CHANGED", "*");
		if (in_array('resolution', $this->changed_fields)) $xtpl->assign("RESOLUTION_CHANGED", "*");

		$xtpl->assign("BUG_BUG_ID", $bean->id);
		$xtpl->assign("BUG_BUG_NUMBER", $bean->bug_number);
		$xtpl->assign("BUG_SUBJECT", $bean->name);
		$xtpl->assign("BUG_TYPE", $app_list_strings['bug_type_dom'][$bean->type]);
		$xtpl->assign("BUG_PRIORITY", $app_list_strings['bug_priority_dom'][$bean->priority]);
		$xtpl->assign("BUG_STATUS", $app_list_strings['bug_status_dom'][$bean->status]);
		$xtpl->assign("BUG_RESOLUTION", $app_list_strings['bug_resolution_dom'][$bean->resolution]);
		$xtpl->assign("BUG_FIXED_IN_RELEASE", $this->release_id_to_name($bean->fixed_in_release));
		$xtpl->assign("ASSIGNER", "{$current_user->first_name} {$current_user->last_name}");
		
		/*
		** @author: Jim Bartek
		** SUGARINTERNAL CUSTOMIZATION
		** ITRequest #: 17511
		** Description: Bug Updates being sent to internal users does not include a link to sugar internal.  I changed the functions so that it creates an internal template/mail object and an external template/mail object.  It also builds two arrays of users to send to, internal and external and uses the appropriate mail object to send the email
		*/
                $review_note_txt = "You may communicate with the engineer assigned to this Bug by posting a Note here: ";
		$review_note_txt .= "\nhttp://www.sugarcrm.com/crm/index.php?option=com_sugarbugs&task=view&caseID={$bean->id}";
		
		if(false === $external) {
			$port = "";
                	if (isset($_SERVER['SERVER_PORT']) && $_SERVER['SERVER_PORT'] != 80) {
                        	$port = ":{$_SERVER['SERVER_PORT']}";
                	}
			$url = "{$sugar_config['site_url']}{$port}/index.php?module=Bugs&action=DetailView&record={$bean->id}";
                        $review_note_txt .= "\nYou may review this Bug at: \n{$url}";
		}
                
		$xtpl->assign("REVIEW_BUG_TEXT", $review_note_txt);
		/* END SUGARINTERNAL CUSTOMIZATION */

		//END DEE CUSTOMIZATION
		$xtpl->parse($template_name);
		$xtpl->parse($template_name . "_Subject");

		$mail->Body = from_html(trim($xtpl->text($template_name)));
		$mail->Subject = from_html($xtpl->text($template_name . "_Subject"));

		return $mail;
	}
	/*
	** @author: Jim Bartek
	** SUGARINTERNAL CUSTOMIZATION
	** ITRequest #: 17511
	** Description: Bug Updates being sent to internal users does not include a link to sugar internal.  I changed the functions so that it creates an internal template/mail object and an external template/mail object.  It also builds two arrays of users to send to, internal and external and uses the appropriate mail object to send the email
	*/
	function send_update_email($external_mail, $internal_mail, $bean, $admin) {
		global $current_user;

		SYSLOG(LOG_DEBUG, "dmittalSI4: Email body {$mail->Body}");

		/*
		 @author: EDDY
		** SUGARINTERNAL CUSTOMIZATION
		** ITRequest #: 15044 :: add "user" reference to bugs
		** Description: Add user relationship between Bugs and User
		*/
		//now include any related users
                $related_users = array();
                $related_users = $bean->get_linked_beans('users', 'User');
		/***END CUSTOMIZATION***/	
		$external_send_to = array();
		$internal_send_to = array( );
		//DEE CUSTOMIZATION ITREQUEST - 8131
		if (!empty($related_users) || !empty($bean->portal_name_c) || (isset($this->send_update_support_rep) && !empty($this->send_update_support_rep))) {
			$external_submitter_mail = file_get_contents("http://www.sugarcrm.com/crm/get_email.php?key=E9A8b8e777b77b&user={$bean->portal_name_c}");
			if (!empty($external_submitter_mail)) {
				$external_send_to[] = $external_submitter_mail;
			}
			//DEE CUSTOMIZATION - ITREQUEST 8131
			if(!empty($this->send_update_support_rep)) {
				$internal_send_to[] = $this->send_update_support_rep;
			}
			//END DEE CUSTOMIZATION
			if ($current_user->id != $bean->assigned_user_id) {
				$internal_user = new User();
				$internal_user->retrieve($bean->assigned_user_id);
				//DEE CUSTOMIZATION ITREQUEST - 8131
				if (!empty($internal_user->email1) && $internal_user->email1 != $this->send_update_support_rep) {
					$internal_send_to[] = $internal_user->email1;
				}
				else {
					//DEE CUSTOMIZATION ITREQUEST - 8131
					if (!empty($internal_user->email2) && $internal_user->email2 != $this->send_update_support_rep) {
						$internal_send_to[] = $internal_user->email2;
					}
				}
			}
			/*
			 @author: EDDY
			** SUGARINTERNAL CUSTOMIZATION
			** ITRequest #: 15044 :: add "user" reference to bugs
			** Description: Add user relationship between Bugs and User
			*/
			//add related users to send to array
			if(!empty($related_users)){
				foreach($related_users as $rel_user){
					//user email1 if it exists, otherwise use email2
	                                if (!empty($rel_user->email1) && $rel_user->email1 != $this->send_update_support_rep) {
        	                                $internal_send_to[] = $rel_user->email1;
                	                }
                        	        else {
                                	        //DEE CUSTOMIZATION ITREQUEST - 8131
                                        	if (!empty($rel_user->email2) && $rel_user->email2 != $this->send_update_support_rep) {
                                                	$internal_send_to[] = $rel_user->email2;
	                                        }
                                	}

				}
			}
			/***END CUSTOMIZATION***/	
			
			if (!empty($external_send_to)) {
				if ($admin->settings['mail_sendtype'] == "SMTP") {
					$external_mail->Mailer = "smtp";
					$external_mail->Host = $admin->settings['mail_smtpserver'];
					$external_mail->Port = $admin->settings['mail_smtpport'];
					if ($admin->settings['mail_smtpauth_req']) {
						$external_mail->SMTPAuth = TRUE;
						$external_mail->Username = $admin->settings['mail_smtpuser'];
						$external_mail->Password = $admin->settings['mail_smtppass'];
					}
				}

				$external_mail->From = $admin->settings['notify_fromaddress'];
				$external_mail->FromName = (empty($admin->settings['notify_fromname'])) ? "" : $admin->settings['notify_fromname'];

				foreach ($external_send_to as $email_address) {
					SYSLOG(LOG_DEBUG, "dmittalSI4: emails {$email_address}");
					$external_mail->AddAddress($email_address);
					$external_mail->Send();
					$external_mail->ClearAddresses();
				}

			}

                        if (!empty($internal_send_to)) {
                                if ($admin->settings['mail_sendtype'] == "SMTP") {
                                        $internal_mail->Mailer = "smtp";
                                        $internal_mail->Host = $admin->settings['mail_smtpserver'];
                                        $internal_mail->Port = $admin->settings['mail_smtpport'];
                                        if ($admin->settings['mail_smtpauth_req']) {
                                                $internal_mail->SMTPAuth = TRUE;
                                                $internal_mail->Username = $admin->settings['mail_smtpuser'];
                                                $internal_mail->Password = $admin->settings['mail_smtppass'];
                                        }
                                }

                                $internal_mail->From = $admin->settings['notify_fromaddress'];
                                $internal_mail->FromName = (empty($admin->settings['notify_fromname'])) ? "" : $admin->settings['notify_fromname'];

                                foreach ($internal_send_to as $email_address) {
                                        SYSLOG(LOG_DEBUG, "dmittalSI4: emails {$email_address}");
                                        $internal_mail->AddAddress($email_address);
                                        $internal_mail->Send();
                                        $internal_mail->ClearAddresses();
                                }

                        }

		}

	}
	/* END SUGARINTERNAL CUSTOMIZATION */

	function release_id_to_name($id) {
		global $db;

		$query = "SELECT name from releases WHERE id = '{$id}'";
		$result = $db->query($query,true," Error getting release name: ");
		$row = $db->fetchByAssoc($result);

		return $row['name'];
	}


}
?>
