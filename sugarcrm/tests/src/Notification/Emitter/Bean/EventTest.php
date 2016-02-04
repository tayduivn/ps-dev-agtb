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

namespace Sugarcrm\SugarcrmTests\Notification\Emitter\Bean;

use Sugarcrm\Sugarcrm\Notification\Emitter\Bean\Event as BeanEvent;

/**
 * Class EventTest
 *
 * @covers Sugarcrm\Sugarcrm\Notification\Emitter\Bean\Event
 */
class EventTest extends \Sugar_PHPUnit_Framework_TestCase
{
    /**
     * Check if string representation of emitter is given in constructor data.
     *
     * @covers Sugarcrm\Sugarcrm\Notification\Emitter\Bean\Event::__toString
     * @dataProvider toStringProvider
     * @param string $eventName
     */
    public function testToString($eventName)
    {
        $event = new BeanEvent($eventName);
        $this->assertEquals($eventName, (string)$event);
    }

    /**
     * Data provider for testToString.
     *
     * @see EventTest::testToString
     * @return array
     */
    public static function toStringProvider()
    {
        return array(
            'emptyString' => array(
                'eventName' => '',
            ),
            'someString' => array(
                'eventName' => 'test' . rand(1000, 1999),
            ),
        );
    }

    /**
     * Check exception if bean was not set.
     *
     * @covers Sugarcrm\Sugarcrm\Notification\Emitter\Bean\Event::getBean
     * @expectedException \LogicException
     */
    public function testGetBeanThrowsIfBeanWasNotSet()
    {
        $event = new BeanEvent('test' . rand(1000, 1999));
        $event->getBean();
    }

    /**
     * Data provider for testGetBeanAndSetBean
     *
     * @see EventTest::testGetBeanAndSetBean
     * @return array
     */
    public static function getBeanAndSetBeanProvider()
    {
        return array(
            'accounts' => array(
                'constructorBean' => 'Meetings',
                'methodBean' => 'Accounts',
            ),
            'meetings' => array(
                'constructorBean' => 'Accounts',
                'methodBean' => 'Meetings',
            ),
        );
    }

    /**
     * Checks behavior of constructor, setBean, getBean methods.
     *
     * @covers Sugarcrm\Sugarcrm\Notification\Emitter\Bean\Event::__construct
     * @covers Sugarcrm\Sugarcrm\Notification\Emitter\Bean\Event::setBean
     * @covers Sugarcrm\Sugarcrm\Notification\Emitter\Bean\Event::getBean
     * @dataProvider getBeanAndSetBeanProvider
     * @param string $constructorBean
     * @param string $methodBean
     */
    public function testGetBeanAndSetBean($constructorBean, $methodBean)
    {
        $constructorBean = \BeanFactory::getBean($constructorBean);
        $constructorBean->id = create_guid();
        $methodBean = \BeanFactory::getBean($methodBean);
        $methodBean->id = create_guid();

        $event = new BeanEvent(null, $constructorBean);
        $this->assertEquals($constructorBean, $event->getBean());
        $result = $event->setBean($methodBean);
        $this->assertEquals($methodBean, $event->getBean());
        $this->assertEquals($event, $result);
    }

    /**
     * Check exception if bean was not set.
     *
     * @covers Sugarcrm\Sugarcrm\Notification\Emitter\Bean\Event::getModuleName
     * @expectedException \LogicException
     */
    public function testGetModuleNameThrowsIfBeanNotSet()
    {
        $event = new BeanEvent('foo' . rand(1000, 9999));
        $event->getModuleName();
    }

    /**
     * Check if covered function returns module name of bean given in event constructor.
     *
     * @dataProvider getModuleNameBeanSetInConstructorProvider
     * @covers Sugarcrm\Sugarcrm\Notification\Emitter\Bean\Event::getModuleName
     * @param string $beanModule
     */
    public function testGetModuleNameBeanSetInConstructor($beanModule)
    {
        $beanModule = \BeanFactory::getBean($beanModule);
        $beanModule->id = create_guid();
        $event = new BeanEvent('test', $beanModule);
        $this->assertEquals($beanModule->module_name, $event->getModuleName());
    }

    /**
     * Data provider for testGetModuleNameBeanSetInConstructor.
     *
     * @see EventTest::testGetModuleNameBeanSetInConstructor
     * @return array
     */
    public static function getModuleNameBeanSetInConstructorProvider()
    {
        return array(
            'userBean' => array(
                'beanModule' => 'Users',
            ),
            'accountBean' => array(
                'beanModule' => 'Accounts',
            ),
            'teamBean' => array(
                'beanModule' => 'Teams',
            ),
        );
    }

    /**
     * Data provider for testGetModuleName.
     *
     * @see EventTest::testGetModuleName
     * @return array
     */
    public static function getModuleNameProvider()
    {
        return array(
            'accounts' => array(
                'constructorBean' => 'Meetings',
                'methodBean' => 'Accounts',
            ),
            'meetings' => array(
                'constructorBean' => 'Accounts',
                'methodBean' => 'Meetings',
            ),
        );
    }

    /**
     * Check if covered function returns module name of bean given in setBean function.
     *
     * @covers Sugarcrm\Sugarcrm\Notification\Emitter\Bean\Event::getModuleName
     * @dataProvider getModuleNameProvider
     * @param string $constructorBean
     * @param string $methodBean
     */
    public function testGetModuleName($constructorBean, $methodBean)
    {
        $constructorBean = \BeanFactory::getBean($constructorBean);
        $constructorBean->id = create_guid();
        $methodBean = \BeanFactory::getBean($methodBean);
        $methodBean->id = create_guid();

        $event = new BeanEvent(null, $constructorBean);
        $this->assertEquals($constructorBean->module_name, $event->getModuleName());
        $event->setBean($methodBean);
        $this->assertEquals($methodBean->module_name, $event->getModuleName());
    }
}
