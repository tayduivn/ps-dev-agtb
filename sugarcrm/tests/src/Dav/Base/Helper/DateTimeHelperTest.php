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

namespace Sugarcrm\SugarcrmTests\Dav\Base\Helper;

/**
 * Class DateTimeHelperTest
 * @package Sugarcrm\SugarcrmTestsUnit\Dav\Base\Helper
 *
 * @coversDefaultClass Sugarcrm\Sugarcrm\Dav\Base\Helper\DateTimeHelper
 */
class DateTimeHelperTest extends \PHPUnit_Framework_TestCase
{
    protected function getEventTemplateObject($template, $isText = false)
    {
        $calendarData = file_get_contents(dirname(__FILE__) . '/EventsTemplates/' . $template . '.ics');

        if ($isText) {
            return $calendarData;
        }

        $vEvent = \Sabre\VObject\Reader::read($calendarData);

        return $vEvent;
    }

    public function durationToSecondsProvider()
    {
        return array(
            array(
                'duration' => '-PT15M',
                'seconds' => - 900,
            ),
            array(
                'duration' => 'P15DT5H20S',
                'seconds' => 1314020,
            )
        );
    }

    public function secondsToDurationProvider()
    {
        return array(
            array(
                'seconds' => - 900,
                'duration' => '-PT15M',
            ),
            array(
                'seconds' => 900,
                'duration' => 'PT15M',
            ),
            array(
                'seconds' => 1314020,
                'duration' => 'P15DT5H20S',
            )
        );
    }

    public function davDateToSugarProvider()
    {
        return array(
            array(
                'vEvent' => $this->getEventTemplateObject('datetime'),
                'select' => 'DTSTART',
                'sugarDateTime' => new \SugarDateTime('2015-08-06 10:00:00', new \DateTimeZone('Europe/Berlin')),
            ),
            array(
                'vEvent' => $this->getEventTemplateObject('datetime1'),
                'select' => 'DTSTART',
                'sugarDateTime' => new \SugarDateTime('2015-08-06 10:00:00', new \DateTimeZone('UTC')),
            ),
            array(
                'vEvent' => $this->getEventTemplateObject('datetime2'),
                'select' => 'DTSTART',
                'sugarDateTime' => new \SugarDateTime('2015-08-06 10:00:00', new \DateTimeZone('UTC')),
            ),
            array(
                'vEvent' => $this->getEventTemplateObject('datetime3'),
                'select' => 'DTSTART',
                'sugarDateTime' => new \SugarDateTime('2015-08-06 00:00:00', new \DateTimeZone('UTC')),
            ),
        );
    }

    /**
     * @param string $duration
     * @param int $expectedResult
     *
     * @covers       Sugarcrm\Sugarcrm\Dav\Base\Helper\DateTimeHelper::durationToSeconds
     *
     * @dataProvider durationToSecondsProvider
     */
    public function testDurationToSecond($duration, $expectedResult)
    {
        $helperMock = $this->getDateTimeHelperMock();
        $result = $helperMock->durationToSeconds($duration);
        $this->assertEquals($expectedResult, $result);
    }

    /**
     * @param int $seconds
     * @param string $expectedResult
     *
     * @covers       Sugarcrm\Sugarcrm\Dav\Base\Helper\DateTimeHelper::secondsToDuration
     *
     * @dataProvider secondsToDurationProvider
     */
    public function testSecondsToDuration($seconds, $expectedResult)
    {
        $helperMock = $this->getDateTimeHelperMock();
        $result = $helperMock->secondsToDuration($seconds);
        $this->assertEquals($expectedResult, $result);
    }

    /**
     * @param $vEvent
     * @param string $elementToSelect
     * @param string $expectedDateTime
     *
     * @covers       Sugarcrm\Sugarcrm\Dav\Base\Helper\DateTimeHelper::davDateToSugar
     *
     * @dataProvider davDateToSugarProvider
     */
    public function testDavDateToSugar($vEvent, $elementToSelect, $expectedDateTime)
    {
        $helperMock = $this->getDateTimeHelperMock();
        $dateTimeElement = array_shift($vEvent->getBaseComponent()->select($elementToSelect));
        $result = $helperMock->davDateToSugar($dateTimeElement);
        $this->assertEquals($expectedDateTime, $result);
    }

    /**
     * Get basic DateTimeHelper mock with mocked getCurrentUser() method.
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    protected function getDateTimeHelperMock()
    {
        $userMock = $this->getMockBuilder('\User')
            ->disableOriginalConstructor()
            ->setMethods(array('getPreference'))
            ->getMock();
        $userMock->expects($this->any())->method('getPreference')->with('timezone')->willReturn('UTC');

        $helperMock = $this->getMockBuilder('Sugarcrm\Sugarcrm\Dav\Base\Helper\DateTimeHelper')
            ->disableOriginalConstructor()
            ->setMethods(array('getCurrentUser'))
            ->getMock();
        $helperMock->expects($this->any())->method('getCurrentUser')->willReturn($userMock);

        return $helperMock;
    }
}
