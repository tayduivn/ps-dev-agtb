<?php
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

require_once ("clients/base/api/ModuleApi.php");
require_once ("tests/SugarTestRestUtilities.php");

/**
 * @group ApiTests
 */
class ModuleApiTest extends Sugar_PHPUnit_Framework_TestCase
{
    public $accounts, $account_ids;
    public $roles;
    /**
     * @var ModuleApi
     */
    public $moduleApi;
    public $serviceMock;

    public static function setUpBeforeClass()
    {
        SugarTestHelper::setUp("beanList");
        SugarTestHelper::setUp("beanFiles");
        SugarTestHelper::setUp("current_user");
    }

    public function setUp()
    {
        // load up the unifiedSearchApi for good times ahead
        $this->moduleApi = new ModuleApi();
        $account = BeanFactory::newBean('Accounts');
        $account->name = "ModulaApiTest setUp Account";
        $account->assigned_user_id = $GLOBALS['current_user']->id;
        $account->save();
        $this->accounts[] = $account;
        $this->serviceMock = SugarTestRestUtilities::getRestServiceMock();
    }

    public static function tearDownAfterClass()
    {
        // delete the bunch of accounts crated
        $GLOBALS['db']->query("DELETE FROM accounts WHERE assigned_user_id = '{$GLOBALS['current_user']->id}'");

        SugarTestAccountUtilities::deleteM2MRelationships('contacts');
        SugarTestAccountUtilities::removeAllCreatedAccounts();
        SugarTestContactUtilities::removeAllCreatedContacts();

        SugarACL::resetACLs();
        SugarTestHelper::tearDown();
    }

    // test set favorite
    public function testSetFavorite()
    {
        $result = $this->moduleApi->setFavorite($this->serviceMock,
            array('module' => 'Accounts', 'record' => $this->accounts[0]->id));
        $this->assertTrue((bool)$result['my_favorite'], "Was not set to true");

        $this->assertArrayHasKey('following', $result, 'API response does not contain "following" key');
        $this->assertNotEmpty($result['following'], 'Bean was not auto-followed when marked as favorite');

        return $this->accounts[0];
    }

    /**
     * @depends testSetFavorite
     */
    public function testRemoveFavorite(Account $account)
    {
        $result = $this->moduleApi->unsetFavorite($this->serviceMock,
            array('module' => 'Accounts', 'record' => $account->id)
        );

        $this->assertArrayHasKey('my_favorite', $result, 'API response does not contain "my_favorite" key');
        $this->assertEmpty($result['my_favorite'], 'Bean was not removed from favorites');

        $this->assertArrayHasKey('following', $result, 'API response does not contain "following" key');
        $this->assertNotEmpty($result['following'], 'Bean was auto-unfollowed when removed from favorites');
    }
    // test set favorite of deleted record
    public function testSetFavoriteDeleted()
    {
        $this->accounts[0]->mark_deleted($this->accounts[0]->id);
        $this->setExpectedException('SugarApiExceptionNotFound',
            "Could not find record: {$this->accounts[0]->id} in module: Accounts");
        $result = $this->moduleApi->setFavorite($this->serviceMock,
            array('module' => 'Accounts', 'record' => $this->accounts[0]->id));
    }
    // test remove favorite of deleted record
    public function testRemoveFavoriteDeleted()
    {
        $result = $this->moduleApi->setFavorite($this->serviceMock,
            array('module' => 'Accounts', 'record' => $this->accounts[0]->id));
        $this->assertTrue((bool)$result['my_favorite'], "Was not set to true");

        $this->accounts[0]->deleted = 1;
        $this->accounts[0]->save();
        $this->setExpectedException('SugarApiExceptionNotFound',
            "Could not find record: {$this->accounts[0]->id} in module: Accounts");

        $result = $this->moduleApi->setFavorite($this->serviceMock,
            array('module' => 'Accounts', 'record' => $this->accounts[0]->id));
    }
    // test set my_favorite on bean
    public function testSetFavoriteOnBean()
    {
        $result = $this->moduleApi->updateRecord($this->serviceMock,
            array('module' => 'Accounts', 'record' => $this->accounts[0]->id, "my_favorite" => true));
        $this->assertTrue((bool)$result['my_favorite'], "Was not set to true");
    }
    // test remove my_favorite on bean
    public function testRemoveFavoriteOnBean()
    {
        $result = $this->moduleApi->updateRecord($this->serviceMock,
            array('module' => 'Accounts', 'record' => $this->accounts[0]->id, "my_favorite" => true));
        $this->assertTrue((bool)$result['my_favorite'], "Was not set to true");

        $result = $this->moduleApi->updateRecord($this->serviceMock,
            array('module' => 'Accounts', 'record' => $this->accounts[0]->id,
                "my_favorite" => false));
        $this->assertFalse((bool)$result['my_favorite'], "Was not set to False");
    }

    public function testCreate()
    {
        $result = $this->moduleApi->createRecord($this->serviceMock, array('module' => 'Accounts', 'name' => 'Test Account', 'assigned_user_id' => $GLOBALS['current_user']->id));
        $this->assertNotEmpty($result);
        $this->assertArrayHasKey("id", $result);
        $this->assertEquals("Test Account", $result['name']);

        $account = BeanFactory::newBean('Accounts');
        $account->retrieve($result['id']);
        $this->assertAttributeNotEmpty('id',$account);
        $this->assertEquals("Test Account", $account->name);
    }

    public function testprocessAfterCreateOperations_afterSaveOperationSpecified_copiesRelationships()
    {
        $accountBean = SugarTestAccountUtilities::createAccount();
        $contactBean = SugarTestContactUtilities::createContact();

        $accountBean->load_relationship('contacts');
        $accountBean->contacts->add($contactBean);

        $newAccountBean = SugarTestAccountUtilities::createAccount();

        $GLOBALS['dictionary']['Account']['after_create'] = array(
            'copy_rel_from' => array(
                'contacts',
            )
        );

        $moduleApi = new ModuleApiTestMock();
        $moduleApi->processAfterCreateOperationsMock(
            array(
                 'module' => 'Accounts',
                 'after_create' => array(
                     'copy_rel_from' => $accountBean->id
                 )
            ),
            $newAccountBean
        );

        unset($GLOBALS['dictionary']['Account']['after_create']);

        $newAccountBean->load_relationship('contacts');
        $newAccountBean->contacts->getBeans();

        $this->assertEquals(1, count($newAccountBean->contacts->beans));
    }

    public function testprocessAfterCreateOperations_copyRelFromVarDefNotSpecified_doesNotCopyRelationships()
    {
        $accountBean = SugarTestAccountUtilities::createAccount();
        $contactBean = SugarTestContactUtilities::createContact();

        $accountBean->load_relationship('contacts');
        $accountBean->contacts->add($contactBean);

        $newAccountBean = SugarTestAccountUtilities::createAccount();

        $moduleApi = new ModuleApiTestMock();
        $moduleApi->processAfterCreateOperationsMock(
            array(
                 'module' => 'Accounts',
                 'after_create' => array(
                     'copy_rel_from' => $accountBean->id
                 )
            ),
            $newAccountBean
        );

        $newAccountBean->load_relationship('contacts');
        $newAccountBean->contacts->getBeans();

        $this->assertEquals(0, count($newAccountBean->contacts->beans));
    }

    public function testprocessAfterCreateOperations_copyRelFromUrlParameterNotSpecified_doesNotCopyRelationships()
    {
        $accountBean = SugarTestAccountUtilities::createAccount();
        $contactBean = SugarTestContactUtilities::createContact();

        $accountBean->load_relationship('contacts');
        $accountBean->contacts->add($contactBean);

        $newAccountBean = SugarTestAccountUtilities::createAccount();

        $GLOBALS['dictionary']['Account']['after_create'] = array(
            'copy_rel_from' => array(
                'contacts',
            )
        );

        $moduleApi = new ModuleApiTestMock();
        $moduleApi->processAfterCreateOperationsMock(
            array(
                 'module' => 'Accounts',
            ),
            $newAccountBean
        );

        unset($GLOBALS['dictionary']['Account']['after_create']);

        $newAccountBean->load_relationship('contacts');
        $newAccountBean->contacts->getBeans();

        $this->assertEquals(0, count($newAccountBean->contacts->beans));
    }

    public function testUpdate()
    {
        $result = $this->moduleApi->createRecord($this->serviceMock, array('module' => 'Accounts', 'name' => 'Test Account', 'assigned_user_id' => $GLOBALS['current_user']->id));
        $this->assertNotEmpty($result);
        $this->assertArrayHasKey("id", $result);
        $id = $result['id'];

        $result = $this->moduleApi->updateRecord($this->serviceMock,
                array('module' => 'Accounts', 'record' => $id, 'name' => 'Changed Account'));
        $this->assertArrayHasKey("id", $result);
        $this->assertEquals($id, $result['id']);

        $account = BeanFactory::newBean('Accounts');
        $account->retrieve($result['id']);
        $this->assertAttributeNotEmpty('id',$account);
        $this->assertEquals("Changed Account", $account->name);
    }

    public function testUpdateNonConflict()
    {
        $result = $this->moduleApi->createRecord($this->serviceMock, array('module' => 'Accounts', 'name' => 'Test Account',
            'assigned_user_id' => $GLOBALS['current_user']->id,
        ));
        $this->assertNotEmpty($result);
        $this->assertArrayHasKey("id", $result);
        $id = $result['id'];
        $timedate = TimeDate::getInstance();
        $dm = $timedate->fromIso($result['date_modified']);

        $result = $this->moduleApi->updateRecord($this->serviceMock,
                array('module' => 'Accounts', 'record' => $id, 'name' => 'Changed Account',
                        '_headers' => array('X_TIMESTAMP' => $timedate->asIso($dm)),
                ));
        $this->assertArrayHasKey("id", $result);
        $this->assertEquals($id, $result['id']);
    }

    /**
     * @expectedException SugarApiExceptionEditConflict
     */
    public function testUpdateConflict()
    {
        $result = $this->moduleApi->createRecord($this->serviceMock, array('module' => 'Accounts', 'name' => 'Test Account',
                'assigned_user_id' => $GLOBALS['current_user']->id,
        ));
        $this->assertNotEmpty($result);
        $this->assertArrayHasKey("id", $result);
        $id = $result['id'];
        $timedate = TimeDate::getInstance();
        // change modified data to not match the record
        $dm = $timedate->fromIso($result['date_modified'])->get("-1 minute");

        try {
            $result = $this->moduleApi->updateRecord($this->serviceMock,
                    array('module' => 'Accounts', 'record' => $id, 'name' => 'Changed Account',
                            '_headers' => array('X_TIMESTAMP' => $timedate->asIso($dm)),
                    ));
        } catch(SugarApiExceptionEditConflict $e) {
            $this->assertNotEmpty($e->extraData);
            $this->arrayHasKey("record", $e->extraData);
            $this->assertEquals('Test Account', $e->extraData['record']['name']);
            throw $e;
        }
    }

    public function testViewNoneCreate()
    {
        // setup ACL
        $rejectacl = $this->getMock('SugarACLStatic');
        $rejectacl->expects($this->any())->method('checkAccess')->will($this->returnCallback(function($module, $view, $context) {
                if($module == 'Accounts' && $view == 'view') {
                    return false;
                }
                return true;
            }
        ));
        SugarACL::setACL('Accounts', array($rejectacl));
        // create a record
        $result = $this->moduleApi->createRecord($this->serviceMock, array('module' => 'Accounts', 'name' => 'Test Account', 'assigned_user_id' => $GLOBALS['current_user']->id));
        // verify only id returns
        $this->assertNotEmpty($result);
        $this->assertArrayHasKey("id", $result);
        $this->assertArrayNotHasKey("name", $result);
    }
}

class ModuleApiTestMock extends ModuleApi
{
    public function processAfterCreateOperationsMock($args, SugarBean $bean)
    {
        $this->processAfterCreateOperations($args, $bean);
    }
}
