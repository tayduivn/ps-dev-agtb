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

class MailerApi extends ModuleApi
{
	public function __construct() {}

	public function registerApiRest() {
		$api = array(
			'listMail'     => array(
				'reqType'   => 'GET',
				'path'      => array('Mail'),
				'pathVars'  => array(''),
				'method'    => 'listMail',
				'shortHelp' => 'List Mail Records',
				'longHelp'  => 'include/api/html/modules/Mailer/MailApi.html#listMail',
			),

			'retrieveMail' => array(
				'reqType'   => 'GET',
				'path'      => array('Mail', '?'),
				'pathVars'  => array('', 'email_id'),
				'method'    => 'retrieveMail',
				'shortHelp' => 'Retrieve Mail Record',
				'longHelp'  => 'include/api/html/modules/Mailer/MailApi.html#retrieveMail',
			),

			'sendMail'     => array(
				'reqType'   => 'POST',
				'path'      => array('Mail'),
				'pathVars'  => array(''),
				'method'    => 'bridgeMail',
				'shortHelp' => 'Create Mail Item',
				'longHelp'  => 'include/api/html/modules/Mailer/MailApi.html#createMail',
			),
		);

		return $api;
	}

    public function bridgeMail($api, $args) {
        require_once("include/OutboundEmail/OutboundEmail.php");
        require_once("include/ytree/Tree.php");
        require_once("include/ytree/ExtNode.php");

        global $mod_strings;
        global $app_strings;
        global $current_user;
        global $sugar_config;
        global $locale;
        global $timedate;
        global $beanList;
        global $beanFiles;

        $email = new Email();
        $email->email2init();
        $ie = new InboundEmail();
        $ie->email = $email;

        $GLOBALS['log']->debug("********** EMAIL 2.0 - Asynchronous - at: sendEmail");

        $sea = new SugarEmailAddress();

        $email->type = 'out';
        $email->status = 'sent';

        if(isset($_REQUEST['email_id']) && !empty($_REQUEST['email_id'])) {// && isset($_REQUEST['saveDraft']) && !empty($_REQUEST['saveDraft'])) {
            $email->retrieve($_REQUEST['email_id']); // uid is GUID in draft cases
        }
        if (isset($_REQUEST['uid']) && !empty($_REQUEST['uid'])) {
            $email->uid = $_REQUEST['uid'];
        }


        /*---------------------------------------------------------------*/

        $GLOBALS['log']->debug("********** EMAIL 2.0 - Asynchronous - at: fillComposeCache");
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

        //------  email2send looks for 'saveDraft' entry to determine if Save Draft operation else SendMail
        // ( isset($request['saveDraft']) );



        //------  email2send looks for an accountId in entry to 'fromAccount' - if Found, use User InboundEmail settings
        //  if(!empty($request['fromAccount']))
        //	    $ie = new InboundEmail();
        //      $ie->retrieve($request['fromAccount']);


        //  if(!empty($request['attachments']))   (guid from upload || filename)
        //      e.g.     "aaaa-bbbb-cccc-ddddd-eeeeepackers.tiff"

        // if(!empty($request['documents'])) {

        // if(!empty($request['templateAttachments'])) {


        $request = array(
            'sendSubject'       => $args['subject'],
            'sendDescription'   => urldecode($args['html_body']),
            'sendTo'            => $sendto,
            'sendCc'            => $sendcc,
            'sendBcc'           => $sendbcc,
        );


        $_REQUEST = array_merge($_REQUEST, $request);

        $s = "";
        for ($j=0; $j<count($sendto); $j++) {
            $rec = $sendto[$j];
            if (!empty($rec['display']))
                $s .= trim($rec['display'])." ";
            $s .= '<'.$rec['email'].'>';
            if ($j+1<count($sendto)) $s .= ',';
        }
        $_REQUEST['sendTo'] = htmlspecialchars($s);
        $request['sendTo'] = htmlspecialchars($s);

        $s = "";
        for ($j=0; $j<count($sendcc); $j++) {
            $rec = $sendcc[$j];
            if (!empty($rec['display']))
                $s .= trim($rec['display'])." ";
            $s .= '<'.$rec['email'].'>';
            if ($j+1<count($sendcc)) $s .= ',';
        }
        $_REQUEST['sendCc'] = htmlspecialchars($s);
        $request['sendCc'] = htmlspecialchars($s);

        $s = "";
        for ($j=0; $j<count($sendbcc); $j++) {
            $rec = $sendbcc[$j];
            if (!empty($rec['display']))
                $s .= trim($rec['display'])." ";
            $s .= '<'.$rec['email'].'>';
            if ($j+1<count($sendbcc)) $s .= ',';
        }
        $_REQUEST['sendBcc'] = htmlspecialchars($s);
        $request['sendBcc'] = htmlspecialchars($s);

        if (count($out['fromAccounts']) > 0) {
            $_REQUEST['fromAccount']  = $out['fromAccounts'][0]['value'];
        }

        $_REQUEST['setEditor'] = '1';

        $sendResult = $email->email2Send($request);


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

        $mailer = new Mailer();
        $mailer->setSender(new EmailIdentity($admin->settings['notify_fromaddress'], $admin->settings['notify_fromname']));

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

        $success = $mailer->send();
        if (!$success) {

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
		$result = array();
		return $result;
	}


	/**
	 * @param $api
	 * @param $args
	 * @return array
	 */
	public function retrieveMail($api, $args) {
		$result = array();
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
