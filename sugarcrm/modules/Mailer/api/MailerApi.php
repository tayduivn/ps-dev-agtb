<?php
if(!defined('sugarEntry') || !sugarEntry) die('Not A Valid Entry Point');
/********************************************************************************
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

require_once('include/api/ModuleApi.php');
require_once('modules/Mailer/Mailer.php');

class MailerApi extends ModuleApi {

    public function __construct()
    {

    }

    public function registerApiRest()
    {
        $api = array (
            'listMail' => array(
                'reqType' => 'GET',
                'path' => array('Mail'),
                'pathVars' => array(''),
                'method' => 'listMail',
                'shortHelp' => 'List Mail Records',
                'longHelp' => 'include/api/html/modules/Mailer/MailApi.html#listMail',
            ),

            'retrieveMail' => array(
                'reqType' => 'GET',
                'path' => array('Mail','?'),
                'pathVars' => array('','email_id'),
                'method' => 'retrieveMail',
                'shortHelp' => 'Retrieve Mail Record',
                'longHelp' => 'include/api/html/modules/Mailer/MailApi.html#retrieveMail',
            ),

            'sendMail' => array(
                'reqType' => 'POST',
                'path' => array('Mail'),
                'pathVars' => array(''),
                'method' => 'createMail',
                'shortHelp' => 'Create Mail Item',
                'longHelp' => 'include/api/html/modules/Mailer/MailApi.html#createMail',
            ),
        );

        return $api;
    }


    /**
     *
     */
    public function createMail($api, $args)
    {
        $admin = new Administration();
        $admin->retrieveSettings();

        $mailConfig = new MailerConfig();
        if($admin->settings['mail_sendtype'] == "SMTP")
        {
            $mailConfig->setProtocol("smtp");
            $mailConfig->setHost($admin->settings['mail_smtpserver']);
            $mailConfig->setPort($admin->settings['mail_smtpport']);

            //if($admin->settings['mail_smtpauth_req']) {
            //    $mail->SMTPAuth = TRUE;
            //    $mail->Username = $admin->settings['mail_smtpuser'];
            //    $mail->Password = $admin->settings['mail_smtppass'];
            //}
            //if ($admin->settings['mail_smtpssl'] == 1) {
            //    $mail->SMTPSecure = 'ssl';
            //}
            //else if ($admin->settings['mail_smtpssl'] == 2) {
            //    $mail->SMTPSecure = 'tls';
            //}
        }
        else
            $mailConfig->setProtocol("sendmail");

        $mailer = new Mailer();
        $mailer->setConfig($mailConfig);

        $fromEmail = $admin->settings['notify_fromaddress'];
        $fromName  = empty($admin->settings['notify_fromname']) ? ' ' : $admin->settings['notify_fromname'];
        $mailer->setFrom(new EmailIdentity($fromEmail, $fromName));

        if (is_array($args["to_addresses"])) {
            foreach($args["to_addresses"] AS $toAddress) {
                $recipient = $this->getRecipient($toAddress);
                if ($recipient) {
                    $mailer->addToRecipient($recipient);
                }
            }
        }

        if (is_array($args["cc_addresses"])) {
            foreach($args["cc_addresses"] AS $ccAddress) {
                $recipient = $this->getRecipient($ccAddress);
                if ($recipient) {
                    $mailer->addCcRecipient($recipient);
                }
            }
        }

        if (is_array($args["bcc_addresses"])) {
            foreach($args["bcc_addresses"] AS $bccAddress) {
                $recipient = $this->getRecipient($bccAddress);
                if ($recipient) {
                    $mailer->addBccRecipient($recipient);
                }
            }
        }

        if (isset($args["subject"])) {
            $mailer->setSubject($args["subject"]);
        }

        if (isset($args["text_body"])) {
            $mailer->setTextBody($args["text_body"]);
        }

        if (isset($args["html_body"])) {
            // $args["html_body"] = urldecode($args["html_body"]);
            $mailer->setHtmlBody($args["html_body"]);
        }

        $success = $mailer->send();
        if (!$success) {

        }

        $result = array(
            "FUNCTION"   =>  "sendMail",
            "ARGS" => $args,
            "SUCCESS" => $success
        );

        return $result;
    }


    /**
     *
     */
    public function listMail($api, $args)
    {
        $result = array();
        return $result;
    }


    /**
     *
     */
    public function retrieveMail($api, $args)
    {
        $result = array();
        return $result;
    }



    /**
     *  Local Functions
     */

    protected function getRecipient($data) {
        if (is_array($data) && !empty($data['email'])) {
            $email = $data['email'];
            if (isset($data['name'])) {
                $name = $data['name'];
            }
            $recipient = new EmailIdentity($email, $name);
        }
        return $recipient;
    }
}
