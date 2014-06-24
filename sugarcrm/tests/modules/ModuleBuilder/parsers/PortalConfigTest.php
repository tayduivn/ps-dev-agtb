<?php
//FILE SUGARCRM flav=ent ONLY

/*
 * Your installation or use of this SugarCRM file is subject to the applicable
 * terms available at
 * http://support.sugarcrm.com/06_Customer_Center/10_Master_Subscription_Agreements/.
 * If you do not agree to all of the applicable terms or do not have the
 * authority to bind the entity as an authorized representative, then do not
 * install or use this SugarCRM file.
 *
 * Copyright (C) SugarCRM Inc. All rights reserved.
 */

require_once("modules/ModuleBuilder/parsers/parser.portalconfig.php");
require_once("modules/ACLRoles/ACLRole.php");

class PortalConfigParserTest extends Sugar_PHPUnit_Framework_TestCase
{
    var $requestVars = array(
        'appName' => 'testApp',
        'serverUrl' => 'testURL',
        'maxQueryResult' => '5'
    );


    public function setUp()
    {
        global $app_list_strings;
        $app_list_strings = return_app_list_strings_language($GLOBALS['current_language']);
        SugarTestHelper::setUp('mod_strings', array('ModuleBuilder'));
    }

    public function tearDown()
    {
        if (isset($this->user->id)) {
            $GLOBALS['db']->query("DELETE FROM users WHERE id = '{$this->user->id}'");
            if ($GLOBALS['db']->tableExists('users_cstm')) {
                $GLOBALS['db']->query("DELETE FROM users_cstm WHERE id_c = '{$this->user->id}'");
            }
        }
        SugarTestHelper::tearDown();
    }

    public function test_PortalConfigParserHandleSaveConfig()
    {

        $retrievedSettings = array();
        foreach ($this->requestVars as $varKey => $value) {
            $_REQUEST[$varKey] = $value;
        }
        $parser = new ParserModifyPortalConfig();
        $parser->handleSave();
        $result = $GLOBALS['db']->query("SELECT * FROM config WHERE category = 'portal'");

        while ($row = $GLOBALS['db']->fetchByAssoc($result)) {
            $retrievedSettings[$row['name']] = $row['value'];
        }

        // Add additional assertions for oddball failures on CI
        foreach ($this->requestVars as $varKey => $value) {
            // Grab our value
            $test = $retrievedSettings[$varKey];
            
            // First assertion
            $this->assertNotEmpty($test, "DB result for key $varKey should not be empty. Retrieved: ".print_r($retrievedSettings, true));
            
            //  Decode step one
            $he = html_entity_decode($test);
            $this->assertNotEmpty($he, "HTML Entity Decoded value for key $varKey should not be empty: start value - $test");
            
            // Decode step two
            $jd = json_decode($he);
            $this->assertNotEmpty($jd, "JSON Decoded value for $varKey should not be empty: start value - $test, html_entity_decode value - $he");
            
            // Actual assertion
            $this->assertEquals($jd, $value, "JSON Decoded value for $varKey should be equal to $value, actual value is $jd: start value - $test, html_entity_decode value - $he");
        }


    }

    public function test_PortalConfigCreateUser()
    {

        $parser = new ParserModifyPortalConfig();
        $this->user = $parser->getPortalUser();
        $this->assertNotEquals($this->user->id, '');
        $oUserId = $this->user->id;
        $this->user = $parser->getPortalUser();
        $this->assertEquals($this->user->id, $oUserId);
    }

    public function test_RemoveOAuthForPortalUser()
    {
        $parser = new ParserModifyPortalConfig();
        $this->user = $parser->getPortalUser();
        $result = $GLOBALS['db']->getOne("SELECT name FROM oauth_consumer WHERE c_key='support_portal'");
        $this->assertNotNull($result, "getPortalUser should create an OAuth consumer");
        $parser->removeOAuthForPortalUser();
        $result = $GLOBALS['db']->getOne("SELECT name FROM oauth_consumer WHERE c_key='support_portal'");
        $this->assertFalse($result, "removeOAuthForPortalUser should remove OAuth consumer");
    }

    public function test_PortalConfigCreateRole()
    {
        $parser = new ParserModifyPortalConfig();

        $this->role = $parser->getPortalACLRole();
        $this->assertNotEquals($this->role->id, '');

        $oRoleId = $this->role->id;

        $this->role = $parser->getPortalACLRole();

        $this->assertEquals($this->role->id, $oRoleId);
    }
}
