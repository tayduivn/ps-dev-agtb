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

namespace Sugarcrm\SugarcrmTests\JobQueue\Handler;

use Sugarcrm\Sugarcrm\JobQueue\Handler\MassUpdate;
use Sugarcrm\SugarcrmTestsUnit\TestReflection;

class MassUpdateTest extends \Sugar_PHPUnit_Framework_TestCase
{
    /**
     * @var \Account
     */
    protected $account;

    public function setUp()
    {
        \SugarTestHelper::setUp('current_user', array(true, 1));
        \SugarTestHelper::setUp('app_list_strings');
        $this->account = \SugarTestAccountUtilities::createAccount();
    }

    public function tearDown()
    {
        \SugarTestAccountUtilities::removeAllCreatedAccounts();
        \SugarTestHelper::tearDown();
    }

    /**
     * @expectedException \Exception
     */
    public function testNoAction()
    {
        new MassUpdate('invalidAction', $this->account->module_name, array($this->account->id));
    }

    /**
     * @expectedException \Exception
     */
    public function testNoRecords()
    {
        new MassUpdate('save', $this->account->module_name, array());
    }

    public function testDeleteAction()
    {
        $handler = new MassUpdate('delete', $this->account->module_name, array($this->account->id));
        $handler->run();

        $this->assertNull($this->account->retrieve());
    }

    public function testUpdateAction()
    {
        $expectedValue = 'test';
        $data = array('account_type' => $expectedValue);
        $handler = new MassUpdate('save', $this->account->module_name, array($this->account->id), $data);
        $handler->run();

        $this->account->retrieve();
        $this->assertEquals($expectedValue, $this->account->account_type);
    }

    public function testAddToProspectList()
    {
        $handler = new MassUpdate(
            'save',
            $this->account->module_name,
            array($this->account->id),
            array(),
            array('testListId')
        );
        $massUpdateMock = $this->getMock(
            'MassUpdate',
            array('add_prospects_to_prospect_list', 'remove_prospects_from_prospect_list')
        );
        $massUpdateMock->expects($this->once())->method('add_prospects_to_prospect_list');
        TestReflection::setProtectedValue($handler, 'massUpdate', $massUpdateMock);

        $handler->run();
    }

    public function testRemoveFromProspectList()
    {
        $handler = new MassUpdate(
            'delete',
            $this->account->module_name,
            array($this->account->id),
            array(),
            array('testListId')
        );
        $massUpdateMock = $this->getMock(
            'MassUpdate',
            array('add_prospects_to_prospect_list', 'remove_prospects_from_prospect_list')
        );
        $massUpdateMock->expects($this->once())->method('remove_prospects_from_prospect_list');
        TestReflection::setProtectedValue($handler, 'massUpdate', $massUpdateMock);

        $handler->run();
    }
}
