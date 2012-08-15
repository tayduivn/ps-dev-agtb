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
require_once 'MailerConfig.php';

class Mailer
{
	protected $config;
	protected $from;
	protected $recipients;
	protected $subject;
	protected $htmlBody;
	protected $textBody;

	public function __construct() {
		$this->recipients = new RecipientsCollection();
	}

	/**
	 * @param $config
	 */
	public function setConfig($config) {
		$this->config = $config;
	}

	/**
	 * @return MailConfig
	 */
	public function getConfig() {
		if (!($this->config instanceof MailerConfig)) {
			$this->config = new MailerConfig(); // load the defaults
		}

		return $this->config;
	}

	/**
	 * @param EmailIdentity $from
	 */
	public function setFrom(EmailIdentity $from) {
		$this->from = $from;
	}

	/**
	 * @return EmailIdentity
	 */
	public function getFrom() {
		return $this->from;
	}

	/**
	 * Provide a partial or complete recipient list from scratch. Passing no parameters will effectively
	 * clear the list, if one exists.
	 *
	 * @param array $to     Array of EmailIdentity objects representing the recipients in the TO header.
	 * @param array $cc     Array of EmailIdentity objects representing the recipients in the CC header.
	 * @param array $bcc    Array of EmailIdentity objects representing the recipients in the BCC header.
	 */
	public function setRecipients($to = array(), $cc = array(), $bcc = array()) {
		$this->recipients->clearAll();
		$this->addRecipientsTo($to);
		$this->addRecipientsCc($cc);
		$this->addRecipientsBcc($bcc);
	}

	/**
	 * @param array $recipients   Array of EmailIdentity objects.
	 */
	public function addRecipientsTo($recipients = array()) {
		$this->recipients->addTo($recipients);
	}

	/**
	 * @param array $recipients   Array of EmailIdentity objects.
	 */
	public function addRecipientsCc($recipients = array()) {
		$this->recipients->addCc($recipients);
	}

	/**
	 * @param array $recipients   Array of EmailIdentity objects.
	 */
	public function addRecipientsBcc($recipients = array()) {
		$this->recipients->addBcc($recipients);
	}

	/**
	 * @return RecipientsCollection
	 */
	public function getRecipients() {
		return $this->recipients;
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
	 * @return string
	 */
	public function getTextBody() {
		return $this->textBody;
	}

	/**
	 * @param string $htmlBody
	 */
	public function setHtmlBody($htmlBody) {
		$this->htmlBody = $htmlBody;
	}

	/**
	 * @return string
	 */
	public function getHtmlBody() {
		return $this->htmlBody;
	}

	/**
	 * @throws MailerException
	 */
	public function send() {
		$mail = new PHPMailer();

		try {
			$this->transferConnectionData($mail);
			$this->transferHeaders($mail);
			$this->transferRecipients($mail);

			if (!$mail->IsError()) {
				$mail->Send();
			}

			if ($mail->IsError()) {
				throw new MailerException($mail->ErrorInfo);
			}
		} catch (MailerException $me) {
			$GLOBALS['log']->error($me->getMessage());
		}
	}

	/**
	 * @param PHPMailer $mail
	 */
	protected function transferConnectionData(PHPMailer &$mail) {
		$config = $this->getConfig();
		$mail->Mailer = $config->getProtocol();
		$mail->Host = $config->getHost();
		$mail->Port = $config->getPort();
	}

	/**
	 * @param PHPMailer $mail
	 */
	protected function transferHeaders(PHPMailer &$mail) {
		$from = $this->getFrom();
		$fromEmail = $from->getEmail();

		if (!is_string($fromEmail)) {
			throw new MailerException("Invalid from email address");
		}

		$mail->From = $fromEmail;
		$mail->FromName = $from->getName();

		$subject = $this->getSubject();

		if (!is_string($subject)) {
			throw new MailerException("Invalid subject");
		}

		$mail->Subject = $this->getSubject();
	}

	/**
	 * @param PHPMailer $mail
	 */
	protected function transferRecipients(PHPMailer &$mail) {
		$recipients = $this->getRecipients();
		$to = $recipients->getTo();
		$cc = $recipients->getCc();
		$bcc = $recipients->getBcc();

		foreach ($to as $recipient) {
			$mail->AddAddress($recipient->getEmail(), $recipient->getName());
		}

		foreach ($cc as $recipient) {
			$mail->AddCC($recipient->getEmail(), $recipient->getName());
		}

		foreach ($bcc as $recipient) {
			$mail->AddBCC($recipient->getEmail(), $recipient->getName());
		}
	}

	/**
	 * @param PHPMailer $mail
	 * @throws MailerException
	 */
	protected function transferBody(PHPMailer &$mail) {
		$htmlBody = $this->getHtmlBody();
		$textBody = $this->getTextBody();

		if ($htmlBody && $textBody) {
			$mail->IsHTML(true);
			$mail->Body = $htmlBody;
			$mail->AltBody = $textBody;
		} elseif ($htmlBody) {
			// you should never actually send an email without a plain-text part, but we'll allow it (for now)
			$mail->Body = $htmlBody;
		} elseif ($textBody) {
			$mail->Body = $textBody;
		} else {
			throw new MailerException("No email body was provided");
		}
	}
}
