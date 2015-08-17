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

namespace Sugarcrm\SugarcrmTestsUnit\Dav\Base\Helper;

/**
 * Class DateTimeHelperTest
 * @package Sugarcrm\SugarcrmTestsUnit\Dav\Base\Helper
 *
 * @coversDefaultClass Sugarcrm\Sugarcrm\Dav\Base\Helper\DateTimeHelper
 */
class DateTimeHelperTest extends \PHPUnit_Framework_TestCase
{
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
                'datetime' => '2015-01-01 00:00:01',
                'timezone' => 'Europe/Minsk',
                'sugarDateTime' => '2014-12-31 21:00:01',
            ),
            array(
                'datetime' => '2015-01-01 00:00:01',
                'timezone' => 'UTC',
                'sugarDateTime' => '2015-01-01 00:00:01',
            )
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
        $helperMock = $this->getMockBuilder('Sugarcrm\Sugarcrm\Dav\Base\Helper\DateTimeHelper')
                           ->disableOriginalConstructor()
                           ->setMethods(null)
                           ->getMock();
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
        $helperMock = $this->getMockBuilder('Sugarcrm\Sugarcrm\Dav\Base\Helper\DateTimeHelper')
                           ->disableOriginalConstructor()
                           ->setMethods(null)
                           ->getMock();
        $result = $helperMock->secondsToDuration($seconds);

        $this->assertEquals($expectedResult, $result);
    }

    /**
     * @param string $datetime
     * @param string $timezone
     * @param string $expectedDateTime
     *
     * @covers       Sugarcrm\Sugarcrm\Dav\Base\Helper\DateTimeHelper::davDateToSugar
     *
     * @dataProvider davDateToSugarProvider
     */
    public function testDavDateToSugar($datetime, $timezone, $expectedDateTime)
    {
        $helperMock = $this->getMockBuilder('Sugarcrm\Sugarcrm\Dav\Base\Helper\DateTimeHelper')
                           ->disableOriginalConstructor()
                           ->setMethods(null)
                           ->getMock();

        $calendarMock = $this->getMockBuilder('Sabre\VObject\Component\VCalendar')
                             ->disableOriginalConstructor()
                             ->setMethods(null)
                             ->getMock();

        $tz = new \DateTimeZone($timezone);
        $dt = new \DateTime($datetime, $tz);
        $dt->setTimeZone($tz);

        $dateTimeElement = $calendarMock->createProperty('DTSTART');
        $dateTimeElement->setDateTime($dt);

        $result = $helperMock->davDateToSugar($dateTimeElement);

        $this->assertEquals($expectedDateTime, $result);
    }
}
