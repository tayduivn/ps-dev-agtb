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
require_once 'MailerException.php';
require_once 'EmailIdentity.php';
require_once 'RecipientsCollection.php';

class Mailer
{
	protected $mailer;
	protected $configs;
	protected $sender;
	protected $recipients;
	protected $subject;
	protected $htmlBody;
	protected $textBody;

	public function __construct() {
		$this->reset();
	}

	public function reset() {
		$this->mailer = new PHPMailer();
		$this->loadDefaultConfigs();
		$this->recipients = new RecipientsCollection();
		$this->subject = null;
		$this->htmlBody = null;
		$this->textBody = null;
	}

	/**
	 * Initialize or replace the configurations with the defaults for this sending strategy.
	 */
	public function loadDefaultConfigs() {
		//@todo for now use the PHPMailer defaults, but eventually use this as a way to default to SMTP
		$defaults = array(
			'protocol' => 'mail', // smtp or other send mail program
			'host'     => 'localhost', // smtp host
			'port'     => 25 // smtp port
		);

		$this->setConfigs($defaults);
	}

	/**
	 * Use this method to replace the default configurations. This will replace the previous configurations;
	 * it will not merge the configurations.
	 *
	 * @param array $configs
	 */
	public function setConfigs($configs) {
		$this->configs = $configs;
	}

	/**
	 * Merge the passed in configurations with the existing configurations.
	 *
	 * @param array $configs
	 */
	public function mergeConfigs($configs) {
		$this->configs = array_merge($this->configs, $configs);
	}

	/**
	 * @return array
	 */
	public function getConfigs() {
		return $this->configs;
	}

	/**
	 * @param EmailIdentity $sender
	 */
	public function setSender(EmailIdentity $sender) {
		$this->sender = $sender;
	}

	/**
	 * @param array $recipients     Array of EmailIdentity objects.
	 * @return array    Array of invalid recipients
	 */
	public function addRecipientsTo($recipients = array()) {
		return $this->recipients->addRecipients($recipients);
	}

	/**
	 * @param array $recipients     Array of EmailIdentity objects.
	 * @return array    Array of invalid recipients
	 */
	public function addRecipientsCc($recipients = array()) {
		return $this->recipients->addRecipients($recipients, RecipientsCollection::FunctionAddCc);
	}

	/**
	 * @param array $recipients     Array of EmailIdentity objects.
	 * @return array    Array of invalid recipients
	 */
	public function addRecipientsBcc($recipients = array()) {
		return $this->recipients->addRecipients($recipients, RecipientsCollection::FunctionAddBcc);
	}

	/**
	 * @param string $subject
	 */
	public function setSubject($subject) {
		$this->subject = $subject;
	}

	/**
	 * @return string
	 */
	public function getSubject() {
		return $this->subject;
	}

	/**
	 * @param string $textBody
	 */
	public function setTextBody($textBody) {
		$this->textBody = $textBody;
	}

	/**
	 * @param string $htmlBody
	 */
	public function setHtmlBody($htmlBody) {
		$this->htmlBody = $htmlBody;
	}

	/**
     * @return boolean  true=success
	 */
	public function send() {
		try {
			if (!($this->mailer instanceof PHPMailer)) {
				throw new MailerException("Invalid mailer");
			}

			$this->transferConnectionData();
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
			$GLOBALS['log']->error($me->getMessage());
			return false;
		}

		return true;
	}

	protected function transferConnectionData() {
		$this->mailer->Mailer = $this->configs->getProtocol();
		$this->mailer->Host = $this->configs->getHost();
		$this->mailer->Port = $this->configs->getPort();
	}

	protected function transferHeaders() {
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

	protected function transferRecipients() {
		$to = $this->recipients->getTo();
		$cc = $this->recipients->getCc();
		$bcc = $this->recipients->getBcc();

		//@todo should you be able to initiate a send without any To recipients?
		foreach ($to as $recipient) {
			$this->mailer->AddAddress($recipient->getEmail(), $recipient->getName());
		}

		foreach ($cc as $recipient) {
			$this->mailer->AddCC($recipient->getEmail(), $recipient->getName());
		}

		foreach ($bcc as $recipient) {
			$this->mailer->AddBCC($recipient->getEmail(), $recipient->getName());
		}
	}

	/**
	 * @throws MailerException
	 */
	protected function transferBody() {
		if ($this->htmlBody && $this->textBody) {
			$this->mailer->IsHTML(true);
			$this->mailer->Body = $this->htmlBody;
			$this->mailer->AltBody = $this->textBody;
		} elseif ($this->textBody) {
			$this->mailer->Body = $this->textBody;
		} elseif ($this->htmlBody) {
			// you should never actually send an email without a plain-text part, but we'll allow it (for now)
			$this->mailer->Body = $this->htmlBody;
		} else {
			throw new MailerException("No email body was provided");
		}
	}
}
