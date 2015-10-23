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

use Sugarcrm\SugarcrmTestsUnit\TestReflection;

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
        $calDavBean = $this->getCalDavBeanMock($bean);
        $calDavBean->parent_id = true;
        $this->assertInstanceOf('\SugarBean', $calDavBean->getBean());

        $adapterMock = $this->getMockBuilder('\stdClass')
            ->setMethods(array('save', 'import'))
            ->getMock();
        $factoryMock = $this->getFactoryMock($adapterMock);

        $handler = $this->getHandlerMock($factoryMock);

        $adapterMock->expects($this->once())->method('import');

        $handler->import($calDavBean);
    }

    /**
     * @covers \Sugarcrm\Sugarcrm\Dav\Cal\Handler::export
     */
    public function testExport()
    {
        $bean = $this->getBeanMock('\Meeting');

        $adapterMock = $this->getMockBuilder('\stdClass')
            ->setMethods(array('save', 'export'))
            ->getMock();

        $factoryMock = $this->getFactoryMock($adapterMock);
        $calDavMock = $this->getCalDavBeanMock();

        $handler = $this->getHandlerMock($factoryMock);

        $handler->expects($this->once())->method('getDavBean')->willReturn($calDavMock);

        $adapterMock->expects($this->once())->method('export');
        $handler->export($bean);
    }


    /**
     * @covers \Sugarcrm\Sugarcrm\Dav\Cal\Handler::export
     */
    public function testExportWithParent()
    {
        $bean = $this->getBeanMock('\Meeting');

        $adapterMock = $this->getMockBuilder('\stdClass')
            ->setMethods(array('save', 'export'))
            ->getMock();

        $factoryMock = $this->getFactoryMock($adapterMock);
        $handler = $this->getHandlerMock($factoryMock, true);

        $calDavMock = $this->getCalDavBeanMock();

        $adapterMock->expects($this->once())->method('export');
        $handler->expects($this->once())->method('getParentBean');
        $handler->expects($this->once())->method('getDavBean')->willReturn($calDavMock);

        $handler->export($bean);
    }



    /**
     * @param string $beanClass
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    protected function getBeanMock($beanClass)
    {
        $beanMock = $this->getMockBuilder($beanClass)
            ->disableOriginalConstructor()
            ->setMethods(array('getBean', 'save', 'load_relationship'))
            ->getMock();
        return $beanMock;
    }

    /**
     * @param \PHPUnit_Framework_MockObject_MockObject $relatedBean
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    protected function getCalDavBeanMock($relatedBean = false)
    {
        $calDavMock = $this->getMockBuilder('\CalDavEvent')
            ->disableOriginalConstructor()
            ->setMethods(array('getBean','findByBean', 'getSynchronizationObject'))
            ->getMock();

        $beanMock = $this->getBeanMock('\stdClass');
        if ($relatedBean) {
            $calDavMock->method('getBean')->willReturn($relatedBean);
        }
        $calDavMock->method('findByBean')->willReturn($beanMock);

        return $calDavMock;
    }

    /**
     * @param $factoryMock
     * @param bool|false $setParentBean
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    protected function getHandlerMock($factoryMock, $setParentBean = false)
    {
        $handlerMethods = array('getAdapterFactory', 'getDavBean');
        if ($setParentBean) {
            $handlerMethods[] = 'getParentBean';
            $handlerMethods[] = 'isBeanChild';
        }

        $handlerMock = $this->getMockBuilder('\Sugarcrm\Sugarcrm\Dav\Cal\Handler')
            ->setMethods($handlerMethods)
            ->getMock();

        if ($setParentBean) {
            $parentBeanMock = $this->getMockBuilder('\SugarBean')
                ->disableOriginalConstructor()
                ->getMock();
            $handlerMock->method('isBeanChild')->willReturn(true);
            $handlerMock->method('getParentBean')->willReturn($parentBeanMock);
        }

        $handlerMock->method('getAdapterFactory')->willReturn($factoryMock);

        return $handlerMock;
    }

    /**
     * @param \PHPUnit_Framework_MockObject_MockObject $adapterMock
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    protected function getFactoryMock($adapterMock)
    {
        $factoryMock = $this->getMockBuilder('\Sugarcrm\Sugarcrm\Dav\Cal\Adapter\Factory')
            ->setMethods(array('getAdapter', 'save', 'export'))
            ->getMock();

        $factoryMock->method('getAdapter')->willReturn($adapterMock);
        $factoryMock->method('save')->willReturn(true);
        $factoryMock->method('export')->willReturn(true);

        return $factoryMock;
    }
}
