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

require_once ("clients/base/api/ModuleApi.php");
require_once ("tests/SugarTestRestUtilities.php");

/**
 * @group ApiTests
 */
class Bug63015Test extends Sugar_PHPUnit_Framework_TestCase
{
    public $moduleApi;
    public $serviceMock;
    public $accountIds;

    public static function setUpBeforeClass()
    {
        SugarTestHelper::setUp("beanList");
        SugarTestHelper::setUp("beanFiles");
        SugarTestHelper::setUp("current_user");
    }

    public function setUp()
    {
        $this->moduleApi = new ModuleApi();
        $this->serviceMock = SugarTestRestUtilities::getRestServiceMock();
        $account = BeanFactory::newBean('Accounts');
        $account->name = "ModulaApiTest setUp Account";
        $account->assigned_user_id = $GLOBALS['current_user']->id;
        $account->save();
        $this->accountIds[] = $account->id;
    }

    public function tearDown()
    {
        // delete the bunch of accounts created
        $GLOBALS['db']->query("DELETE FROM accounts WHERE id in('".implode("','", $this->accountIds)."')");
        parent::tearDown();
    }

    public static function tearDownAfterClass()
    {
        SugarTestHelper::tearDown();
    }

    public function testCreateWithExistingId()
    {
        $id = $this->accountIds[0];
        // try to create a record with an existing id. this should throw an exception
        $this->setExpectedException('SugarApiExceptionInvalidParameter');
        $result = $this->moduleApi->createRecord($this->serviceMock, array('module' => 'Accounts','name' => 'Test Account1', 'assigned_user_id' => $GLOBALS['current_user']->id, 'id' => $id));
    }

    public function testCreateWithNewId() 
    {
        $id = create_guid();
        $this->accountIds[] = $id;
        // create a record
        $result = $this->moduleApi->createRecord($this->serviceMock, array('module' => 'Accounts','name' => 'Test Account2', 'assigned_user_id' => $GLOBALS['current_user']->id, 'id' => $id));
        // verify same id is returned
        $this->assertEquals($id, $result['id']);
    }
}
