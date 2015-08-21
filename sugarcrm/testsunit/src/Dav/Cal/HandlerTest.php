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

namespace Sugarcrm\SugarcrmTestsUnit\Dav\Cal;

/**
 * @coversDefaultClass \Sugarcrm\Sugarcrm\Dav\Cal\Handler
 */

class HandlerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @covers \Sugarcrm\Sugarcrm\Dav\Cal\Handler::import
     */
    public function testImport()
    {
        $bean = new \CalDavEvent();

        $jobMock = $this->getJobMock('Sugarcrm\Sugarcrm\Dav\Cal\Handler\JobQueue\Import', array($bean), array('CalDavImport'));
        $jobMock->expects($this->once())->method('run');

        $handlerMock = $this->getHandlerMock($jobMock);

        $handlerMock->import($bean);
    }

    /**
     * @covers \Sugarcrm\Sugarcrm\Dav\Cal\Handler::export
     */
    public function testExport()
    {
        $bean = new \SugarBean();

        $jobMock = $this->getJobMock('Sugarcrm\Sugarcrm\Dav\Cal\Handler\JobQueue\Export', array($bean), array('CalDavExport'));
        $jobMock->expects($this->once())->method('run');

        $handlerMock = $this->getHandlerMock($jobMock);

        $handlerMock->export($bean);
    }



    /**
     * Get Mock object for hook handler
     * @return \Sugarcrm\Sugarcrm\Dav\Cal\Handler\JobQueue\Import
     */
    protected function getJobMock($className, $constructorArgs, $methods)
    {
        return $this->getMockBuilder($className)
            ->setConstructorArgs($constructorArgs)
            ->setMethods(array_merge($methods, array('run', 'registerHandler')))
            ->getMock();
    }

    /**
     * @param string $class
     * @param array $methods
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    protected function getHandlerMock($jobMock)
    {
        $handlerMock = $this->getMockBuilder('\Sugarcrm\Sugarcrm\Dav\Cal\Handler')
            ->disableOriginalConstructor()
            ->setMethods(array('getManager','getAdapterFactory'))
            ->getMock();

        $factoryMock = $this->getMockBuilder('\Sugarcrm\Sugarcrm\Dav\Cal\Handler')
            ->setMethods(array('getAdapter'))
            ->getMock();
        $factoryMock->method('getAdapter')->willReturn(true);

        $handlerMock->method('getManager')->willReturn($jobMock);
        $handlerMock->method('getAdapterFactory')->willReturn($factoryMock);

        return $handlerMock;
    }
}
