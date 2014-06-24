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

require_once('include/generic/LayoutManager.php');
require_once('modules/Reports/Report.php');

/**
 * Test Quarter filters for report date/time fields
 *
 * @author avucinic@sugarcrm.com
 */
class Bug63814Test extends Sugar_PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        SugarTestHelper::setUp('current_user');
        SugarTestHelper::setUp('beanFiles');
        SugarTestHelper::setUp('beanList');
    }

    public function tearDown()
    {
        SugarTestOpportunityUtilities::removeAllCreatedOpportunities();
        SugarTestHelper::tearDown();
    }

    /**
     * Test if fiscal query filters for DateTime type fields are working properly
     *
     * @param $date - date for which to to find the quarter
     * @param $modifyFilter - Modification to start/end date
     * @param $expectedStart - Expected start date in query
     * @param $expectedEnd - Expected end date in query
     * @param $timezone - User timezone
     *
     * @dataProvider filterDataProvider
     */
    public function testDateTimeFiscalQueryFilter($date, $type, $modifyFilter, $expectedStart, $expectedEnd, $timezone)
    {
        $GLOBALS['current_user']->setPreference('timezone', $timezone);

        $layoutManager = new LayoutManager();
        $layoutManager->setAttribute('reporter', new Report());
        $SWFDT = new SugarWidgetFielddatetime63814Test($layoutManager);
        $layoutDef = array(
            'qualifier_name' => 'quarter',
            'type' => $type
        );

        $result = $SWFDT->getQuarterFilter($layoutDef, $modifyFilter, $date);

        $this->assertContains($expectedStart, $result, 'Greater than part of query generated incorrectly.');
        $this->assertContains($expectedEnd, $result, 'Lower than part of query generated incorrectly.');
    }

    public static function filterDataProvider()
    {
        return array(
            array(
                '2013-05-05',
                'datetime',
                '',
                ">='2013-04-01 07:00:00'",
                "<='2013-07-01 06:59:59'",
                'America/Los_Angeles'
            ),
            array(
                '1987-01-01',
                'datetime',
                '+3 month',
                ">='1987-03-31 21:00:00'",
                "<='1987-06-30 20:59:59'",
                'Europe/Helsinki'
            ),
            array(
                '2013-09-08',
                'datetime',
                '-3 month',
                ">='2013-04-01 00:00:00'",
                "<='2013-06-30 23:59:59'",
                'UTC'
            ),
            array(
                '2013-05-05',
                'date',
                '',
                ">='2013-04-01 00:00:00'",
                "<='2013-06-30 23:59:59'",
                'America/Los_Angeles'
            ),
            array(
                '1987-01-01',
                'date',
                '+3 month',
                ">='1987-04-01 00:00:00'",
                "<='1987-06-30 23:59:59'",
                'Europe/Helsinki'
            ),
            array(
                '2013-09-08',
                'date',
                '-3 month',
                ">='2013-04-01 00:00:00'",
                "<='2013-06-30 23:59:59'",
                'UTC'
            ),
        );
    }
}

/**
 * Helper class for testing getQuarterFilter() method
 */
class SugarWidgetFielddatetime63814Test extends SugarWidgetFielddatetime
{
    public function getQuarterFilter($layout_def, $modifyFilter, $date = '')
    {
        return parent::getQuarterFilter($layout_def, $modifyFilter, $date);
    }
}
