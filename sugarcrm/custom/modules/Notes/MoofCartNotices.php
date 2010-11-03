<?php
if(!defined('sugarEntry') || !sugarEntry) die('Not A Valid Entry Point');

class NoteMoofCartNotices {
	var $mail_from_email = "sales-ops@sugarcrm.com";
	var $mail_from_name = "SugarCRM Sales-Ops";

	function notifyOpportunityOwner(&$bean, $event, $arguments) {
		global $moduleList, $beanList, $beanFiles, $current_user;

		$GLOBALS['log']->fatal("jmo9 here ... {$bean->parent_type} ... {$current_user->id}");

		require_once('custom/si_custom_files/MoofCartHelper.php');

		// the 'MoofCart' user is creating a Note related to an Opportunity, so let's continue...
		if ($bean->parent_type == 'Opportunities' && $current_user->id == MoofCartHelper::$moof_cart_user_id) {
		$GLOBALS['log']->fatal("jmo9 here2");
			require_once($beanFiles[$beanList[$bean->parent_type]]);
			$parent_bean = new $beanList[$bean->parent_type]();
			$parent_bean->disable_row_level_security = TRUE;
			$parent_bean->retrieve($bean->parent_id);

			require_once("modules/Administration/Administration.php");
			$admin = new Administration();
			$admin->retrieveSettings();

			if ($admin->settings['notify_on']) {
		$GLOBALS['log']->fatal("jmo9 here3");

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

		$template_name = "MoofCartNote";

		$xtpl->assign("NOTE_SUBJECT", $bean->name);
		$xtpl->assign("NOTE_NOTE", $bean->description);

		$xtpl->assign("PARENT_NAME", $parent_bean->name);

		$port = "";
		if (isset($_SERVER['SERVER_PORT']) && $_SERVER['SERVER_PORT'] != 80) {
			$port = ":{$_SERVER['SERVER_PORT']}";
		}

		$url = "{$sugar_config['site_url']}{$port}/index.php?module=Notes&action=DetailView&record={$bean->id}";
		$parent_url = "{$sugar_config['site_url']}{$port}/index.php?module={$parent_bean->module_dir}&action=DetailView&record={$parent_bean->id}";

		$xtpl->assign("REVIEW_NOTE_TEXT", "You may review this Note at:\n<{$url}>\n\nYou may review this {$parent_bean->object_name} at:\n<{$parent_url}>");

		$xtpl->parse($template_name);
		$xtpl->parse($template_name . "_Subject");

		$mail->Body = from_html(trim($xtpl->text($template_name)));
		$mail->Subject = from_html($xtpl->text($template_name . "_Subject"));

		return $mail;
	}

	function send_update_email($mail, $bean, $parent_bean, $admin) {
		global $current_user;

		$send_to = array();
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
		$GLOBALS['log']->fatal("jmo9 here5");

		if (!empty($send_to)) {
		$GLOBALS['log']->fatal("jmo9 here6 ... " . var_export($send_to, TRUE));

			if ($admin->settings['mail_sendtype'] == "SMTP") {
				$mail->Mailer = "smtp";
				$mail->Host = $admin->settings['mail_smtpserver'];
				$mail->Port = $admin->settings['mail_smtpport'];
				if ($admin->settings['mail_smtpauth_req']) {
		$GLOBALS['log']->fatal("jmo9 here7");

					$mail->SMTPAuth = TRUE;
					$mail->Username = $admin->settings['mail_smtpuser'];
					$mail->Password = $admin->settings['mail_smtppass'];
				}
			}

			$mail->From = $this->mail_from_email;
			$mail->FromName = $this->mail_from_name;

			foreach ($send_to as $email_address) {
				$mail->AddAddress($email_address);
				$mail->Send();
				$mail->ClearAddresses();
			}

		}
	}
}
?>
