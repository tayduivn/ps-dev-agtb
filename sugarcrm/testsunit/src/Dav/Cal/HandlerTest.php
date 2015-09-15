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
        $bean = $this->getBeanMock('\Meeting');
        $calDavBean = $this->getSugarBeanMock($bean);
        $calDavBean->parent_id = true;
        $this->assertInstanceOf('\SugarBean', $calDavBean->getBean());

        $factoryMock = $this->getMockBuilder('\Sugarcrm\Sugarcrm\Dav\Cal\Adapter\Factory')
            ->setMethods(array('getAdapter', 'save', 'import'))
            ->getMock();

        $adapterMock = $this->getMockBuilder('\stdClass')
            ->setMethods(array('save', 'import'))
            ->getMock();

        $factoryMock->method('getAdapter')->willReturn($adapterMock);
        $factoryMock->method('save')->willReturn(true);
        $factoryMock->method('import')->willReturn(true);

        $handler = $this->getHandlerMock($factoryMock);
        $adapterMock->expects($this->once())->method('import');
        $handler->import($calDavBean);
    }

    protected function getBeanMock()
    {
        $beanMock = $this->getMockBuilder('\CalDavEvent')
            ->disableOriginalConstructor()
            ->setMethods(array('getBean', 'save'))
            ->getMock();
        return $beanMock;
    }

    protected function getSugarBeanMock($relatedBean)
    {
        $beanMock = $this->getMockBuilder('\CalDavEvent')
            ->disableOriginalConstructor()
            ->setMethods(array('getBean'))
            ->getMock();

        $beanMock->method('getBean')->willReturn($relatedBean);

        return $beanMock;
    }

    /**
     * @param string $class
     * @param array $methods
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    protected function getHandlerMock($factoryMock)
    {
        $handlerMock = $this->getMockBuilder('\Sugarcrm\Sugarcrm\Dav\Cal\Handler')
            ->disableOriginalConstructor()
            ->setMethods(array('getAdapterFactory'))
            ->getMock();

        $handlerMock->method('getAdapterFactory')->willReturn($factoryMock);
        //$handlerMock->method('import')->willReturn(true);

        return $handlerMock;
    }
}
