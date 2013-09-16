<?php
//FILE SUGARCRM flav=pro ONLY

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
 * Copyright  2004-2013 SugarCRM Inc.  All rights reserved.
 */

require_once 'modules/Reports/Report.php';
require_once 'modules/Reports/sugarpdf/sugarpdf.summary.php';

class Bug38016Test extends Sugar_PHPUnit_Framework_TestCase
{
    protected static $fixturesPath;

    private $report;
    private $summaryView;

    public static function setUpBeforeClass()
    {
        parent::setUpBeforeClass();
        self::$fixturesPath = __DIR__ . '/Fixtures/';
    }

    protected function setUp()
    {
        $beanList = array();
        $beanFiles = array();
        require 'include/modules.php';
        $GLOBALS['beanList'] = $beanList;
        $GLOBALS['beanFiles'] = $beanFiles;
        $fixture = file_get_contents(self::$fixturesPath . get_class($this) . '.json');
        $this->report = new Report($fixture);
        $GLOBALS['module'] = 'Reports';
        $this->summaryView = new ReportsSugarpdfSummary();
        $this->summaryView->bean = & $this->report;
    }

    protected function tearDown()
    {
        unset($GLOBALS['module']);
        unset($GLOBALS['beanFiles']);
        unset($GLOBALS['beanList']);
    }

    public function testSummationQueryMadeWithoutCountColumn()
    {
        // FIXME we shouldn't be suppressing errors
        @$this->summaryView->display();
        $this->assertTrue(!empty($this->report->total_query));
    }
}
