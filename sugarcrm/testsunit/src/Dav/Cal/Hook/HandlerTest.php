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

namespace Sugarcrm\SugarcrmTestsUnit\Dav\Cal\Hook;

use Sugarcrm\SugarcrmTestsUnit\TestReflection;
use Sugarcrm\Sugarcrm\Dav\Cal\Hook\Handler as LogicHookHandler;

require_once 'tests/SugarTestCalDavUtilites.php';

/**
 * @coversDefaultClass \Sugarcrm\Sugarcrm\Dav\Cal\Hook\Handler
 */

class HandlerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @dataProvider providerImportExportTest
     * @covers \Sugarcrm\Sugarcrm\Dav\Cal\Hook\Handler::run
     * @param \SugarBean|\CalDavEvent $bean
     * @param string $managerHandlerName name of handler that should be processed during hook's runnig process
     * @param int $managerHandlerNameCount Expected count of calls
     * @param string $parentModuleName
     */

    public function testRunHook($bean, $managerHandlerName, $managerHandlerNameCount, $parentModuleName)
    {
        $managerMock = $this->getManagerMock(array($managerHandlerName));
        $hookHandlerMock = $this->getHandlerMock($managerMock);

        if ($parentModuleName) {
            $bean->parent_type = $parentModuleName;
        }
        $managerMock->expects($this->exactly($managerHandlerNameCount))->method($managerHandlerName);

        $hookHandlerMock->run($bean, null, null);
    }

    /**
     * @covers \Sugarcrm\Sugarcrm\Dav\Cal\Hook\Handler::getManager
     */
    public function testGetManager()
    {
        $handlerObject = new LogicHookHandler();
        $manager = TestReflection::callProtectedMethod($handlerObject, 'getManager');
        $this->assertInstanceOf('\Sugarcrm\Sugarcrm\JobQueue\Manager\Manager', $manager);
    }

    /**
     * @covers \Sugarcrm\Sugarcrm\Dav\Cal\Hook\Handler::getAdapterFactory
     */
    public function testGetAdapterFactory()
    {
        $handlerObject = new LogicHookHandler();
        $manager = TestReflection::callProtectedMethod($handlerObject, 'getAdapterFactory');
        $this->assertInstanceOf('\Sugarcrm\Sugarcrm\Dav\Cal\Adapter\Factory', $manager);
    }

    /**
     * @param $managerMock
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    protected function getHandlerMock($managerMock)
    {
        $handler = $this->getMockBuilder('Sugarcrm\Sugarcrm\Dav\Cal\Hook\Handler')
            ->disableOriginalConstructor()
            ->setMethods(array('getManager','getAdapterFactory', 'getCurrentUserId'))
            ->getMock();

        $adapterFactoryMock = $this->getMockBuilder('\Sugarcrm\Sugarcrm\Dav\Cal\Adapter\Factory')
            ->setMethods(array('getAdapter'))
            ->getMock();
        $adapterFactoryMock->method('getAdapter')->willReturn(true);

        $handler->method('getManager')->willReturn($managerMock);
        $handler->method('getAdapterFactory')->willReturn($adapterFactoryMock);
        $handler->method('getCurrentUserId')->willReturn(3);
        return $handler;
    }

    /**
     * Get data for testImportBean function
     * @return array
     */

    public function providerImportExportTest()
    {
        return array(
            array($this->getBeanMock('SugarBean'), 'calDavExport', 1 ,''),
            array($this->getBeanMock('CalDavEvent'), 'calDavImport', 1, ''),
            array($this->getBeanMock('CalDavEvent'), 'calDavImport', 0, 'CalDavEvents'),
        );
    }

    /**
     * @param string $beanClass
     * @return \PHPUnit_Framework_MockObject_MockObject
     */

    protected function getBeanMock($beanClass)
    {
        $beanMock = $this->getMockBuilder($beanClass)
            ->disableOriginalConstructor()
            ->setMethods(array('getBean'))
            ->getMock();
        $relatedBean = new \stdClass();
        $relatedBean->module_name = '';
        $beanMock->method('getBean')->willReturn($relatedBean);
        return $beanMock;
    }

    /**
     * Get Mock object for hook handler
     * @param string[] $managerMethods array of methods that should be overrided in mock
     * @return mixed
     */
    protected function getManagerMock($managerMethods)
    {
        $managerMock = $this->getMockBuilder('Sugarcrm\Sugarcrm\JobQueue\Manager\Manager')
            ->disableOriginalConstructor()
            ->setMethods($managerMethods)
            ->getMock();

        return $managerMock;
    }
}
