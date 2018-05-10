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

namespace Sugarcrm\SugarcrmTestsUnit\modules\ReportSchedules;

use PHPUnit\Framework\TestCase;
use Sugarcrm\SugarcrmTestsUnit\TestReflection;

/**
 * @coversDefaultClass \ReportSchedule
 */
class ReportScheduleTest extends TestCase
{
    /**
     * @covers ::getQuery
     * @dataProvider providerTestGetQuery
     */
    public function testGetQuery($scheduleType, $expected)
    {
        $mockObject = $this->getReportsScheduleMock();
        $result = TestReflection::callProtectedMethod($mockObject, 'getQuery', array('test_id', $scheduleType));
        call_user_func(array($this, $expected['assert']), $expected['subString'], $result);
    }

    /**
     * @return array
     */
    public function providerTestGetQuery()
    {
        return array(
            // pro
            array(
                'pro',
                array(
                    'assert' => 'assertContains',
                    'subString' => 'reportschedules_users',
                ),
            ),
            // ent
            array(
                'ent',
                array(
                    'assert' => 'assertNotContains',
                    'subString' => 'reportschedules_users',
                ),
            ),
        );
    }

    /**
     * @param null|array $methods
     * @return \ReportsSchedule
     */
    protected function getReportsScheduleMock($methods = null)
    {
        return $this->getMockBuilder('ReportSchedule')
            ->disableOriginalConstructor()
            ->setMethods($methods)
            ->getMock();
    }
}
