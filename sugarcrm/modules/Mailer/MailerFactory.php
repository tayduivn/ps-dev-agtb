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

require_once "modules/Emails/MailConfiguration.php";     // needs to be able to access the MailConfiguration
require_once "modules/Emails/MailConfigurationPeer.php"; // needs the constants that represent the modes
require_once 'EmailIdentity.php';                        // requires EmailIdentity to represent each recipient
require_once 'EmailHeaders.php';                         // email headers are contained in an EmailHeaders object

/**
 * Factory to create Mailers.
 */
class MailerFactory
{
    // protected members

    // Maps the mode from a MailConfiguration to the class that represents the sending strategy for that
    // configuration.
    // key = mode; value = mailer class
    protected static $modeToMailerMap = array(
        "default"                        => array(
            "path"  => ".",            // the path to the class file without trailing slash ("/")
            "class" => "SimpleMailer", // the name of the class
        ),
        MailConfigurationPeer::MODE_SMTP => array(
            "path"  => ".",
            "class" => "SugarMailer",
        ),
    );

    /**
     * Determines the correct Mailer to use based on the configuration that is provided to it and constructs and
     * returns that object.
     *
     * @static
     * @access public
     * @param MailConfiguration $config required The configuration that provides context to the chosen sending
     *                                           strategy.
     * @return mixed An object of one of the Mailers defined in $modeToMailerMap.
     * @throws MailerException
     */
    public static function getMailer(MailConfiguration $config) {
        // copy the config value becuase you don't want to modify the object by reassigning a public variable
        // in the case of mode being null
        //@todo better validation on the mode
        $mode = is_null($config->mode) ? "default" : strtolower($config->mode); // make sure it's lower case

        $from    = new EmailIdentity($config->sender_email, $config->sender_name); // can bubble up a MailerException
        $headers = self::buildHeadersForMailer($from);
        $mailer  = self::buildMailer($mode); // can bubble up a MailerException
        self::configureMailer($mailer, $config);
        $mailer->setHeaders($headers);

        return $mailer;
    }

    /**
     * Instantiates the requisite Mailer and returns it.
     *
     * @static
     * @access private
     * @param string $mode required The mode that represents the sending strategy.
     * @return mixed An object of one of the Mailers defined in $modeToMailerMap.
     * @throws MailerException
     */
    private static function buildMailer($mode) {
        $path   = self::$modeToMailerMap[$mode]["path"];
        $class  = self::$modeToMailerMap[$mode]["class"];
        $file   = "{$path}/{$class}.php";
        @include_once $file; // suppress errors

        if (!class_exists($class)) {
            throw new MailerException("Invalid Mailer: Could not find class '{$class}'", MailerException::InvalidMailer);
        }

        return new $class();
    }

    /**
     * Replaces the Mailer's default configurations with the configurations found in the MailerConfiguration object.
     *
     * @static
     * @access private
     * @param BaseMailer        $mailer required An object of one of the Mailers defined in $modeToMailerMap that
     *                                           extends BaseMailer.
     * @param MailConfiguration $config required The configuration that provides context to the chosen sending
     *                                           strategy.
     */
    private static function configureMailer(BaseMailer &$mailer, MailConfiguration $config) {
        // setup the mailer's known configurations
        $mailer->setConfig("smtp.host", $config->config_data['mail_smtpserver']);
        $mailer->setConfig("smtp.port", $config->config_data['mail_smtpport']);

        if ($config->config_data['mail_smtpauth_req']) {
            // require authentication with the SMTP server
            $mailer->setConfig("smtp.authenticate", true);
            $mailer->setConfig("smtp.username", $config->config_data['mail_smtpuser']);
            $mailer->setConfig("smtp.password", $config->config_data['mail_smtppass']); //@todo wrap this value in from_html()?
        }

        // determine the appropriate encryption layer for the sending strategy
        if ($config->config_data['mail_smtpssl'] === 1) {
            $mailer->setConfig("smtp.secure", MailConfigurationPeer::SecureSsl);
        } elseif ($config->config_data['mail_smtpssl'] === 2) {
            $mailer->setConfig("smtp.secure", MailConfigurationPeer::SecureTls);
        }
    }

    /**
     * Constructs and returns the Headers object to be used by the Mailer and takes care of initializing the From
     * header.
     *
     * @static
     * @access private
     * @param EmailIdentity $from
     * @return EmailHeaders
     */
    private static function buildHeadersForMailer(EmailIdentity $from) {
        // add the known email headers
        $headers = new EmailHeaders();
        $headers->setFrom($from);

        return $headers;
    }
}
