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
require_once('modules/Mailer/SimpleMailer.php');

class EmailsApi extends ModuleApi
{
    public function __construct() {}

    public function registerApiRest() {
        $api = array(
            /**
            'listMail'     => array(
                'reqType'   => 'GET',
                'path'      => array('Emails'),
                'pathVars'  => array(''),
                'method'    => 'listMail',
                'shortHelp' => 'List Email Records',
                'longHelp'  => 'include/api/html/modules/Emails/EmailsApi.html#listMail',
            ),
            **/

            'retrieveMail' => array(
                'reqType'   => 'GET',
                'path'      => array('Emails', '?'),
                'pathVars'  => array('', 'email_id'),
                'method'    => 'retrieveMail',
                'shortHelp' => 'Retrieve Email Record',
                'longHelp'  => 'include/api/html/modules/Emails/EmailsApi.html#retrieveMail',
            ),

            'sendMail'     => array(
                'reqType'   => 'POST',
                'path'      => array('Emails'),
                'pathVars'  => array(''),
                'method'    => 'bridgeMail',
                'shortHelp' => 'Create Mail Item',
                'longHelp'  => 'include/api/html/modules/Emails/EmailsApi.html#createMail',
            ),
        );

        return $api;
    }

    public function bridgeMail($api, $args) {
        require_once("include/OutboundEmail/OutboundEmail.php");
        require_once("include/ytree/Tree.php");
        require_once("include/ytree/ExtNode.php");

        global $app_strings;
        global $current_user;
        global $timedate;

        $email = new Email();
        $email->email2init();

        $email->type = 'out';
        $email->status = 'sent';

        $ie = new InboundEmail();
        $ie->email = $email;

        if(isset($_REQUEST['email_id']) && !empty($_REQUEST['email_id'])) {// && isset($_REQUEST['saveDraft']) && !empty($_REQUEST['saveDraft'])) {
            $email->retrieve($_REQUEST['email_id']); // uid is GUID in draft cases
        }
        if (isset($_REQUEST['uid']) && !empty($_REQUEST['uid'])) {
            $email->uid = $_REQUEST['uid'];
        }


        /*---------------------------------------------------------------*/

        $GLOBALS['log']->error("********** EMAIL 2.0 - Asynchronous - at: fillComposeCache");
        $out = array();
        $email_templates_arr = $email->et->getEmailTemplatesArray();
        natcasesort($email_templates_arr);
        $out['emailTemplates'] = $email_templates_arr;
        $sigs = $current_user->getSignaturesArray();
        // clean "none"
        foreach($sigs as $k => $v) {
            if($k == "") {
                $sigs[$k] = $app_strings['LBL_NONE'];
            } else if (is_array($v) && isset($v['name'])){
                $sigs[$k] = $v['name'];
            } else{
                $sigs[$k] = $v;
            }
        }
        $out['signatures'] = $sigs;
        $out['fromAccounts'] = $email->et->getFromAccountsArray($ie);
        $out['errorArray'] = array();

        $oe = new OutboundEmail();
        if( $oe->doesUserOverrideAccountRequireCredentials($current_user->id) )
        {
            $overideAccount = $oe->getUsersMailerForSystemOverride($current_user->id);
            //If the user override account has not been created yet, create it for the user.
            if($overideAccount == null)
                $overideAccount = $oe->createUserSystemOverrideAccount($current_user->id);

            $out['errorArray'] = array($overideAccount->id => $app_strings['LBL_EMAIL_WARNING_MISSING_USER_CREDS']);
        }

        /*---------------------------------------------------------------*/



        $sendto = array();
        if (is_array($args["to_addresses"])) {
            foreach ($args["to_addresses"] AS $toAddress) {
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
        if (is_array($args["cc_addresses"])) {
            foreach ($args["cc_addresses"] AS $ccAddress) {
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
        if (is_array($args["bcc_addresses"])) {
            foreach ($args["bcc_addresses"] AS $bccAddress) {
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
            if ($j+1<count($sendto)) $s .= ',';
        }
        $sendto_addresses = htmlspecialchars($s);

        $s = "";
        for ($j=0; $j<count($sendcc); $j++) {
            $rec = $sendcc[$j];
            if (!empty($rec['display']))
                $s .= trim($rec['display'])." ";
            $s .= '<'.$rec['email'].'>';
            if ($j+1<count($sendcc)) $s .= ',';
        }
        $sendcc_addresses = htmlspecialchars($s);

        $s = "";
        for ($j=0; $j<count($sendbcc); $j++) {
            $rec = $sendbcc[$j];
            if (!empty($rec['display']))
                $s .= trim($rec['display'])." ";
            $s .= '<'.$rec['email'].'>';
            if ($j+1<count($sendbcc)) $s .= ',';
        }
        $sendbcc_addresses= htmlspecialchars($s);


        $attachments=null;
        if (is_array($args["attachments"]) && ($numAttachments = count($args["attachments"])) > 0) {
            $attachments="";
            for ($i=0; $i<$numAttachments; $i++) {
                $attachment = $args["attachments"][$i];
                if ($i>0) {
                    $attachments .= "::";
                }
                $attachments .= $attachment["id"].$attachment["name"];
            }
        }

        $documents=null;
        if (is_array($args["documents"]) && ($numDocuments = count($args["documents"])) > 0) {
            $documents="";
            for ($i=0; $i<$numDocuments; $i++) {
                $document = $args["documents"][$i];
                if ($i>0) {
                    $documents .= "::";
                }
                $documents .= $document["id"].$document["name"];
            }
        }

        $request = array(

            'sendSubject'       => $args['subject'],
            'sendTo'            => $sendto_addresses,
            'sendCc'            => $sendcc_addresses,
            'sendBcc'           => $sendbcc_addresses,

            /*******/

            'saveToSugar'       => '1',

        );

        if (!empty($args['html_body'])) {
            $request['sendDescription']  = urldecode($args['html_body']);
            $request['setEditor'] = '1';

        }
        else if (!empty($args['text_body'])) {
            $request['sendDescription']  = urldecode($args['text_body']);
        }
        else {
            $request['sendDescription']  = '';
        }

        // Send From User Account
        if (count($out['fromAccounts']) > 0) {
            $request['fromAccount']  = $out['fromAccounts'][0]['value'];
        }

        if (!empty($attachments)) {
            $request['attachments']  = $attachments;
        }

        if (!empty($documents)) {
            $request['documents']  = $documents;
        }


        if (is_array($args["relate_to"])) {
            $relate_to = $args["relate_to"];
            if (!empty($relate_to["type"]) && !empty($relate_to["id"])) {
                $request['parent_type'] = $relate_to["type"];
                $request['parent_id'] = $relate_to["id"];
            }
        }


        if (is_array($args["teams"])) {
            $teams = $args["teams"];
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

        if ($args['status'] == 'draft') {
            $request['saveDraft'] = 'true';    // Send is the default behavior
        }

        $_REQUEST = array_merge($_REQUEST, $request);
        $edata=null;
        try {
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
                "FUNCTION"   => "sendMail",
                "ARGS"       => $args,
                "REQUEST"    => $request,
                "ERROR_MESSAGE" => $e->getMessage(),
                "ERROR_DATA" => $edata,
            );
            return $result;
        }

        // ob_end_clean();

        $result = array(
            "FUNCTION"   => "sendMail",
            "ARGS"       => $args,
            "REQUEST"    => $request,
            "OUT"        => $out,
            "SUCCESS"    => $sendResult
        );

        return $result;
    }


    /**
     * @param $api
     * @param $args
     * @return array
     */
    public function createMail($api, $args) {
        $admin = new Administration();
        $admin->retrieveSettings();

        $mailer = new SimpleMailer();
        $mailer->setFrom(new EmailIdentity($admin->settings['notify_fromaddress'], $admin->settings['notify_fromname']));

        if (is_array($args["to_addresses"])) {
            foreach ($args["to_addresses"] AS $toAddress) {
                $recipient = $this->generateEmailIdentity($toAddress);
                if ($recipient) {
                    $mailer->addRecipientsTo($recipient);
                }
            }
        }

        if (is_array($args["cc_addresses"])) {
            foreach ($args["cc_addresses"] AS $ccAddress) {
                $recipient = $this->generateEmailIdentity($ccAddress);
                if ($recipient) {
                    $mailer->addRecipientsCc($recipient);
                }
            }
        }

        if (is_array($args["bcc_addresses"])) {
            foreach ($args["bcc_addresses"] AS $bccAddress) {
                $recipient = $this->generateEmailIdentity($bccAddress);
                if ($recipient) {
                    $mailer->addRecipientsBcc($recipient);
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
            $args["html_body"] = urldecode($args["html_body"]);
            $mailer->setHtmlBody($args["html_body"]);
        }

        try {
            $success = $mailer->send();

            if (!$success) {

            }
        } catch(Exception $e) {

        }

        $result = array(
            "FUNCTION"   => "sendMail",
            "ARGS"       => $args,
            "SUCCESS"    => $success
        );

        return $result;
    }


    /**
     * @param $api
     * @param $args
     * @return array
     */
    public function listMail($api, $args) {
        $success=true;
        $result = array(
            "FUNCTION"   => "listMail",
            "ARGS"       => $args,
            "SUCCESS"    => $success
        );
        return $result;
    }


    /**
     * @param $api
     * @param $args
     * @return array
     */
    public function retrieveMail($api, $args) {
        $success=true;
        $result = array(
            "FUNCTION"   => "retrieveMail",
            "ARGS"       => $args,
            "SUCCESS"    => $success
        );
        return $result;
    }


    /**
     *  Local Functions
     */

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
