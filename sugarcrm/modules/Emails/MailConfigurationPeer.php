<?php
if(!defined('sugarEntry') || !sugarEntry) die('Not A Valid Entry Point');
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

require_once 'include/OutboundEmail/OutboundEmail.php';
require_once 'modules/Emails/MailConfiguration.php';

class MailConfigurationPeer {

    const MODE_SMTP = "smtp";
    const MODE_WEB  = "web";

    /**
     * @return array MailConfigurations
     */
    public static function getMailConfigurations(User $user, $systemOnly=false) {
        $mailConfigurations = array();
        $oe = new OutboundEmail();
        $system = $oe->getSystemMailerSettings();
        $ret = $user->getUsersNameAndEmail();
        if (empty($ret['email'])) {
            $systemReturn = $user->getSystemDefaultNameAndEmail();
            $ret['email'] = $systemReturn['email'];
            $ret['name'] = from_html($systemReturn['name']);
        } else {
            $ret['name'] = from_html($ret['name']);
        }

        if (!$systemOnly) {
            /* Retrieve any Inbound User Mail Accounts and the Outbound Mail Accounts Associated with them */
            $ie = new InboundEmail();
            $ieAccounts = $ie->retrieveAllByGroupIdWithGroupAccounts($user->id);
            foreach($ieAccounts as $k => $v) {
                $name = $v->get_stored_options('from_name');
                $addr = $v->get_stored_options('from_addr');
                $storedOptions = unserialize(base64_decode($v->stored_options));
                // var_dump($storedOptions);
                if ($name != null && $addr != null) {
                    $name = from_html($name);
                    $mailConfiguration = new MailConfiguration($user);
                    $mailConfiguration->config_id   = $storedOptions["outbound_email"];
                    $mailConfiguration->type = 'user';
                    $mailConfiguration->sender_name = "{$name}";
                    $mailConfiguration->sender_email = "{$addr}";
                    $mailConfiguration->display_name = "{$name} ({$addr})";
                    $mailConfiguration->personal = (bool) ($v->is_personal);

                    $oe = new OutboundEmail();
                    $oe->retrieve($mailConfiguration->config_id);
                    $mailConfiguration->config_data = self::toArray($oe);
                    $mailConfiguration->mode = strtolower($mailConfiguration->config_data['mail_sendtype']);
                    $mailConfigurations[] = $mailConfiguration;
                } // if
            } // foreach
        }


        //Substitute in the users system override if its available.
        $userSystemOverride = $oe->getUsersMailerForSystemOverride($user->id);
        $personal = false;
        if($userSystemOverride != null) {
            $system = $userSystemOverride;
            $personal = true;
        }
        if (!empty($system->mail_smtpserver)) {
            $mailConfiguration = new MailConfiguration($user);
            $mailConfiguration->config_id   = $system->id;
            $mailConfiguration->type = 'system';
            $mailConfiguration->sender_name = "{$ret['name']}";
            $mailConfiguration->sender_email = "{$ret['email']}";
            $mailConfiguration->display_name = "{$ret['name']} ({$ret['email']})";
            $mailConfiguration->personal = $personal;

            $oe = new OutboundEmail();
            $oe->retrieve($system->id);
            $mailConfiguration->config_data = self::toArray($oe);
            $mailConfiguration->mode = strtolower($mailConfiguration->config_data['mail_sendtype']);
            $mailConfigurations[] = $mailConfiguration;
        }
        return $mailConfigurations;
    }


    private static function toArray($obj, $scalarOnly=true)
    {
        $fields = get_object_vars($obj);
        $arr = array();

        foreach($fields as $name => $type) {
            if (isset($obj->$name)) {
                if ((!$scalarOnly) || ( !is_array($obj->$name) && !is_object($obj->$name)) )
                    $arr[$name] = $obj->$name;
            } else {
                $arr[$name] = '';
            }
        }
        return $arr;
    }
}