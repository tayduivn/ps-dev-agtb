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
namespace Sugarcrm\SugarcrmTestsUnit\modules\Reports\clients\base\api;

use PHPUnit\Framework\TestCase;
use Sugarcrm\SugarcrmTestsUnit\TestReflection;

/**
 * @coversDefaultClass \ReportsApi
 */
class ReportsApiTest extends TestCase
{
    /**
     * @covers ::getGroupFilterFieldDef
     * @dataProvider providerTestGetGroupFilterFieldDef
     */
    public function testGetGroupFilterFieldDef($reportDef, $field, $expected)
    {
        $mockApi = $this->getReportsApiMock();
        $result = TestReflection::callProtectedMethod($mockApi, 'getGroupFilterFieldDef', [$reportDef, $field]);
        $this->assertSame($result, $expected);
    }

    public function providerTestGetGroupFilterFieldDef()
    {
        return [
            // industry
            [
                [
                    'group_defs' => [
                        [
                            'table_key' => 'self',
                            'name' => 'industry',
                            'type' => 'enum',
                        ],
                    ],
                ],
                'industry',
                [
                    'table_key' => 'self',
                    'name' => 'industry',
                    'type' => 'enum',
                ],
            ],
            // self:industry
            [
                [
                    'group_defs' => [
                        [
                            'table_key' => 'self',
                            'name' => 'industry',
                            'type' => 'enum',
                        ],
                    ],
                ],
                'self:industry',
                [
                    'table_key' => 'self',
                    'name' => 'industry',
                    'type' => 'enum',
                ],
            ],
            // Accounts:contacts:lead_source
            [
                [
                    'group_defs' => [
                        [
                            'table_key' => 'Accounts:contacts',
                            'name' => 'lead_source',
                            'type' => 'enum',
                        ],
                    ],
                ],
                'Accounts:contacts:lead_source',
                [
                    'table_key' => 'Accounts:contacts',
                    'name' => 'lead_source',
                    'type' => 'enum',
                ],
            ],
        ];
    }

    /**
     * @covers ::addGroupFilters
     * @dataProvider providerTestAddGroupFilters
     */
    public function testAddGroupFilters($reportDef, $groupFilters, $mockedFieldDef, $expected)
    {
        $mockApi = $this->getReportsApiMock(['getGroupFilterFieldDef']);
        $mockApi->method('getGroupFilterFieldDef')->willReturn($mockedFieldDef);
        $result = TestReflection::callProtectedMethod($mockApi, 'addGroupFilters', [$reportDef, $groupFilters]);
        $this->assertSame($result['filters_def']['Filter_1'], $expected);
    }

    public function providerTestAddGroupFilters()
    {
        return [
            // empty value
            [
                [
                    'group_defs' => [
                        [
                            'table_key' => 'self',
                            'name' => 'industry',
                            'type' => 'enum',
                        ],
                    ],
                    'filters_def' => [
                        'Filter_1' => [
                            'operator' => 'AND',
                        ],
                    ],
                ],
                [['industry' => '']],
                [
                    'table_key' => 'self',
                    'name' => 'industry',
                    'type' => 'enum',
                ],
                [
                    0 => [
                        'adhoc' => true,
                        'name' => 'industry',
                        'table_key' => 'self',
                        'qualifier_name' => 'empty',
                        'input_name0' => 'empty',
                        'input_name1' => 'on',
                    ],
                    'operator' => 'AND',
                ],
            ],
            // name
            [
                [
                    'group_defs' => [
                        [
                            'table_key' => 'self',
                            'name' => 'name',
                            'type' => 'name',
                        ],
                    ],
                    'filters_def' => [
                        'Filter_1' => [
                            'operator' => 'AND',
                        ],
                    ],
                ],
                [['name' => '123 Corp']],
                [
                    'table_key' => 'self',
                    'name' => 'name',
                    'type' => 'name',
                ],
                [
                    0 => [
                        'adhoc' => true,
                        'name' => 'name',
                        'table_key' => 'self',
                        'qualifier_name' => 'equals',
                        'input_name0' => '123 Corp',
                    ],
                    'operator' => 'AND',
                ],
            ],
            // enum. no report filters
            [
                [
                    'group_defs' => [
                        [
                            'table_key' => 'self',
                            'name' => 'industry',
                            'type' => 'enum',
                        ],
                    ],
                    'filters_def' => [
                        'Filter_1' => [
                            'operator' => 'AND',
                        ],
                    ],
                ],
                [['industry' => 'Engineering']],
                [
                    'table_key' => 'self',
                    'name' => 'industry',
                    'type' => 'enum',
                ],
                [
                    0 => [
                        'adhoc' => true,
                        'name' => 'industry',
                        'table_key' => 'self',
                        'qualifier_name' => 'one_of',
                        'input_name0' => ['Engineering'],
                    ],
                    'operator' => 'AND',
                ],
            ],
            // enum. report filter exists
            [
                [
                    'group_defs' => [
                        [
                            'table_key' => 'self',
                            'name' => 'industry',
                            'type' => 'enum',
                        ],
                    ],
                    'filters_def' => [
                        'Filter_1' => [
                            'operator' => 'AND',
                            0 => [
                                'name' => 'name',
                                'table_key' => 'self',
                                'qualifier_name' => 'starts_with',
                                'input_name0' => '_Test_Account',
                                'input_name1' => 'on',
                            ],
                        ],
                    ],
                ],
                [['industry' => 'Engineering']],
                [
                    'table_key' => 'self',
                    'name' => 'industry',
                    'type' => 'enum',
                ],
                [
                    0 => [
                        'operator' => 'AND',
                        0 => [
                            'name' => 'name',
                            'table_key' => 'self',
                            'qualifier_name' => 'starts_with',
                            'input_name0' => '_Test_Account',
                            'input_name1' => 'on',
                        ],
                    ],
                    1 => [
                        [
                            'adhoc' => true,
                            'name' => 'industry',
                            'table_key' => 'self',
                            'qualifier_name' => 'one_of',
                            'input_name0' => ['Engineering'],
                        ],
                        'operator' => 'AND',
                    ],
                    'operator' => 'AND',
                ],
            ],
            // date
            [
                [
                    'group_defs' => [
                        [
                            'table_key' => 'self',
                            'name' => 'date_closed',
                            'type' => 'date',
                        ],
                    ],
                ],
                [['date_closed' => '2017-08-22']],
                [
                    'table_key' => 'self',
                    'name' => 'date_closed',
                    'type' => 'date',
                ],
                [
                    0 => [
                        'adhoc' => true,
                        'name' => 'date_closed',
                        'table_key' => 'self',
                        'qualifier_name' => 'on',
                        'input_name0' => '2017-08-22',
                    ],
                    'operator' => 'AND',
                ],
            ],
            // datetime
            [
                [
                    'group_defs' => [
                        [
                            'table_key' => 'self',
                            'name' => 'date_entered',
                            'type' => 'datetime',
                            'column_function' => 'month',
                            'qualifier_name' => 'month',
                        ],
                    ],
                ],
                [['date_entered' => ['2017-08-01', '2017-08-31']]],
                [
                    'table_key' => 'self',
                    'name' => 'date_entered',
                    'type' => 'datetime',
                    'column_function' => 'month',
                    'qualifier_name' => 'month',
                ],
                [
                    0 => [
                        'adhoc' => true,
                        'name' => 'date_entered',
                        'table_key' => 'self',
                        'qualifier_name' => 'between_dates',
                        'input_name0' => '2017-08-01',
                        'input_name1' => '2017-08-31',
                    ],
                    'operator' => 'AND',
                ],
            ],
            // radioenum
            [
                [
                    'group_defs' => [
                        [
                            'table_key' => 'self',
                            'name' => 'quote_type',
                            'type' => 'radioenum',
                        ],
                    ],
                    'filters_def' => [
                        'Filter_1' => [
                            'operator' => 'AND',
                        ],
                    ],
                ],
                [['quote_type' => 'Quotes']],
                [
                    'table_key' => 'self',
                    'name' => 'quote_type',
                    'type' => 'radioenum',
                ],
                [
                    0 => [
                        'adhoc' => true,
                        'name' => 'quote_type',
                        'table_key' => 'self',
                        'qualifier_name' => 'is',
                        'input_name0' => 'Quotes',
                    ],
                    'operator' => 'AND',
                ],
            ],
            // id
            [
                [
                    'group_defs' => [
                        [
                            'table_key' => 'self',
                            'name' => 'campaign_id',
                            'type' => 'id',
                        ],
                    ],
                    'filters_def' => [
                        'Filter_1' => [
                            'operator' => 'AND',
                        ],
                    ],
                ],
                [['campaign_id' => 'd3c7d650-882b-11e7-8f59-f45c898a3ce7']],
                [
                    'table_key' => 'self',
                    'name' => 'campaign_id',
                    'type' => 'id',
                ],
                [
                    0 => [
                        'adhoc' => true,
                        'name' => 'campaign_id',
                        'table_key' => 'self',
                        'qualifier_name' => 'is',
                        'input_name0' => 'd3c7d650-882b-11e7-8f59-f45c898a3ce7',
                    ],
                    'operator' => 'AND',
                ],
            ],
        ];
    }

    /**
     * @covers ::getPagination
     * @dataProvider providerTestGetPagination
     */
    public function testGetPagination($args, $mockLimit, $expected)
    {
        $mockApi = $this->getReportsApiMock(['checkMaxListLimit']);
        $mockApi->method('checkMaxListLimit')->willReturn($mockLimit);
        $result = TestReflection::callProtectedMethod($mockApi, 'getPagination', [null, $args]);
        $this->assertSame($result, $expected);
    }

    public function providerTestGetPagination()
    {
        return [
            [['offset' => 0, 'max_num' => 20], 20, [0, 20]],
            [['offset' => 20, 'max_num' => 20], 20, [20, 20]],
            [['max_num' => 20], 20, [0, 20]],
            [['offset' => 0], -1, [0, -1]],
            [['offset' => -1], -1, [0, -1]],
            [['offset' => 0, 'max_num' => -1], -1, [0, -1]],
        ];
    }

    /**
     * @param null|array $methods
     * @return \ReportsApi
     */
    protected function getReportsApiMock($methods = null)
    {
        return $this->getMockBuilder('ReportsApi')
            ->disableOriginalConstructor()
            ->setMethods($methods)
            ->getMock();
    }
}
