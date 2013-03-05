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
 * $Id: Menu.php 44341 2009-02-21 01:03:11Z maubert $
 * Description:  TODO To be written.
 * Portions created by SugarCRM are Copyright (C) SugarCRM, Inc.
 * All Rights Reserved.
 * Contributor(s): ______________________________________..
 ********************************************************************************/


function canSendPassword() {
    global $mod_strings,
           $current_user,
           $app_strings;

    require_once "modules/OutboundEmailConfiguration/OutboundEmailConfigurationPeer.php";

    if ($current_user->is_admin) {
        $emailTemplate                             = new EmailTemplate();
        $emailTemplate->disable_row_level_security = true;

        if ($emailTemplate->retrieve($GLOBALS['sugar_config']['passwordsetting']['generatepasswordtmpl']) == '') {
            return $mod_strings['LBL_EMAIL_TEMPLATE_MISSING'];
        }

        if (empty($emailTemplate->body) && empty($emailTemplate->body_html)) {
            return $app_strings['LBL_EMAIL_TEMPLATE_EDIT_PLAIN_TEXT'];
        }

        if (!OutboundEmailConfigurationPeer::validSystemMailConfigurationExists($current_user)) {
            return $mod_strings['ERR_SERVER_SMTP_EMPTY'];
        }

        $emailErrors = $mod_strings['ERR_EMAIL_NOT_SENT_ADMIN'];

        try {
            $config = OutboundEmailConfigurationPeer::getSystemDefaultMailConfiguration();

            if ($config instanceof OutboundSmtpEmailConfiguration) {
                $emailErrors .= "<br>-{$mod_strings['ERR_SMTP_URL_SMTP_PORT']}";

                if ($config->isAuthenticationRequired()) {
                    $emailErrors .= "<br>-{$mod_strings['ERR_SMTP_USERNAME_SMTP_PASSWORD']}";
                }
            }
        } catch (MailerException $me) {
            // might want to report the error
        }

        $emailErrors .= "<br>-{$mod_strings['ERR_RECIPIENT_EMAIL']}";
        $emailErrors .= "<br>-{$mod_strings['ERR_SERVER_STATUS']}";

        return $emailErrors;
    }

    return $mod_strings['LBL_EMAIL_NOT_SENT'];
}

function  hasPasswordExpired($username)
{
    $usr_id=User::retrieve_user_id($username);
	$usr= BeanFactory::getBean('Users', $usr_id);
    $type = '';
	if ($usr->system_generated_password == '1'){
        $type='syst';
    }
	//BEGIN SUGARCRM flav=pro ONLY
    else{
        $type='user';
    }
	//END SUGARCRM flav=pro ONLY

    if ($usr->portal_only=='0'){
	    global $mod_strings;
	    $res=$GLOBALS['sugar_config']['passwordsetting'];
	  	if ($type != '') {
		    switch($res[$type.'expiration']){

	        case '1':
		    	global $timedate;
		    	if ($usr->pwd_last_changed == ''){
		    		$usr->pwd_last_changed= $timedate->nowDb();
		    		$usr->save();
		    		}

		        $expireday = $res[$type.'expirationtype']*$res[$type.'expirationtime'];
		        $expiretime = $timedate->fromUser($usr->pwd_last_changed)->get("+{$expireday} days")->ts;

			    if ($timedate->getNow()->ts < $expiretime)
			    	return false;
			    else{
			    	$_SESSION['expiration_type']= $mod_strings['LBL_PASSWORD_EXPIRATION_TIME'];
			    	return true;
			    	}
				break;


		    case '2':
		    	$login=$usr->getPreference('loginexpiration');
		    	$usr->setPreference('loginexpiration',$login+1);
		        $usr->save();
		        if ($login+1 >= $res[$type.'expirationlogin']){
		        	$_SESSION['expiration_type']= $mod_strings['LBL_PASSWORD_EXPIRATION_LOGIN'];
		        	return true;
		        }
		        else
		            {
			    	return false;
			    	}
		    	break;

		    case '0':
		        return false;
		   	 	break;
		    }
		}
    }
}
