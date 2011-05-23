<?php
// jostrow/sbaroudi (792)

require_once('custom/include/SugarAlerts/SugarAlertsHelper.php');

class SugarAlerts {
	private $alerts_table_name = 'sugar_alerts';
	private $subscriptions_table_name = 'sugar_alerts_subscriptions';

	protected $bean;

	public function handleAlert($bean = FALSE) {

		$this->bean = $bean;

		// get a list of users who are subscribed to this alert type
		$subscribed_users = $this->getSubscribedUsers(get_class($this));
		
		// exclude the current user from the list of people to notify -- they did it!
		// NOTE: COMMENTING OUT FOR EASIER TESTING -- UNCOMMENT THIS LATER
		//if (array_key_exists($GLOBALS['current_user']->id, $subscribed_users)) {
		//	unset($subscribed_users[$GLOBALS['current_user']->id]);
		//}

		// Alert classes can define a custom user filter that limits the users who will be alerted
		// If this method exists, call it and filter the list of subscribed users from above
		if (method_exists($this, 'customUserFilter')) {
			$customUserFilter = $this->customUserFilter();

			$subscribed_users = array_intersect($subscribed_users, $customUserFilter);
		}

		// loop through the Users who have subscribed to this alert type
		// figure out which display locations they've subscribed to and store the alert accordingly (or e-mail it!)

		foreach ($subscribed_users as $user_id) {

			$prefs = $this->getUserSubscribedAlertTypes($user_id);

			if (in_array('email', $prefs[get_class($this)])) {
				$this->sendEmailNotification($user_id, $this->generateEmailContent());
			}

			if (in_array('dashlet', $prefs[get_class($this)])) {
				$alert_text = $this->generateDashletContent();
				$this->insertIntoDb(get_class($this), 'dashlet', $alert_text, $user_id);
			}

			if (in_array('cube', $prefs[get_class($this)])) {
				$alert_text = $this->generateCubeContent();
				$this->insertIntoDb(get_class($this), 'cube', $alert_text, $user_id);
			}

		}

		return TRUE;

	}

	// designed to be implemented by alert plugin classes, which extend this class
	public static function getAlertTitle() {
		return 'Default Alert Title';
	}

	// DONE
	public function deleteAlert($id) {

		$GLOBALS['db']->query("UPDATE {$this->alerts_table_name} SET deleted = 1 WHERE id = '" . $GLOBALS['db']->quote($id) . "'");

		return TRUE;
	}

	private function sendEmailNotification($user_id, $email_content) {

		global $locale;

		require_once('modules/Users/User.php');
		require_once("include/SugarPHPMailer.php");

		// set up the User we're sending the e-mail to
		$notify_user = new User;
		$notify_user->disable_row_level_security = TRUE;
		$notify_user->retrieve($user_id);

		$user_emailaddress = $notify_user->emailAddress->getPrimaryAddress($notify_user);
		if (empty($user_emailaddress)) {

			$GLOBALS['log']->warn("SugarAlerts: no e-mail address configured for User {$notify_user->user_name}, aborting");
			return FALSE;

		}

		$user_fullname = $notify_user->full_name;

		$notify_mail = new SugarPHPMailer();
		$OBCharset = $locale->getPrecedentPreference('default_email_charset');

		$notify_mail->AddAddress($user_emailaddress, $locale->translateCharsetMIME(trim($user_fullname), 'UTF-8', $OBCharset));
		$notify_mail->Subject = 'SFA Alert: ' . from_html($email_content['subject']);
		$notify_mail->Body = from_html(trim($email_content['body']));

		$notify_mail->prepForOutbound();
		$notify_mail->setMailerForSystem();

		// retrieve system settings
		$admin = new Administration();
		$admin->retrieveSettings();

		$notify_mail->From = $admin->settings['notify_fromaddress'];
		$notify_mail->FromName = (empty($admin->settings['notify_fromname'])) ? "" : $admin->settings['notify_fromname'];

		if(!$notify_mail->Send()) {
			$GLOBALS['log']->warn("SugarAlerts: error sending e-mail (method: {$notify_mail->Mailer}), (error: {$notify_mail->ErrorInfo})");
			return FALSE;
		}

		$GLOBALS['log']->info("SugarAlerts: e-mail successfully sent");
		return TRUE;

	}

	// DONE
	public function getUserUnreadAlertCount($user_or_id, $display_location) {
		$count = 0;

		$user_alerts = $this->getUserAlerts($user_or_id, $display_location);

		foreach ($user_alerts as $user_alert) {

			if ($user_alert['is_read'] == 0) {
				$count++;
			}

		}

		return $count;
	}


	// DONE
	public function getUserAlerts($user_or_id, $display_location, $limit = 20, $mark_as_read = FALSE) {
		$user_id = $user_or_id;
		if(!is_string($user_or_id)){
			$user_id = $user_or_id->id;
		}
		
		// pull the alert types and display locations that this user has subscribed to
		$subscribed_alert_types = $this->getUserSubscribedAlertTypes($user_id);

		$user_alerts = array();

		$user_alerts_res = $GLOBALS['db']->query("SELECT id, date_entered, alert_type, alert_text, user_id, is_read FROM {$this->alerts_table_name}
			WHERE user_id = '{$user_id}' AND display_location = '" . $GLOBALS['db']->quote($display_location) . "' AND deleted = 0
			ORDER BY date_entered DESC LIMIT {$limit}");

		while ($user_alerts_row = $GLOBALS['db']->fetchByAssoc($user_alerts_res)) {

			// does the user want to see this type of alert in this display location?
			// if not, the row probably shouldn't be in the table -- but let's doublecheck their preferences

			if (in_array($display_location, $subscribed_alert_types[$user_alerts_row['alert_type']])) {
				$user_alerts_row['alert_text'] = html_entity_decode($user_alerts_row['alert_text']);
				$user_alerts[$user_alerts_row['id']] = $user_alerts_row;
			}

		}

		// should we mark these alerts as read, after retrieving them?
		// marking them read based on the list of IDs we pulled above ensures that we only update alerts that were actually displayed
		if ($mark_as_read) {

			$alert_ids = array_keys($user_alerts);

			if (!empty($alert_ids)) {
				$mark_as_read_res = $GLOBALS['db']->query("UPDATE {$this->alerts_table_name} SET is_read = 1 WHERE id IN ('" . implode("','", $alert_ids) . "')");
			}

		}

		return $user_alerts;
	
	}

	// DONE
	private function getSubscribedUsers($alert_type) {
		$subscribed_users = array();

		$subscribed_users_res = $GLOBALS['db']->query("SELECT DISTINCT user_id FROM {$this->subscriptions_table_name} WHERE alert_type = '" . $GLOBALS['db']->quote($alert_type) . "'");
		while ($subscribed_users_row = $GLOBALS['db']->fetchByAssoc($subscribed_users_res)) {
			$subscribed_users[] = $subscribed_users_row['user_id'];
		}

		return $subscribed_users;
	}

	// DONE
	public function getUserSubscribedAlertTypes($user_bean_or_id) {
		$user_subscribed_alerts = array();

		if (is_string($user_bean_or_id)) {
			$user_id = $user_bean_or_id;
		}
		else {
			$user_id = $user_bean_or_id->id;
		}

		// begin by pulling alert types and display locations that the given user has subscribed to...

		$user_subscribed_alerts_res = $GLOBALS['db']->query("SELECT alert_type, display_location FROM {$this->subscriptions_table_name} WHERE user_id = '{$user_id}'");
		while ($user_subscribed_alerts_row = $GLOBALS['db']->fetchByAssoc($user_subscribed_alerts_res)) {
			$user_subscribed_alerts[$user_subscribed_alerts_row['alert_type']][] = $user_subscribed_alerts_row['display_location'];
		}

		return $user_subscribed_alerts;
	}

	// DONE
	public static function getAlertTypes() {
		$alert_types = array();
		$alert_files = array();

		getFiles($alert_files, 'custom/include/SugarAlerts/Alerts');

		foreach ($alert_files as $file) {
			$alert_types[] = basename($file, '.php');
		}

		return $alert_types;
	}
	
	// DONE
	// MAKE PRIVATE
	private function insertIntoDb($alert_type, $display_location, $alert_text, $user_id) {

		$GLOBALS['db']->query("INSERT INTO {$this->alerts_table_name} SET
			id = '" . create_guid() . "',
			date_entered = '".gmdate('Y-m-d H:i:s')."',
			alert_type = '" . $GLOBALS['db']->quote($alert_type) . "',
			display_location = '" . $GLOBALS['db']->quote($display_location) . "',
			alert_text = '" . $GLOBALS['db']->quote($alert_text) . "',
			user_id = '" . $GLOBALS['db']->quote($user_id) . "',
			is_read = 0,
			deleted = 0
		");

		return TRUE;

	}

	public function handlePreferencesSave($user_id, $save_array) {

		// clear all existing alert subscription preferences
		$GLOBALS['db']->query("DELETE FROM {$this->subscriptions_table_name} WHERE user_id = '" . $GLOBALS['db']->quote($user_id) . "'");

		// loop through the array provided to us by the Users EditView and insert any preferences
		// since these are coming from checkboxes, any element that's present in the array means the checkbox was checked
		foreach ($save_array as $alert_type => $alert_array) {

			foreach ($alert_array as $display_location => $on_off) {

				$GLOBALS['db']->query("INSERT INTO {$this->subscriptions_table_name} SET
					alert_type = '" . $GLOBALS['db']->quote($alert_type) . "',
					display_location = '" . $GLOBALS['db']->quote($display_location) . "',
					user_id = '" . $GLOBALS['db']->quote($user_id) . "'
				");

			}

		}

		return TRUE;

	}

}
