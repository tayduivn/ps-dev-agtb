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

/**
 * @coversDefaultClass \Sugarcrm\Sugarcrm\Dav\Cal\Handler\JobQueue\Export
 */

class ExportTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @covers \Sugarcrm\Sugarcrm\Dav\Cal\Handler\JobQueue\Export::run
     */
    public function testRun()
    {
        $bean = $this->getSugarBeanMock();
        $handlerMock = $this->getHandlerMock();

        $export = $this->getExportHandlerMock($handlerMock, $bean);
        $handlerMock->expects($this->once())->method('export');

        $result = $export->run();

        $this->assertEquals(\SchedulersJob::JOB_SUCCESS, $result);
    }

    /**
     * test method when bean is invalid and not extends from \SugarBean
     * @covers \Sugarcrm\Sugarcrm\Dav\Cal\Handler\JobQueue\Export::run
     * @expectedException \InvalidArgumentException
     */
    public function testInvalidBean()
    {
        $bean = $bean = new \stdClass();
        $handlerMock = $this->getHandlerMock();
        $export = $this->getExportHandlerMock($handlerMock, $bean);
        $export->run();
    }

    /**
     * test bean that hasn't adapater
     * @covers \Sugarcrm\Sugarcrm\Dav\Cal\Handler\JobQueue\Export::run
     * @expectedException \LogicException
     */
    public function testBeanWithoutAdapter()
    {
        $bean = $this->getSugarBeanMock();
        $handlerMock = $this->getHandlerMock();
        $export = $this->getExportHandlerMock($handlerMock, $bean, true);
        $export->run();
    }

    /**
     * return SugarBean object mock
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    protected function getSugarBeanMock()
    {
        return $this->getMockBuilder('\SugarBean')->disableOriginalConstructor()->getMock();
    }

    /**
     * return mock object for \Sugarcrm\Sugarcrm\Dav\Cal\Handler
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    protected function getHandlerMock()
    {
        return $this->getMockBuilder('\Sugarcrm\Sugarcrm\Dav\Cal\Handler')
            ->setMethods(array('export'))
            ->getMock();
    }

    /**
     * return handler for new job creation
     * @param $handlerMock
     * @param $bean
     * @param bool $checkAdapter if set to true we should real adapter check. Anyway return true like adapter exists
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    protected function getExportHandlerMock($handlerMock, $bean, $checkAdapter = false)
    {
        $exportMock = $this->getMockBuilder('Sugarcrm\Sugarcrm\Dav\Cal\Handler\JobQueue\Export')
            ->disableOriginalConstructor()
            ->setMethods(array('getAdapterFactory', 'getHandler', 'getBean', 'getCurrentUser', 'getAssignedUser'))
            ->getMock();

        $adapterFactoryMock = $this->getMockBuilder('\Sugarcrm\Sugarcrm\Dav\Cal\Adapter\Factory')
            ->setMethods(array('getAdapter'))
            ->getMock();

        if (!$checkAdapter) {
            $adapterFactoryMock->method('getAdapter')->willReturn(true);
        }

        $userMock = $this->getMockBuilder('\User')
            ->disableOriginalConstructor()
            ->getMock();

        $exportMock->method('getAdapterFactory')->willReturn($adapterFactoryMock);
        $exportMock->method('getHandler')->willReturn($handlerMock);
        $exportMock->method('getBean')->willReturn($bean);
        $exportMock->method('getCurrentUser')->willReturn($userMock);
        $exportMock->method('getAssignedUser')->willReturn($userMock);

        return $exportMock;
    }
}
