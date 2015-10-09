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
 * Class BaseTest
 * @package Sugarcrm\SugarcrmTestsUnit\Dav\Cal\Handler\JobQueue
 *
 * @coversDefaultClass \Sugarcrm\Sugarcrm\Dav\Cal\Handler\JobQueue\Base
 */
class BaseTest extends \PHPUnit_Framework_TestCase
{
    public function setJobToEndProvider()
    {
        return array(
            array(
                'saveCounter' => 3,
                'actualJobCounter' => 1,
                'managerCalls' => 1,
                'managerMethod' => 'reschedule',
                'return' => true,
            ),
            array(
                'saveCounter' => 3,
                'actualJobCounter' => 1,
                'managerCalls' => 1,
                'managerMethod' => 'reschedule',
                'return' => true,
            ),
            array(
                'saveCounter' => 3,
                'actualJobCounter' => 2,
                'managerCalls' => 0,
                'managerMethod' => 'reschedule',
                'return' => false,
            )
        );
    }

    /**
     * Test correct adapter returning
     * @covers Sugarcrm\Sugarcrm\Dav\Cal\Handler\JobQueue\Base::getAdapterFactory
     */
    public function testGetAdapterFactory()
    {
        $baseHandler = $this->getMockBuilder('\Sugarcrm\Sugarcrm\Dav\Cal\Handler\JobQueue\Base')
                            ->disableOriginalConstructor()
                            ->setMethods(array())
                            ->getMockForAbstractClass();
        $adapter = TestReflection::callProtectedMethod($baseHandler, 'getAdapterFactory');
        $this->assertInstanceOf('Sugarcrm\Sugarcrm\Dav\Cal\Adapter\Factory', $adapter);
    }

    /**
     * Test correct handler returning
     * @covers Sugarcrm\Sugarcrm\Dav\Cal\Handler\JobQueue\Base::getHandler
     */
    public function testGetHandler()
    {
        $baseHandler = $this->getMockBuilder('\Sugarcrm\Sugarcrm\Dav\Cal\Handler\JobQueue\Base')
                            ->disableOriginalConstructor()
                            ->setMethods(array())
                            ->getMockForAbstractClass();
        $handler = TestReflection::callProtectedMethod($baseHandler, 'getHandler');
        $this->assertInstanceOf('Sugarcrm\Sugarcrm\Dav\Cal\Handler', $handler);
    }

    /**
     * @param int $saveCounter
     * @param int $actualJobCounter
     * @param int $managerCalls
     * @param string $managerMethod
     * @param bool $expectedResult
     *
     * @dataProvider setJobToEndProvider
     *
     * @covers       \Sugarcrm\Sugarcrm\Dav\Cal\Handler\JobQueue\Base::setJobToEnd
     */
    public function testSetJobToEnd($saveCounter, $actualJobCounter, $managerCalls, $managerMethod, $expectedResult)
    {
        $eventMock = $this->getMockBuilder('\CalDavEvent')
                          ->disableOriginalConstructor()
                          ->setMethods(array('getSynchronizationObject'))
                          ->getMock();

        $syncMock = $this->getMockBuilder('\CalDavSynchronization')
                         ->disableOriginalConstructor()
                         ->setMethods(array('getJobCounter'))
                         ->getMock();

        $eventMock->fetched_row = array(1);
        $eventMock->module_name = 'CalDavEvents';

        $handlerMock = $this->getMockBuilder('\Sugarcrm\Sugarcrm\Dav\Cal\Handler\JobQueue\Base')
                            ->disableOriginalConstructor()
                            ->setMethods(array($managerMethod))
                            ->getMockForAbstractClass();

        TestReflection::setProtectedValue($handlerMock, 'saveCounter', $saveCounter);

        $eventMock->method('getSynchronizationObject')->willReturn($syncMock);
        $syncMock->expects($this->once())->method('getJobCounter')->willReturn($actualJobCounter);

        $handlerMock->expects($this->exactly($managerCalls))
                    ->method($managerMethod)
                    ->with();

        $result = TestReflection::callProtectedMethod(
            $handlerMock,
            'setJobToEnd',
            array($eventMock)
        );

        $this->assertEquals($expectedResult, $result);
    }
}
