<?php
/*
 * By installing or using this file, you are confirming on behalf of the entity
 * subscribed to the SugarCRM Inc. product ("Company") that Company is bound by
 * the SugarCRM Inc. Master Subscription Agreement ("MSA"), which is viewable at:
 * http://www.sugarcrm.com/master-subscription-agreement
 *
 * If Company is not bound by the MSA, then by installing or using this file
 * you are agreeing unconditionally that Company will be bound by the MSA and
 * certifying that you have authority to bind Company accordingly.
 *
 * Copyright (C) 2004-2013 SugarCRM Inc. All rights reserved.
 */

require_once('include/generic/LayoutManager.php');
require_once('modules/Reports/Report.php');

/**
 * Test Days Before date filter
 */
class Bug60372Test extends Sugar_PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        SugarTestHelper::setUp('current_user');
        SugarTestHelper::setUp('beanFiles');
        SugarTestHelper::setUp('beanList');
        SugarTestHelper::setUp('timedate');
    }

    public function tearDown()
    {
        SugarTestHelper::tearDown();
    }

    /**
     * Test if days before filter returns proper query
     *
     * @param $daysBefore - Number of days before today
     * @param $expected - Expected generated day
     * @param $currentDate - In regards to current date
     *
     * @dataProvider filterDataProvider
     */
    public function testDateTimeFiscalQueryFilter($qualifier, $days, $expected, $currentDate)
    {
        global $timedate;
        $timedate->setNow($timedate->fromDb($currentDate));

        $layoutManager = new LayoutManager();
        $layoutManager->setAttribute('reporter', new Report());
        $SWFDT = new SugarWidgetFielddatetime($layoutManager);
        $layoutDef = array(
            'type' => 'datetime',
            'input_name0' => $days,
        );

        $result = $SWFDT->$qualifier($layoutDef);

        $this->assertContains($expected, $result, 'Query contains improper dates.');
    }

    public static function filterDataProvider()
    {
        return array(
            array(
                'queryFilterTP_last_n_days',
                5,
                "*>='2014-01-26 00:00:00' AND *<='2014-01-30 23:59:59",
                '2014-01-30 08:00:00'
            ),
            array(
                'queryFilterTP_next_n_days',
                2,
                "*>='2014-02-15 00:00:00' AND *<='2014-02-16 23:59:59'",
                '2014-02-15 07:00:00'
            ),
        );
    }
}
