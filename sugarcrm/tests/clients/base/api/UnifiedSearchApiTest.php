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

require_once 'clients/base/api/UnifiedSearchApi.php';
require_once 'clients/base/api/ModuleApi.php';
require_once 'tests/SugarTestRestUtilities.php';
require_once 'tests/SugarTestACLUtilities.php';
/**
 * @group ApiTests
 */
class UnifiedSearchApiTest extends Sugar_PHPUnit_Framework_TestCase
{
    public $accounts;
    public $roles;
    public $unifiedSearchApi;
    public $moduleApi;
    public $serviceMock;

    public function setUp()
    {
        SugarTestHelper::setUp("current_user");
        SugarTestHelper::setUp('ACLStatic');
        SugarTestHelper::setUp('app_strings');
        SugarTestHelper::setUp('app_list_strings');

        // create a bunch of accounts
        for ($x=0; $x<10; $x++) {
            $acc = BeanFactory::newBean('Accounts');
            $acc->name = 'UnifiedSearchApiTest Account ' . create_guid();
            $acc->assigned_user_id = $GLOBALS['current_user']->id;
            $acc->save();
            $this->accounts[] = $acc;
        }
        // load up the unifiedSearchApi for good times ahead
        $this->unifiedSearchApi = new UnifiedSearchApi();
        $this->moduleApi = new ModuleApi();
        $this->serviceMock = SugarTestRestUtilities::getRestServiceMock();
    }

    public function tearDown()
    {
        $GLOBALS['current_user']->is_admin = 1;
        // delete the bunch of accounts crated
        foreach ($this->accounts AS $account) {
            $account->mark_deleted($account->id);
        }
        // unset unifiedSearchApi
        unset($this->unifiedSearchApi);
        unset($this->moduleApi);
        // clean up all roles created
        SugarTestACLUtilities::tearDown();
        SugarTestHelper::tearDown();
        parent::tearDown();
    }

    // test that when read only is set for every field you can still retrieve
    // @Bug 60225
    public function testReadOnlyFields()
    {
        // create role that is all fields read only
        $role = SugarTestACLUtilities::createRole('UNIFIEDSEARCHAPI - UNIT TEST ' . create_guid(), array('Accounts'), array('access', 'view', 'list', 'export'));

        // get all the accounts fields and set them readonly
        foreach ($this->accounts[0]->field_defs AS $fieldName => $params) {
            SugarTestACLUtilities::createField($role->id, "Accounts", $fieldName, 50);
        }

        SugarTestACLUtilities::setupUser($role);
        SugarTestHelper::clearACLCache();
        // test I can retreive accounts
        $args = array('module_list' => 'Accounts',);
        $list = $this->unifiedSearchApi->globalSearch($this->serviceMock, $args);
        $this->assertNotEmpty($list['records'], "Should have some accounts: " . print_r($list, true));
    }

    // if you have view only you shouldn't be able to create, but you should be able to retrieve records
    public function testViewOnly()
    {
        // create a role that is view only
        $role = SugarTestACLUtilities::createRole('UNIFIEDSEARCHAPI - UNIT TEST ' . create_guid(), array('Accounts', ), array('access', 'view', 'list', ));

        SugarTestACLUtilities::setupUser($role);
        SugarTestHelper::clearACLCache();

        // test I can retrieve accounts
        $args = array('module_list' => 'Accounts',);
        $list = $this->unifiedSearchApi->globalSearch($this->serviceMock, $args);
        $this->assertNotEmpty($list['records'], "Should have some accounts: " . print_r($list, true));
        // test I can't create
        $this->setExpectedException(
          'SugarApiExceptionNotAuthorized', 'You are not authorized to create Accounts. Contact your administrator if you need access.'
        );
        $result = $this->moduleApi->createRecord($this->serviceMock, array('module' => 'Accounts', 'name' => 'UnifiedSearchApi Create Denied - ' . create_guid()));
    }
}
