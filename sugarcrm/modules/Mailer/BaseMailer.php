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

require_once "IMailer.php";              // requires IMailer in order to implement it
require_once "MailerException.php";      // requires MailerException in order to throw exceptions of that type
require_once "RecipientsCollection.php"; // stores recipients in a RecipientsCollection
require_once "EmailHeaders.php";         // email headers are contained in an EmailHeaders object

/**
 * This class implements the basic functionality that is expected from a Mailer.
 *
 * @abstract
 * @implements IMailer
 */
abstract class BaseMailer implements IMailer
{
    // protected members
    protected $config;
    protected $headers;
    protected $recipients;
    protected $htmlBody;
    protected $textBody;
    protected $attachments;

    /**
     * @access public
     * @param MailerConfiguration $config required
     */
    public function __construct(MailerConfiguration $config) {
        $this->reset(); // the equivalent of initializing the Mailer object's properties

        $this->config = $config;
    }

    /**
     * Set the object properties back to their initial default values.
     *
     * @access public
     */
    public function reset() {
        $this->clearAttachments();
        $this->clearHeaders();

        $this->recipients = new RecipientsCollection();
        $this->htmlBody   = null;
        $this->textBody   = null;
    }

    /**
     * Replaces the existing email headers with the headers passed in as a parameter.
     *
     * @access public
     * @param EmailHeaders $headers required
     */
    public function setHeaders(EmailHeaders $headers) {
        $this->headers = $headers;
    }

    /**
     * Replaces the existing email headers with an EmailHeaders object hydrated from the array passed in as a parameter.
     *
     * @access public
     * @param array $headers required
     */
    public function constructHeaders($headers = array()) {
        $this->headers->buildFromArray($headers);
    }

    /**
     * Restores the email headers to a fresh EmailHeaders object.
     *
     * @access public
     */
    public function clearHeaders() {
        $this->headers = new EmailHeaders();
    }

    /**
     * Clears the recipients from the selected recipient lists. By default, clear all recipients.
     *
     * @access public
     * @param bool $to  true=clear the To list; false=leave the To list alone
     * @param bool $cc  true=clear the CC list; false=leave the CC list alone
     * @param bool $bcc true=clear the BCC list; false=leave the BCC list alone
     */
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

    /**
     * Adds recipients to the To list.
     *
     * @access public
     * @param array $recipients Array of EmailIdentity objects.
     */
    public function addRecipientsTo($recipients = array()) {
        $this->recipients->addRecipients($recipients);
    }

    /**
     * Removes the recipients from the To list.
     *
     * @access public
     */
    public function clearRecipientsTo() {
        $this->recipients->clearTo();
    }

    /**
     * Adds recipients to the CC list.
     *
     * @access public
     * @param array $recipients Array of EmailIdentity objects.
     */
    public function addRecipientsCc($recipients = array()) {
        return $this->recipients->addRecipients($recipients, RecipientsCollection::FunctionAddCc);
    }

    /**
     * Removes the recipients from the CC list.
     *
     * @access public
     */
    public function clearRecipientsCc() {
        $this->recipients->clearCc();
    }

    /**
     * Adds recipients to the BCC list.
     *
     * @access public
     * @param array $recipients Array of EmailIdentity objects.
     */
    public function addRecipientsBcc($recipients = array()) {
        return $this->recipients->addRecipients($recipients, RecipientsCollection::FunctionAddBcc);
    }

    /**
     * Removes the recipients from the BCC list.
     *
     * @access public
     */
    public function clearRecipientsBcc() {
        $this->recipients->clearBcc();
    }

    /**
     * Sets the plain-text part of the email.
     *
     * @access public
     * @param string $body required
     */
    public function setTextBody($body) {
        $this->textBody = $body;
    }

    /**
     * Sets the HTML part of the email.
     *
     * @access public
     * @param string $body required
     */
    public function setHtmlBody($body) {
        $this->htmlBody = $body;
    }

    /**
     * Adds an attachment from a path on the filesystem.
     *
     * @access public
     * @param string      $path     required Path to the file being attached.
     * @param null|string $name              Name of the file to be used to identify the attachment.
     * @param string      $encoding          The encoding used on the file. Should be one of the valid encodings from Encoding.
     * @param string      $mimeType          Should be a valid MIME type.
     */
    public function addAttachment($path, $name = null, $encoding = Encoding::Base64, $mimeType = "application/octet-stream") {
        $this->attachments[] = new Attachment($path, $name, $encoding, $mimeType);
    }

    /**
     * Adds an embedded attachment. This can include images, sounds, and just about any other document. Make sure to set
     * the $mimeType to the appropriate type. For JPEG images use "image/jpeg" and for GIF images use "image/gif".
     *
     * @access public
     * @param string      $path     required Path to the file being attached.
     * @param string      $cid      required The Content-ID used to reference the image in the message.
     * @param null|string $name              Name of the file to be used to identify the attachment.
     * @param string      $encoding          The encoding used on the file. Should be one of the valid encodings from Encoding.
     * @param string      $mimeType          Should be a valid MIME type.
     */
    public function addEmbeddedImage($path, $cid, $name = null, $encoding = Encoding::Base64, $mimeType = "application/octet-stream") {
        $this->attachments[] = new EmbeddedImage($path, $cid, $name, $encoding, $mimeType);
    }

    /**
     * Removes any existing attachments by restoring the container to an empty array.
     *
     * @access public
     */
    public function clearAttachments() {
        $this->attachments = array();
    }

    /**
     * Returns true if the value passed in as a parameter is a valid message part. Use this method to determine if a
     * message has an HTML part or a plain-text part. If both parts exist, then the message is multi-part.
     *
     * @access protected
     * @param string $part required The content of the message part to inspect.
     * @return bool
     */
    protected function hasMessagePart($part) {
        // the content is only valid if it's a string and it's not empty
        if (is_string($part) && $part != "") {
            return true;
        }

        return false;
    }
}
