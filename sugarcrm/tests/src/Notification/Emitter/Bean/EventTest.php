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
                'eventName' => 'after_save' . rand(1000, 1999),
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
        $event = new BeanEvent('after_save');
        $event->getBean();
    }

    /**
     * Check if covered function returns bean given in event constructor.
     *
     * @covers Sugarcrm\Sugarcrm\Notification\Emitter\Bean\Event::getBean
     */
    public function testGetBeanReturnsBeanFromConstructor()
    {
        $bean = \BeanFactory::getBean('Users');
        $event = new BeanEvent('test' . rand(1000, 1999), $bean);
        $this->assertEquals($bean, $event->getBean());
    }

    /**
     * Check if covered function returns bean given in setBean function.
     *
     * @covers Sugarcrm\Sugarcrm\Notification\Emitter\Bean\Event::getBean
     * @covers Sugarcrm\Sugarcrm\Notification\Emitter\Bean\Event::setBean
     * @dataProvider getBeanReturnsBeanFromSetBeanProvider
     * @param string $eventName
     * @param string $targetBeanModule
     */
    public function testGetBeanReturnsBeanFromSetBean($eventName, $targetBeanModule)
    {
        $targetBean = \BeanFactory::getBean($targetBeanModule);
        $targetBean->id = create_guid();
        $event = new BeanEvent($eventName);
        $event->setBean($targetBean);
        $this->assertEquals($targetBean, $event->getBean());
    }

    /**
     * Data provider for testGetBeanReturnsBeanFromSetBean.
     *
     * @see EventTest::testGetBeanReturnsBeanFromSetBean
     * @return array
     */
    public static function getBeanReturnsBeanFromSetBeanProvider()
    {
        return array(
            'constructorWithoutBean' => array(
                'eventName' => 'after_save',
                'targetBeanModule' => 'Accounts',
            ),
            'constructorWithBean' => array(
                'eventName' => 'after_save',
                'targetBeanModule' => 'Meetings',
            ),
        );
    }

    /**
     * Check if covered function returns bean given in setBean function, not in constructor.
     *
     * @covers Sugarcrm\Sugarcrm\Notification\Emitter\Bean\Event::getBean
     * @covers Sugarcrm\Sugarcrm\Notification\Emitter\Bean\Event::setBean
     * @dataProvider getBeanInitializeEventWithBeanProvider
     * @param string $eventName
     * @param string $targetBeanModule
     */
    public function testGetBeanInitializeEventWithBean($eventName, $targetBeanModule)
    {
        $targetBean = \BeanFactory::getBean($targetBeanModule);
        $targetBean->id = create_guid();
        $constructorBean = \BeanFactory::getBean('Users');
        $constructorBean->id = create_guid();
        $event = new BeanEvent($eventName, $constructorBean);
        $event->setBean($targetBean);
        $this->assertEquals($targetBean, $event->getBean());
    }

    /**
     * Data provider for testGetBeanInitializeEventWithBean.
     *
     * @see EventTest::testGetBeanInitializeEventWithBean
     * @return array
     */
    public static function getBeanInitializeEventWithBeanProvider()
    {
        return array(
            'constructorWithoutBean' => array(
                'eventName' => 'after_save',
                'targetBeanModule' => 'Accounts',
            ),
            'constructorWithBean' => array(
                'eventName' => 'after_save',
                'targetBeanModule' => 'Meetings',
            ),
        );
    }

    /**
     * Check if covered function set bean to event and return event object.
     *
     * @covers Sugarcrm\Sugarcrm\Notification\Emitter\Bean\Event::setBean
     * @covers Sugarcrm\Sugarcrm\Notification\Emitter\Bean\Event::getBean
     */
    public function testSetBeanSetsBeanAndReturnsThis()
    {
        $accountBean = \BeanFactory::getBean('Accounts');
        $accountBean->id = create_guid();

        $leadBean = \BeanFactory::getBean('Leads');
        $leadBean->id = create_guid();

        $event = new BeanEvent('Accounts', $accountBean);
        $result = $event->setBean($leadBean);

        $this->assertEquals($leadBean, $event->getBean());
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
     * @param string $beanModuleName
     */
    public function testGetModuleNameBeanSetInConstructor($beanModuleName)
    {
        $bean = \BeanFactory::getBean($beanModuleName);
        $bean->id = create_guid();
        $event = new BeanEvent('test', $bean);
        $this->assertEquals($bean->module_name, $event->getModuleName());
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
                'beanModuleName' => 'Users',
            ),
            'accountBean' => array(
                'beanModuleName' => 'Accounts',
            ),
            'teamBean' => array(
                'beanModuleName' => 'Teams',
            ),
        );
    }


    /**
     * Check if covered function returns module name of bean given in setBean function.
     *
     * @covers Sugarcrm\Sugarcrm\Notification\Emitter\Bean\Event::getModuleName
     * @dataProvider getModuleNameFromSetBeanProvider
     * @param string $eventName
     * @param string $targetBeanModule
     */
    public function testGetModuleNameFromSetBean($eventName, $targetBeanModule)
    {
        $bean = \BeanFactory::getBean($targetBeanModule);
        $bean->id = create_guid();
        $event = new BeanEvent($eventName);
        $event->setBean($bean);
        $this->assertEquals($bean->module_name, $event->getModuleName());
    }

    /**
     * Data provider for testGetModuleNameFromSetBean.
     *
     * @see EventTest::testGetModuleNameFromSetBean
     * @return array
     */
    public static function getModuleNameFromSetBeanProvider()
    {
        return array(
            'constructorWithoutBean' => array(
                'eventName' => 'after_save' . rand(1000, 1999),
                'targetBeanModule' => 'Users',
            ),
            'constructorWithBean' => array(
                'eventName' => 'after_save' . rand(2000, 2999),
                'targetBeanModule' => 'Meetings',
            ),
        );
    }
}
