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

require_once "MailerException.php";
require_once "Encoding.php";

class MailerConfiguration
{
    // protected members
    protected $hostname; // the hostname to use in Message-ID and Received headers and as default HELO string
                         // not the server hostname
    protected $charset;  // the character set of the message
    protected $encoding;
    protected $wordwrap; // number of characters per line before the message body wrap

    public function __construct() {
        $this->loadDefaultConfigs();
    }

    /**
     * Set the mailer configuration to its default settings for this sending strategy.
     *
     * @access public
     */
    public function loadDefaultConfigs() {
        $this->setHostname();
        $this->setCharset();
        $this->setEncoding();
        $this->setWordwrap();
    }

    /**
     * Sets or overwrites a configuration with the value passed in for the key ($config).
     *
     * @access public
     * @param string $config required The configuration key.
     * @param mixed  $value  required The configuration value.
     */
    public function setHostname($hostname = "") {
        $this->hostname = $hostname;
    }

    /**
     * Returns the configuration value at the specified key ($config).
     *
     * @access public
     * @param string $config required The configuration key.
     * @return mixed The value stored at the specified key.
     * @throws MailerException
     */
    public function getHostname() {
        return $this->hostname;
    }

    public function setCharset($charset = "utf-8") {
        //@todo make sure it's a string
        $this->charset = $charset;
    }

    public function getCharset() {
        return $this->charset;
    }

    // default to quoted-printable for plain/text
    public function setEncoding($encoding = Encoding::QuotedPrintable) {
        //@todo make sure it's one of the valid encodings from Encoding?
        $this->encoding = $encoding;
    }

    public function getEncoding() {
        return $this->encoding;
    }

    public function setWordwrap($chars = 996) {
        //@todo make sure it's an int
        $this->wordwrap = $chars;
    }

    public function getWordwrap() {
        return $this->wordwrap;
    }
}
