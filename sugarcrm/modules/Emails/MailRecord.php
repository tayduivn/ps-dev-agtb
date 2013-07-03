<?php
if (!defined('sugarEntry') || !sugarEntry) {
    die('Not A Valid Entry Point');
}
/*********************************************************************************
 * The contents of this file are subject to the SugarCRM Professional End User
 * License Agreement ("License") which can be viewed at
 * http://www.sugarcrm.com/EULA.  By installing or using this file, You have
 * unconditionally agreed to the terms and conditions of the License, and You may
 * not use this file except in compliance with the License. Under the terms of the
 * license, You shall not, among other things: 1) decodesublicense, resell, rent, lease,
 * redistribute, assign or otherwise transfer Your rights to the Software, and 2)
 * use the Software for timesharing or service bureau purposes such as hosting the
 * Software for commercial gain and/or for the benefit of a third party.  Use of
 * the Software may be subject to applicable fees and any use of the Software
 * without first paying applicable fees is strictly prohibited.  You do not have
 * the right to remove SugarCRM copyrights from the source code or user interface.
 * All copies of the Covered Code must include on each user interface screen:
 * (i) the "Powered by SugarCRM" logo and (ii) the SugarCRM copyright notice
 * in the same form as they appear in the distribution.  See full license for
 * requirements.  Your Warranty, Limitations of liability and Indemnity are
 * expressly stated in the License.  Please refer to the License for the specific
 * language governing these rights and limitations under the License.
 * Portions created by SugarCRM are Copyright (C) 2004 SugarCRM, Inc.;
 * All Rights Reserved.
 ********************************************************************************/

require_once 'modules/Mailer/EmailIdentity.php';
require_once 'modules/Mailer/MailerException.php';

class MailRecord
{
    static private $statuses = array(
        "draft", // draft
        "ready", // ready to be sent
        "sending", // transient status
        "sent", // final status
    );

    public $emailBean;
    public $mailConfig;
    public $toAddresses;
    public $ccAddresses;
    public $bccAddresses;
    public $attachments;
    public $documents;
    public $teams;
    public $related;
    public $subject;
    public $html_body;
    public $text_body;

    function __construct() {}

    /**
     * Saves the email as a draft.
     *
     * @return array
     */
    public function saveAsDraft()
    {
        return $this->toEmailBean("draft");
    }

    /**
     * Saves and sends the email.
     *
     * @return array
     */
    public function send()
    {
        return $this->toEmailBean("ready");
    }

    /**
     * Prepares and executes the email request according to the expectations of the status.
     *
     * @param $status
     * @return array
     * @throws MailerException
     */
    protected function toEmailBean($status)
    {
        $result = array();
        $email  = null;

        if (is_object($this->emailBean)) {
            $email = $this->emailBean;
        } else {
            $email = new Email();
        }

        $email->email2init();

        $fromAccount = null;

        if (!empty($this->mailConfig)) {
            $fromAccount = $this->mailConfig;
        }

        $to  = $this->addRecipients($this->toAddresses);
        $cc  = $this->addRecipients($this->ccAddresses);
        $bcc = $this->addRecipients($this->bccAddresses);

        $attachments = $this->addAttachments($this->attachments);
        $documents   = $this->addAttachments($this->documents, true);

        $request  = $this->setupSendRequest($status, $fromAccount, $to, $cc, $bcc, $attachments, $documents);
        $_REQUEST = array_merge($_REQUEST, $request);

        $errorData  = null;
        $sendResult = false;

        try {
            $this->startCapturingOutput();
            $sendResult = $email->email2Send($request);
            $errorData  = $this->endCapturingOutput();

            if (strlen($errorData) > 0) {
                throw new MailerException('Email2Send returning unexpected output: ' . $errorData);
            }

            $result = array(
                "SUCCESS" => $sendResult,
                "EMAIL"   => $email,
                "REQUEST" => $request,
            );
        } catch (Exception $e) {
            if (is_null($errorData)) {
                $errorData = $this->endCapturingOutput();
            }

            if (!($e instanceof MailerException)) {
                $e = new MailerException($e->getMessage());
            }
            $GLOBALS["log"]->error($e->getLogMessage());

            $result = array(
                "SUCCESS"       => false,
                "EMAIL"         => $email,
                "REQUEST"       => $request,
                "ERROR_MESSAGE" => $e->getUserFriendlyMessage(),
                "ERROR_DATA"    => $errorData,
            );
        }

        return $result;
    }

    /**
     * Constructs the email request that will passed on.
     *
     * @param string $status
     * @param null   $from
     * @param string $to
     * @param string $cc
     * @param string $bcc
     * @param string $attachments
     * @param string $documents
     * @return array
     */
    protected function setupSendRequest(
        $status = "ready",
        $from = null,
        $to = "",
        $cc = "",
        $bcc = "",
        $attachments = "",
        $documents = ""
    ) {
        $request = array(
            "fromAccount"     => $from,
            "sendSubject"     => $this->subject,
            "sendTo"          => $to,
            "sendCc"          => $cc,
            "sendBcc"         => $bcc,
            "saveToSugar"     => "1",
            "sendDescription" => "", // defaulted to an empty string
        );

        if (!empty($this->html_body)) {
            $request["sendDescription"] = urldecode($this->html_body);
            $request["setEditor"]       = "1";
        } elseif (!empty($this->text_body)) {
            $request["sendDescription"] = urldecode($this->text_body);
        }

        if (!empty($attachments)) {
            $request["attachments"] = $attachments;
        }

        if (!empty($documents)) {
            $request["documents"] = $documents;
        }

        if (is_array($this->related) && !empty($this->related["type"]) && !empty($this->related["id"])) {
            $request["parent_type"] = $this->related["type"];
            $request["parent_id"]   = $this->related["id"];
        }

        if (is_array($this->teams) && !empty($this->teams["primary"])) {
            $request["primaryteam"] = $this->teams["primary"];
            $teamIds                = array($this->teams["primary"]);

            if (isset($this->teams["other"]) && is_array(($this->teams["other"]))) {
                foreach ($this->teams["other"] as $teamId) {
                    $teamIds[] = $teamId;
                }
            }

            $request["teamIds"] = implode(",", $teamIds);
        }

        if ($status == "draft") {
            $request["saveDraft"] = "true"; // send ("ready") is the default behavior
        }

        return $request;
    }

    /**
     * Starts the output buffer. Wraps the function call so that it is possible to mock/stub this behavior.
     */
    protected function startCapturingOutput()
    {
        ob_start();
    }

    /**
     * Collects the contents from the output buffer and cleans the buffer. Wraps the function calls so that it is
     * possible to mock/stub this behavior.
     *
     * @return string
     */
    protected function endCapturingOutput()
    {
        $contents = ob_get_contents();
        ob_end_clean();

        return $contents;
    }

    /**
     * Format recipient addresses as comma-separated strings.
     *
     * @param array $recipients
     * @return string
     */
    protected function addRecipients($recipients = array())
    {
        $addedRecipients = array();

        if (is_array($recipients)) {
            foreach ($recipients as $recipient) {
                $identity = $this->generateEmailIdentity($recipient);

                if ($identity) {
                    $formattedRecipient = array();
                    $name               = $identity->getName();

                    if (!empty($name)) {
                        $formattedRecipient[] = $name;
                    }

                    $formattedRecipient[] = "<" . $identity->getEmail() . ">";

                    // add the formatted recipient to the array of all recipients to be imploded
                    // separate the name and email address by a single space
                    $addedRecipients[] = implode(" ", $formattedRecipient);
                }
            }
        }

        return implode(", ", $addedRecipients);
    }

    /**
     * Format attachments or documents for inclusion in the email request.
     *
     * @param array $attachments
     * @param bool  $isDocuments
     * @return string
     */
    protected function addAttachments($attachments = array(), $isDocuments = false)
    {
        $addedAttachments = array();

        if (is_array($attachments)) {
            foreach ($attachments as $attachment) {
                $addedAttachments[] = ($isDocuments) ? $attachment : $attachment["id"] . $attachment["name"];
            }
        }

        return implode("::", $addedAttachments);
    }

    /**
     * Returns an EmailIdentity object from the set of recipients data that is passed in.
     *
     * @param $data
     * @return EmailIdentity
     */
    protected function generateEmailIdentity($data)
    {
        $recipient = null;

        if (is_array($data) && !empty($data['email'])) {
            $email = $data['email'];
            $name  = null;

            if (isset($data['name'])) {
                $name = $data['name'];
            }

            $recipient = new EmailIdentity($email, $name);
        }

        return $recipient;
    }
}
