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

require_once('modules/Emails/MailConfigurationPeer.php');

/**
 *
 */
class MailConfigurationPeerTest extends Sugar_PHPUnit_Framework_TestCase
{
    public static $current_user;
    public static $system_config_exists=true;
    public static $system_config;

    public static function setUpBeforeClass()
    {
        $sql = "DELETE FROM outbound_email where name like 'SugarTest%'";
        $GLOBALS['db']->query($sql);
        //printf("(DELETE) SQL: %s\n",$sql);
        $sql = "DELETE FROM inbound_email where name like 'SugarTest%'";
        $GLOBALS['db']->query($sql);
        //printf("(DELETE) SQL: %s\n",$sql);

        $GLOBALS['current_user'] = SugarTestUserUtilities::createAnonymousUser();
        self::$current_user = $GLOBALS['current_user'];
        $obj = self::get_system_mail_config();
        if ($obj) {
            self::$system_config_exists=true;
            self::$system_config = $obj;
        }
        else {
            self::$system_config_exists=false;
            $obj = new OutboundEmail();
            $obj->new_with_id = TRUE;
            $obj->name = 'system';
            $obj->type = 'system';
            $obj->user_id = '1';
            $obj->mail_sendtype = "SMTP";
            $obj->mail_smtptype = "other";
            $obj->mail_smtpserver = "smtp.yahoomailservice.com";
            $obj->mail_smtpport   = "25";
            $obj->mail_smtpuser = "YahooUser";
            $obj->mail_smtppass = "YahooUserPassword";
            $obj->mail_smtpauth_req = '1';
            $obj->mail_smtpssl = '0';
            $obj->save();
            self::$system_config = $obj;
        }
    }

    public static function tearDownAfterClass()
    {
        $sql = "DELETE FROM outbound_email where name like 'SugarTest%'";
        $GLOBALS['db']->query($sql);
        //printf("(DELETE) SQL: %s\n",$sql);
        $sql = "DELETE FROM inbound_email where name like 'SugarTest%'";
        $GLOBALS['db']->query($sql);
        //printf("(DELETE) SQL: %s\n",$sql);

        SugarTestUserUtilities::removeAllCreatedAnonymousUsers();
        unset($GLOBALS['current_user']);
        parent::tearDown();
    }

   function testgetMailConfigurations_All_Success()
    {
        $config1_array = array(
            "from_name"         => "Sugar UnitTest1",
            "from_email"        => "unit_test1@sugar_unit_test.net",
            "name"              => 'SugarTest1',
            "type"              => 'user',
            "user_id"           => self::$current_user->id,
            "mail_sendtype"     => "SMTP",
            "mail_smtptype"     => "other",
            "mail_smtpserver"   => "smtp.yahoomailservice.com",
            "mail_smtpport"     => "25",
            "mail_smtpuser"     => "YahooUser",
            "mail_smtppass"     => "YahooUserPassword",
            "mail_smtpauth_req" => '1',
            "mail_smtpssl"      => '0',
        );

        $config2_array = array(
            "from_name"         => "Sugar UnitTest2",
            "from_email"        => "unit_test2@sugar_unit_test.net",
            "name"              => 'SugarTest2',
            "type"              => 'user',
            "user_id"           => self::$current_user->id,
            "mail_sendtype"     => "SMTP",
            "mail_smtptype"     => "other",
            "mail_smtpserver"   => "smtp.yahoomailservice.com",
            "mail_smtpport"     => "25",
            "mail_smtpuser"     => "YahooUser",
            "mail_smtppass"     => "YahooUserPassword",
            "mail_smtpauth_req" => '1',
            "mail_smtpssl"      => '0',
        );

        list($ib1, $ob1) = self::createInboundAndOutboundEmail($config1_array);
        list($ib2, $ob2) = self::createInboundAndOutboundEmail($config2_array);

        $mail_configs_expected = array(
            $ob1->id  => $ob1->name,
            $ob2->id  => $ob2->name,
            self::$system_config->id => self::$system_config->name
        );
        //print_r($mail_configs_expected);

        $configs = MailConfigurationPeer::getMailConfigurations(self::$current_user,false);

        $mail_configs_actual = array();
        if (is_array($configs)) {
            foreach($configs AS $config) {
                $mail_configs_actual[$config->config_id] = $config->config_name;
            }
        }
        //print_r($mail_configs_actual);

        self::deleteInboundEmail($ib1->id,$ib1->name);
        self::deleteInboundEmail($ib2->id,$ib2->name);

        self::deleteOutboundEmail($ob1->id,$ob1->name);
        self::deleteOutboundEmail($ob2->id,$ob2->name);
        if (!self::$system_config_exists) {
            self::deleteOutboundEmail(self::$system_config->id,self::$system_config->name);
        }

        $this->assertEquals($mail_configs_expected, $mail_configs_actual, "Unexpected list for 'ALL' MailConfigurations");
    }


    function testgetMailConfigurations_SystemOnly_Success()
    {
        $config1_array = array(
            "from_name"         => "Sugar UnitTest1",
            "from_email"        => "unit_test1@sugar_unit_test.net",
            "name"              => 'SugarTest1',
            "type"              => 'user',
            "user_id"           => self::$current_user->id,
            "mail_sendtype"     => "SMTP",
            "mail_smtptype"     => "other",
            "mail_smtpserver"   => "smtp.yahoomailservice.com",
            "mail_smtpport"     => "25",
            "mail_smtpuser"     => "YahooUser",
            "mail_smtppass"     => "YahooUserPassword",
            "mail_smtpauth_req" => '1',
            "mail_smtpssl"      => '0',
        );

        $config2_array = array(
            "from_name"         => "Sugar UnitTest2",
            "from_email"        => "unit_test2@sugar_unit_test.net",
            "name"              => 'SugarTest2',
            "type"              => 'user',
            "user_id"           => self::$current_user->id,
            "mail_sendtype"     => "SMTP",
            "mail_smtptype"     => "other",
            "mail_smtpserver"   => "smtp.yahoomailservice.com",
            "mail_smtpport"     => "25",
            "mail_smtpuser"     => "YahooUser",
            "mail_smtppass"     => "YahooUserPassword",
            "mail_smtpauth_req" => '1',
            "mail_smtpssl"      => '0',
        );

        list($ib1, $ob1) = self::createInboundAndOutboundEmail($config1_array);
        list($ib2, $ob2) = self::createInboundAndOutboundEmail($config2_array);

        $mail_configs_expected = array(
            self::$system_config->id => self::$system_config->name
        );
        //print_r($mail_configs_expected);

        $configs = MailConfigurationPeer::getMailConfigurations(self::$current_user,true);

        $mail_configs_actual = array();
        if (is_array($configs)) {
            foreach($configs AS $config) {
                $mail_configs_actual[$config->config_id] = $config->config_name;
            }
        }
        //print_r($mail_configs_actual);

        self::deleteInboundEmail($ib1->id,$ib1->name);
        self::deleteInboundEmail($ib2->id,$ib2->name);

        self::deleteOutboundEmail($ob1->id,$ob1->name);
        self::deleteOutboundEmail($ob2->id,$ob2->name);
        if (!self::$system_config_exists) {
            self::deleteOutboundEmail(self::$system_config->id,self::$system_config->name);
        }

        $this->assertEquals($mail_configs_expected, $mail_configs_actual, "Unexpected list for 'SYSTEM' MailConfigurations");
    }



    private static function createOutboundEmail($config) {
        $obj = new OutboundEmail();

        $obj->new_with_id   = true;
        $obj->id            = create_guid();

        $obj->name              = $config['name'];
        $obj->type              = $config['type'];
        $obj->user_id           = $config['user_id'];
        $obj->mail_sendtype     = $config['mail_sendtype'];
        $obj->mail_smtptype     = $config['mail_smtptype'];
        $obj->mail_smtpserver   = $config['mail_smtpserver'];
        $obj->mail_smtpport     = $config['mail_smtpport'];
        $obj->mail_smtpuser     = $config['mail_smtpuser'];
        $obj->mail_smtppass     = $config['mail_smtppass'];
        $obj->mail_smtpauth_req = $config['mail_smtpauth_req'];
        $obj->mail_smtpssl      = $config['mail_smtpssl'];
        $obj->save();

        //printf("(CREATE): ID=%s  NAME=%s\n",$obj->id,$obj->name);
        return $obj;
    }


    private static function createInboundAndOutboundEmail($config) {
        /*------ Outbound Email -----------------*/
        $ob = self::createOutboundEmail($config);

        /*------ Inbound Email -----------------*/
        $stored_options['from_name'] = $config['from_name'];
        $stored_options['from_addr'] = $config['from_email'];
        $stored_options["outbound_email"] = $ob->id;
        $encoded_stored_options = base64_encode(serialize($stored_options));

        $ib = new InboundEmail();

        $ib->new_with_id    = true;
        $ib->id             = create_guid();

        $ib->name           = $config['name'];
        $ib->stored_options = $encoded_stored_options;
        $ib->is_personal    = true;
        $ib->created_by     = $config['user_id'];
        $ib->group_id       = $config['user_id'];
        $ib->save();

        //printf("(CREATE IB): ID=%s  NAME=%s\n",$ib->id,$ib->name);
        //printf("(CREATE OB): ID=%s  NAME=%s\n",$ob->id,$ob->name);
        return array($ib, $ob);
    }


    private static function deleteOutboundEmail($id, $name) {
        $sql = "DELETE FROM outbound_email where id='{$id}' AND name='{$name}'";
        $GLOBALS['db']->query($sql);
        //printf("(DELETE) SQL: %s\n",$sql);
    }


    private static function deleteInboundEmail($id, $name) {
        $sql = "DELETE FROM inbound_email where id='{$id}' AND name='{$name}'";
        $GLOBALS['db']->query($sql);
        //printf("(DELETE) SQL: %s\n",$sql);
    }


    private static function get_system_mail_config() {
        $q = "SELECT id FROM outbound_email WHERE type = 'system'";
        $r = $GLOBALS['db']->query($q);
        $a = $GLOBALS['db']->fetchByAssoc($r);
        if(empty($a)) {
            return null;
        }
        $oe = new OutboundEmail();
        $oe->retrieve($a['id']);
        return $oe;
    }

}