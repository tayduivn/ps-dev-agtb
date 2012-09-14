<?php
if (!defined('sugarEntry') || !sugarEntry) die('Not A Valid Entry Point');

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

/**
 * This class encapsulates properties and behavior of email identities, which are the email address and a name, if one
 * is associated with the email address. An email identity can be considered to look like "Bob Smith" <bsmith@yahoo.com>
 * in practice.
 */
class EmailIdentity
{
    // private members
    private $email; // The email address used in this identity.
    private $name;  // The name that accompanies the email address.

    /**
     * @access public
     * @param string      $email required
     * @param string|null $name  Should be a string unless no name is associated with the email address.
     */
    public function __construct($email, $name = null) {
        $this->setEmail($email);
        $this->setName($name);
    }

    /**
     * @access public
     * @param string $email required
     * @throws MailerException
     * @todo still need to do more to validate the email address
     */
    public function setEmail($email) {
        // validate the email address
        if (!is_string($email)) {
            //@todo stringify $email and add it to the message
            throw new MailerException("Invalid email address", MailerException::InvalidEmailAddress);
        }

        $this->email = trim($email);
    }

    /**
     * @access public
     * @return string
     */
    public function getEmail() {
        return $this->email;
    }

    /**
     * @access public
     * @param string|null $name required Should be a string unless no name is associated with the email address.
     */
    public function setName($name) {
        // if $name is null, then trim will return an empty string, which is perfectly acceptable
        $this->name = trim($name);
    }

    /**
     * Returns a string if a name exists, or an empty string or null if a name does not exist.
     *
     * @access public
     * @return string
     */
    public function getName() {
        return $this->name;
    }

    /**
     * Convert special HTML entities back to characters in cases where the email address contains characters that are
     * considered invalid for email. Call this method on the object before transferring the object or its email property
     * to a package that is being used to deliver email.
     *
     * @access public
     * @bug 31778
     */
    public function decode() {
        // add back in html characters (apostrophe ' and ampersand &) to email addresses
        // this was causing email bounces in names like "O'Reilly@example.com" being sent over as "O&#039;Reilly@example.com"
        // transferred from the commit found at https://github.com/sugarcrm/Mango/commit/67b9144cd7bfa5425a98e28a1f7d9e994c7826bc
        $this->email = htmlspecialchars_decode($this->email, ENT_QUOTES);
    }
}
