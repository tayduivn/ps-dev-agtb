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

namespace Sugarcrm\SugarcrmTestsUnit\Audit\Formatter;

use Sugarcrm\Sugarcrm\Audit\Formatter\Date;

/**
 *
 * @coversDefaultClass \Sugarcrm\Sugarcrm\Audit\Formatter\Email
 *
 */
class DateTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @covers ::formatRows
     * @dataProvider formatRowsProvider
     */
    public function testFormatRows($rows, $expectedDateCalls, $isoDates, $expectedOutput)
    {
        $tdMock = $this->createMock(\TimeDate::class);
        $tdMock->expects($this->exactly($expectedDateCalls))
            ->method('fromDbType')
            ->willReturn(new \DateTime());
        $tdMock->expects($this->exactly($expectedDateCalls))
            ->method('asIso')
            ->willReturnOnConsecutiveCalls(...$isoDates);

        $dateFormatter = new Date($tdMock);
        $dateFormatter->formatRows($rows);

        $this->assertSame($expectedOutput, $rows);
    }

    public function formatRowsProvider()
    {
        return [
            //Data set 1
            [
                //rows
                [
                    [
                        'field_name' => 'repeat_until',
                        'data_type' => 'date',
                        'before' => '2011-10-05',
                        'after' => '2018-02-27',
                    ],
                    [
                        'field_name' => 'date_end',
                        'data_type' => 'datetime',
                        'before' => null,
                        'after' => '2018-02-27T14:48:00.000Z',
                    ],
                    [
                        'field_name' => 'foo',
                        'data_type' => 'bool',
                        'before' => null,
                        'after' => true,
                    ],
                ],
                3,
                //email id=>address map
                [
                    '2011-10-05 IN ISO',
                    '2018-02-27 IN ISO',
                    '2018-02-27T14:48:00.000Z IN ISO',

                ],
                //Expected formated changes
                [
                    [
                        'field_name' => 'repeat_until',
                        'data_type' => 'date',
                        'before' => '2011-10-05 IN ISO',
                        'after' => '2018-02-27 IN ISO',
                    ],
                    [
                        'field_name' => 'date_end',
                        'data_type' => 'datetime',
                        'before' => null,
                        'after' => '2018-02-27T14:48:00.000Z IN ISO',
                    ],
                    [
                        'field_name' => 'foo',
                        'data_type' => 'bool',
                        'before' => null,
                        'after' => true,
                    ],
                ],
            ],
        ];
    }
}
