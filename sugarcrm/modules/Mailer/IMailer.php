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

require_once "Encoding.php";      // needs to know the valid encodings that are available for email
require_once "EmailIdentity.php"; // requires EmailIdentity for representing email senders and recipients
require_once "MailerConfiguration.php";

/**
 * This defines the basic interface that is expected from a Mailer.
 *
 * @interface
 */
interface IMailer
{
    /**
     * @abstract
     * @access public
     * @param MailerConfiguration $config required
     */
    public function __construct(MailerConfiguration $config);

    /**
     * Set the object properties back to their initial default values.
     *
     * @abstract
     * @access public
     */
    public function reset();

    /**
     * Replaces the existing email headers with the headers passed in as a parameter.
     *
     * @abstract
     * @access public
     * @param EmailHeaders $headers required
     */
    public function setHeaders(EmailHeaders $headers);

    /**
     * Adds or replaces header values.
     *
     * @access public
     * @param string $key   required Should look like the real header it represents.
     * @param mixed  $value required The value of the header.
     * @throws MailerException
     */
    public function setHeader($key, $value);

    /**
     * Adds or replaces the Subject header.
     *
     * @access public
     * @param string $subject required
     * @throws MailerException
     */
    public function setSubject($subject);

    /**
     * Restores the email headers to a fresh EmailHeaders object.
     *
     * @abstract
     * @access public
     */
    public function clearHeaders();

    /**
     * Clears the recipients from the selected recipient lists. By default, clear all recipients.
     *
     * @abstract
     * @access public
     * @param bool $to  true=clear the To list; false=leave the To list alone
     * @param bool $cc  true=clear the CC list; false=leave the CC list alone
     * @param bool $bcc true=clear the BCC list; false=leave the BCC list alone
     */
    public function clearRecipients($to = true, $cc = true, $bcc = true);

    /**
     * Adds recipients to the To list.
     *
     * @abstract
     * @access public
     * @param array $recipients Array of EmailIdentity objects.
     */
    public function addRecipientsTo($recipients = array());

    /**
     * Removes the recipients from the To list.
     *
     * @abstract
     * @access public
     */
    public function clearRecipientsTo();

    /**
     * Adds recipients to the CC list.
     *
     * @abstract
     * @access public
     * @param array $recipients Array of EmailIdentity objects.
     */
    public function addRecipientsCc($recipients = array());

    /**
     * Removes the recipients from the CC list.
     *
     * @abstract
     * @access public
     */
    public function clearRecipientsCc();

    /**
     * Adds recipients to the BCC list.
     *
     * @abstract
     * @access public
     * @param array $recipients Array of EmailIdentity objects.
     */
    public function addRecipientsBcc($recipients = array());

    /**
     * Removes the recipients from the BCC list.
     *
     * @abstract
     * @access public
     */
    public function clearRecipientsBcc();

    /**
     * Sets the plain-text part of the email.
     *
     * @abstract
     * @access public
     * @param string $body required
     */
    public function setTextBody($body);

    /**
     * Sets the HTML part of the email.
     *
     * @abstract
     * @access public
     * @param string $body required
     */
    public function setHtmlBody($body);

    /**
     * Adds an attachment from a path on the filesystem.
     *
     * @abstract
     * @access public
     * @param string      $path     required Path to the file being attached.
     * @param null|string $name              Name of the file to be used to identify the attachment.
     * @param string      $encoding          The encoding used on the file. Should be one of the valid encodings from Encoding.
     * @param string      $mimeType          Should be a valid MIME type.
     */
    public function addAttachment($path, $name = null, $encoding = Encoding::Base64, $mimeType = "application/octet-stream");

    /**
     * Adds an embedded attachment. This can include images, sounds, and just about any other document. Make sure to set
     * the $mimeType to the appropriate type. For JPEG images use "image/jpeg" and for GIF images use "image/gif".
     *
     * @abstract
     * @access public
     * @param string      $path     required Path to the file being attached.
     * @param string      $cid      required The Content-ID used to reference the image in the message.
     * @param null|string $name              Name of the file to be used to identify the attachment.
     * @param string      $encoding          The encoding used on the file. Should be one of the valid encodings from Encoding.
     * @param string      $mimeType          Should be a valid MIME type.
     */
    public function addEmbeddedImage($path, $cid, $name = null, $encoding = Encoding::Base64, $mimeType = "application/octet-stream");

    /**
     * Removes any existing attachments by restoring the container to an empty array.
     *
     * @abstract
     * @access public
     */
    public function clearAttachments();

    /**
     * Performs the send of an email using the package that is being used to deliver email.
     *
     * @abstract
     * @access public
     * @throws MailerException
     */
    public function send();
}
