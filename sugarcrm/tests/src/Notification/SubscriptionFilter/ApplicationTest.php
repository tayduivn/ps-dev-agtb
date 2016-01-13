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

namespace Sugarcrm\SugarcrmTests\Notification\SubscriptionFilter;

use Sugarcrm\Sugarcrm\Notification\SubscriptionFilter\Application as SubscriptionFilterApplication;
use Sugarcrm\Sugarcrm\Notification\Emitter\Application\Event as ApplicationEvent;

/**
 * Class ApplicationTest
 *
 * @covers Sugarcrm\Sugarcrm\Notification\SubscriptionFilter\Application
 */
class ApplicationTest extends \Sugar_PHPUnit_Framework_TestCase
{
    /** @var SubscriptionFilterApplication */
    protected $applicationFilter = null;

    /** @var ApplicationEvent */
    protected $event = null;

    /** @var \SugarQuery_Builder_Where|\PHPUnit_Framework_MockObject_MockObject */
    protected $whereMock = null;

    /** @var \SugarQuery|\PHPUnit_Framework_MockObject_MockObject */
    protected $queryMock = null;

    /**
     * {@inheritdoc}
     */
    protected function setUp()
    {
        parent::setUp();
        $this->applicationFilter = new SubscriptionFilterApplication();
        $this->event = new ApplicationEvent('update' . rand(1000, 1999));
        $this->whereMock = $this->getMock('SugarQuery_Builder_Where', array(), array(), '', false);
        $this->queryMock = $this->getMock('SugarQuery');
        $this->queryMock->method('where')->willReturn($this->whereMock);
    }

    /**
     * String representation of object should be Application.
     *
     * @covers Sugarcrm\Sugarcrm\Notification\SubscriptionFilter\Application::__toString
     */
    public function testToString()
    {
        $result = (string)$this->applicationFilter;
        $this->assertEquals('Application', $result);
    }

    /**
     * Data provider for testFilterQuery.
     *
     * @see Sugarcrm\SugarcrmTests\Notification\SubscriptionFilter::testFilterQuery
     * @return array
     */
    public static function filterQueryProvider()
    {
        $alias1 = 'users' . rand(1000, 1999);
        $alias2 = 'users' . rand(2000, 2999);
        return array(
            'firstAlias' => array(
                'alias' => $alias1,
                'expectedResult' => $alias1,
            ),
            'secondAlias' => array(
                'alias' => $alias2,
                'expectedResult' => $alias2,
            ),
        );
    }

    /**
     * Should return users as alias of table. Should add condition is_admin=1 to query.
     *
     * @dataProvider filterQueryProvider
     * @covers Sugarcrm\Sugarcrm\Notification\SubscriptionFilter\Application::filterQuery
     * @param string $alias
     * @param string $expectedResult
     */
    public function testFilterQuery($alias, $expectedResult)
    {
        $this->whereMock->expects($this->once())
            ->method('equals')
            ->with(
                $this->equalTo('is_admin'),
                $this->equalTo(1)
            );

        $this->queryMock->method('getFromAlias')->willReturn($alias);
        $result = $this->applicationFilter->filterQuery($this->event, $this->queryMock);
        $this->assertEquals($expectedResult, $result);
    }

    /**
     * Should returns 1000.
     *
     * @covers Sugarcrm\Sugarcrm\Notification\SubscriptionFilter\Application::getOrder
     */
    public function testGetOrder()
    {
        $result = $this->applicationFilter->getOrder();
        $this->assertEquals(1000, $result);
    }

    /**
     * Data provider for testSupports.
     *
     * @see Sugarcrm\SugarcrmTests\Notification\SubscriptionFilter\ApplicationTest::testSupports
     * @return array
     */
    public static function supportsProvider()
    {
        return array(
            'eventIsApplicationEventReturnsTrue' => array(
                'eventClass' => 'Sugarcrm\Sugarcrm\Notification\Emitter\Application\Event',
                'eventName' => 'update' . rand(1000, 1999),
                'expected' => true,
            ),
            'eventIsReminderEventReturnsFalse' => array(
                'eventClass' => 'Sugarcrm\Sugarcrm\Notification\Emitter\Reminder\Event',
                'eventName' => 'update' . rand(1000, 1999),
                'expected' => false,
            ),
        );
    }

    /**
     * Should return true when param is Application object
     * Should return false when param is not application object.
     *
     * @dataProvider supportsProvider
     * @covers Sugarcrm\Sugarcrm\Notification\SubscriptionFilter\Application::supports
     * @param string $eventClass
     * @param string $eventName
     * @param bool $expected
     */
    public function testSupports($eventClass, $eventName, $expected)
    {
        $result = $this->applicationFilter->supports(new $eventClass($eventName));
        $this->assertEquals($expected, $result);
    }
}
