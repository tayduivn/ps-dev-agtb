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
        $result = TestReflection::callProtectedMethod($mockApi, 'getGroupFilterFieldDef', array($reportDef, $field));
        $this->assertSame($result, $expected);
    }

    public function providerTestGetGroupFilterFieldDef()
    {
        return array(
            // industry
            array(
                array(
                    'group_defs' => array(
                        array(
                            'table_key' => 'self',
                            'name' => 'industry',
                            'type' => 'enum',
                        ),
                    ),
                ),
                'industry',
                array(
                    'table_key' => 'self',
                    'name' => 'industry',
                    'type' => 'enum',
                ),
            ),
            // self:industry
            array(
                array(
                    'group_defs' => array(
                        array(
                            'table_key' => 'self',
                            'name' => 'industry',
                            'type' => 'enum',
                        ),
                    ),
                ),
                'self:industry',
                array(
                    'table_key' => 'self',
                    'name' => 'industry',
                    'type' => 'enum',
                ),
            ),
            // Accounts:contacts:lead_source
            array(
                array(
                    'group_defs' => array(
                        array(
                            'table_key' => 'Accounts:contacts',
                            'name' => 'lead_source',
                            'type' => 'enum',
                        ),
                    ),
                ),
                'Accounts:contacts:lead_source',
                array(
                    'table_key' => 'Accounts:contacts',
                    'name' => 'lead_source',
                    'type' => 'enum',
                ),
            ),
        );
    }

    /**
     * @covers ::addGroupFilters
     * @dataProvider providerTestAddGroupFilters
     */
    public function testAddGroupFilters($reportDef, $groupFilters, $mockedFieldDef, $expected)
    {
        $mockApi = $this->getReportsApiMock(array('getGroupFilterFieldDef'));
        $mockApi->method('getGroupFilterFieldDef')->willReturn($mockedFieldDef);
        $result = TestReflection::callProtectedMethod($mockApi, 'addGroupFilters', array($reportDef, $groupFilters));
        $this->assertSame($result['filters_def']['Filter_1'], $expected);
    }

    public function providerTestAddGroupFilters()
    {
        return array(
            // empty value
            array(
                array(
                    'group_defs' => array(
                        array(
                            'table_key' => 'self',
                            'name' => 'industry',
                            'type' => 'enum',
                        ),
                    ),
                    'filters_def' => array(
                        'Filter_1' => array(
                            'operator' => 'AND',
                        ),
                    ),
                ),
                array(array('industry' => '')),
                array(
                    'table_key' => 'self',
                    'name' => 'industry',
                    'type' => 'enum',
                ),
                array(
                    0 => array(
                        'adhoc' => true,
                        'name' => 'industry',
                        'table_key' => 'self',
                        'qualifier_name' => 'empty',
                        'input_name0' => 'empty',
                        'input_name1' => 'on',
                    ),
                    'operator' => 'AND',
                ),
            ),
            // name
            array(
                array(
                    'group_defs' => array(
                        array(
                            'table_key' => 'self',
                            'name' => 'name',
                            'type' => 'name',
                        ),
                    ),
                    'filters_def' => array(
                        'Filter_1' => array(
                            'operator' => 'AND',
                        ),
                    ),
                ),
                array(array('name' => '123 Corp')),
                array(
                    'table_key' => 'self',
                    'name' => 'name',
                    'type' => 'name',
                ),
                array(
                    0 => array(
                        'adhoc' => true,
                        'name' => 'name',
                        'table_key' => 'self',
                        'qualifier_name' => 'equals',
                        'input_name0' => '123 Corp',
                    ),
                    'operator' => 'AND',
                ),
            ),
            // enum. no report filters
            array(
                array(
                    'group_defs' => array(
                        array(
                            'table_key' => 'self',
                            'name' => 'industry',
                            'type' => 'enum',
                        ),
                    ),
                    'filters_def' => array(
                        'Filter_1' => array(
                            'operator' => 'AND',
                        ),
                    ),
                ),
                array(array('industry' => 'Engineering')),
                array(
                    'table_key' => 'self',
                    'name' => 'industry',
                    'type' => 'enum',
                ),
                array(
                    0 => array(
                        'adhoc' => true,
                        'name' => 'industry',
                        'table_key' => 'self',
                        'qualifier_name' => 'one_of',
                        'input_name0' => array('Engineering'),
                    ),
                    'operator' => 'AND',
                ),
            ),
            // enum. report filter exists
            array(
                array(
                    'group_defs' => array(
                        array(
                            'table_key' => 'self',
                            'name' => 'industry',
                            'type' => 'enum',
                        ),
                    ),
                    'filters_def' => array(
                        'Filter_1' => array(
                            'operator' => 'AND',
                            0 => array(
                                'name' => 'name',
                                'table_key' => 'self',
                                'qualifier_name' => 'starts_with',
                                'input_name0' => '_Test_Account',
                                'input_name1' => 'on',
                            ),
                        ),
                    ),
                ),
                array(array('industry' => 'Engineering')),
                array(
                    'table_key' => 'self',
                    'name' => 'industry',
                    'type' => 'enum',
                ),
                array(
                    0 => array(
                        'operator' => 'AND',
                        0 => array(
                            'name' => 'name',
                            'table_key' => 'self',
                            'qualifier_name' => 'starts_with',
                            'input_name0' => '_Test_Account',
                            'input_name1' => 'on',
                        ),
                    ),
                    1 => array(
                        array(
                            'adhoc' => true,
                            'name' => 'industry',
                            'table_key' => 'self',
                            'qualifier_name' => 'one_of',
                            'input_name0' => array('Engineering'),
                        ),
                        'operator' => 'AND',
                    ),
                    'operator' => 'AND',
                ),
            ),
            // date
            array(
                array(
                    'group_defs' => array(
                        array(
                            'table_key' => 'self',
                            'name' => 'date_closed',
                            'type' => 'date',
                        ),
                    ),
                ),
                array(array('date_closed' => '2017-08-22')),
                array(
                    'table_key' => 'self',
                    'name' => 'date_closed',
                    'type' => 'date',
                ),
                array(
                    0 => array(
                        'adhoc' => true,
                        'name' => 'date_closed',
                        'table_key' => 'self',
                        'qualifier_name' => 'on',
                        'input_name0' => '2017-08-22',
                    ),
                    'operator' => 'AND',
                ),
            ),
            // datetime
            array(
                array(
                    'group_defs' => array(
                        array(
                            'table_key' => 'self',
                            'name' => 'date_entered',
                            'type' => 'datetime',
                            'column_function' => 'month',
                            'qualifier_name' => 'month',
                        ),
                    ),
                ),
                array(array('date_entered' => array('2017-08-01', '2017-08-31'))),
                array(
                    'table_key' => 'self',
                    'name' => 'date_entered',
                    'type' => 'datetime',
                    'column_function' => 'month',
                    'qualifier_name' => 'month',
                ),
                array(
                    0 => array(
                        'adhoc' => true,
                        'name' => 'date_entered',
                        'table_key' => 'self',
                        'qualifier_name' => 'between_dates',
                        'input_name0' => '2017-08-01',
                        'input_name1' => '2017-08-31',
                    ),
                    'operator' => 'AND',
                ),
            ),
            // radioenum
            array(
                array(
                    'group_defs' => array(
                        array(
                            'table_key' => 'self',
                            'name' => 'quote_type',
                            'type' => 'radioenum',
                        ),
                    ),
                    'filters_def' => array(
                        'Filter_1' => array(
                            'operator' => 'AND',
                        ),
                    ),
                ),
                array(array('quote_type' => 'Quotes')),
                array(
                    'table_key' => 'self',
                    'name' => 'quote_type',
                    'type' => 'radioenum',
                ),
                array(
                    0 => array(
                        'adhoc' => true,
                        'name' => 'quote_type',
                        'table_key' => 'self',
                        'qualifier_name' => 'is',
                        'input_name0' => 'Quotes',
                    ),
                    'operator' => 'AND',
                ),
            ),
            // id
            array(
                array(
                    'group_defs' => array(
                        array(
                            'table_key' => 'self',
                            'name' => 'campaign_id',
                            'type' => 'id',
                        ),
                    ),
                    'filters_def' => array(
                        'Filter_1' => array(
                            'operator' => 'AND',
                        ),
                    ),
                ),
                array(array('campaign_id' => 'd3c7d650-882b-11e7-8f59-f45c898a3ce7')),
                array(
                    'table_key' => 'self',
                    'name' => 'campaign_id',
                    'type' => 'id',
                ),
                array(
                    0 => array(
                        'adhoc' => true,
                        'name' => 'campaign_id',
                        'table_key' => 'self',
                        'qualifier_name' => 'is',
                        'input_name0' => 'd3c7d650-882b-11e7-8f59-f45c898a3ce7',
                    ),
                    'operator' => 'AND',
                ),
            ),
        );
    }

    /**
     * @covers ::getPagination
     * @dataProvider providerTestGetPagination
     */
    public function testGetPagination($args, $mockLimit, $expected)
    {
        $mockApi = $this->getReportsApiMock(array('checkMaxListLimit'));
        $mockApi->method('checkMaxListLimit')->willReturn($mockLimit);
        $result = TestReflection::callProtectedMethod($mockApi, 'getPagination', array(null, $args));
        $this->assertSame($result, $expected);
    }

    public function providerTestGetPagination()
    {
        return array(
            array(array('offset' => 0, 'max_num' => 20), 20, array(0, 20)),
            array(array('offset' => 20, 'max_num' => 20), 20, array(20, 20)),
            array(array('max_num' => 20), 20, array(0, 20)),
            array(array('offset' => 0), -1, array(0, -1)),
            array(array('offset' => -1), -1, array(0, -1)),
            array(array('offset' => 0, 'max_num' => -1), -1, array(0, -1)),
        );
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
