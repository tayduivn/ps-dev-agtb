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

/**
 * @coversDefaultClass \Sugarcrm\Sugarcrm\Dav\Cal\Handler\JobQueue\Export
 */

class ExportTest extends \PHPUnit_Framework_TestCase
{
    public function runProvider()
    {
        return array(
            array(
                'setJobToEnd' => false,
                'exportCount' => 1,
                'result' => 'success'
            ),
            array(
                'setJobToEnd' => true,
                'exportCount' => 0,
                'result' => 'cancelled'
            ),
        );
    }

    /**
     * @param bool $setJobToEndResult
     * @param int $exportCount
     * @param string $jobExpectedResult
     *
     * @covers \Sugarcrm\Sugarcrm\Dav\Cal\Handler\JobQueue\Export::run
     *
     * @dataProvider runProvider
     */
    public function testRun($setJobToEndResult, $exportCount, $jobExpectedResult)
    {
        $bean = $this->getSugarBeanMock();
        $handlerMock = $this->getHandlerMock();

        $calDavBean = $this->getMockBuilder('\CalDavEvent')
                           ->disableOriginalConstructor()
                           ->setMethods(array('getSynchronizationObject'))
                           ->getMock();

        $syncObject = $this->getMockBuilder('\CalDavSynchronization')
                           ->disableOriginalConstructor()
                           ->setMethods(array('setJobCounter'))
                           ->getMock();

        $export = $this->getExportHandlerMock($handlerMock, $bean);
        $handlerMock->expects($this->exactly($exportCount))->method('export')->willReturn(true);
        $handlerMock->expects($this->once())->method('getDavBean')->willReturn($calDavBean);
        $calDavBean->expects($this->exactly($exportCount))->method('getSynchronizationObject')->willReturn($syncObject);
        $syncObject->expects($this->exactly($exportCount))->method('setJobCounter');

        $export->expects($this->once())
               ->method('setJobToEnd')
               ->with($calDavBean)
               ->willReturn($setJobToEndResult);

        $result = $export->run();

        $this->assertEquals($jobExpectedResult, $result);
    }

    /**
     * @covers \Sugarcrm\Sugarcrm\Dav\Cal\Handler\JobQueue\Export::reschedule
     */
    public function testReschedule()
    {
        $exportMock = $this->getMockBuilder('Sugarcrm\Sugarcrm\Dav\Cal\Handler\JobQueue\Export')
                           ->disableOriginalConstructor()
                           ->setMethods(array('getManager'))
                           ->getMock();

        $managerMock = $this->getMockBuilder('Sugarcrm\Sugarcrm\JobQueue\Manager\Manager')
                            ->disableOriginalConstructor()
                            ->setMethods(array('calDavExport'))
                            ->getMock();

        $exportMock->expects($this->once())->method('getManager')->willReturn($managerMock);
        $managerMock->expects($this->once())->method('calDavExport')->with(array(1, 2), 'test', 3);

        TestReflection::setProtectedValue($exportMock, 'moduleName', 'test');
        TestReflection::setProtectedValue($exportMock, 'fetchedRow', array(1, 2));
        TestReflection::setProtectedValue($exportMock, 'saveCounter', 3);

        TestReflection::callProtectedMethod($exportMock, 'reschedule');
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
            ->setMethods(array('export', 'getDavBean'))
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
            ->setMethods(array('getAdapterFactory', 'getHandler', 'getBean', 'setJobToEnd'))
            ->getMock();

        $adapterFactoryMock = $this->getMockBuilder('\Sugarcrm\Sugarcrm\Dav\Cal\Adapter\Factory')
            ->setMethods(array('getAdapter'))
            ->getMock();

        if (!$checkAdapter) {
            $adapterFactoryMock->method('getAdapter')->willReturn(true);
        }

        $exportMock->method('getAdapterFactory')->willReturn($adapterFactoryMock);
        $exportMock->method('getHandler')->willReturn($handlerMock);
        $exportMock->method('getBean')->willReturn($bean);

        return $exportMock;
    }
}
