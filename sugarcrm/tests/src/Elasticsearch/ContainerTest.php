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

namespace Sugarcrm\SugarcrmTest\Elasticsearch;

use Sugarcrm\Sugarcrm\Elasticsearch\Container;

/**
 * Service Container tests
 */
class ContainerTest extends \Sugar_PHPUnit_Framework_TestCase
{
    /**
     * @covers \Sugarcrm\Sugarcrm\Elasticsearch\Container::__get
     * @dataProvider dataProviderTestGetOverloadLazyLoad
     * @group unit
     */
    public function testGetOverloadLazyLoad($property)
    {
        $initMethod = 'init' . ucfirst($property);
        $container = $this->getContainerMock(array($initMethod));
        $container->expects($this->once())
            ->method($initMethod);

        $container->$property;
    }

    public function dataProviderTestGetOverloadLazyLoad()
    {
        return array(
            array('logger'),
            array('metaDataHelper'),
            array('queueManager'),
            array('client'),
            array('indexPool'),
            array('indexManager'),
            array('mappingManager'),
            array('indexer'),
        );
    }

    /**
     * @covers \Sugarcrm\Sugarcrm\Elasticsearch\Container::getConfig
     * @covers \Sugarcrm\Sugarcrm\Elasticsearch\Container::setConfig
     * @group unit
     */
    public function testSetConfig()
    {
        $container = $this->getContainerMock();

        // empty base values
        $this->assertEquals(array(), $container->getConfig('engine'));
        $this->assertEquals(array(), $container->getConfig('global'));

        // default value
        $this->assertEquals(array('default'), $container->getConfig('foo', array('default')));

        // setter existing key
        $container->setConfig('engine', array('bar'));
        $this->assertEquals(array('bar'), $container->getConfig('engine'));

        // setter new key
        $container->setConfig('new', array('beer'));
        $this->assertEquals(array('beer'), $container->getConfig('new'));
    }

    /**
     * @covers \Sugarcrm\Sugarcrm\Elasticsearch\Container::registerProviders
     * @covers \Sugarcrm\Sugarcrm\Elasticsearch\Container::getRegisteredProviders
     * @covers \Sugarcrm\Sugarcrm\Elasticsearch\Container::isProviderAvailable
     * @covers \Sugarcrm\Sugarcrm\Elasticsearch\Container::registerProvider
     * @covers \Sugarcrm\Sugarcrm\Elasticsearch\Container::unregisterProvider
     * @group unit
     */
    public function testRegisterProviders()
    {
        // test if default providers are properly registered
        $container = new Container();
        $this->assertEquals(array('GlobalSearch'), $container->getRegisteredProviders());

        // Register/unregister new provider
        $this->assertFalse($container->isProviderAvailable('new'));
        $container->registerProvider('new');
        $this->assertTrue($container->isProviderAvailable('new'));
        $container->unregisterProvider('new');
        $this->assertFalse($container->isProviderAvailable('new'));
    }

    /**
     * @covers \Sugarcrm\Sugarcrm\Elasticsearch\Container::getProvider
     * @dataProvider dataProviderTestGetProvider
     * @group unit
     */
    public function testGetProvider($provider, $class)
    {
        $container = $this->getContainerMock(array('newProvider'));
        $container->expects($this->once())
            ->method('newProvider')
            ->with($this->equalTo($class));

        $container->registerProviders();
        $container->getProvider($provider);
    }

    public function dataProviderTestGetProvider()
    {
        return array(
            array(
                'GlobalSearch',
                '\Sugarcrm\Sugarcrm\Elasticsearch\Provider\GlobalSearch\GlobalSearch',
            ),
        );
    }

    /**
     * @return \Sugarcrm\Sugarcrm\Elasticsearch\Container
     */
    protected function getContainerMock(array $methods = null)
    {
        return $this->getMockBuilder('Sugarcrm\Sugarcrm\Elasticsearch\Container')
            ->disableOriginalConstructor()
            ->setMethods($methods)
            ->getMock();
    }
}
