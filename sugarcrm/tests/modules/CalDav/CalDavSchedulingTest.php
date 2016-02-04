<?php

require_once 'tests/SugarTestCalDavUtilites.php';

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

/**
 * Class CalDavSchedulingTest
 *
 * @coversDefaultClass \CalDavScheduling
 */
class CalDavSchedulingTest extends PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        SugarTestHelper::setUp('current_user');
        parent::setUp();
    }

    public function tearDown()
    {
        SugarTestCalDavUtilities::deleteAllCreatedCalendars();
        SugarTestCalDavUtilities::deleteCreatedEvents();
        SugarTestCalDavUtilities::deleteSchedulingObjects();
        SugarTestUserUtilities::removeAllCreatedAnonymousUsers();
        parent::tearDown();
    }

    public function setSchedulingEventDataProvider()
    {
        return array(
            array(
                'objectUri' => 'uri.isc',
                'calendarData' => 'BEGIN:VCALENDAR
BEGIN:VEVENT
uid:test
DTSTART;VALUE=DATE:20160101
END:VEVENT
END:VCALENDAR',
                'result' => true,
                'expectedObject' => array(
                    'assigned_user_id' => 'test_user',
                    'uri' => 'uri.isc',
                    'calendar_data' => 'BEGIN:VCALENDAR
BEGIN:VEVENT
uid:test
DTSTART;VALUE=DATE:20160101
END:VEVENT
END:VCALENDAR',
                    'data_size' => 90,
                    'etag' => 'c3d48c3c99615a99a764be4fc95c9ca9',
                )
            ),
            array(
                'objectUri' => 'uri.isc',
                'calendarData' => '',
                'result' => false,
                'expectedObject' => array()
            ),
        );
    }

    /**
     * @param $objectURI
     * @param $calendarData
     * @param $expectedResult
     * @param array $expectedObject
     *
     * @covers       \CalDavScheduling::setSchedulingEventData
     *
     * @dataProvider setSchedulingEventDataProvider
     */
    public function testSetSchedulingEventData($objectURI, $calendarData, $expectedResult, array $expectedObject)
    {
        $userMock = $this->getMockBuilder('\User')
                         ->disableOriginalConstructor()
                         ->setMethods(array('getUserByPrincipalString'))
                         ->getMock();
        $userMock->id = 'test_user';

        $schedulingMock = $this->getMockBuilder('\CalDavScheduling')
                               ->disableOriginalConstructor()
                               ->setMethods(null)
                               ->getMock();

        $result = $schedulingMock->setSchedulingEventData($userMock, $objectURI, $calendarData);

        $this->assertEquals($expectedResult, $result);

        foreach ($expectedObject as $key => $value) {
            $this->assertEquals($value, $schedulingMock->$key);
        }
    }

    /**
     * @covers \CalDavScheduling::getByAssigned
     */
    public function testGetByAssigned()
    {
        $beanMock = $this->getMockBuilder('\CalDavScheduling')
                         ->setMethods(null)
                         ->getMock();

        $user1 = SugarTestUserUtilities::createAnonymousUser();
        $user2 = SugarTestUserUtilities::createAnonymousUser();

        $calendarData = 'BEGIN:VCALENDAR
BEGIN:VEVENT
uid:test
DTSTART;VALUE=DATE:20160101
END:VEVENT
END:VCALENDAR';

        $sch1 = SugarTestCalDavUtilities::createSchedulingObject($user1, 'user1.ics', $calendarData);
        $sch2 = SugarTestCalDavUtilities::createSchedulingObject($user1, 'user11.ics', $calendarData);
        $sch3 = SugarTestCalDavUtilities::createSchedulingObject($user2, 'user2.ics', $calendarData);

        $result = $beanMock->getByAssigned($user2->id);

        $this->assertEquals('user2.ics', $result[$sch3->id]->uri);
        $this->assertArrayNotHasKey($sch1->id, $result);
        $this->assertArrayNotHasKey($sch2->id, $result);
    }

    /**
     * @covers \CalDavScheduling::getByUri
     */
    public function testGetByUri()
    {
        $beanMock = $this->getMockBuilder('\CalDavScheduling')
                         ->setMethods(null)
                         ->getMock();

        $user1 = SugarTestUserUtilities::createAnonymousUser();
        $user2 = SugarTestUserUtilities::createAnonymousUser();

        $calendarData = 'BEGIN:VCALENDAR
BEGIN:VEVENT
uid:test
DTSTART;VALUE=DATE:20160101
END:VEVENT
END:VCALENDAR';

        SugarTestCalDavUtilities::createSchedulingObject($user1, 'user1.ics', $calendarData);
        SugarTestCalDavUtilities::createSchedulingObject($user1, 'user11.ics', $calendarData);
        SugarTestCalDavUtilities::createSchedulingObject($user2, 'user2.ics', $calendarData);

        $result = $beanMock->getByUri('user11.ics', $user1->id);
        $this->assertInstanceOf('\CalDavScheduling', $result);
        $this->assertEquals('user11.ics', $result->uri);

        $result = $beanMock->getByUri('user111.ics', $user1->id);
        $this->assertNull($result);
    }
}
