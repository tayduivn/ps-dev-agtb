<?php
if (!defined('sugarEntry') || !sugarEntry) die('Not A Valid Entry Point');

/*********************************************************************************
 * The contents of this file are subject to the SugarCRM Master Subscription
 * Agreement ("License") which can be viewed at
 * http://www.sugarcrm.com/crm/master-subscription-agreement
 * By installing or using this file, You have unconditionally agreed to the
 * terms and conditions of the License, and You may not use this file except in
 * compliance with the License.  Under the terms of the license, You shall not,
 * among other things: 1) sublicense, resell, rent, lease, redistribute, assign
 * or otherwise transfer Your rights to the Software, and 2) use the Software
 * for timesharing or service bureau purposes such as hosting the Software for
 * commercial gain and/or for the benefit of a third party.  Use of the Software
 * may be subject to applicable fees and any use of the Software without first
 * paying applicable fees is strictly prohibited.  You do not have the right to
 * remove SugarCRM copyrights from the source code or user interface.
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
 * by SugarCRM are Copyright (C) 2004-2011 SugarCRM, Inc.; All Rights Reserved.
 ********************************************************************************/

require_once "modules/Mailer/MailerFactory.php"; // imports all of the Mailer classes that are needed

class ReportsUtilities
{
    private $user;
    private $language;

    public function __construct() {
        global $current_user,
               $current_language;

        $this->user     = $current_user;
        $this->language = $current_language;
    }

    /**
     * Notify the report owner of an invalid report definition.
     *
     * @param User   $recipient required
     * @param string $message   required
     * @throws MailerException Allows exceptions to bubble up for the caller to report if desired.
     */
    public function sendNotificationOfInvalidReport($recipient, $message) {
        $mailer = MailerFactory::getSystemDefaultMailer();

        // set the subject of the email
        $mod_strings = return_module_language($this->language, "Reports");
        $mailer->setSubject($mod_strings["ERR_REPORT_INVALID_SUBJECT"]);

        // set the body of the email...

        $textOnly = EmailFormatter::isTextOnly($message);
        if ($textOnly) {
            $mailer->setTextBody($message);
        } else {
            $textBody = strip_tags(br2nl($message)); // need to create the plain-text part
            $mailer->setTextBody($textBody);
            $mailer->setHtmlBody($message);
        }

        // add the recipient...

        // first get all email addresses known for this recipient
        $recipientEmailAddresses = array($recipient->email1, $recipient->email2);
        $recipientEmailAddresses = array_filter($recipientEmailAddresses);

        // then retrieve first non-empty email address
        $recipientEmailAddress = array_shift($recipientEmailAddresses);

        // a MailerException is raised if $email is invalid, which prevents the call to send below
        $mailer->addRecipientsTo(new EmailIdentity($recipientEmailAddress));

        // send the email
        $mailer->send();
    }
}
