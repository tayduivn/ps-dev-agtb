<?php
/*********************************************************************************
 * The contents of this file are subject to the SugarCRM Master Subscription
 * Agreement ("License") which can be viewed at
 * http://www.sugarcrm.com/crm/master-subscription-agreement
 * By installing or using this file, You have unconditionally agreed to the
 * terms and conditions of the License, and You may not use this file except in
 * compliance with the License.  Under the terms of the license, You shall not,
 * among other things: 1) sublicense, resell, rent, lease, redistribute, assign
 * or otherwise transfer Your rights to the Software, and 2) use the Software
 * for timesharing or service bureau purposes such as hosting the Software for
 * commercial gain and/or for the benefit of a third party.  Use of the Software
 * may be subject to applicable fees and any use of the Software without first
 * paying applicable fees is strictly prohibited.  You do not have the right to
 * remove SugarCRM copyrights from the source code or user interface.
 *
 * All copies of the Covered Code must include on each user interface screen:
 *  (i) the "Powered by SugarCRM" logo and
 *  (ii) the SugarCRM copyright notice
 * in the same form as they appear in the distribution.  See full license for
 * requirements.
 *
 * Your Warranty, Limitations of liability and Indemnity are expressly stated
 * in the License.  Please refer to the License for the specific language
 * governing these rights and limitations under the License.  Portions created
 * by SugarCRM are Copyright (C) 2004-2012 SugarCRM, Inc.; All Rights Reserved.
 ********************************************************************************/

class OutboundEmailConfigurationTestHelper {
    private static $existingConfigurations = array();

    public static function backupExistingConfigurations() {
        self::$existingConfigurations = array();

        $sql    = "SELECT id FROM outbound_email";
        $result = $GLOBALS["db"]->query($sql);

        while ($row = $GLOBALS["db"]->fetchByAssoc($result)) {
            $outboundEmail = new OutboundEmail();
            $outboundEmail->retrieve($row["id"]);
            self::$existingConfigurations[] = $outboundEmail;
            $outboundEmail->delete();
        }

        $sql    = "SELECT id FROM inbound_email";
        $result = $GLOBALS["db"]->query($sql);

        while ($row = $GLOBALS["db"]->fetchByAssoc($result)) {
            $inboundEmail = new InboundEmail();
            $inboundEmail->retrieve($row["id"], true, false);
            self::$existingConfigurations[] = $inboundEmail;
            $inboundEmail->hardDelete($inboundEmail->id);
        }
    }

    public static function restoreExistingConfigurations() {
        self::removeAllCreatedEmailRecords();

        foreach (self::$existingConfigurations as $config) {
            $config->new_with_id = true;
            $config->save();
        }
    }

    public static function createSeedConfigurations($seedCount = 1) {
        $configs = array();

        for ($i = 0; $i < $seedCount; $i++) {
            $config = OutboundEmailConfigurationTestHelper::createOutboundEmailConfig(
                "User{$i}",
                $GLOBALS["current_user"]->id,
                "user",
                "user{$i}@unit.net",
                "User{$i}"
            );

            list($inboundEmail, $outboundEmail) =
                OutboundEmailConfigurationTestHelper::createInboundAndOutboundEmail($config);

            $configs[$i] = array(
                "inbound"  => $inboundEmail,
                "outbound" => $outboundEmail,
            );
        }

        return $configs;
    }

    public static function createOutboundEmailConfig($name = "System", $userId = "1", $type = "system",
        $fromEmail = "system@unit.net", $fromName = "System"
    ) {
        $config                        = array();
        $config["name"]                = $name;
        $config["type"]                = $type;
        $config["user_id"]             = $userId;
        $config["from_email"]          = $fromEmail;
        $config["from_name"]           = $fromName;
        $config["mail_sendtype"]       = "SMTP";
        $config["mail_smtptype"]       = "other";
        $config["mail_smtpserver"]     = "smtp.yahoomailservice.com";
        $config["mail_smtpport"]       = "25";
        $config["mail_smtpuser"]       = "YahooUser";
        $config["mail_smtppass"]       = "YahooUserPassword";
        $config["mail_smtpauth_req"]   = "1";
        $config["mail_smtpssl"]        = "0";

        return $config;
    }

    public static function createOutboundEmail($config) {
        $outboundEmail                    = new OutboundEmail();
        $outboundEmail->new_with_id       = true;
        $outboundEmail->id                = create_guid();
        $outboundEmail->name              = $config["name"];
        $outboundEmail->type              = $config["type"];
        $outboundEmail->user_id           = $config["user_id"];
        $outboundEmail->mail_sendtype     = $config["mail_sendtype"];
        $outboundEmail->mail_smtptype     = $config["mail_smtptype"];
        $outboundEmail->mail_smtpserver   = $config["mail_smtpserver"];
        $outboundEmail->mail_smtpport     = $config["mail_smtpport"];
        $outboundEmail->mail_smtpuser     = $config["mail_smtpuser"];
        $outboundEmail->mail_smtppass     = $config["mail_smtppass"];
        $outboundEmail->mail_smtpauth_req = $config["mail_smtpauth_req"];
        $outboundEmail->mail_smtpssl      = $config["mail_smtpssl"];
        $outboundEmail->save();

        return $outboundEmail;
    }

    public static function createInboundAndOutboundEmail($config) {
        // outbound email
        $outboundEmail = OutboundEmailConfigurationTestHelper::createOutboundEmail($config);

        // inbound email
        $storedOptions                   = array();
        $storedOptions["from_addr"]      = $config["from_email"];
        $storedOptions["from_name"]      = $config["from_name"];
        $storedOptions["outbound_email"] = $outboundEmail->id;

        $inboundEmail                 = new InboundEmail();
        $inboundEmail->new_with_id    = true;
        $inboundEmail->id             = create_guid();
        $inboundEmail->name           = $config["name"];
        $inboundEmail->stored_options = base64_encode(serialize($storedOptions));
        $inboundEmail->is_personal    = true;
        $inboundEmail->created_by     = $config["user_id"];
        $inboundEmail->group_id       = $config["user_id"];
        $inboundEmail->save();

        return array($inboundEmail, $outboundEmail);
    }

    private static function removeAllCreatedEmailRecords() {
        $sql = "DELETE FROM outbound_email";
        $GLOBALS["db"]->query($sql);

        $sql = "DELETE FROM inbound_email";
        $GLOBALS["db"]->query($sql);
    }
}
