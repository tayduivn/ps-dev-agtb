<?php

/**
 * By installing or using this file, you are confirming on behalf of the entity
 * subscribed to the SugarCRM Inc. product ('Company') that Company is bound by
 * the SugarCRM Inc. Master Subscription Agreement ('MSA'), which is viewable at:
 * http://www.sugarcrm.com/master-subscription-agreement
 *
 * If Company is not bound by the MSA, then by installing or using this file
 * you are agreeing unconditionally that Company will be bound by the MSA and
 * certifying that you have authority to bind Company accordingly.
 *
 * Copyright (C) 2004-2013 SugarCRM Inc. All rights reserved.
 */
require_once 'modules/Accounts/clients/base/api/AccountsRelateApi.php';
require_once 'tests/SugarTestRestUtilities.php';

/**
 * @group ApiTests
 */
class PAT189Test extends Sugar_PHPUnit_Framework_TestCase
{
    /** @var AccountsRelateApi */
    private $api;
    private $serviceMock;

    /** @var Account */
    private $account1;

    /** @var Account */
    private $account2;

    /** @var Contact */
    private $contact;

    /** @var Call */
    private $call;

    public static function setUpBeforeClass()
    {
        SugarTestHelper::setUp('beanList');
        SugarTestHelper::setUp('beanFiles');
        SugarTestHelper::setUp('current_user');
    }

    protected function setUp()
    {
        parent::setUp();

        $this->api = new AccountsRelateApi();
        $this->serviceMock = SugarTestRestUtilities::getRestServiceMock();

        $this->account1 = SugarTestAccountUtilities::createAccount();
        $this->account2 = SugarTestAccountUtilities::createAccount();

        $this->contact = SugarTestContactUtilities::createContact();
        $this->contact->load_relationship('accounts');
        $this->contact->accounts->add($this->account1);

        $this->call = SugarTestCallUtilities::createCall();
        $this->call->parent_type = 'Contacts';
        $this->call->parent_id = $this->contact->id;
        $this->call->save();
    }

    protected function tearDown()
    {
        SugarTestCallUtilities::removeAllCreatedCalls();
        SugarTestContactUtilities::removeAllCreatedContacts();
        SugarTestAccountUtilities::removeAllCreatedAccounts();

        parent::tearDown();
    }

    public static function tearDownAfterClass()
    {
        SugarTestHelper::tearDown();
    }

    public function testRelatedCallIsSelected()
    {
        $calls = $this->getCalls($this->account1);
        $this->assertContains($this->call->id, $calls);
    }

    public function testUnrelatedCallIsNotSelected()
    {
        $calls = $this->getCalls($this->account2);
        $this->assertNotContains($this->call->id, $calls);
    }

    private function getCalls(Account $account)
    {
        $result = $this->api->filterRelated(
            $this->serviceMock,
            array(
                'module' => 'Accounts',
                'record' => $account->id,
                'link_name' => 'calls',
                'include_child_items' => true,
            )
        );

        $this->assertArrayHasKey('records', $result, 'Filter result doesn\'t have "records" key');
        $this->assertInternalType('array', $result['records'], 'Filter result "records" is not an array');

        $calls = array();
        foreach ($result['records'] as $record) {
            $this->assertArrayHasKey('id', $record, 'Record doesn\'t have "id" key');
            $calls[] = $record['id'];
        }

        return $calls;
    }
}
