<?php
if (!defined('sugarEntry') || !sugarEntry) die('Not A Valid Entry Point');

/**
 * LICENSE: The contents of this file are subject to the SugarCRM Professional
 * End User License Agreement ("License") which can be viewed at
 * http://www.sugarcrm.com/EULA.  By installing or using this file, You have
 * unconditionally agreed to the terms and conditions of the License, and You
 * may not use this file except in compliance with the License.  Under the
 * terms of the license, You shall not, among other things: 1) sublicense,
 * resell, rent, lease, redistribute, assign or otherwise transfer Your
 * rights to the Software, and 2) use the Software for timesharing or service
 * bureau purposes such as hosting the Software for commercial gain and/or for
 * the benefit of a third party.  Use of the Software may be subject to
 * applicable fees and any use of the Software without first paying applicable
 * fees is strictly prohibited.  You do not have the right to remove SugarCRM
 * copyrights from the source code or user interface.
 *
 * All copies of the Covered Code must include on each user interface screen:
 *  (i) the "Powered by SugarCRM" logo and
 *  (ii) the SugarCRM copyright notice
 * in the same form as they appear in the distribution.  See full license for
 * requirements.
 *
 * Your Warranty, Limitations of liability and Indemnity are expressly stated
 * in the License.  Please refer to the License for the specific language
 * governing these rights and limitations under the License.  Portions created
 * by SugarCRM are Copyright (C) 2006 SugarCRM, Inc.; All Rights Reserved.
 */

// $Id: EmailAuthenticateUser.php 51443 2009-10-12 20:34:36Z jmertic $

/**
 * This file is where the user authentication occurs. No redirection should happen in this file.
 *
 */

require_once('modules/Users/authentication/SugarAuthenticate/SugarAuthenticateUser.php');
require_once "modules/Mailer/MailerFactory.php"; // imports all of the Mailer classes that are needed

class EmailAuthenticateUser extends SugarAuthenticateUser
{
    private $passwordLength = 4;

    /**
     * This is called when a user logs in.
     *
     * @param string $name
     * @param string $password
     * @return boolean
     */
    public function loadUserOnLogin($name, $password) {
        global $login_error;

        $GLOBALS['log']->debug("Starting user load for {$name}");

        if (empty($name) || empty($password)) {
            return false;
        }

        if (empty($_SESSION['lastUserId'])) {
            $input_hash = SugarAuthenticate::encodePassword($password);
            $user_id    = $this->authenticateUser($name, $input_hash);

            if (empty($user_id)) {
                $GLOBALS['log']->fatal("SECURITY: User authentication for {$name} failed");
                return false;
            }
        }

        if (empty($_SESSION['emailAuthToken'])) {
            $_SESSION['lastUserId']     = $user_id;
            $_SESSION['lastUserName']   = $name;
            $_SESSION['emailAuthToken'] = '';

            for ($i = 0; $i < $this->passwordLength; $i++) {
                $_SESSION['emailAuthToken'] .= chr(mt_rand(48, 90));
            }

            $_SESSION['emailAuthToken'] = str_replace(array('<', '>'), array('#', '@'), $_SESSION['emailAuthToken']);
            $_SESSION['login_error']    = 'Please Enter Your User Name and Emailed Session Token';
            $this->sendEmailPassword($user_id, $_SESSION['emailAuthToken']);
            return false;
        } else {
            if (strcmp($name, $_SESSION['lastUserName']) == 0 && strcmp($password, $_SESSION['emailAuthToken']) == 0) {
                $this->loadUserOnSession($_SESSION['lastUserId']);
                unset($_SESSION['lastUserId']);
                unset($_SESSION['lastUserName']);
                unset($_SESSION['emailAuthToken']);
                return true;
            }

        }

        $_SESSION['login_error'] = 'Please Enter Your User Name and Emailed Session Token';
        return false;
    }


    /**
     * Sends the users password to the email address.
     *
     * @param string $user_id
     * @param string $password
     */
    public function sendEmailPassword($user_id, $password) {
        $result = $GLOBALS['db']->query("SELECT email1, email2, first_name, last_name FROM users WHERE id='{$user_id}'");
        $row    = $GLOBALS['db']->fetchByAssoc($result);

        if (empty($row['email1']) && empty($row['email2'])) {
            $_SESSION['login_error'] = 'Please contact an administrator to setup up your email address associated to this account';
        } else {
            $mailTransmissionProtocol = "unknown";

            try {
                $mailer                   = MailerFactory::getSystemDefaultMailer();
                $mailTransmissionProtocol = $mailer->getMailTransmissionProtocol();

                // add the recipient...

                // first get all email addresses known for this recipient
                $recipientEmailAddresses = array($row["email1"], $row["email2"]);
                $recipientEmailAddresses = array_filter($recipientEmailAddresses);

                // then retrieve first non-empty email address
                $recipientEmailAddress = array_shift($recipientEmailAddresses);

                // get the recipient name that accompanies the email address
                $recipientName = "{$row["first_name"]} {$row["last_name"]}";

                $mailer->addRecipientsTo(new EmailIdentity($recipientEmailAddress, $recipientName));

                // override the From header
                $from = new EmailIdentity("no-reply@sugarcrm.com", "Sugar Authentication");
                $mailer->setHeader(EmailHeaders::From, $from);

                // set the subject
                $mailer->setSubject("Sugar Token");

                // set the body of the email... looks to be plain-text only
                $mailer->setTextBody("Your sugar session authentication token  is: {$password}");

                $mailer->send();
                $GLOBALS["log"]->info("Notifications: e-mail successfully sent");
            } catch (MailerException $me) {
                $message = $me->getMessage();
                $GLOBALS["log"]->warn("Notifications: error sending e-mail (method: {$mailTransmissionProtocol}), (error: {$message})");
            }
        }
    }
}
