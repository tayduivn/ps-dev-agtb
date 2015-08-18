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

namespace Sugarcrm\SugarcrmTestsUnit\Dav\Cal\Handler\JobQueue;

use Sugarcrm\SugarcrmTestsUnit\TestReflection;
use Sugarcrm\Sugarcrm\Dav\Cal\Handler\JobQueue\Import as JQImport;

/**
 * @coversDefaultClass \Sugarcrm\Sugarcrm\Dav\Cal\Handler\JobQueue\Import
 */

class ImportTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @covers \Sugarcrm\Sugarcrm\Dav\Cal\Handler\JobQueue\Import::run
     */
    public function testRun()
    {
        $bean = $this->getCalDavBeanMock($this->getSugarBeanMock());
        $handlerMock = $this->getHandlerMock();
        $import = $this->getImportHandlerMock($handlerMock, $bean);

        $handlerMock->expects($this->once())->method('import');
        $result = $import->run();
        $this->assertEquals(\SchedulersJob::JOB_SUCCESS, $result);
    }

    /**
     * test method when bean is invalid and not extends from \CalDavEvent
     * @covers \Sugarcrm\Sugarcrm\Dav\Cal\Handler\JobQueue\Import::run
     * @expectedException \InvalidArgumentException
     */
    public function testInvalidBean()
    {
        $bean = $bean = new \stdClass();
        $handlerMock = $this->getHandlerMock();
        $import = $this->getImportHandlerMock($handlerMock, $bean);
        $import->run();
    }

    /**
     * Test CalDavbean that hasn't adapater
     * @covers \Sugarcrm\Sugarcrm\Dav\Cal\Handler\JobQueue\Import::run
     * @expectedException \LogicException
     */
    public function testBeanWithoutAdapter()
    {
        $bean = $this->getSugarBeanMock(new \stdClass());
        $handlerMock = $this->getHandlerMock();
        $export = $this->getImportHandlerMock($handlerMock, $bean, true);
        $export->run();
    }


    /**
     * Test correct adapter returning
     * @covers Sugarcrm\Sugarcrm\Dav\Cal\Handler\JobQueue\Import::getAdapterFactory
     */
    public function testGetAdapterFactory()
    {
        $sugarBean = $this->getSugarBeanMock();
        $importObject = new JQImport($this->getCalDavBeanMock($sugarBean));
        $adapter = TestReflection::callProtectedMethod($importObject, 'getAdapterFactory');
        $this->assertInstanceOf('Sugarcrm\Sugarcrm\Dav\Cal\Adapter\Factory', $adapter);
    }

    /**
     * Test correct handler returning
     * @covers Sugarcrm\Sugarcrm\Dav\Cal\Handler\JobQueue\Import::getHandler
     */
    public function testGetHandler()
    {
        $sugarBean = $this->getSugarBeanMock();
        $importObject = new JQImport($this->getCalDavBeanMock($sugarBean));
        $handler = TestReflection::callProtectedMethod($importObject, 'getHandler');
        $this->assertInstanceOf('Sugarcrm\Sugarcrm\Dav\Cal\Handler', $handler);
    }

    /**
    * return mock object for \Sugarcrm\Sugarcrm\Dav\Cal\Handler
    * @return \PHPUnit_Framework_MockObject_MockObject
    */
    protected function getHandlerMock()
    {
        return $this->getMockBuilder('\Sugarcrm\Sugarcrm\Dav\Cal\Handler')
            ->setMethods(array('import'))
            ->getMock();
    }

    /**
     * return mock for CalDavEvent object
     * @param object $getBeanResult object that should return getBean method
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    public function getCalDavBeanMock($getBeanResult)
    {
        $bean = $this->getMockBuilder('\CalDavEvent')
            ->disableOriginalConstructor()
            ->setMethods(array('getBean'))
            ->getMock();

        $bean->method('getBean')->willReturn($getBeanResult);
        return $bean;
    }

    /**
     * return mock for SugarBean object
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    protected function getSugarBeanMock()
    {
        return $this->getMockBuilder('\SugarBean')
            ->disableOriginalConstructor()
            ->getMock();
    }

    /**
     * retun handler for new job creation
     * @param $handlerMock
     * @param $bean
     * @param bool $checkAdapter if set to true we should real adapter check. Anyway return true like adapter exists
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    protected function getImportHandlerMock($handlerMock, $bean, $checkAdapter = false)
    {
        $importMock = $this->getMockBuilder('Sugarcrm\Sugarcrm\Dav\Cal\Handler\JobQueue\Import')
            ->setConstructorArgs(array($bean))
            ->setMethods(array('getAdapterFactory', 'getHandler'))
            ->getMock();

        $adapterFactoryMock = $this->getMockBuilder('\Sugarcrm\Sugarcrm\Dav\Cal\Adapter\Factory')
            ->setMethods(array('getAdapter'))
            ->getMock();
        if (!$checkAdapter) {
            $adapterFactoryMock->method('getAdapter')->willReturn(true);
        }

        $importMock->method('getAdapterFactory')->willReturn($adapterFactoryMock);
        $importMock->method('getHandler')->willReturn($handlerMock);

        return $importMock;
    }
}
