<?php
if (!defined('sugarEntry') || !sugarEntry) {
    die('Not A Valid Entry Point');
}

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

require_once "MailerException.php"; // requires MailerException in order to throw exceptions of that type
require_once "EmailHeaders.php"; // email headers are contained in an EmailHeaders object
require_once "EmailIdentity.php"; // requires EmailIdentity to build the From header

// external imports
require_once "modules/OutboundEmailConfiguration/OutboundEmailConfigurationPeer.php"; // needs the constants that
                                                                                      // represent the modes; also
                                                                                      // imports
                                                                                      // OutboundSmtpEmailConfiguration
                                                                                      // and OutboundEmailConfiguration

/**
 * Factory to create Mailers.
 */
class MailerFactory
{
    /**
     * This retrieves the System Default Outbound Mail configuration.
     *
     * @return mixed the System Default Mail Configuration.
     * @throws MailerException Allows MailerExceptions to bubble up.
     */
    public static function getSystemDefaultMailer()
    {
        // get the System Default configuration that the Mailer needs
        $mailConfiguration = OutboundEmailConfigurationPeer::getSystemDefaultMailConfiguration();

        // generate the Mailer
        $mailer = static::getMailer($mailConfiguration);

        return $mailer;
    }

    /**
     * In many cases, the correct Mailer is the one that is produced from the configuration associated with a
     * particular user. This method makes the necessary calls to produce that Mailer, in order to obey the DRY
     * principle.
     *
     * @param User $user required The user from which the mail configuration is retrieved.
     * @return mixed An object of one of the Mailers defined in $modeToMailerMap.
     * @throws MailerException Allows MailerExceptions to bubble up.
     */
    public static function getMailerForUser(User $user)
    {
        // get the configuration that the Mailer needs
        $mailConfiguration = static::getOutboundEmailConfiguration($user);
        // Bug #59513
        // until PHP 5.3 is standard on test environments, static:: cannot be used for late static binding

        // generate the Mailer
        $mailer = static::getMailer($mailConfiguration);

        return $mailer;
    }

    /**
     * Determines the correct Mailer to use based on the configuration that is provided to it and constructs and
     * returns that object. This method allows the caller to get a Mailer with a configuration that overrides the
     * user's configuration.
     *
     * @static
     * @access public
     * @param OutboundEmailConfiguration $config          required The configuration that provides context to the chosen sending
     *                                                    strategy.
     * @return mixed An object of one of the Mailers defined in $modeToMailerMap.
     * @throws MailerException Allows MailerExceptions to bubble up.
     */
    public static function getMailer(OutboundEmailConfiguration $config)
    {
        $headers = static::buildHeadersForMailer($config->getFrom(), $config->getReplyTo());
        $mailer  = static::buildMailer($config);
        $mailer->setHeaders($headers);

        return $mailer;
    }

    /**
     * Instantiates the requisite Mailer and returns it.
     *
     * @static
     * @access private
     * @param OutboundEmailConfiguration $config          required Must be an OutboundEmailConfiguration or a type that derives
     *                                                    from it.
     * @return mixed An object of one of the Mailers defined in $modeToMailerMap.
     * @throws MailerException
     */
    private static function buildMailer(OutboundEmailConfiguration $config)
    {
        $mode     = $config->getMode();
        $strategy = static::getStrategy($mode);
        $mailer   = null;

        if (is_null($strategy)) {
            throw new MailerException(
                "Invalid Mailer: Could not find a strategy for mode '{$mode}'",
                MailerException::InvalidMailer
            );
        }

        if (class_exists($strategy)) {
            $mailer = new $strategy($config);
        }

        if (!($mailer instanceof $strategy)) {
            throw new MailerException(
                "Invalid Mailer: Could not find the strategy defined by class '{$strategy}'",
                MailerException::InvalidMailer
            );
        }

        return $mailer;
    }

    /**
     * Constructs and returns the Headers object to be used by the Mailer and takes care of initializing the From
     * and Sender headers.
     *
     * @static
     * @access private
     * @param EmailIdentity $from             required The true sender of the email.
     * @param EmailIdentity $replyTo          Should be an EmailIdentity, but null is acceptable if no Reply-To header
     *                                        is to be set.
     * @return EmailHeaders
     * @throws MailerException
     */
    private static function buildHeadersForMailer(EmailIdentity $from, EmailIdentity $replyTo = null)
    {
        // add the known email headers
        $headers = new EmailHeaders();
        $headers->setHeader(EmailHeaders::From, $from);
        $headers->setHeader(EmailHeaders::Sender, $from);

        // add the Reply-To header, but only if it should be different from the From header
        if (!is_null($replyTo)) {
            $headers->setHeader(EmailHeaders::ReplyTo, $replyTo);
        }

        return $headers;
    }

    /**
     * Returns the system outbound email configuration associated with the specified user.
     *
     * @access protected
     * @param User $user required The user from which the mail configuration is retrieved.
     * @return OutboundEmailConfiguration An OutboundEmailConfiguration object or one that derives from it.
     * @throws MailerException Allows MailerExceptions to bubble up.
     */
    protected static function getOutboundEmailConfiguration(User $user)
    {
        return OutboundEmailConfigurationPeer::getSystemMailConfiguration($user);
    }

    /**
     * Maps the mode from a OutboundEmailConfiguration to the class that represents the sending strategy for that
     * configuration.
     *
     * @static
     * @access protected
     * @return array key = mode; value = mailer class
     */
    protected static function getStrategies()
    {
        return array(
            OutboundEmailConfigurationPeer::MODE_SMTP => "SmtpMailer",
        );
    }

    /**
     * Returns the class name for the sending strategy that is used for the defined mode.
     *
     * @static
     * @access protected
     * @param $mode
     * @return null|string The class name for the chosen strategy.
     */
    protected static function getStrategy($mode)
    {
        $strategy   = null;
        $strategies = static::getStrategies();

        if (array_key_exists($mode, $strategies)) {
            $strategy = $strategies[$mode];
        }

        return $strategy;
    }
}
