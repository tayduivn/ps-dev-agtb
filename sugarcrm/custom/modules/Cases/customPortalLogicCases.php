<?php
if(!defined('sugarEntry') || !sugarEntry) die('Not A Valid Entry Point');
		/*
		 @author: DTam
		** SUGARINTERNAL CUSTOMIZATION
		** ITRequest #: 17275
		** Description: Add email notification to users on cases subpanel if status or priority change
		*/
class CaseCustomPortal {
	// define fields to track and send update if changed
	var $fields_to_track = array(
		'status',
		'priority_level'
	);

	var $changed_fields = array();

	function sendUpdates($bean, $event, $arguments) {
		// if case already exists and is now being resaved
		if (!empty($bean->fetched_row)) {
			require_once("modules/Administration/Administration.php");
			$admin = new Administration();
			$admin->retrieveSettings();

			if ($admin->settings['notify_on']) {
				// check for changed fields from fields_to_track
				foreach ($this->fields_to_track as $field) {
					if ($bean->$field != $bean->fetched_row[$field]) {
						$this->changed_fields[] = $field;
					}
				}
				// if some tracked fields changed during this save send update email
				if (!empty($this->changed_fields)) {

					$mail = $this->create_updated_email($bean);
					$success = $this->send_update_email($mail, $bean, $admin);

				}
			}
		}
	}

	function create_updated_email($bean) {
		global $sugar_config, $app_list_strings, $current_user;

		require_once("include/phpmailer/class.phpmailer.php");
		require_once("XTemplate/xtpl.php");

		$current_language = empty($_SESSION['authenticated_user_language']) ? $sugar_config['default_language'] : $_SESSION['authenticated_user_language'];

		$xtpl = new XTemplate("include/language/{$current_language}.notify_template.html");
		$mail = new PHPMailer();
		// use CaseUpdated template in {$current_language}.notify_template.html
		$template_name = "CaseUpdated";
		// save values for template
		if (in_array('status', $this->changed_fields)) $xtpl->assign("STATUS_CHANGED", "*");
		if (in_array('priority_level', $this->changed_fields)) $xtpl->assign("PRIORITY_CHANGED", "*");
		$xtpl->assign("CASE_CASE_ID", $bean->id);
		$xtpl->assign("CASE_CASE_NUMBER", $bean->case_number);
		$xtpl->assign("CASE_SUBJECT", $bean->name);
		$xtpl->assign("CASE_PRIORITY", $app_list_strings['Support Priority Levels'][$bean->priority_level]);
		$xtpl->assign("CASE_STATUS", $app_list_strings['case_status_dom'][$bean->status]);
		$xtpl->assign("ASSIGNER", "{$current_user->first_name} {$current_user->last_name}");

		$port = "";
        if (isset($_SERVER['SERVER_PORT']) && $_SERVER['SERVER_PORT'] != 80) {
            $port = ":{$_SERVER['SERVER_PORT']}";
        }
		$url = "{$sugar_config['site_url']}{$port}/index.php?module=Cases&action=DetailView&record={$bean->id}";
        $review_note_txt = "You may review this Case at: \n{$url}";
		$xtpl->assign("REVIEW_CASE_TEXT", $review_note_txt);
		$xtpl->parse($template_name);
		$xtpl->parse($template_name . "_Subject");
		$mail->Body = from_html(trim($xtpl->text($template_name)));
		$mail->Subject = from_html($xtpl->text($template_name . "_Subject"));

		return $mail;
	}

	function send_update_email($mail, $bean, $admin) {
		global $current_user;
		/*
		 @author: DTam
		** SUGARINTERNAL CUSTOMIZATION
		** ITRequest #: 17275
		** Description: Add linked users to email
		*/
        $related_users = array();
        $related_users = $bean->get_linked_beans('users', 'User');	
		$send_to = array();

		if (!empty($related_users) || !empty($bean->portal_name_c) || (isset($this->send_update_support_rep) && !empty($this->send_update_support_rep))) {
			$external_submitter_mail = file_get_contents("http://www.sugarcrm.com/crm/get_email.php?key=E9A8b8e777b77b&user={$bean->portal_name_c}");
			if (!empty($external_submitter_mail)) {
				$send_to[] = $external_submitter_mail;
			}

			if ($current_user->id != $bean->assigned_user_id) {
				$internal_user = new User();
				$internal_user->retrieve($bean->assigned_user_id);
				if (!empty($internal_user->email1)) {
					$send_to[] = $internal_user->email1;
				}
				else {
					if (!empty($internal_user->email2)) {
						$send_to[] = $internal_user->email2;
					}
				}
			}
			/*
			 @author: DTam
			** SUGARINTERNAL CUSTOMIZATION
			** ITRequest #: 17275
			** Description: Add user relationship between Cases and User
			*/
			//add related users to send to array
			if(!empty($related_users)){
				foreach($related_users as $rel_user){
					//user email1 if it exists, otherwise use email2
	                                if (!empty($rel_user->email1)) {
        	                                $send_to[] = $rel_user->email1;
                	                }
                        	        else {
                                        	if (!empty($rel_user->email2)) {
                                                	$send_to[] = $rel_user->email2;
	                                        }
                                	}

				}
			}
			/***END CUSTOMIZATION***/	
			
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

				$mail->From = $admin->settings['notify_fromaddress'];
				$mail->FromName = (empty($admin->settings['notify_fromname'])) ? "" : $admin->settings['notify_fromname'];

				foreach ($send_to as $email_address) {
					$mail->AddAddress($email_address);
					$mail->Send();
					$mail->ClearAddresses();
				}

			}

		}

	}
	/*
	 @author: DTam
	** SUGARINTERNAL CUSTOMIZATION
	** ITRequest #: 17400, 18041
	** Description: Add Resolution Time field to Cases, Add Closed Date/Time field to Cases
	** Wiki: internalwiki.sjc.sugarcrm.pvt/index.php/CustomPortalLogicCases.php
	*/
	function updateResTime($bean, $event, $arguments) {
		global $timedate;
		$oldStatus = $bean->fetched_row['status'];
		// already closed check
		if ($oldStatus== 'Closed' || $oldStatus == 'Closed Defect' || $oldStatus == 'Closed Feature' || $oldStatus == 'Closed No Response' ) {
			$closedFlag = true;
		}
		// only update before save if status is changing to closed, closed defect, closed feature or closed no response but not if already closed
		if (($event == 'before_save') && ($bean->status != $oldStatus) && ($bean->status == 'Closed' || $bean->status == 'Closed Defect' || $bean->status == 'Closed Feature' || $bean->status == 'Closed No Response' ) && (!$closedFlag)) {
			// initialize business time object and time range for being open and holidays
			require("custom/modules/Home/Dashlets/SLAcountdown/BusinessTime.php");
			require("custom/modules/Home/Dashlets/SLAcountdown/SLAcountdown.data.php");
			$bt= new BusinessTimeForSLA();
			// after discussion with jeff open time is ~24 hours to adjust for different timezones from different users
			$opentime = array(
				"Mon" => array(
					"open" => "00:00:00",
					"close" => "23:59:59",
				),
				"Tue" => array(
					"open" => "00:00:00",
					"close" => "23:59:59",
				),
				"Wed" => array(
					"open" => "00:00:00",
					"close" => "23:59:59",
				),
				"Thu" => array(
					"open" => "00:00:00",
					"close" => "23:59:59",
				),
				"Fri" => array(
					"open" => "00:00:00",
					"close" => "23:59:59",
				),
			);
			$holidays = $dashletData['SLAcountdown']['holidays'];
			$bt->opentime = $opentime;
			$bt->holiday = $holidays;
			// convert datetime stamps to times
			$start = strtotime($bean->fetched_row['date_entered']);
			$end = strtotime($bean->date_modified);
			// initialize times in businessTime obj
			$bt->start = $start;
			$bt->end = $end;
			// calculate time open, returned in array as $time['ontime']
			date_default_timezone_set($userTZ);
			/*@author: DTam
			** SUGARINTERNAL CUSTOMIZATION
			** ITRequest #: 18455
			** Description: Resolution time is populated incorrectly when duplicating cases
			**              override time to zero when duplicating cases which have no date entered when saved.
			*/
			if ($bean->fetched_row['date_entered'] != '') {
			  $time = $bt->calculate();
			} else {
			  $time['ontime'] = 0;
			}
			/* END SUGARINTERNAL CUSTOMIZATION */
			$time = $bt->calculate();
			// time is in seconds so X sec/((60sec/min)*(60min/hr)*(23.99hrs/workday)) = X work days
			$days = $time['ontime']/(60*60*23.9901);
			$bean->resolution_time_c = $days;
			//$debug = 'ver 9.2s1 dtam cases res time fired. old status:' . $oldStatus . ' to ' . $bean->status . ' created: ' . $bean->fetched_row['date_entered'] . ' modified: ' . $bean->date_modified . ' time: ' . $time['ontime']. ' days: ' . $days; //REMOVE WHEN APPROVED FOR GO LIVE
			//SYSLOG(LOG_DEBUG, $debug); //REMOVE WHEN APPROVED FOR GO LIVE
			
			// Also update closed date time as they are sister fields
			$bean->closed_date_time_c = $timedate->get_gmt_db_datetime();
		}
	}
}
?>
