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

class Headers
{
	const MessageId                 = 'Message-ID';
	const Priority                  = 'Priority';
	const DispositionNotificationTo = 'Disposition-Notification-To';
	const From                      = 'From';
	const ReplyTo                   = 'Reply-To';
	const Sender                    = 'Sender';
	const Subject                   = 'Subject';

	protected $messageId;
	protected $priority;
	protected $requestConfirmation;
	protected $from;
	protected $replyTo;
	protected $sender;
	protected $subject;
	protected $custom;

	public function __construct() {
		$this->setPriority();
		$this->setRequestConfirmation();
		$this->clearCustomHeaders();
	}

	public function buildFromArray($headers = array()) {
		foreach ($headers as $key => $value) {
			// the keys should look the real headers they represent
			switch ($key) {
				case self::MessageId:
					$this->setMessageId($value);
					break;
				case self::Priority:
					$this->setPriority($value);
					break;
				case self::DispositionNotificationTo:
					$this->setRequestConfirmation($value);
					break;
				case self::From:
					$this->setFrom($value);
					break;
				case self::ReplyTo:
					$this->setReplyTo($value);
					break;
				case self::Sender:
					$this->setSender($value);
					break;
				case self::Subject:
					$this->setSubject($value);
					break;
				default:
					// it's not known, so it must be a custom header
					$this->addCustomHeader($key, $value);
					break;
			}
		}
	}

	public function setMessageId($id) {
		$this->messageId = $id;
	}

	public function getMessageId() {
		return $this->messageId;
	}

	/**
	 * @param int $priority
	 *
	 * @todo throw an exception if not an int?
	 */
	public function setPriority($priority = 3) {
		if (is_integer($priority)) {
			$this->priority = $priority;
		}
	}

	public function getPriority() {
		return $this->priority;
	}

	/**
	 * @param bool $request
	 *
	 * @todo throw an exception if not a boolean?
	 */
	public function setRequestConfirmation($request = false) {
		if (is_bool($request)) {
			$this->requestConfirmation = $request;
		}
	}

	public function getRequestConfirmation() {
		return $this->requestConfirmation;
	}

	/**
	 * @param EmailIdentity $from
	 *
	 * @todo throw an exception if not an EmailIdentity?
	 */
	public function setFrom(EmailIdentity $from) {
		$this->from = $from;
	}

	public function getFrom() {
		return $this->from;
	}

	/**
	 * @param EmailIdentity $replyTo
	 *
	 * @todo throw an exception if not an EmailIdentity?
	 */
	public function setReplyTo(EmailIdentity $replyTo) {
		$this->replyTo = $replyTo;
	}

	public function getReplyTo() {
		return $this->replyTo;
	}

	/**
	 * @param EmailIdentity $sender
	 *
	 * @todo throw an exception if not an EmailIdentity?
	 */
	public function setSender(EmailIdentity $sender) {
		$this->sender = $sender;
	}

	public function getSender() {
		return $this->sender;
	}

	/**
	 * @param $subject
	 *
	 * @todo throw an exception if not a string?
	 */
	public function setSubject($subject) {
		if (is_string($subject)) {
			$this->subject = $subject;
		}
	}

	public function getSubject() {
		return $this->subject;
	}

	public function clearCustomHeaders() {
		$this->custom = array();
	}

	/**
	 * @param $key
	 * @param $value
	 *
	 * @todo throw an exception if the custom header is invalid?
	 * @todo do we need to prevent overwriting a non-custom header?
	 */
	public function addCustomHeader($key, $value) {
		if (is_string($key) && is_string($value)) {
			$this->custom[$key] = $value;
		}
	}

	/**
	 * @return array
	 * @throws MailerException
	 */
	public function packageHeaders() {
		$headers = array();

		$this->packageFrom($headers);
		$this->packageReplyTo($headers);
		$this->packageSender($headers);
		$this->packageMessageId($headers);
		$this->packagePriority($headers);
		$this->packageRequestConfirmation($headers);
		$this->packageSubject($headers);
		$this->packageCustomHeaders($headers);

		return $headers;
	}

	private function packageFrom(&$headers) {
		$from = $this->getFrom();
		$fromEmail = $from->getEmail();

		if (!($from instanceof EmailIdentity)) {
			throw new MailerException("Invalid header: " . self::From);
		}

		$headers[self::From] = array($fromEmail, $from->getName());
	}

	private function packageReplyTo(&$headers) {
		$replyTo = $this->getReplyTo();

		if (!($replyTo instanceof EmailIdentity)) {
			throw new MailerException("Invalid header: " . self::ReplyTo);
		}

		$headers[self::ReplyTo] = array($replyTo->getEmail(), $replyTo->getName());
	}

	private function packageSender(&$headers) {
		$sender = $this->getSender();

		if (!is_null($sender)) {
			if (!($sender instanceof EmailIdentity)) {
				throw new MailerException("Invalid header: " . self::Sender);
			}

			$headers[self::Sender] = $sender->getEmail();
		}
	}

	private function packageMessageId(&$headers) {
		$messageId = $this->getMessageId();

		if (!is_null($messageId)) {
			$headers[self::MessageId] = $messageId;
		}
	}

	private function packagePriority(&$headers) {
		$headers[self::Priority] = $this->getPriority();
	}

	private function packageRequestConfirmation(&$headers) {
		if ($this->getRequestConfirmation()) {
			$sender = $this->getSender();

			if (!is_null($sender)) {
				$headers[self::DispositionNotificationTo] = $sender->getEmail();
			} else {
				$from = $this->getFrom();
				$headers[self::DispositionNotificationTo] = $from->getEmail();
			}
		}
	}

	private function packageSubject(&$headers) {
		$subject = $this->getSubject();

		if (is_null($subject)) {
			throw new MailerException("Invalid header: " . self::Subject);
		}

		$headers[self::Subject] = $subject;
	}

	private function packageCustomHeaders(&$headers) {
		foreach ($this->custom as $key => $value) {
			$headers[$key] = $value;
		}
	}
}
