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

require_once "MailerException.php";                      // requires MailerException in order to throw exceptions of
                                                         // that type
require_once "EmailHeaders.php";                         // email headers are contained in an EmailHeaders object
require_once "EmailIdentity.php";                        // requires EmailIdentity to build the From header
require_once "SmtpMailer.php";                           // requires SmtpMailer in order to create a SmtpMailer

// external imports
require_once "modules/OutboundEmailConfiguration/SmtpMailerConfiguration.php";    // required if producing an SMTP
                                                                                  // Mailer; also imports
                                                                                  // MailerConfiguration
require_once "modules/OutboundEmailConfiguration/MailConfigurationPeer.php";      // needs the constants that represent
                                                                                  // the modes
require_once "modules/OutboundEmailConfiguration/OutboundEmailConfiguration.php"; // uses the properties to produce the
                                                                                  // expected mailer

/**
 * Factory to create Mailers.
 */
class MailerFactory
{
    // protected members

    // Maps the mode from a OutboundEmailConfiguration to the class that represents the sending strategy for that
    // configuration.
    // key = mode; value = mailer class
    protected static $modeToMailerMap = array(
        MailConfigurationPeer::MODE_SMTP => array(
            "path"  => ".",          // the path to the class file without trailing slash ("/")
            "class" => "SmtpMailer", // the name of the class
        ),
        MailConfigurationPeer::MODE_WEB  => array(
            "path"  => ".",
            "class" => "WebMailer",
        ),
    );

    /**
     * In many cases, the correct Mailer is the one that is produced from the configuration associated with a
     * particular user. This method makes the necessary calls to produce that Mailer, in order to obey the DRY
     * principle.
     *
     * @param User $user required The user from which the mail configuration is retrieved.
     * @return mixed An object of one of the Mailers defined in $modeToMailerMap.
     * @throws MailerException Allows MailerExceptions to bubble up.
     */
    public static function getMailerForUser(User $user) {
        // get the configuration that the Mailer needs
        $mailConfiguration = MailConfigurationPeer::getSystemMailConfiguration($user);

        // generate the Mailer
        $mailer = self::getMailer($mailConfiguration);

        return $mailer;
    }

    /**
     * Determines the correct Mailer to use based on the configuration that is provided to it and constructs and
     * returns that object. This method allows the caller to get a Mailer with a configuration that overrides the
     * user's configuration.
     *
     * @static
     * @access public
     * @param OutboundEmailConfiguration $config required The configuration that provides context to the chosen sending
     *                                                    strategy.
     * @return mixed An object of one of the Mailers defined in $modeToMailerMap.
     * @throws MailerException
     */
    public static function getMailer(OutboundEmailConfiguration $config) {
        // copy the config value because you don't want to modify the object by reassigning a public variable
        // in the case of mode being null
        $mode = is_null($config->mode) ? MailConfigurationPeer::MODE_SMTP : $config->mode;
        $mode = strtolower($mode); // make sure it's lower case

        if (!MailConfigurationPeer::isValidMode($mode)) {
            throw new MailerException("Invalid Mailer: '{$mode}' is an invalid mode", MailerException::InvalidMailer);
        }

        // the rest of the method calls can bubble up a MailerException

        $sender  = new EmailIdentity($config->sender_email, $config->sender_name);
        $replyTo = null;

        // add the Reply-To header, but only if it should be different from the From header
        if (!empty($config->replyto_email)) {
            $replyTo = new EmailIdentity($config->replyto_email, $config->replyto_name);
        }

        $headers = self::buildHeadersForMailer($sender, $replyTo);
        $mailer  = self::buildMailer($mode, $config->mailerConfigData);
        $mailer->setHeaders($headers);

        return $mailer;
    }

    /**
     * Instantiates the requisite Mailer and returns it.
     *
     * @static
     * @access private
     * @param string              $mode   required The mode that represents the sending strategy.
     * @param MailerConfiguration $config required Must be a MailerConfiguration or a type that derives from it.
     * @return mixed An object of one of the Mailers defined in $modeToMailerMap.
     * @throws MailerException
     */
    private static function buildMailer($mode, MailerConfiguration $config) {
        $path   = self::$modeToMailerMap[$mode]["path"];
        $class  = self::$modeToMailerMap[$mode]["class"];
        $file   = "{$path}/{$class}.php";
        @include_once $file; // suppress errors

        if (!class_exists($class)) {
            throw new MailerException(
                "Invalid Mailer: Could not find class '{$class}'",
                MailerException::InvalidMailer
            );
        }

        return new $class($config);
    }

    /**
     * Constructs and returns the Headers object to be used by the Mailer and takes care of initializing the From
     * and Sender headers.
     *
     * @static
     * @access private
     * @param EmailIdentity $sender  required The true sender of the email.
     * @param EmailIdentity $replyTo          Should be an EmailIdentity, but null is acceptable if no Reply-To header
     *                                        is to be set.
     * @return EmailHeaders
     * @throws MailerException
     */
    private static function buildHeadersForMailer(EmailIdentity $sender, EmailIdentity $replyTo = null) {
        // add the known email headers
        $headers = new EmailHeaders();
        $headers->setHeader(EmailHeaders::From, $sender);
        $headers->setHeader(EmailHeaders::Sender, $sender);

        // add the Reply-To header, but only if it should be different from the From header
        if (!is_null($replyTo)) {
            $headers->setHeader(EmailHeaders::ReplyTo, $replyTo);
        }

        return $headers;
    }
}
