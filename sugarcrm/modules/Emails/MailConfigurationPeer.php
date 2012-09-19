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

class MailConfigurationPeer {

    const MODE_SMTP = "smtp";
    const MODE_WEB  = "web";

    /**
     * @return array MailConfigurations
     */
    public static function getMailConfigurations(User $user, $systemOnly=false) {
        $mailConfigurations = array();
        $ret                = $user->getUsersNameAndEmail();

        if (empty($ret['email'])) {
            $systemReturn = $user->getSystemDefaultNameAndEmail();
            $ret['email'] = $systemReturn['email'];
            $ret['name']  = from_html($systemReturn['name']);
        } else {
            $ret['name'] = from_html($ret['name']);
        }

        if (!$systemOnly) {
            /* Retrieve any Inbound User Mail Accounts and the Outbound Mail Accounts Associated with them */
            $ie         = new InboundEmail();
            $ieAccounts = $ie->retrieveAllByGroupIdWithGroupAccounts($user->id);

            foreach ($ieAccounts as $k => $v) {
                $name          = $v->get_stored_options('from_name');
                $addr          = $v->get_stored_options('from_addr');
                $storedOptions = unserialize(base64_decode($v->stored_options));
                // var_dump($storedOptions);

                if ($name != null && $addr != null) {
                    $name                            = from_html($name);
                    $mailConfiguration               = new MailConfiguration($user);
                    $mailConfiguration->config_id    = $storedOptions["outbound_email"];
                    $mailConfiguration->config_type  = 'user';
                    $mailConfiguration->sender_name  = "{$name}";
                    $mailConfiguration->sender_email = "{$addr}";
                    $mailConfiguration->display_name = "{$name} ({$addr})";
                    $mailConfiguration->personal     = (bool)($v->is_personal);

                    // turn the OutboundEmail object into a useable set of mail configurations
                    $oe = new OutboundEmail();
                    $oe->retrieve($mailConfiguration->config_id);
                    $oeAsArray                           = self::toArray($oe);
                    $mailConfiguration->mode             = strtolower($oeAsArray['mail_sendtype']);
                    $mailConfiguration->config_name      = $oeAsArray['name'];
                    $mailConfiguration->mailerConfigData = self::buildMailerConfiguration(
                        $oeAsArray,
                        $mailConfiguration->mode
                    );

                    $mailConfigurations[] = $mailConfiguration;
                } // if
            } // foreach
        }

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

            // turn the OutboundEmail object into a useable set of mail configurations
            $oe = new OutboundEmail();
            $oe->retrieve($system->id);
            $oeAsArray                           = self::toArray($oe);
            $mailConfiguration->mode             = strtolower($oeAsArray['mail_sendtype']);
            $mailConfiguration->config_name      = $oeAsArray['name'];
            $mailConfiguration->mailerConfigData = self::buildMailerConfiguration(
                $oeAsArray,
                $mailConfiguration->mode
            );

            $mailConfigurations[] = $mailConfiguration;
        }

        return $mailConfigurations;
    }

    private static function buildMailerConfiguration($oe, $mode) {
        $mailerConfig = null;

        // setup the mailer's known configurations based on the type of mailer
        switch ($mode) {
            case self::MODE_SMTP:
                $mailerConfig = new SmtpMailerConfiguration();
                $mailerConfig->setConfig("smtp.host", $oe['mail_smtpserver']);
                $mailerConfig->setConfig("smtp.port", $oe['mail_smtpport']);

                if ($oe['mail_smtpauth_req']) {
                    // require authentication with the SMTP server
                    $mailerConfig->setConfig("smtp.authenticate", true);
                    $mailerConfig->setConfig("smtp.username", $oe['mail_smtpuser']);
                    //@todo wrap this value in from_html()? do now or at time of transfer?
                    $mailerConfig->setConfig("smtp.password", $oe['mail_smtppass']);
                }

                // determine the appropriate encryption layer for the sending strategy
                if ($oe['mail_smtpssl'] === 1) {
                    $mailerConfig->setConfig("smtp.secure", SmtpMailerConfiguration::SecureSsl);
                } elseif ($oe['mail_smtpssl'] === 2) {
                    $mailerConfig->setConfig("smtp.secure", SmtpMailerConfiguration::SecureTls);
                }

                break;
            default:
                $mailerConfig = new MailerConfiguration();
                break;
        }

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