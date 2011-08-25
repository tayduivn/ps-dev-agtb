<?php
if(!defined('sugarEntry') || !sugarEntry) die('Not A Valid Entry Point');
/*********************************************************************************
 *The contents of this file are subject to the SugarCRM Professional End User License Agreement
 *("License") which can be viewed at http://www.sugarcrm.com/EULA.
 *By installing or using this file, You have unconditionally agreed to the terms and conditions of the License, and You may
 *not use this file except in compliance with the License. Under the terms of the license, You
 *shall not, among other things: 1) sublicense, resell, rent, lease, redistribute, assign or
 *otherwise transfer Your rights to the Software, and 2) use the Software for timesharing or
 *service bureau purposes such as hosting the Software for commercial gain and/or for the benefit
 *of a third party.  Use of the Software may be subject to applicable fees and any use of the
 *Software without first paying applicable fees is strictly prohibited.  You do not have the
 *right to remove SugarCRM copyrights from the source code or user interface.
 * All copies of the Covered Code must include on each user interface screen:
 * (i) the "Powered by SugarCRM" logo and
 * (ii) the SugarCRM copyright notice
 * in the same form as they appear in the distribution.  See full license for requirements.
 *Your Warranty, Limitations of liability and Indemnity are expressly stated in the License.  Please refer
 *to the License for the specific language governing these rights and limitations under the License.
 *Portions created by SugarCRM are Copyright (C) 2004 SugarCRM, Inc.; All Rights Reserved.
 ********************************************************************************/
/*********************************************************************************
 * $Id: GeneratePassword.php 38040 2008-07-21 17:21:56Z awu $
 * Description:  TODO: To be written.
 * Portions created by SugarCRM are Copyright (C) SugarCRM, Inc.
 * All Rights Reserved.
 * Contributor(s): ______________________________________..
 ********************************************************************************/

    require_once('include/entryPoint.php');

    require_once('modules/Users/language/en_us.lang.php');
    global $app_strings;
    global $sugar_config;
    global $new_pwd;

  	$mod_strings=return_module_language('','Users');
  	$res=$GLOBALS['sugar_config']['passwordsetting'];
	$regexmail = "/^\w+(['\.\-\+]?\w+)*@\w+([\.-]?\w+)*(\.\w{2,4})+\$/";

///////////////////////////////////////////////////
///////  Retrieve user

    $usr= new user();
    if(isset( $_POST['username']) && isset($_POST['user_email'] )){
    	if ($_POST['username'] != '' && $_POST['user_email'] != ''){
	        $usr_id=$usr->retrieve_user_id($_POST['username']);
	        $usr->retrieve($usr_id);
	        if ($usr->email1 !=  $_POST['user_email']){
	            echo $mod_strings['ERR_PASSWORD_USERNAME_MISSMATCH'];
	            return;
    	    }
    	    if ($usr->portal_only || $usr->is_group){
	            echo $mod_strings['LBL_PROVIDE_USERNAME_AND_EMAIL'];
	            return;
    	    }
    	}
    	else
    	{
    		echo  $mod_strings['LBL_PROVIDE_USERNAME_AND_EMAIL'];
    		return;
    	}
    }
    else{
        if (isset($_POST['userId']) && $_POST['userId'] != ''){
            $usr->retrieve($_POST['userId']);
        }
        else{
        	if(isset( $_POST['sugar_user_name']) && isset($_POST['sugar_user_name'] )){
				$usr_id=$usr->retrieve_user_id($_POST['sugar_user_name']);
	        	$usr->retrieve($usr_id);
			}
    		else{
    			echo  $mod_strings['ERR_USER_INFO_NOT_FOUND'];
            	return;
    		}
    	}
    }

///////
///////////////////////////////////////////////////

///////////////////////////////////////////////////
///////  Check email address

	if (!preg_match($regexmail, $usr->emailAddress->getPrimaryAddress($usr))){
		echo $mod_strings['ERR_EMAIL_INCORRECT'];
		return;
	}

///////
///////////////////////////////////////////////////


	// if i need to generate a password (not a link)
    if (!isset($_POST['link'])){
	    $charBKT='';
	    //chars to select from
	    $LOWERCASE = "abcdefghijklmnpqrstuvwxyz";
	    $NUMBER = "0123456789";
	    $UPPERCASE = "ABCDEFGHIJKLMNOPQRSTUVWXYZ";
	    $SPECIAL = '~!@#$%^&*()_+=-{}|'; 
	    $condition = 0;
	    $charBKT.=$UPPERCASE.$LOWERCASE.$NUMBER;
	    $password="";
		//BEGIN SUGARCRM flav=pro ONLY

	    // Count the number of requirements
	    if($res['onenumber']=='1')
	        $condition+=1;
	    if($res['onelower']=='1')
	        $condition+=1;
	    if($res['oneupper']=='1')
	        $condition+=1;
	    if($res['onespecial']=='1')
	        $condition+=1;
	    // if there is more requirements than the minimum lenght, minimum lenght= number of requirements
	    $lenght = $res['minpwdlength']<=$condition ? $condition : $res['minpwdlength'];
	    if($lenght<6)
		//END SUGARCRM flav=pro ONLY

	    	$lenght='6';
	    // Create random characters for the ones that doesnt have requirements
	    for ($i=0;$i<$lenght-$condition;$i++)  // loop and create password
	       $password = $password . substr ($charBKT, rand() % strlen($charBKT), 1);
		//BEGIN SUGARCRM flav=pro ONLY

	    if($res['onelower']=='1'){ // If a lower caracter is required, i add one in the password
	    	if(strlen($password)!='0') // If there is other characters in the password, I insert one in a random position
	    		$password =substr_replace ($password , substr ($LOWERCASE, rand() % strlen($LOWERCASE), 1), rand() % strlen($password) ,0);
	    	else // otherwise i put one in first position
	    		$password = $password . substr ($LOWERCASE, rand() % strlen($LOWERCASE), 1);
	    }
	    if($res['onenumber']=='1'){
	    	if(strlen($password)!='0')
	    		$password =substr_replace ($password , substr ($NUMBER, rand() % strlen($NUMBER), 1), rand() % strlen($password) ,0);
	    	else
	    		$password = $password . substr ($NUMBER, rand() % strlen($NUMBER), 1);
	    }
	    if($res['oneupper']=='1'){
	    	if(strlen($password)!='0')
	    		$password =substr_replace ($password , substr ($UPPERCASE, rand() % strlen($UPPERCASE), 1), rand() % strlen($password) ,0);
	    	else
	    		$password = $password . substr ($UPPERCASE, rand() % strlen($UPPERCASE), 1);
	    }
	    if($res['onespecial']=='1'){
	    	if(strlen($password)!='0')
	    		$password =substr_replace ($password , substr ($SPECIAL, rand() % strlen($SPECIAL), 1), rand() % strlen($password) ,0);
	    	else
	    		$password = $password . substr ($SPECIAL, rand() % strlen($SPECIAL), 1);
	    }
	   //END SUGARCRM flav=pro ONLY

    }

///////////////////////////////////////////////////
///////  Create URL

// if i need to generate a link
if (isset($_POST['link']) && $_POST['link'] == '1'){
	global $timedate;
	$guid=create_guid();
	$url=$GLOBALS['sugar_config']['site_url']."/index.php?entryPoint=Changenewpassword&guid=$guid";
	$time_now=TimeDate::getInstance()->nowDb();
	//$q2="UPDATE `users_password_link` SET `deleted` = '1' WHERE `username` = '".$_POST['username']."'";
	//$usr->db->query($q2);
	$q = "INSERT INTO users_password_link (id, username, date_generated) VALUES('".$guid."','".$_POST['username']."',' ".$time_now."' ) ";
	$usr->db->query($q);
}
///////
///////////////////////////////////////////////////

///////  Email creation
	global $sugar_config, $current_user;
    if (isset($_POST['link']) && $_POST['link'] == '1')
    	$emailTemp_id = $res['lostpasswordtmpl'];
    else
    	$emailTemp_id = $res['generatepasswordtmpl'];

    $emailTemp = new EmailTemplate();
    $emailTemp->disable_row_level_security = true;
    if ($emailTemp->retrieve($emailTemp_id) == ''){
        echo $mod_strings['LBL_EMAIL_TEMPLATE_MISSING'];
        $new_pwd='4';
        return;}

    //replace instance variables in email templates
    $htmlBody = $emailTemp->body_html;
    $body = $emailTemp->body;
    if (isset($_POST['link']) && $_POST['link'] == '1'){
    	$htmlBody = str_replace('$contact_user_link_guid', $url, $htmlBody);
    	$body = str_replace('$contact_user_link_guid', $url, $body);
    }
    else{
    	$htmlBody = str_replace('$contact_user_user_hash', $password, $htmlBody);
    	$body = str_replace('$contact_user_user_hash', $password, $body);
    }
    // Bug 36833 - Add replacing of special value $instance_url
    $htmlBody = str_replace('$config_site_url',$sugar_config['site_url'], $htmlBody);
    $body = str_replace('$config_site_url',$sugar_config['site_url'], $body);
    
    $htmlBody = str_replace('$contact_user_user_name', $usr->user_name, $htmlBody);
    $htmlBody = str_replace('$contact_user_pwd_last_changed', TimeDate::getInstance()->nowDb(), $htmlBody);
    $body = str_replace('$contact_user_user_name', $usr->user_name, $body);
    $body = str_replace('$contact_user_pwd_last_changed', TimeDate::getInstance()->nowDb(), $body);
    $emailTemp->body_html = $htmlBody;
    $emailTemp->body = $body;
    require_once('include/SugarPHPMailer.php');

    $itemail=$usr->emailAddress->getPrimaryAddress($usr);
    //retrieve IT Admin Email
    //_ppd( $emailTemp->body_html);
    //retrieve email defaults
    $emailObj = new Email();
    $defaults = $emailObj->getSystemDefaultEmail();
    $mail = new SugarPHPMailer();
    $mail->setMailerForSystem();
    //$mail->IsHTML(true);
    $mail->From = $defaults['email'];
    $mail->FromName = $defaults['name'];
    $mail->ClearAllRecipients();
    $mail->ClearReplyTos();
    $mail->Subject=from_html($emailTemp->subject);
    if($emailTemp->text_only != 1){
        $mail->IsHTML(true);
        $mail->Body=from_html($emailTemp->body_html);
        $mail->AltBody=from_html($emailTemp->body);
    }
    else {
        $mail->Body_html=from_html($emailTemp->body_html);
        $mail->Body=from_html($emailTemp->body);
    }
    if($mail->Body == '' && $current_user->is_admin){
    	echo $app_strings['LBL_EMAIL_TEMPLATE_EDIT_PLAIN_TEXT'];
        $new_pwd='4';
    	return;}
    if($mail->Mailer == 'smtp' && $mail->Host ==''&& $current_user->is_admin){
    	echo $mod_strings['ERR_SERVER_SMTP_EMPTY'];
        $new_pwd='4';
    	return;}

    $mail->prepForOutbound();
    $hasRecipients = false;

    if (!empty($itemail)){
        if($hasRecipients){
            $mail->AddBCC($itemail);
        }else{
            $mail->AddAddress($itemail);
        }
        $hasRecipients = true;
    }
    $success = false;
    if($hasRecipients){
    	$success = @$mail->Send();
    }

    //now create email
    if($success){

        $emailObj->team_id = 1;
        $emailObj->to_addrs= '';
        $emailObj->type= 'archived';
        $emailObj->deleted = '0';
        $emailObj->name = $mail->Subject ;
        $emailObj->description = $mail->Body;
        $emailObj->description_html =null;
        $emailObj->from_addr = $mail->From;
        $emailObj->parent_type = 'User';
        $emailObj->date_sent =TimeDate::getInstance()->nowDb();
        $emailObj->modified_user_id = '1';
        $emailObj->created_by = '1';
        $emailObj->status='sent';
        $retId = $emailObj->save();
        echo '1';
        if (!isset($_POST['link'])){
	        $user_hash = strtolower(md5($password));
	        $usr->setPreference('loginexpiration','0');
	        $usr->setPreference('lockout','');
		$usr->setPreference('loginfailed','0');
		$usr->savePreferencesToDB();
	        //set new password
	        $now=TimeDate::getInstance()->nowDb();
	        $query = "UPDATE $usr->table_name SET user_hash='$user_hash', system_generated_password='1', pwd_last_changed='$now' where id='$usr->id'";
	        $usr->db->query($query, true, "Error setting new password for $usr->user_name: ");
        }
    }else{
    	$new_pwd='4';
    	if ($current_user->is_admin){
    		$email_errors=$mod_strings['ERR_EMAIL_NOT_SENT_ADMIN'];
    		if ($mail->Mailer == 'smtp')
    			$email_errors.="\n-".$mod_strings['ERR_SMTP_URL_SMTP_PORT'];
    		if ($mail->SMTPAuth)
    		 	$email_errors.="\n-".$mod_strings['ERR_SMTP_USERNAME_SMTP_PASSWORD'];
    		$email_errors.="\n-".$mod_strings['ERR_RECIPIENT_EMAIL'];
    		$email_errors.="\n-".$mod_strings['ERR_SERVER_STATUS'];
    		echo $email_errors;
    	}
    	else
    		echo $mod_strings['LBL_EMAIL_NOT_SENT'];
    }
    return;

?>
