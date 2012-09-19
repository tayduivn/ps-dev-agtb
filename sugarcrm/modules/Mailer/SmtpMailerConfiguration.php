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

require_once "MailerConfiguration.php";

class SmtpMailerConfiguration extends MailerConfiguration
{
    // constants used for documenting which smtp.secure configurations are valid
    const SecureNone = "";
    const SecureSsl  = "ssl";
    const SecureTls  = "tls";

    // private members
    private $host;         // the hostname of the SMTP server to use
    // multiple hosts can be supplied, but all hosts must be separated by a semicolon
    // (e.g. "smtp1.example.com;smtp2.example.com") and hosts will be tried in order
    // the port for the host can be defined using the format:
    //     hostname:port
    private $port;         // the SMTP port to use on the server
    private $secure;       // the SMTP connection prefix ("", "ssl" or "tls")
    private $authenticate; // true=require authentication on the SMTP server
    private $username;     // the username to use if smtp.authenticate=true
    private $password;     // the password to use if smtp.authenticate=true

    /**
     * Extends the default configurations for this sending strategy. Adds default SMTP configurations needed to send
     * email over SMTP using PHPMailer.
     *
     * @access public
     */
    public function loadDefaultConfigs() {
        parent::loadDefaultConfigs(); // load the base defaults

        $this->setHost();
        $this->setPort();
        $this->setSecure();
        $this->requireAuthentication();
        $this->setUsername();
        $this->setPassword();
    }

    public function setHost($host = "localhost") {
        //@todo make sure it's a string
        $this->host = $host;
    }

    public function getHost() {
        return $this->host;
    }

    public function setPort($port = 25) {
        //@todo make sure it's an int
        $this->port = $port;
    }

    public function getPort() {
        return $this->port;
    }

    public function setSecure($secure = self::SecureNone) {
        //@todo make sure it's one of the valid secure consts
        $this->secure = $secure;
    }

    public function getSecure() {
        return $this->secure;
    }

    //@todo is this the best name?
    public function requireAuthentication($required = false) {
        //@todo make sure it's a bool
        $this->authenticate = $required;
    }

    //@todo is this the best name?
    public function authenticationIsRequired() {
        return $this->authenticate;
    }

    public function setUsername($username = "") {
        //@todo make sure it's a string
        $this->username = $username;
    }

    public function getUsername() {
        return $this->username;
    }

    public function setPassword($password = "") {
        //@todo make sure it's a string
        //@todo do the from_html() thing?
        $this->password = $password;
    }

    public function getPassword() {
        return $this->password;
    }
}
