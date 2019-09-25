<?php declare(strict_types=1);
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

namespace Sugarcrm\SugarcrmTestUnit\inc\SugarObjects\templates\issue;

use PHPUnit\Framework\TestCase;

/**
 * Class IssueTest
 * @coversDefaultClass \Issue
 */
class IssueTest extends TestCase
{
    /**
     * @covers ::isResolved
     * @dataProvider providerIsResolved
     */
    public function testIsResolved(?string $status, bool $expected)
    {
        $bean = $this->createMockIssue();
        $bean->status = $status;
        $this->assertEquals($expected, $bean->isResolved());
    }

    public function providerIsResolved(): array
    {
        return [
            ['Closed', true],
            ['Rejected', true],
            ['Duplicate', true],
            ['New', false],
            ['Pending Input', false],
            ['Assigned', false],
            [null, false],
        ];
    }

    //BEGIN SUGARCRM flav=ent ONLY
    /**
     * @covers ::isNewlyResolved
     * @dataProvider providerIsNewlyResolved
     */
    public function testIsNewlyResolved(bool $isResolved, string $status, ?array $fetchedStatus, bool $expected)
    {
        $bean = $this->createMockIssue(['isResolved']);
        $bean->expects($this->once())
            ->method('isResolved')
            ->willReturn($isResolved);
        $bean->status = $status;
        $bean->fetched_row = $fetchedStatus;
        $this->assertEquals($expected, $bean->isNewlyResolved());
    }

    public function providerIsNewlyResolved(): array
    {
        return [
            [false, 'New', array('status' => 'Irrelevant'), false],
            [true, 'Closed', array('status' => 'Closed'), false],
            [true, 'Closed', array('status' => 'Rejected'), false],
            [true, 'Closed', array('status' => 'New'), true],
            [true, 'Closed', array(), true],
            [true, 'Closed', null, true],
        ];
    }

    /**
     * @covers ::calculateResolutionTime
     * @dataProvider providerCalculateResolutionTime
     */
    public function testCalculateResolutionTime(string $resolvedDatetime, string $dateEntered, int $expectedMinutes)
    {
        $bean = $this->createMockIssue();
        $bean->resolved_datetime = $resolvedDatetime;
        $bean->date_entered = $dateEntered;
        $this->assertEquals($expectedMinutes, $bean->calculateResolutionTime());
    }

    public function providerCalculateResolutionTime(): array
    {
        return [
            ['2019-01-05 05:00:00', '2019-01-05 04:30:00', 30],
            ['2019-01-05 05:00:00', '2019-01-05 04:00:00', 60],
            ['2019-01-05 05:00:00', '2019-01-05 03:30:00', 90],
            ['2019-01-05 05:00:00', '2019-01-05 04:55:59', 5], // 4 min, 1 second - rounded up
            ['2019-01-05 05:00:00', '2019-01-05 05:00:00', 0],
        ];
    }

    /**
     * @covers ::calculateResolutionTime
     * @dataProvider providerCalculateResolutionTimeWhenTimeAlreadyDetermined
     */
    public function testCalculateResolutionTimeWhenTimeAlreadyDetermined(string $timeToResolution, int $expectedTime)
    {
        $bean = $this->createMockIssue();
        $bean->time_to_resolution = $timeToResolution;
        $this->assertEquals($expectedTime, $bean->calculateResolutionTime());
    }

    public function providerCalculateResolutionTimeWhenTimeAlreadyDetermined(): array
    {
        return [
            ['7', 7],
            ['6.2', 7], // shouldn't happen, but checking anyways
            ['0', 0],
        ];
    }

    /**
     * @covers ::calculateResolutionTime
     */
    public function testCalculateResolutionTimeThrowsWhenNegative()
    {
        $bean = $this->createMockIssue();
        $bean->resolved_datetime = '1975-01-05 05:00:00';
        $bean->date_entered = '2019-01-05 05:00:00'; // way way later than resolution time
        $bean->object_name = 'Issue';

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Issue cannot have a resolution time earlier than its creation time');

        $bean->calculateResolutionTime();
    }

    public function providerGetHoursBetween()
    {
        return [
            [
                'start' => '9/1/2019 08:00:00',
                'end' => '9/4/2019 14:00:00',
                'expect' => 78,
            ],
            [
                'start' => '9/4/2019 08:00:00',
                'end' => '9/1/2019 14:00:00',
                'expect' => 0,
            ],
            [
                'start' => '8/1/2019 08:00:00',
                'end' => '8/4/2019 14:00:00',
                'expect' => 78,
            ],
            [
                'start' => '2019-08-30 09:15:00',
                'end' => '2019-09-04 17:30:00',
                'expect' => 128.25,
            ],
            [
                'start' => '9/25/2019 00:00:00',
                'end' => '9/25/2019 17:15:00',
                'expect' => 17.25,
            ],
            [
                'start' => '9/25/2019 08:00:00',
                'end' => '9/25/2019 08:00:00',
                'expect' => 0,
            ],
        ];
    }

    /**
     * @covers ::getHoursBetween
     * @dataProvider providerGetHoursBetween
     */
    public function testGetHoursBetween(string $start, string $end, float $expect)
    {
        $startDT = new \SugarDateTime($start);
        $endDT = new \SugarDateTime($end);

        $bean = $this->createMockIssue();
        $actual = $bean->getHoursBetween($startDT, $endDT);
        $this->assertEquals($expect, $actual['calendarHours']);
    }
    //END SUGARCRM flav=ent ONLY

    public function createMockIssue(array $methods = [])
    {
        return $this->createPartialMock(\Issue::class, $methods);
    }
}
