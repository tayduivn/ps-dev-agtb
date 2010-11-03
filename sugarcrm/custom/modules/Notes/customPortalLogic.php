<?php
if(!defined('sugarEntry') || !sugarEntry) die('Not A Valid Entry Point');

class NoteCustomPortal {
	var $mail_from_email = "portal-noreply@sugarcrm.com";
	var $mail_from_name = "SugarCRM";

	function sendUpdates(&$bean, $event, $arguments) {
		global $moduleList, $beanList, $beanFiles;
		
		$arguments['check_notify'] = true;
		if(isset($bean->assigned_user_id)){
			// Remove the assigned user id from notes, so we don't get unwanted notifications
			// from the product standard notifications to the contact owner
			unset($bean->assigned_user_id);
		}
		
		if (($bean->parent_type == "Bugs" || $bean->parent_type == "Cases") && !empty($bean->parent_id) && $arguments['check_notify']) {
			require_once($beanFiles[$beanList[$bean->parent_type]]);
			$parent_bean = new $beanList[$bean->parent_type]();
			$parent_bean->disable_row_level_security = TRUE;
			$parent_bean->retrieve($bean->parent_id);

			require_once("modules/Administration/Administration.php");
			$admin = new Administration();
			$admin->retrieveSettings();
			if ($admin->settings['notify_on'] && $bean->portal_flag == 1) {
				$mail = $this->create_updated_email($bean, $parent_bean);
				$success = $this->send_update_email($mail, $bean, $parent_bean, $admin);
			}
		}
	}

	function create_updated_email($bean, $parent_bean) {
		global $sugar_config, $app_list_strings, $current_user;

		require_once("include/phpmailer/class.phpmailer.php");
		require_once("XTemplate/xtpl.php");

		$current_language = empty($_SESSION['authenticated_user_language']) ? $sugar_config['default_language'] : $_SESSION['authenticated_user_language'];

		$xtpl = new XTemplate("include/language/{$current_language}.notify_template.html");
		$mail = new PHPMailer();

		$template_name = "Note";

		$xtpl->assign("NOTE_SUBJECT", $bean->name);
		$xtpl->assign("NOTE_NOTE", $bean->description);
		$xtpl->assign("NOTE_ATTACHMENT_LINE", empty($bean->filename) ? "" : "Attachment: {$bean->filename}\n");

		$xtpl->assign("PARENT_TYPE", $parent_bean->object_name);
		$xtpl->assign("PARENT_TYPE_UPPER", strtoupper($parent_bean->object_name));
		$xtpl->assign("PARENT_NAME", $parent_bean->name);
		$xtpl->assign("PARENT_NUMBER", ($parent_bean->object_name == "Case") ? $parent_bean->case_number : $parent_bean->bug_number);

		if (!empty($bean->portal_name_c)) {
	                $port = "";
			if (isset($_SERVER['SERVER_PORT']) && $_SERVER['SERVER_PORT'] != 80) {
				$port = ":{$_SERVER['SERVER_PORT']}";
			}

			$url = "{$sugar_config['site_url']}{$port}/index.php?module=Notes&action=DetailView&record={$bean->id}";
			$parent_url = "{$sugar_config['site_url']}{$port}/index.php?module={$parent_bean->module_dir}&action=DetailView&record={$parent_bean->id}";

			$xtpl->assign("ASSIGNER", "{$bean->portal_name_c} (Mambo user)");
			$xtpl->assign("REVIEW_NOTE_TEXT", "You may review this Note at:\n<{$url}>\n\nYou may review this {$parent_bean->object_name} at:\n<{$parent_url}>");
		}
		else {
			$review_note_txt = "You may review this Note by logging into the ";

			if ($parent_bean->object_name == "Case") {
				$review_note_txt .= "Support Portal:\n\n";
//				$review_note_txt .= "http://www.sugarcrm.com/network/redirect.php?to=support_portal&task=view&caseID={$parent_bean->id}";
//				$review_note_txt .= "https://www.sugarcrm.com/crm/sites/all/includes/caseportal/index.php?module=Cases&action=DetailView&id={$parent_bean->id}";
				/*
				** @author: DTam
				** SUGARINTERNAL CUSTOMIZATION
				** ITRequest #: 9606
				** Description: Links to cases on portal are not displaying the style sheet 
				** Wiki customization page: 
				*/
				$review_note_txt .= "https://www.sugarcrm.com/crm/case-tracker.html?view=detail&id={$parent_bean->id}";
				/** END SUGARINTERNAL CUSTOMIZATION **/
				$review_note_txt .= "\n\nReplies to this Note must be entered through the Support Portal.";
			}
			else {
				$review_note_txt .= "{$parent_bean->object_name} Tracker";
			}

			$xtpl->assign("ASSIGNER", $current_user->first_name . " " . $current_user->last_name);
			$xtpl->assign("REVIEW_NOTE_TEXT", $review_note_txt);
		}

		$xtpl->parse($template_name);
		$xtpl->parse($template_name . "_Subject");

		$mail->Body = from_html(trim($xtpl->text($template_name)));
		$mail->Subject = from_html($xtpl->text($template_name . "_Subject"));

		return $mail;
	}

	function send_update_email($mail, $bean, $parent_bean, $admin) {
		global $current_user;

		$send_to = array();
		if (!empty($bean->portal_name_c)) {
			$assigned_user = new User();
			$assigned_user->retrieve($parent_bean->assigned_user_id);
			if (!empty($assigned_user->email1)) {
				$send_to[] = $assigned_user->email1;
			}
			else {
				if (!empty($assigned_user->email2)) {
					$send_to[] = $assigned_user->email2;
				}
			}
		}
		else {
			$submitter_name = ($parent_bean->object_name == "Case") ? $parent_bean->submitter_c : $parent_bean->portal_name_c;
			$external_submitter_mail = file_get_contents("http://www.sugarcrm.com/crm/get_email.php?key=E9A8b8e777b77b&user={$submitter_name}");
			if (!empty($external_submitter_mail)) {
				$send_to[] = $external_submitter_mail;
			}
		}

		if (!empty($send_to)) {
			if ($admin->settings['mail_sendtype'] == "SMTP") {
				$mail->Mailer = "smtp";
				$mail->Host = $admin->settings['mail_smtpserver'];
				$mail->Port = $admin->settings['mail_smtpport'];
				if ($admin->settings['mail_smtpauth_req']) {
					$mail->SMTPAuth = TRUE;
					$mail->Username = $admin->settings['mail_smtpuser'];
					$mail->Password = $admin->settings['mail_smtppass'];
				}
			}

			$mail->From = $this->mail_from_email;
			$mail->FromName = $this->mail_from_name;
			$fp = fopen($GLOBALS['sugar_config']['log_dir'].'customer_notifications.log', 'a');
			foreach ($send_to as $email_address) {
				fwrite($fp, date('Y-m-d H:i:s').','.$email_address.','.$bean->id.','.$parent_bean->id."\n");
				$mail->AddAddress($email_address);
				$mail->Send();
				$mail->ClearAddresses();
			}
			fclose($fp);

		}
	}
}
?>
