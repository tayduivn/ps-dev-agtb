<?php
if(!defined('sugarEntry') || !sugarEntry) die('Not A Valid Entry Point');
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


// require_once 'include/OutboundEmail/OutboundEmail.php';
require_once 'modules/Mailer/EmailIdentity.php';
require_once 'modules/Mailer/MailerException.php';

class MailRecord {

    public  $emailBean;
    static private $statuses = array (
        "draft",    // draft
        "ready",    // ready to be sent
        "sending",  // transient status
        "sent",     // final status
    );

    var $current_user;

    /* MailRecord Properties */
    public  $toAddresses;
    public  $ccAddresses;
    public  $bccAddresses;

    public  $attachments;
    public  $documents;
    public  $teams;
    public  $related;

    public  $subject;
    public  $html_body;
    public  $text_body;

    private $email_id;

    function __construct(User $current_user)
    {
        $this->current_user = $current_user;
    }

    static public function fromEmail(User $current_user, Email $email)
    {
        // $email->retrieve($email_id);
        $email->email2init();

        $mailRecord = new MailRecord($current_user);

        /**
        $mailRecord->toAddresses  = $args["to_addresses"];
        $mailRecord->ccAddresses  = $args["cc_addresses"];
        $mailRecord->bccAddresses = $args["bcc_addresses"];

        $mailRecord->attachments  = $args["attachments"];
        $mailRecord->documents    = $args["documents"];
        $mailRecord->teams        = $args["teams"];
        $mailRecord->related      = $args["related"];

        $mailRecord->subject      = $args["subject"];
        $mailRecord->html_body    = $args["html_body"];
        $mailRecord->text_body    = $args["text_body"];
         **/

        return $mailRecord;
    }


    public function saveAsDraft() {
        $result = $this->toEmailBean("draft");
        return $result;
    }

    public function send() {
        $result = $this->toEmailBean("ready");

        /**
        if ($result["SUCCESS"]) {
        $email = $result["EMAIL"];
        $email->type = "out";
        $email->status = "sent";
        $email->save();
        }
         **/

        return $result;
    }

    public function schedule($timedate) {
        // $pDate	= $timedate->to_display_date_time("08/12/2012 03:00:15");
        $result = $this->toEmailBean("draft");

        /**
        if ($result["SUCCESS"]) {
        $email = $result["EMAIL"];
        $email->type = "out";
        $email->status = "ready";
        $email->save();
        }
         **/

        return $result;
    }


    private function toEmailBean($status) {
        if (is_object($this->emailBean)) {
            $email = $this->emailBean;
        } else {
            $email = new Email();
        }

        $email->email2init();

        $ie = new InboundEmail();
        $ie->email = $email;

        $fromAccount = $email->et->getFromAccountsArray($ie);
        if (!is_array($fromAccount) || count($fromAccount) == 0) {
            throw new MailerException("System Email Configuration Not Found or Not Complete");
        }

        $sendto = array();
        if (is_array($this->toAddresses)) {
            foreach ($this->toAddresses AS $toAddress) {
                $recipient = $this->generateEmailIdentity($toAddress);
                if ($recipient) {
                    $sendto [] = array(
                        "email"   => $recipient->getEmail(),
                        "display" => $recipient->getName(),
                    );
                }
            }
        }

        $sendcc = array();
        if (is_array($this->ccAddresses)) {
            foreach ($this->ccAddresses AS $ccAddress) {
                $recipient = $this->generateEmailIdentity($ccAddress);
                if ($recipient) {
                    $sendcc [] = array(
                        "email"   => $recipient->getEmail(),
                        "display" => $recipient->getName(),
                    );
                }
            }
        }

        $sendbcc = array();
        if (is_array($this->bccAddresses)) {
            foreach ($this->bccAddresses AS $bccAddress) {
                $recipient = $this->generateEmailIdentity($bccAddress);
                if ($recipient) {
                    $sendbcc [] = array(
                        "email"   => $recipient->getEmail(),
                        "display" => $recipient->getName(),
                    );
                }
            }
        }


        /* Format Recipient Addresses As Comma-Separated strings */

        $s = "";
        for ($j=0; $j<count($sendto); $j++) {
            $rec = $sendto[$j];
            if (!empty($rec['display']))
                $s .= trim($rec['display'])." ";
            $s .= '<'.$rec['email'].'>';
            if ($j+1<count($sendto)) $s .= ', ';
        }
        $sendto_addresses = $s;

        $s = "";
        for ($j=0; $j<count($sendcc); $j++) {
            $rec = $sendcc[$j];
            if (!empty($rec['display']))
                $s .= trim($rec['display'])." ";
            $s .= '<'.$rec['email'].'>';
            if ($j+1<count($sendcc)) $s .= ',';
        }
        $sendcc_addresses = $s;

        $s = "";
        for ($j=0; $j<count($sendbcc); $j++) {
            $rec = $sendbcc[$j];
            if (!empty($rec['display']))
                $s .= trim($rec['display'])." ";
            $s .= '<'.$rec['email'].'>';
            if ($j+1<count($sendbcc)) $s .= ',';
        }
        $sendbcc_addresses= $s;

        $attachments=null;
        if (is_array($this->attachments) && ($numAttachments = count($this->attachments)) > 0) {
            $attachments="";
            for ($i=0; $i<$numAttachments; $i++) {
                $attachment = $this->attachments[$i];
                if ($i>0) {
                    $attachments .= "::";
                }
                $attachments .= $attachment["id"].$attachment["name"];
            }
        }

        $documents=null;
        if (is_array($this->documents) && ($numDocuments = count($this->documents)) > 0) {
            $documents="";
            for ($i=0; $i<$numDocuments; $i++) {
                $document = $this->documents[$i];
                if ($i>0) {
                    $documents .= "::";
                }
                $documents .= $document["id"];
            }
        }

        $request = array(
            'fromAccount'       => $fromAccount[0]['value'],

            'sendSubject'       => $this->subject,
            'sendTo'            => $sendto_addresses,
            'sendCc'            => $sendcc_addresses,
            'sendBcc'           => $sendbcc_addresses,

            /*******/

            'saveToSugar'       => '1',

        );

        if (!empty($this->html_body)) {
            $request['sendDescription']  = urldecode($this->html_body);
            $request['setEditor'] = '1';

        }
        else if (!empty($this->text_body)) {
            $request['sendDescription']  = urldecode($this->text_body);
        }
        else {
            $request['sendDescription']  = '';
        }

        if (!empty($attachments)) {
            $request['attachments']  = $attachments;
        }

        if (!empty($documents)) {
            $request['documents']  = $documents;
        }


        if (is_array($this->related)) {
            $related = $this->related;
            if (!empty($related["type"]) && !empty($related["id"])) {
                $request['parent_type'] = $related["type"];
                $request['parent_id'] = $related["id"];
            }
        }


        if (is_array($this->teams)) {
            $teams = $this->teams;
            if (!empty($teams["primary"])) {
                $request['primaryteam'] = $teams["primary"];
                $request['teamIds'] = $teams["primary"];
                if (isset($teams["other"]) && is_array(($teams["other"]))) {
                    foreach ($teams["other"] AS $team_id) {
                        $request['teamIds'] .= ',' . $team_id;
                    }
                }
            }
        }

        $email->type = 'out';
        $email->status = 'sent';
        if ($status == "draft") {
            $request['saveDraft'] = 'true';    // Send is the default behavior
            $email->type = 'draft';
            $email->status = 'draft';
        }

        $_REQUEST = array_merge($_REQUEST, $request);
        $edata=null;
        $sendResult = false;
        try {
            ob_start();
            $sendResult = $email->email2Send($request);
            $edata = ob_get_contents();
            ob_end_clean();
            if (strlen($edata) > 0) {
                throw new MailerException("Internal Error");
            }
        } catch (Exception $e) {
            if ($edata == null) {
                $edata = ob_get_contents();
                ob_end_clean();
            }

            $result = array(
                "SUCCESS"    => false,
                "EMAIL"      => $email,
                "REQUEST"    => $request,
                "ERROR_MESSAGE" => $e->getMessage(),
                "ERROR_DATA" => $edata,
            );
            return $result;
        }

        $result = array(
            "SUCCESS"    => $sendResult,
            "EMAIL"      => $email,
            "REQUEST"    => $request,
        );

        return $result;
    }


    /**
     * @param $data
     * @return EmailIdentity
     */
    protected function generateEmailIdentity($data) {
        if (is_array($data) && !empty($data['email'])) {
            $email = $data['email'];
            $name = null;
            if (isset($data['name'])) {
                $name = $data['name'];
            }
            $recipient = new EmailIdentity($email, $name);
        }
        return $recipient;
    }

}