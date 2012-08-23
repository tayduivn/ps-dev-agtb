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

require_once 'IMailer.php';
require_once 'MailerException.php';
require_once 'RecipientsCollection.php';

abstract class BaseMailer implements IMailer
{
	protected $mailer;
	protected $configs;
	protected $messageId;
	protected $priority;
	protected $requestConfirmation;
	protected $from;
	protected $replyTo;
	protected $sender;
	protected $recipients;
	protected $subject;
	protected $htmlBody;
	protected $textBody;
	protected $attachments;
	protected $embeddedImages;

	public function __construct() {
		$this->reset();
	}

	public function reset() {
		$this->mailer         = null;
		$this->loadDefaultConfigs();
		$this->messageId      = null;
		$this->setPriority();
		$this->setRequestConfirmation();
		$this->from           = null;
		$this->replyTo        = null;
		$this->sender         = null;
		$this->recipients     = new RecipientsCollection();
		$this->subject        = null;
		$this->htmlBody       = null;
		$this->textBody       = null;
		$this->attachments    = array();
		$this->embeddedImages = array();
	}

	public function loadDefaultConfigs() {
		$defaults = array(
			'protocol' => 'smtp',
			'hostname' => '',
			'charset'  => 'utf-8',
			'encoding' => 'quoted-printable', // default to quoted-printable for plain/text
			'wordwrap' => 996,
			'smtp'     => array(
				'host'         => 'localhost',
				'port'         => 25,
				'secure'       => '',
				'authenticate' => false,
				'username'     => '',
				'password'     => '',
				'timeout'      => 10,
				'persist'      => false,
			),
		);

		$this->setConfigs($defaults);
	}

	public function setConfigs($configs) {
		$this->configs = $configs;
	}

	public function mergeConfigs($configs) {
		$this->configs = array_merge($this->configs, $configs);
	}

	public function setConfig($config, $value) {
		$this->configs[$config] = $value;
	}

	public function getConfigs() {
		return $this->configs;
	}

	public function setMessageId($id) {
		$this->messageId = $id;
	}

	public function setPriority($priority = 3) {
		$this->priority = $priority;
	}

	public function setRequestConfirmation($request = false) {
		$this->requestConfirmation = $request;
	}

	public function setFrom(EmailIdentity $from) {
		$this->from = $from;
	}

	public function setReplyTo(EmailIdentity $replyTo) {
		$this->replyTo = $replyTo;
	}

	public function setSender(EmailIdentity $sender) {
		$this->sender = $sender;
	}

	public function addRecipientsTo($recipients = array()) {
		return $this->recipients->addRecipients($recipients);
	}

	public function addRecipientsCc($recipients = array()) {
		return $this->recipients->addRecipients($recipients, RecipientsCollection::FunctionAddCc);
	}

	public function addRecipientsBcc($recipients = array()) {
		return $this->recipients->addRecipients($recipients, RecipientsCollection::FunctionAddBcc);
	}

	public function setSubject($subject) {
		$this->subject = $subject;
	}

	public function getSubject() {
		return $this->subject;
	}

	public function setTextBody($textBody) {
		$this->textBody = $textBody;
	}

	public function setHtmlBody($htmlBody) {
		$this->htmlBody = $htmlBody;
	}

	public function addAttachment($path, $name = null, $encoding = 'base64', $mimeType = 'application/octet-stream') {
		$this->attachments[] = array(
			'path'     => $path,
			'name'     => $name,
			'encoding' => $encoding,
			'mimetype' => $mimeType,
		);
	}

	public function addEmbeddedImage($path, $cid, $name = null, $encoding = 'base64', $mimeType = 'application/octet-stream') {
		$this->embeddedImages[] = array(
			'path'     => $path,
			'cid'      => $cid,
			'name'     => $name,
			'encoding' => $encoding,
			'mimetype' => $mimeType,
		);
	}
}
