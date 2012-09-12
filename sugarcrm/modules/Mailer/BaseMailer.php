<?php
if (!defined('sugarEntry') || !sugarEntry) die('Not A Valid Entry Point');

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
require_once 'EmailHeaders.php';

abstract class BaseMailer implements IMailer
{
    protected $configs;
    protected $headers;
    protected $recipients;
    protected $htmlBody;
    protected $textBody;
    protected $attachments;

    public function __construct() {
        $this->reset();
    }

    public function reset() {
        $this->loadDefaultConfigs();
        $this->clearAttachments();
        $this->clearHeaders();

        $this->recipients = new RecipientsCollection();
        $this->htmlBody   = null;
        $this->textBody   = null;
    }

    public function loadDefaultConfigs() {
        $defaults = array(
            'hostname' => '',
            'charset'  => 'utf-8',
            'encoding' => Encoding::QuotedPrintable, // default to quoted-printable for plain/text
            'wordwrap' => 996,
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

    public function getConfig($config) {
        if (!array_key_exists($config, $this->configs)) {
            throw new MailerException("Configuration does not exist: {$config}");
        }

        return $this->configs[$config];
    }

    public function setHeaders(EmailHeaders $headers) {
        $this->headers = $headers;
    }

    public function constructHeaders($headers = array()) {
        //@todo dependent on $this->headers being an EmailHeaders object; throw an exception if not?
        $this->headers->buildFromArray($headers);
    }

    public function clearHeaders() {
        $this->headers = new EmailHeaders();
    }

    public function clearRecipients($to = true, $cc = true, $bcc = true) {
        if ($to) {
            $this->clearRecipientsTo();
        }

        if ($cc) {
            $this->clearRecipientsCc();
        }

        if ($bcc) {
            $this->clearRecipientsBcc();
        }
    }

    public function addRecipientsTo($recipients = array()) {
        return $this->recipients->addRecipients($recipients);
    }

    public function clearRecipientsTo() {
        $this->recipients->clearTo();
    }

    public function addRecipientsCc($recipients = array()) {
        return $this->recipients->addRecipients($recipients, RecipientsCollection::FunctionAddCc);
    }

    public function clearRecipientsCc() {
        $this->recipients->clearCc();
    }

    public function addRecipientsBcc($recipients = array()) {
        return $this->recipients->addRecipients($recipients, RecipientsCollection::FunctionAddBcc);
    }

    public function clearRecipientsBcc() {
        $this->recipients->clearBcc();
    }

    public function setTextBody($textBody) {
        $this->textBody = $textBody;
    }

    public function setHtmlBody($htmlBody) {
        $this->htmlBody = $htmlBody;
    }

    public function addAttachment($path, $name = null, $encoding = Encoding::Base64, $mimeType = 'application/octet-stream') {
        $this->attachments[] = new Attachment($path, $name, $encoding, $mimeType);
    }

    public function addEmbeddedImage($path, $cid, $name = null, $encoding = Encoding::Base64, $mimeType = 'application/octet-stream') {
        $this->attachments[] = new EmbeddedImage($path, $cid, $name, $encoding, $mimeType);
    }

    public function clearAttachments() {
        $this->attachments = array();
    }

    protected function hasMessagePart($part) {
        if (is_string($part) && $part != '') {
            return true;
        }

        return false;
    }
}
