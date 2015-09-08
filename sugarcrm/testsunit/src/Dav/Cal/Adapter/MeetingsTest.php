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

namespace Sugarcrm\SugarcrmTestsUnit\Dav\Cal\Adapter;

use Sugarcrm\SugarcrmTestsUnit\TestReflection;
use Sugarcrm\Sugarcrm\Dav\Cal\Adapter\Meetings as MeetingAdapter;

/**
 * Class for testing Meeting CalDavAdapter
 *
 * @package Sugarcrm\SugarcrmTestsUnit\Dav\Cal\Adapter
 * @coversDefaultClass Sugarcrm\Sugarcrm\Dav\Cal\Adapter\Factory\Meetings
 */
class MeetingsTest extends \PHPUnit_Framework_TestCase
{
    /**
     * data for CalDavEvent mock getters
     * @var array
     */
    protected $calDavBeanProperties = array(
        'getTitle' => 'Cal Dav test Title',
        'getDescription' => 'Event description',
        'getStartDate' => '2015-08-06 08:00:00',
        'getEndDate' => '2015-08-06 16:00:00',
        'getLocation' => 'office',
        'getDuration' => '125'
    );

    /**
     * @covers \Sugarcrm\Sugarcrm\Dav\Cal\Adapter\Meetings::import
     */
    public function testImport()
    {
        /**@var \Meeting $meetingBean */
        $meetingBean = $this->getBeanMock('\Meeting');
        $caldavBean = $this->getCalDavBeanMock();
        $meetings = $this->getMeetingAdapterMock($caldavBean);

        $result = $meetings->import($meetingBean, $caldavBean);

        $this->assertTrue($result);

        $this->assertEquals($meetingBean->name, $this->calDavBeanProperties['getTitle']);
        $this->assertEquals($meetingBean->description, $this->calDavBeanProperties['getDescription']);
        $this->assertEquals($meetingBean->date_start, $this->calDavBeanProperties['getStartDate']);
        $this->assertEquals($meetingBean->date_end, $this->calDavBeanProperties['getEndDate']);
        $this->assertEquals($meetingBean->location, $this->calDavBeanProperties['getLocation']);
        $this->assertEquals($meetingBean->duration_hours, round($this->calDavBeanProperties['getDuration'] / 60));
        $this->assertEquals($meetingBean->duration_minutes, $this->calDavBeanProperties['getDuration'] % 60);

        $result = $meetings->import($meetingBean, $caldavBean);
        $this->assertFalse($result);

    }

    /**
     * @covers \Sugarcrm\Sugarcrm\Dav\Cal\Adapter\Meetings::arrayIndex
     * @dataProvider arrayIndexProvider
     */
    public function testArrayIndex($data, $expected)
    {
        $meetingAdapter = new MeetingAdapter;
        $actualData = TestReflection::callProtectedMethod($meetingAdapter, 'arrayIndex', array('id', $data));
        $this->assertEquals($expected, $actualData);
    }

    /**
     * @covers \Sugarcrm\Sugarcrm\Dav\Cal\Adapter\Meetings::getReminderTimeValues
     */
    public function testGetReminderTimeValues()
    {
        $expectedValues = array(
            -1, 60, 300, 600, 900, 1800, 3600, 7200, 10800, 18000, 86400
        );
        $meetingAdapter = new MeetingAdapter;
        $actualData = TestReflection::callProtectedMethod($meetingAdapter, 'getReminderTimeValues');
        $this->assertEquals(sort($expectedValues), sort($actualData));
    }

    /**
     * return data for testArrayIndex fucntion
     * @return array
     */
    public function arrayIndexProvider()
    {
        return array(
            array(
                array(
                    array('id' => '123', 'data' => 'abc'),
                    array('id' => '345', 'data' => 'def'),
                ),
                array(
                    '123' => array('id' => '123', 'data' => 'abc'),
                    '345' => array('id' => '345', 'data' => 'def')
                )
            ),
            array(
                array(
                    (object)array('id' => '123', 'data' => 'abc'),
                    (object)array('id' => '345', 'data' => 'def'),
                ),
                array(
                    '123' => (object)array('id' => '123', 'data' => 'abc'),
                    '345' => (object)array('id' => '345', 'data' => 'def')
                )
            ),
        );
    }

    /**
     * return adapter mock
     * @param $bean
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    public function getMeetingAdapterMock($bean)
    {
        $adapterMock = $this->getMockBuilder('Sugarcrm\Sugarcrm\Dav\Cal\Adapter\Meetings')
            ->disableOriginalConstructor()
            ->setMethods(array('getNotCachedCalDavEvent'))
            ->getMock();
        $adapterMock->method('getNotCachedCalDavEvent')->willReturn($bean);

        return $adapterMock;
    }

    /**
     * @param string $beanClass
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    protected function getBeanMock($beanClass)
    {
        $beanMock = $this->getMockBuilder($beanClass)
            ->disableOriginalConstructor()
            ->getMock();
        return $beanMock;
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    protected function getCalDavBeanMock()
    {
        $calDavFunctions = array_merge(array_keys($this->calDavBeanProperties), array('getRRule'));
        $beanMock = $this->getMockBuilder('\CalDavEvent')
            ->disableOriginalConstructor()
            ->setMethods($calDavFunctions)
            ->getMock();
        foreach ($this->calDavBeanProperties as $methodName => $returnedValue) {
            $beanMock->method($methodName)->willReturn($returnedValue);
        }
        $beanMock->method('getRRule')->willReturn(array());
        return $beanMock;
    }
}
