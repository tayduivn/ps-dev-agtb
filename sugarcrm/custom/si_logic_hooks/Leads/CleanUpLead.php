<?php
if(!defined('sugarEntry') || !sugarEntry) die('Not A Valid Entry Point');

class CleanUpLead {
	
	// General function to clean up data
	function cleanUpData(& $focus, $event, $arguments){
		global $app_list_strings;
		
		if($event=="before_save"){
			// BEGIN STRIPPING OF WEBSITE IF IT'S NOT SET
			//M2: seems website_c is no longer a valid field and instead is now website on the leadaccounts table.
			if(isset($focus->website) && $focus->website == "http://"){
				$focus->website = "";
			}
			// END STRIPPING OF WEBSITE IF IT'S NOT SET
			
			// BEGIN CHECK FOR VALID MX RECORD FOR EMAIL ADDRESS DOMAIN
			$valid_email = true;
			$valid_phone = true;
			
			// Using the function below, we check that the email1 is valid against the MX record
			if(!empty($focus->email1)){
				$validMX = $this->validEmailDomainMX($focus->email1);
				$stripped_email = str_replace(' ', '', $focus->email1);
				if(!$validMX){
					$focus->invalid_email = 1;
				}
				if(isset($focus->emailAddress)){
					foreach($focus->emailAddress->addresses as $ndx => $emailArr){
						if($emailArr['primary_address'] == 1){
							if(!$validMX && $emailArr['invalid_email'] == 0){
								$focus->emailAddress->addresses[$ndx]['invalid_email'] = 1;
							}
							if($stripped_email != $focus->email1 && $focus->email1 == $focus->emailAddress->addresses[$ndx]['email_address']){
								$focus->emailAddress->addresses[$ndx]['email_address'] = $stripped_email;
							}
						}
					}
				}

				if($stripped_email != $focus->email1){
					$focus->email1 = $stripped_email;
				}
			}
			
			if(isset($focus->invalid_email) && ($focus->invalid_email == 1 || $focus->invalid_email == 'on')){
				$valid_email = false;
			}
			// END CHECK FOR VALID MX RECORD FOR EMAIL ADDRESS DOMAIN
			
			// BEGIN CHECK FOR VALID PHONE NUMBER
			// If the phone is not empty and the number of digits in the phone is less than 5, it's invalid
			if(!empty($focus->phone_work)){
				$phone_work = preg_replace('/[^0-9]/', '', $focus->phone_work);
				if(strlen($phone_work) < 5){
					$valid_phone = false;
				}
			}
			// If the phone is empty, it's invalid
			else{
				$valid_phone = false;
			}
			// END CHECK FOR VALID PHONE NUMBER
			
			// BEGIN JUNK FILTER BASED ON VALID PHONE AND EMAIL
			// If the email is invalid, and the phone number is invalid
			// IT REQUEST 8036 - Only mark as junk if created by lead form, which is created by admin user. Otherwise manually created and should not be junk
			if(!$valid_email && !$valid_phone && $GLOBALS['current_user']->user_name == 'admin'){
				// Assign to Leads_Junk user
				$focus->assigned_user_id = '21030676-7f66-df76-8afb-44adcda44c25';
			}
			// END JUNK FILTER BASED ON VALID PHONE AND EMAIL
			
			// BEGIN CHECK FOR MASS UPDATE TO A SYSTEM STATUS
			if(isset($_REQUEST['massupdate']) && $_REQUEST['massupdate'] == 'true' && isset($focus->status)){
				require_once('custom/si_custom_files/custom_functions.php');
				$system_statuses = getSugarInternalLeadSystemStatuses();
				foreach($system_statuses as $status){
					if($focus->status == $status && $focus->fetched_row['status'] != $status){
						$focus->status = $focus->fetched_row['status'];
					}
				}
			}
			// END CHECK FOR MASS UPDATE TO A SYSTEM STATUS
		}	
			
			
		if($event=="after_save"){
			//end if event is after_save
		}	
		
	//end function stripBlankWebsite
	}

	function validEmailDomainMX($email_address){
		if (strlen($email_address) < 7)
			return false; 
	
		$at_pos = strpos($email_address, '@');
		if ($at_pos === false || $at_pos === 0)
			return false;
	
		list($user, $domain) = split("@", $email_address);
	
		if(strpos($domain, '.') === false){
			return false;
		}
		
		if (checkdnsrr($domain, 'MX')){
			return true;
		}
		else{
			return false;
		}
	}
//end class CleanUpLead
}


?>
