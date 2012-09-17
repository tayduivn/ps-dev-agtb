<?php
//FILE SUGARCRM flav=pro ONLY
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
 *  (i) the "Powered by SugarCRM" logo and
 *  (ii) the SugarCRM copyright notice
 * in the same form as they appear in the distribution.  See full license for requirements.
 *Your Warranty, Limitations of liability and Indemnity are expressly stated in the License.  Please refer
 *to the License for the specific language governing these rights and limitations under the License.
 *Portions created by SugarCRM are Copyright (C) 2004 SugarCRM, Inc.; All Rights Reserved.
 ********************************************************************************/

require_once("modules/ModuleBuilder/parsers/parser.portalconfig.php");
require_once("modules/ACLRoles/ACLRole.php");

class PortalConfigParserTest extends Sugar_PHPUnit_Framework_TestCase
{
    var $requestVars = array(
        'appName' => 'testApp',
        'serverUrl' => 'testURL',
        'maxQueryResult' => '5',
        'fieldsToDisplay' => '5'
    );


    public function setUp()
    {
        //echo "Setup";
    }

    public function tearDown()
    {
        //echo "TearDown";
        if (isset($this->user->id)) {
            $GLOBALS['db']->query("DELETE FROM users WHERE id = '{$this->user->id}'");
            $GLOBALS['db']->query("DELETE FROM users_cstm WHERE id = '{$this->user->id}'");
        }
        //unset($_SESSION['ACL']);
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

        foreach ($this->requestVars as $varKey => $value) {
            $this->assertEquals(json_decode(html_entity_decode($retrievedSettings[$varKey])), $value);
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
