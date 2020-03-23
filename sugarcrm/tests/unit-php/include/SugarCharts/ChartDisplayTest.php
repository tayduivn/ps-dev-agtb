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

namespace Sugarcrm\SugarcrmTestsUnit\inc\SugarCharts;

use PHPUnit\Framework\TestCase;
use Sugarcrm\SugarcrmTestsUnit\TestReflection;

/**
 * @coversDefaultClass \ChartDisplay
 */
class ChartDisplayTest extends TestCase
{
    public function shouldUnformatProvider()
    {
        return [
            ['123.456', 'currency', false],
            ['123,456', 'currency', true],
            [123.456, 'currency', false],
            ['123.456', 'float', true],
            ['123,456', 'float', true],
            [123.456, 'float', false],
            ['123.456', 'integer', true],
            ['123,456', 'integer', true],
            [123456, 'integer', false],
        ];
    }

    /**
     * @covers       ::shouldUnformat
     * @dataProvider shouldUnformatProvider
     *
     * @param mixed $val
     * @param string $type
     * @param boolean $expected
     */
    public function testShouldUnformat($val, $type, $expected)
    {
        $chart = $this->createPartialMock(\ChartDisplay::class, []);

        $reporter = new \stdClass;
        $reporter->report_def = ['numerical_chart_column_type' => $type];
        TestReflection::setProtectedValue($chart, 'reporter', $reporter);

        $result = TestReflection::callProtectedMethod($chart, 'shouldUnformat', [$val]);

        $this->assertEquals($expected, $result);
    }
}
