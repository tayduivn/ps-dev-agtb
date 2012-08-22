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

require_once 'lib/phpmailer/class.phpmailer.php';
require_once 'lib/phpmailer/class.smtp.php';
require_once 'BaseMailer.php';

class SimpleMailer extends BaseMailer
{
	public function reset() {
		parent::reset();
		$this->mailer = new PHPMailer();
	}

	/**
	 * @return boolean  true=success
	 */
	public function send() {
		try {
			if (!($this->mailer instanceof PHPMailer)) {
				throw new MailerException("Invalid mailer");
			}

			$this->transferConfigurations();
			$this->connectToHost();
			$this->transferHeaders();
			$this->transferRecipients();
			$this->transferBody();

			if (!$this->mailer->IsError()) {
				$this->mailer->Send();
			}

			if ($this->mailer->IsError()) {
				throw new MailerException($this->mailer->ErrorInfo);
			}
		} catch (MailerException $me) {
			//@todo consider using status codes and grouping them based on the error level that should be used
			// so that different error levels can be logged
			// could also catch different Exception classes that extend MailerException and log at the level
			// particular to that exception type
			$me->log('error');
			return false;
		}

		return true;
	}

	private function transferConfigurations() {
		$this->mailer->Mailer   = $this->configs['protocol'];
		$this->mailer->CharSet  = $this->configs['charset'];
		$this->mailer->Encoding = $this->configs['encoding'];

		if ($this->configs['protocol'] == 'smtp') {
			$this->mailer->Host          = $this->configs['smtp']['host'];
			$this->mailer->Port          = $this->configs['smtp']['port'];
			$this->mailer->SMTPSecure    = $this->configs['smtp']['secure'];
			$this->mailer->SMTPAuth      = $this->configs['smtp']['authenticate'];
			$this->mailer->Username      = $this->configs['smtp']['username'];
			$this->mailer->Password      = from_html($this->configs['smtp']['password']);
			$this->mailer->Timeout       = $this->configs['smtp']['timeout'];
			$this->mailer->SMTPKeepAlive = $this->configs['smtp']['persist'];
		}
	}

	private function connectToHost() {
		if ($this->configs['protocol'] == 'smtp') {
			$this->mailer->smtp = new SMTP();

			if (!$this->mailer->SmtpConnect()) {
				//@todo need to tell the class what error messages to use, so the following is for reference only
//				global $app_strings;
//				if(isset($this->oe) && $this->oe->type == "system") {
//					$this->SetError($app_strings['LBL_EMAIL_INVALID_SYSTEM_OUTBOUND']);
//				} else {
//					$this->SetError($app_strings['LBL_EMAIL_INVALID_PERSONAL_OUTBOUND']);
//				}
				throw new MailerException('Failed to connect to the remote server');
			}
		}
	}

	private function transferHeaders() {
		$senderEmail = $this->sender->getEmail();

		//@todo should we really validate this email address? can that be done reliably further up in the stack?
		if (!is_string($senderEmail)) {
			throw new MailerException("Invalid sender email address");
		}

		$this->mailer->From = $senderEmail;
		$this->mailer->FromName = $this->sender->getName();

		if (!is_string($this->subject)) {
			throw new MailerException("Invalid subject");
		}

		$this->mailer->Subject = $this->subject;
	}

	private function transferRecipients() {
		$to = $this->recipients->getTo();
		$cc = $this->recipients->getCc();
		$bcc = $this->recipients->getBcc();

		//@todo should you be able to initiate a send without any To recipients?
		foreach ($to as $recipient) {
			$recipient->decode();
			$this->mailer->AddAddress($recipient->getEmail(), $recipient->getName());
		}

		foreach ($cc as $recipient) {
			$recipient->decode();
			$this->mailer->AddCC($recipient->getEmail(), $recipient->getName());
		}

		foreach ($bcc as $recipient) {
			$recipient->decode();
			$this->mailer->AddBCC($recipient->getEmail(), $recipient->getName());
		}
	}

	/**
	 * @throws MailerException
	 */
	private function transferBody() {
		if ($this->htmlBody && $this->textBody) {
			$this->mailer->Encoding = 'base64';
			$this->mailer->IsHTML(true);
			$this->mailer->Body = $this->htmlBody;
			$this->mailer->AltBody = $this->textBody;
		} elseif ($this->textBody) {
			$this->mailer->Body = $this->textBody;
		} elseif ($this->htmlBody) {
			// you should never actually send an email without a plain-text part, but we'll allow it (for now)
			//$this->mailer->Encoding = 'base64'; //@todo do we need this?
			$this->mailer->Body = $this->htmlBody;
		} else {
			throw new MailerException("No email body was provided");
		}
	}
}
