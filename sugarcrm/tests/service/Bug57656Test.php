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
 * by SugarCRM are Copyright (C) 2004-2011 SugarCRM, Inc.; All Rights Reserved.
 ********************************************************************************/

require_once('tests/service/SOAPTestCase.php');
require_once('modules/MySettings/TabController.php');
/**
 * @ticket 57656
 */
class Bug57656Test extends SOAPTestCase
{
    public function setUp()
    {
        $this->_soapURL = $GLOBALS['sugar_config']['site_url'].'/soap.php';

        parent::setUp();
        $this->tabs = new TabController();
        $tabs = $this->orig_tabs = $this->tabs->get_system_tabs();
        if(in_array("Bugs", $tabs)) {
            unset($tabs[array_search("Bugs", $tabs)]);
        }
        $this->tabs->set_system_tabs($tabs);
    }

    public function tearDown()
    {
        if(!empty($this->bugid)) {
            $GLOBALS['db']->query("DELETE FROM bugs WHERE id='{$this->bugid}'");
        }
        $this->tabs->set_system_tabs($this->orig_tabs);
    }

    public function soapClients()
    {
        return array(
            array($GLOBALS['sugar_config']['site_url'].'/soap.php'),
            array($GLOBALS['sugar_config']['site_url'].'/service/v3_1/soap.php')
        );
    }

    /**
     * Test creates new bug report
     * @dataProvider soapClients
     * @group 57656
     */
    public function testCreateBug($url)
    {
        $this->_soapClient = new nusoapclient($url,false,false,false,false,false,600,600);
        $this->_login();
        $params = array(
        array("name" => "name", "value" => "TEST"),
        array("name" => "parent_id", "value" => "5a770071-66ca-6127-5a1a-4cb3a2c46e40"),
        array("name" => "parent_type", "value" => "Accounts"),
        array("name" => "from_addr", "value" => "test@test.com"),
        array("name" => "to_addrs", "value" => "test@test.com"),
        );
        $res = $this->_soapClient->call('set_entry', array($this->_sessionId, 'Bugs', $params));
        $this->assertNotEquals("-1", $res['id'], "Bad bug ID");

        $b = new Bug();
        $b->retrieve($res['id']);
        $this->assertNotEmpty($b->id);

        $this->bugid = $b->id;
    }
}
