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

class OutboundEmailConfigurationTestHelper
{
    private static $existingConfigurations = array();
    private static $systemConfiguration;

    public static function setUp()
    {
        self::backupExistingConfigurations();
        self::$systemConfiguration = self::createSystemOutboundEmailConfiguration();
    }

    public static function tearDown()
    {
        self::restoreExistingConfigurations();
    }

    public static function getSystemConfiguration() {
        return self::$systemConfiguration;
    }

    public static function backupExistingConfigurations()
    {
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

    public static function restoreExistingConfigurations()
    {
        self::removeAllCreatedEmailRecords();

        foreach (self::$existingConfigurations as $configuration) {
            $configuration->new_with_id = true;
            $configuration->save();
        }
    }

    public static function createSystemOutboundEmailConfiguration()
    {
        $configuration = self::mergeOutboundEmailConfigurations();

        return self::createOutboundEmail($configuration);
    }

    public static function createSystemOverrideOutboundEmailConfiguration($userId = "1")
    {
        if (empty($userId)) {
            $userId = $GLOBALS["current_user"]->id;
        }

        $name   = "System Override";
        $configuration = array(
            "name"       => $name,
            "type"       => "system-override",
            "user_id"    => $userId,
            "from_email" => "{$userId}@unit.net",
            "from_name"  => $name,
        );
        $configuration = self::mergeOutboundEmailConfigurations($configuration);

        return self::createOutboundEmail($configuration);
    }

    public static function createUserOutboundEmailConfiguration($userId = "1")
    {
        if (empty($userId)) {
            $userId = $GLOBALS["current_user"]->id;
        }

        $name   = "For User {$userId}";
        $configuration = array(
            "name"       => $name,
            "type"       => "user",
            "user_id"    => $userId,
            "from_email" => "{$userId}@unit.net",
            "from_name"  => $name,
        );
        $configuration = self::mergeOutboundEmailConfigurations($configuration);

        return self::createOutboundEmail($configuration);
    }

    public static function createUserOutboundEmailConfigurations($seedCount = 1)
    {
        $configurations = array();

        for ($i = 0; $i < $seedCount; $i++) {
            $outboundEmail = self::createUserOutboundEmailConfiguration($GLOBALS["current_user"]->id);

            $storedOptions = array(
                "from_addr"      => "{$GLOBALS["current_user"]->id}@unit.net",
                "from_name"      => "For User {$GLOBALS["current_user"]->id}",
                "outbound_email" => $outboundEmail->id,
            );
            $inboundEmail  = self::createInboundEmail($GLOBALS["current_user"]->id, $storedOptions);

            $configurations[$i] = array(
                "inbound"  => $inboundEmail,
                "outbound" => $outboundEmail,
            );
        }

        return $configurations;
    }

    public static function mergeOutboundEmailConfigurations($configuration = array()) {
        $defaults = array(
            "name"              => "System",
            "type"              => "system",
            "user_id"           => "1",
            "from_email"        => "foo@bar.com",
            "from_name"         => "Foo Bar",
            "mail_sendtype"     => "SMTP",
            "mail_smtptype"     => "other",
            "mail_smtpserver"   => "smtp.bar.com",
            "mail_smtpport"     => "25",
            "mail_smtpuser"     => "foo",
            "mail_smtppass"     => "foobar",
            "mail_smtpauth_req" => "1",
            "mail_smtpssl"      => "0",
        );

        return array_merge($defaults, $configuration);
    }

    public static function createOutboundEmail($configuration)
    {
        $outboundEmail                    = new OutboundEmail();
        $outboundEmail->new_with_id       = true;
        $outboundEmail->id                = create_guid();
        $outboundEmail->name              = $configuration["name"];
        $outboundEmail->type              = $configuration["type"];
        $outboundEmail->user_id           = $configuration["user_id"];
        $outboundEmail->mail_sendtype     = $configuration["mail_sendtype"];
        $outboundEmail->mail_smtptype     = $configuration["mail_smtptype"];
        $outboundEmail->mail_smtpserver   = $configuration["mail_smtpserver"];
        $outboundEmail->mail_smtpport     = $configuration["mail_smtpport"];
        $outboundEmail->mail_smtpuser     = $configuration["mail_smtpuser"];
        $outboundEmail->mail_smtppass     = $configuration["mail_smtppass"];
        $outboundEmail->mail_smtpauth_req = $configuration["mail_smtpauth_req"];
        $outboundEmail->mail_smtpssl      = $configuration["mail_smtpssl"];
        $outboundEmail->save();

        return $outboundEmail;
    }

    public static function createInboundEmail($userId = "1", $storedOptions = array())
    {
        if (empty($userId)) {
            $userId = $GLOBALS["current_user"]->id;
        }

        $inboundEmail                 = BeanFactory::getBean("InboundEmail");
        $inboundEmail->new_with_id    = true;
        $inboundEmail->id             = create_guid();
        $inboundEmail->name           = "For User {$userId}";
        $inboundEmail->stored_options = base64_encode(serialize($storedOptions));
        $inboundEmail->is_personal    = true;
        $inboundEmail->created_by     = $userId;
        $inboundEmail->group_id       = $userId;
        $inboundEmail->save();

        return $inboundEmail;
    }

    public static function removeAllCreatedEmailRecords()
    {
        $sql = "DELETE FROM outbound_email";
        $GLOBALS["db"]->query($sql);

        $sql = "DELETE FROM inbound_email";
        $GLOBALS["db"]->query($sql);
    }
}
