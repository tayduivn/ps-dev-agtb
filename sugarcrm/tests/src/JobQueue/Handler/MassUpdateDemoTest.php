<?php
/*
 * Your installation or use of this SugarCRM file is subject to the applicable
 * terms available at
 * http://support.sugarcrm.com/Resources/Master_Subscription_Agreements/.
 * If you do not agree to all of the applicable terms or do not have the
 * authority to bind the entity as an authorized representative, then do not
 * install or use this SugarCRM file.
 *
 * Copyright (C) SugarCRM Inc. All rights reserved.
 */

namespace Sugarcrm\SugarcrmTests\JobQueue\Handler;

use Sugarcrm\Sugarcrm\JobQueue\Handler\MassUpdateDemo;

class MassUpdateDemoTest extends \Sugar_PHPUnit_Framework_TestCase
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
        new MassUpdateDemo('invalidAction', $this->account->module_name, array($this->account->id));
    }

    /**
     * @expectedException \Exception
     */
    public function testNoRecords()
    {
        new MassUpdateDemo('save', $this->account->module_name, array());
    }

    /**
     * Should produce a delete bean task.
     */
    public function testDeleteAction()
    {
        $handler = new MassUpdateDemo('delete', $this->account->module_name, array($this->account->id));

        $managerMock = $this->getMock('Sugarcrm\Sugarcrm\JobQueue\Manager\Manager', array('deleteBeanDemo'));
        $managerMock
            ->expects($this->once())
            ->method('deleteBeanDemo')
            ->with($this->account->module_name, $this->account->id);

        $reflector = new \ReflectionClass($handler);
        $property = $reflector->getProperty('client');
        $property->setAccessible(true);
        $property->setValue($handler, $managerMock);

        $handler->run();
    }

    /**
     * Should produce a update bean task.
     */
    public function testUpdateAction()
    {
        $data = array('account_type' => 'test');
        $handler = new MassUpdateDemo('save', $this->account->module_name, array($this->account->id), $data);

        $managerMock = $this->getMock('Sugarcrm\Sugarcrm\JobQueue\Manager\Manager', array('updateBeanDemo'));
        $managerMock
            ->expects($this->once())
            ->method('updateBeanDemo')
            ->with($this->account->module_name, $this->account->id, $data);

        $reflector = new \ReflectionClass($handler);
        $property = $reflector->getProperty('client');
        $property->setAccessible(true);
        $property->setValue($handler, $managerMock);

        $handler->run();
    }
}
