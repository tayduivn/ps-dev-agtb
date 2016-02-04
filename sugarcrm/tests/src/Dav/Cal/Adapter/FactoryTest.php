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

namespace Sugarcrm\SugarcrmTests\Dav\Cal\Adapter;

use Sugarcrm\Sugarcrm\Dav\Cal\Adapter\Factory;

/**
 * Class FactoryTest
 * @package Sugarcrm\SugarcrmTestsUnit\Dav\Cal\Adapter
 *
 * @coversDefaultClass Sugarcrm\Sugarcrm\Dav\Cal\Adapter\Factory
 */
class FactoryTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @covers Sugarcrm\Sugarcrm\Dav\Cal\Adapter\Factory::getAdapter
     * @dataProvider getExistsAdapters
     */
    public function testGetAdapter($adapterClass)
    {
        $adapter = Factory::getInstance()->getAdapter($adapterClass);
        $this->assertInstanceOf('Sugarcrm\Sugarcrm\Dav\Cal\Adapter\AdapterInterface', $adapter);
    }

    /**
     * @covers Sugarcrm\Sugarcrm\Dav\Cal\Adapter\Factory::getSupportedModules
     */
    public function testGetSupportedModules()
    {
        $existsAdpaters = array('Meetings', 'Calls');
        $factoryMock =  $this->getMockBuilder('Sugarcrm\Sugarcrm\Dav\Cal\Adapter\Factory')
            ->disableOriginalConstructor()
            ->setMethods(array('getModulesList'))
            ->getMock();
        $factoryMock->method('getModulesList')->willReturn($existsAdpaters);

        $adapters = $factoryMock->getSupportedModules();
        $this->assertEquals(sort($existsAdpaters), sort($adapters));
    }

    /**
     * @return array
     */
    public function getExistsAdapters()
    {
        return array(
            array('Meetings')
        );
    }
}
