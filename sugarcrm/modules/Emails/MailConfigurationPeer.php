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

require_once "include/OutboundEmail/OutboundEmail.php";
require_once "modules/InboundEmail/InboundEmail.php";
require_once "MailConfiguration.php";
require_once "modules/Users/User.php";

class MailConfigurationPeer
{
    const MODE_DEFAULT = "default";
    const MODE_SMTP    = "smtp";
    const MODE_WEB     = "web";

    /**
     * Returns true/false indicating whether or not $mode is a valid sending strategy.
     *
     * @static
     * @access public
     * @param string $mode required
     * @return bool
     */
    public static function isValidMode($mode) {
        switch ($mode) {
            case self::MODE_DEFAULT:
            case self::MODE_SMTP:
            case self::MODE_WEB:
                return true;
                break;
            default:
                return false;
                break;
        }
    }


    /**
     * @return MailConfiguration  System or User defined System-Override Mail Configuration
     */
    public static function getSystemMailConfiguration(User $user, Localization $locale = null, $charset = null) {
        $mailConfigurations = self::listMailConfigurations($user, $locale, $charset);

        foreach($mailConfigurations AS $mailConfiguration) {
            if ($mailConfiguration->config_type == 'system') {
                return $mailConfiguration;
            }
        }
        return null;
    }


    /**
     * @return array MailConfigurations
     */
    public static function listMailConfigurations(User $user, Localization $locale = null, $charset = null) {
        if (is_null($locale)) {
            $locale = $GLOBALS["locale"];
        }

        if (is_null($charset)) {
            $charset = $locale->getPrecedentPreference("default_email_charset");
        }

        $mailConfigurations = array();
        $ret                = $user->getUsersNameAndEmail();

        if (empty($ret['email'])) {
            $systemReturn = $user->getSystemDefaultNameAndEmail();
            $ret['email'] = $systemReturn['email'];
            $ret['name']  = from_html($systemReturn['name']);
            $system_replyToAddress = $ret['email'];
        } else {
            $ret['name'] = from_html($ret['name']);
            $system_replyToAddress = '';
        }

        $system_replyToName    = $ret['name'];
        $replyTo = $user->emailAddress->getReplyToAddress($user, true);
        if (!empty($replyTo)) {
            $system_replyToAddress = $replyTo;
        }

        /* Retrieve any Inbound User Mail Accounts and the Outbound Mail Accounts Associated with them */
        $ie         = new InboundEmail();
        $ieAccounts = $ie->retrieveAllByGroupIdWithGroupAccounts($user->id);

        foreach ($ieAccounts as $k => $v) {
            $name          = $v->get_stored_options('from_name');
            $addr          = $v->get_stored_options('from_addr');
            $storedOptions = unserialize(base64_decode($v->stored_options));

            $outbound_config_id = $storedOptions["outbound_email"];
            $oe = null;
            if (!empty($outbound_config_id)) {
                $oe = new OutboundEmail();
                $oe->retrieve($outbound_config_id);
            }
            if ($name != null && $addr != null && !empty($outbound_config_id) && !empty($oe) && ($outbound_config_id == $oe->id)) {
                $name                            = from_html($name);
                $mailConfiguration               = new MailConfiguration($user);
                $mailConfiguration->config_id    = $outbound_config_id;
                $mailConfiguration->config_type  = 'user';
                $mailConfiguration->inbox_id     = $k;
                $mailConfiguration->sender_name  = "{$name}";
                $mailConfiguration->sender_email = "{$addr}";
                $mailConfiguration->display_name = "{$name} ({$addr})";
                $mailConfiguration->personal     = (bool)($v->is_personal);

                $mailConfiguration->replyto_name  = (!empty($storedOptions['reply_to_name']) ?
                    $storedOptions['reply_to_name'] :
                    $mailConfiguration->sender_name);
                $mailConfiguration->replyto_email = (!empty($storedOptions['reply_to_addr']) ?
                    $storedOptions['reply_to_addr'] :
                    $mailConfiguration->sender_email);

                // turn the OutboundEmail object into a useable set of mail configurations
                $oeAsArray                           = self::toArray($oe);
                $mailConfiguration->mode             = strtolower($oeAsArray['mail_sendtype']);
                $mailConfiguration->config_name      = $oeAsArray['name'];
                $mailConfiguration->mailerConfigData = self::buildMailerConfiguration(
                    $oeAsArray,
                    $mailConfiguration->mode,
                    $locale,
                    $charset
                );

                $mailConfigurations[] = $mailConfiguration;
            } // if
        } // foreach

        $oe     = new OutboundEmail();
        $system = $oe->getSystemMailerSettings();

        //Substitute in the users system override if its available.
        $userSystemOverride = $oe->getUsersMailerForSystemOverride($user->id);
        $personal           = false;

        if ($userSystemOverride != null) {
            $system   = $userSystemOverride;
            $personal = true;
        }

        if (!empty($system->mail_smtpserver)) {
            $mailConfiguration               = new MailConfiguration($user);
            $mailConfiguration->config_id    = $system->id;
            $mailConfiguration->config_type  = 'system';
            $mailConfiguration->sender_name  = "{$ret['name']}";
            $mailConfiguration->sender_email = "{$ret['email']}";
            $mailConfiguration->display_name = "{$ret['name']} ({$ret['email']})";
            $mailConfiguration->personal     = $personal;

            $mailConfiguration->replyto_name  = $system_replyToName;
            $mailConfiguration->replyto_email = $system_replyToAddress;

            // turn the OutboundEmail object into a useable set of mail configurations
            $oe = new OutboundEmail();
            $oe->retrieve($system->id);
            $oeAsArray                           = self::toArray($oe);
            $mailConfiguration->mode             = strtolower($oeAsArray['mail_sendtype']);
            $mailConfiguration->config_name      = $oeAsArray['name'];
            $mailConfiguration->mailerConfigData = self::buildMailerConfiguration(
                $oeAsArray,
                $mailConfiguration->mode,
                $locale,
                $charset
            );

            $mailConfigurations[] = $mailConfiguration;
        }

        return $mailConfigurations;
    }


    private static function buildMailerConfiguration($oe, $mode, Localization $locale, $charset) {
        $mailerConfig = null;

        // setup the mailer's known configurations based on the type of mailer
        switch ($mode) {
            case self::MODE_SMTP:
                $mailerConfig = new SmtpMailerConfiguration();
                $mailerConfig->setHost($oe['mail_smtpserver']);
                $mailerConfig->setPort($oe['mail_smtpport']);

                if ($oe['mail_smtpauth_req']) {
                    // require authentication with the SMTP server
                    $mailerConfig->setAuthenticationRequirement(true);
                    $mailerConfig->setUsername($oe['mail_smtpuser']);
                    $mailerConfig->setPassword($oe['mail_smtppass']);
                }

                // determine the appropriate encryption layer for the sending strategy
                if ($oe['mail_smtpssl'] === 1) {
                    $mailerConfig->setCommunicationProtocol(SmtpMailerConfiguration::CommunicationProtocolSsl);
                } elseif ($oe['mail_smtpssl'] === 2) {
                    $mailerConfig->setCommunicationProtocol(SmtpMailerConfiguration::CommunicationProtocolTls);
                }

                break;
            default:
                $mailerConfig = new MailerConfiguration();
                break;
        }

        $mailerConfig->setLocale($locale);
        $mailerConfig->setCharset($charset);

        return $mailerConfig;
    }


    private static function toArray($obj, $scalarOnly=true) {
        $fields = get_object_vars($obj);
        $arr    = array();

        foreach ($fields as $name => $type) {
            if (isset($obj->$name)) {
                if ((!$scalarOnly) || (!is_array($obj->$name) && !is_object($obj->$name))) {
                    $arr[$name] = $obj->$name;
                }
            } else {
                $arr[$name] = '';
            }
        }

        return $arr;
    }
}