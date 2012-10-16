<?php
if (!defined('sugarEntry') || !sugarEntry) die('Not A Valid Entry Point');

/*********************************************************************************
 * The contents of this file are subject to the SugarCRM Professional End User
 * License Agreement ("License") which can be viewed at
 * http://www.sugarcrm.com/EULA.  By installing or using this file, You have
 * unconditionally agreed to the terms and conditions of the License, and You may
 * not use this file except in compliance with the License. Under the terms of the
 * license, You shall not, among other things: 1) decodesublicense, resell, rent, lease,
 * redistribute, assign or otherwise transfer Your rights to the Software, and 2)
 * use the Software for timesharing or service bureau purposes such as hosting the
 * Software for commercial gain and/or for the benefit of a third party.  Use of
 * the Software may be subject to applicable fees and any use of the Software
 * without first paying applicable fees is strictly prohibited.  You do not have
 * the right to remove SugarCRM copyrights from the source code or user interface.
 * All copies of the Covered Code must include on each user interface screen:
 * (i) the "Powered by SugarCRM" logo and (ii) the SugarCRM copyright notice
 * in the same form as they appear in the distribution.  See full license for
 * requirements.  Your Warranty, Limitations of liability and Indemnity are
 * expressly stated in the License.  Please refer to the License for the specific
 * language governing these rights and limitations under the License.
 * Portions created by SugarCRM are Copyright (C) 2004 SugarCRM, Inc.;
 * All Rights Reserved.
 ********************************************************************************/

// external imports
require_once "modules/Users/User.php";
require_once "modules/Mailer/MailerException.php";    // requires MailerException in order to throw exceptions of that
                                                      // type
require_once "modules/Mailer/Encoding.php";           // needs the valid encodings defined in Encoding
require_once "include/Localization/Localization.php"; // required for using the global $locale, which is a Localization

/**
 * Represents the base configurations and contains the logic for setting the configurations for a Mailer.
 */
class OutboundEmailConfiguration
{
    // protected members
    protected $userId;
    protected $configId;
    protected $configName;
    protected $configType;
    protected $inboxId;
    protected $mode;
    protected $personal;
    protected $sender;
    protected $displayName;
    protected $replyToEmail;
    protected $replyToName;
    protected $hostname;     // the hostname to use in Message-ID and Received headers and as default HELO string
                             // not the server hostname
    protected $locale;       // the Localization object necessary for performing character set translations
    protected $charset;      // the character set of the message
    protected $encoding;     // the encoding of the message, which must be one of the valid encodings from Encoding
    protected $wordwrap;     // number of characters per line before the message body wrap

    /**
     * @access public
     */
    public function __construct(User $user) {
        $this->setUserId($user->id);
        $this->loadDefaultConfigs();
    }

    /**
     * Sets the mailer configuration to its default settings for this sending strategy.
     *
     * @access public
     */
    public function loadDefaultConfigs() {
        $this->setHostname();
        $this->setLocale();
        $this->setCharset();
        $this->setEncoding();
        $this->setWordwrap();
    }

    /**
     * Sets or overwrites the hostname configuration.
     *
     * @access public
     * @param string $hostname required
     * @throws MailerException
     */
    public function setHostname($hostname = "") {
        if (!is_string($hostname)) {
            throw new MailerException(
                "Invalid Configuration: hostname must be a string",
                MailerException::InvalidConfiguration
            );
        }

        $this->hostname = $hostname;
    }

    /**
     * Returns the hostname configuration.
     *
     * @access public
     * @return string
     */
    public function getHostname() {
        return $this->hostname;
    }

    /**
     * Sets or overwrites the locale configuration.
     *
     * @access public
     * @param Localization|null $locale Null is an acceptable value for the purposes of defaulting $this->locale to
     *                                  null, but the setter should only be used publicly with a valid Localization
     *                                  object.
     */
    public function setLocale(Localization $locale = null) {
        $this->locale = $locale;
    }

    /**
     * @access public
     * @return Localization|null Null if initialized but never set.
     */
    public function getLocale() {
        return $this->locale;
    }

    /**
     * Sets or overwrites the charset configuration.
     *
     * @access public
     * @param string $charset required
     * @throws MailerException
     */
    public function setCharset($charset = "utf-8") {
        if (!is_string($charset)) {
            throw new MailerException(
                "Invalid Configuration: charset must be a string",
                MailerException::InvalidConfiguration
            );
        }

        $this->charset = $charset;
    }

    /**
     * Returns the charset configuration.
     *
     * @access public
     * @return string
     */
    public function getCharset() {
        return $this->charset;
    }

    /**
     * Sets or overwrites the encoding configuration. Default to quoted-printable for plain/text.
     *
     * @access public
     * @param string $encoding required
     * @throws MailerException
     */
    public function setEncoding($encoding = Encoding::QuotedPrintable) {
        if (!Encoding::isValid($encoding)) {
            throw new MailerException(
                "Invalid Configuration: encoding is invalid",
                MailerException::InvalidConfiguration
            );
        }

        $this->encoding = $encoding;
    }

    /**
     * Returns the encoding configuration.
     *
     * @access public
     * @return string
     */
    public function getEncoding() {
        return $this->encoding;
    }

    /**
     * Sets or overwrites the wordwrap configuration, which is the number of characters before a line will be wrapped.
     *
     * @access public
     * @param int $chars required
     * @throws MailerException
     */
    public function setWordwrap($chars = 996) {
        if (!is_int($chars)) {
            throw new MailerException(
                "Invalid Configuration: wordwrap must be an integer",
                MailerException::InvalidConfiguration
            );
        }

        $this->wordwrap = $chars;
    }

    /**
     * Returns the wordwrap configuration.
     *
     * @access public
     * @return string
     */
    public function getWordwrap() {
        return $this->wordwrap;
    }

    public function setUserId($id) {
        $this->userId = $id;
    }

    public function getUserId() {
        return $this->userId;
    }

    public function setConfigId($id) {
        $this->configId = $id;
    }

    public function getConfigId() {
        return $this->configId;
    }

    public function setConfigName($name) {
        $this->configName = $name;
    }

    public function getConfigName() {
        return $this->configName;
    }

    public function setConfigType($type) {
        $this->configType = $type;
    }

    public function getConfigType() {
        return $this->configType;
    }

    public function setInboxId($id) {
        $this->inboxId = $id;
    }

    public function getInboxId() {
        return $this->inboxId;
    }

    public function setMode($mode) {
        if (empty($mode)) {
            $mode = OutboundEmailConfigurationPeer::MODE_SMTP;
        }

        $mode = strtolower($mode); // make sure it's lower case

        if (!OutboundEmailConfigurationPeer::isValidMode($mode)) {
            throw new MailerException("Invalid Mailer: '{$mode}' is an invalid mode", MailerException::InvalidMailer);
        }

        $this->mode = $mode;
    }

    public function getMode() {
        return $this->mode;
    }

    public function setPersonal($personal = false) {
        $this->personal = $personal;
    }

    public function getPersonal() {
        return $this->personal;
    }

    public function setSender($email, $name = null) {
        $this->sender = new EmailIdentity($email, $name);
    }

    public function getSender() {
        return $this->sender;
    }

    public function setDisplayName($name) {
        $this->displayName = $name;
    }

    public function getDisplayName() {
        return $this->displayName;
    }

    public function setReplyToEmail($email) {
        $this->replyToEmail = $email;
    }

    public function getReplyToEmail() {
        return $this->replyToEmail;
    }

    public function setReplyToName($name) {
        $this->replyToName = $name;
    }

    public function getReplyToName() {
        return $this->replyToName;
    }
}
