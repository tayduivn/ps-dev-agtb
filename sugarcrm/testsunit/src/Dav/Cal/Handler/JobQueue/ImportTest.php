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
     * @var string
     */
    private $testUserId;

    /**
     * setup default values
     */
    public function setUp()
    {
        $this->testUserId = 123;
        parent::setUp();
    }

    public function runProvider()
    {
        return array(
            array(
                'setJobToEnd' => false,
                'importCount' => 1,
                'result' => 'success'
            ),
            array(
                'setJobToEnd' => true,
                'importCount' => 0,
                'result' => 'cancelled'
            ),
        );
    }

    /**
     * @param bool $setJobToEndResult
     * @param int $importCount
     * @param string $jobExpectedResult
     *
     * @covers       \Sugarcrm\Sugarcrm\Dav\Cal\Handler\JobQueue\Import::run
     *
     * @dataProvider runProvider
     */
    public function testRun($setJobToEndResult, $importCount, $jobExpectedResult)
    {
        $bean = $this->getCalDavBeanMock($this->getSugarBeanMock());
        $handlerMock = $this->getHandlerMock();
        $import = $this->getImportHandlerMock($handlerMock, $bean);

        $syncObject = $this->getMockBuilder('\CalDavSynchronization')
                           ->disableOriginalConstructor()
                           ->setMethods(array('setJobCounter'))
                           ->getMock();

        $handlerMock->expects($this->exactly($importCount))->method('import')->willReturn(true);
        $import->expects($this->once())
               ->method('setJobToEnd')
               ->with($bean)
               ->willReturn($setJobToEndResult);

        $bean->expects($this->exactly($importCount))->method('getSynchronizationObject')->willReturn($syncObject);
        $syncObject->expects($this->exactly($importCount))->method('setJobCounter');

        $result = $import->run();
        $this->assertEquals($jobExpectedResult, $result);
    }

    /**
     * @covers \Sugarcrm\Sugarcrm\Dav\Cal\Handler\JobQueue\Import::reschedule
     */
    public function testReschedule()
    {
        $importMock = $this->getMockBuilder('Sugarcrm\Sugarcrm\Dav\Cal\Handler\JobQueue\Import')
                           ->disableOriginalConstructor()
                           ->setMethods(array('getManager'))
                           ->getMock();

        $managerMock = $this->getMockBuilder('Sugarcrm\Sugarcrm\JobQueue\Manager\Manager')
                            ->disableOriginalConstructor()
                            ->setMethods(array('calDavImport'))
                            ->getMock();

        $importMock->expects($this->once())->method('getManager')->willReturn($managerMock);
        $managerMock->expects($this->once())->method('calDavImport')->with(array(1, 2), 'test', 3);

        TestReflection::setProtectedValue($importMock, 'moduleName', 'test');
        TestReflection::setProtectedValue($importMock, 'fetchedRow', array(1, 2));
        TestReflection::setProtectedValue($importMock, 'saveCounter', 3);

        TestReflection::callProtectedMethod($importMock, 'reschedule');
    }

    /**
     * test method when bean is invalid and not extends from \CalDavEvent
     * @covers \Sugarcrm\Sugarcrm\Dav\Cal\Handler\JobQueue\Import::run
     * @expectedException \InvalidArgumentException
     */
    public function testInvalidBean()
    {
        $bean = new \stdClass();
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
                     ->setMethods(array('getBean', 'getSynchronizationObject'))
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
                           ->disableOriginalConstructor()
                           ->setMethods(array('getAdapterFactory', 'getHandler', 'getBean', 'setJobToEnd'))
                           ->getMock();

        $adapterFactoryMock = $this->getMockBuilder('\Sugarcrm\Sugarcrm\Dav\Cal\Adapter\Factory')
                                   ->setMethods(array('getAdapter'))
                                   ->getMock();
        if (!$checkAdapter) {
            $adapterFactoryMock->method('getAdapter')->willReturn(true);
        }

        $importMock->method('getAdapterFactory')->willReturn($adapterFactoryMock);
        $importMock->method('getHandler')->willReturn($handlerMock);
        $importMock->method('getBean')->willReturn($bean);

        return $importMock;
    }
}
