<?php
if(!defined('sugarEntry') || !sugarEntry) die('Not A Valid Entry Point');

class AccountHooks  {

	//DEE CUSTOMIZATION SUGAR EXPRESS
	function applySugarExpress(&$bean, $event, $arguments){ // ODCE Support Cases customization (CE-OnDemand)
                if($event == 'before_save'){
                        require_once("custom/si_custom_files/SugarExpressFunctions.php");
                        spscAccountsHandleSave($bean);
                }
        }
	//END DEE CUSTOMIZATION ODCE

	//DEE CUSTOMIZATION ITREQUEST 3301
        function setSupportServiceLevel(&$focus, $event, $arguments) {
                if($event == "before_save") {
                        if(isset($focus->account_type) && !empty($focus->account_type) && ($focus->account_type == 'Past Customer' || $focus->account_type == 'Past Partner')) {
                                $focus->Support_Service_Level_c = 'no_support';
                        }
                }
        }
        //END DEE CUSTOMIZATION


	function accountCountryRegionMap(&$focus, $event, $arguments) {
		if($event == "before_save"){
			require('custom/si_custom_files/meta/countryRegionMap.php');
			if(!empty($focus->billing_address_country) && array_key_exists($focus->billing_address_country, $countryRegionMap)){
				$focus->region_c = $countryRegionMap[$focus->billing_address_country];
			}
		}
	}

	function validateAccount(&$bean, $event, $arguments){
		// SADEK - DELETE THE LINE BELOW TO GO LIVE WITH THIS
		// return;
		// SADEK - DELETE THE LINE ABOVE TO GO LIVE WITH THIS
		
		global $current_user;
		if($event == "before_save"){
			// If the user is a member of the Finance team, we don't validate
			if($current_user->check_role_membership('Finance')){
				return;
			}

			require_once('custom/si_custom_files/checkRecordValidity.php');
			$checker = new checkRecordValidity();
			$checker->checkValidity($bean, 'custom/si_custom_files/accCheckMeta.php', false);
			$warningString = '';
			if(!empty($checker->warningFields)){
				$fieldDisplay = array();
				foreach($checker->warningFields as $fieldArr){
					$fieldDisplay[] = $fieldArr['display'];
					if(!empty($bean->fetched_row)){
						$bean->$fieldArr['field'] = $bean->fetched_row[$fieldArr['field']];
					}
				}
				foreach($checker->warningArray as $string){
					$warningString .= "$string\n";
				}

				global $sugar_config;
				$body = "";
				if(!empty($bean->fetched_row)){
					$body .= "The following fields have been reverted back to their values before you saved the record:\n";
					$body .= "'" . implode("', '", $fieldDisplay) . "'\n\n";
					$body .= "The reason they were reverted back was due to the following rules:\n";
				}
				else{
					$body .= "Please resolve the following issues before this Account can be saved:\n";
				}
				$body .= $warningString."\n";
				$body .= $sugar_config['site_url']."/index.php?module=Accounts&action=DetailView&record={$bean->id}\n\n";
				$body .= "Please contact sales-ops@sugarcrm.com if you have any questions.\n\nThanks,\nSugar Internal";
				$subject = "Account Validity Check: '{$bean->name}'";
				$from = array('from_name' => 'Sales Ops', 'from_address' => 'sales-ops@sugarcrm.com');
				$cc_user = '';
				if(isset($bean->assigned_user_id) && $bean->assigned_user_id != $current_user->id){
					$cc_user = new User();
					$cc_user->retrieve($bean->assigned_user_id);
				}
				$this->sendAccountNotification($current_user, $subject, $body, $cc_user, $from);
			}

		}
	}


	function sendAccountNotification($notify_user, $subject, $body, $cc_user = '', $from = array()){
		global $sugar_config;
		require_once("include/SugarPHPMailer.php");
		require_once("modules/Administration/Administration.php");

		$admin = new Administration();
		$admin->retrieveSettings();
		$notify_mail = new SugarPHPMailer();
		$body = from_html($body);
		$notify_mail->Body = $body;

		$subject = from_html($subject);
		$notify_mail->Subject = $subject;

		$notify_address = (empty($notify_user->email1)) ? from_html($notify_user->email2) : from_html($notify_user->email1);
		$notify_name = (empty($notify_user->first_name)) ? from_html($notify_user->user_name) : from_html($notify_user->first_name . " " . $notify_user->last_name);

		$notify_mail->AddAddress($notify_address, $notify_name);

		if(!empty($cc_user)){
			$notify_cc_address = (empty($cc_user->email1)) ? from_html($cc_user->email2) : from_html($cc_user->email1);
			$notify_cc_name = (empty($cc_user->first_name)) ? from_html($cc_user->user_name) : from_html($cc_user->first_name . " " . $cc_user->last_name);
			$notify_mail->AddCC($notify_cc_address, $notify_cc_name);
		}

		if ($admin->settings['mail_sendtype'] == "SMTP")
		{
			$notify_mail->Mailer = "smtp";
			$notify_mail->Host = $admin->settings['mail_smtpserver'];
			$notify_mail->Port = $admin->settings['mail_smtpport'];
			if ($admin->settings['mail_smtpauth_req'])
			{
				$notify_mail->SMTPAuth = TRUE;
				$notify_mail->Username = $admin->settings['mail_smtpuser'];
				$notify_mail->Password = $admin->settings['mail_smtppass'];
			}
		}

		$notify_mail->From = $admin->settings['notify_fromaddress'];
		$notify_mail->FromName = (empty($admin->settings['notify_fromname'])) ? "" : $admin->settings['notify_fromname'];
		if(!empty($from) && !empty($from['from_address'])){
			$notify_mail->From = $from['from_address'];
		}
		if(!empty($from) && !empty($from['from_name'])){
			$notify_mail->FromName = $from['from_name'];
		}

		if(!$notify_mail->Send())
		{
			$GLOBALS['log']->fatal("Account Notification Logic Hook: error sending e-mail (method: {$notify_mail->Mailer}), (error: {$notify_mail->ErrorInfo})");
		}
		else
		{
			$GLOBALS['log']->debug("Account Notification Logic Hook: e-mail successfully sent - user {$GLOBALS['current_user']->user_name}");
		}
	}

	function applyOSSC(&$bean, $event, $arguments){ // Open Source Support Cases customization (Sugar Network)
		if($event == 'before_save'){
			require_once("custom/si_custom_files/OSSCFunctions.php");
			osscAccountsHandleSave($bean);
		}
	}

	// See ITRequest #12543
	// When the 'Assigned To' value of an Account changes, update all related Contracts with the new 'Assigned To' value
	function reassignContracts(&$bean, $event, $arguments) {
		if ($bean->assigned_user_id != $bean->fetched_row['assigned_user_id']) {
			$related_contracts = $bean->get_linked_beans('contracts', 'Contract');

			foreach ($related_contracts as $contract) {
				$contract->assigned_user_id = $bean->assigned_user_id;
				$contract->disable_reassignment_hook = TRUE;
				$contract->save();
			}
		}
	}

}
