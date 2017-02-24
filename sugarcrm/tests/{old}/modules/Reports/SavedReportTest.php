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
class SavedReportTest extends Sugar_PHPUnit_Framework_TestCase
{
    protected $dbMock = null;

    protected function setUp()
    {
        parent::setUp();
        SugarTestHelper::setUp('beanFiles');
        SugarTestHelper::setUp('beanList');
        SugarTestHelper::setUp('app_list_strings');
        global $moduleList, $modListHeader, $app_list_strings;
        require 'config.php';
        require 'include/modules.php';
        require_once 'modules/Reports/config.php';
        $GLOBALS['report_modules'] = getAllowedReportModules($modListHeader);
        $this->dbMock = SugarTestHelper::setUp('mock_db');
    }

    protected function tearDown()
    {
        unset($GLOBALS['report_modules']);
        SugarTestHelper::tearDown('mock_db');
        SugarTestHelper::tearDown();
        parent::tearDown();
    }

    /**
     * Make sure that the array returned is a subset of `GLOBALS['report_modules']`
     * and contain values from `$app_list_strings['moduleList']`
     */
    public function test_getModulesDropdown()
    {
        global $app_list_strings;
        $allowed_modules = getModulesDropdown();
        foreach ($allowed_modules as $key => $val) {
            $this->assertArrayHasKey($key, $GLOBALS['report_modules']);
            $this->assertEquals($val, $app_list_strings['moduleList'][$key]);
        }
    }

    /**
     * Checks if last run date is on the bean
     */
    public function testLastRunDate()
    {
        $bean = BeanFactory::getBean('Reports');
        $this->assertArrayHasKey('last_run_date', $bean->field_defs);
    }

    /**
     * Checks if last run date is on the bean
     */
    public function testNextRun()
    {
        $bean = BeanFactory::getBean('Reports');
        $this->assertArrayHasKey('next_run', $bean->field_defs);
    }

    /**
     * to test and ensure the report object is an instance of basic
     */
    public function testReportBeanType()
    {
        $report = new SavedReport();
        $this->assertInstanceOf('Basic', $report, 'report should be a Basic instance');
    }

    /**
     * Data provider for testReportBeanVardefs()
     * @return array reset, type, name
     */
    public function reportBeanVardefsProvider()
    {
        return array(
            // basic
            array('fields', 'description'),
            array('indices' , 'id'),
            array('relationships' , 'reports_modified_user'),

            // assignable
            array('fields', 'assigned_user_id'),
            array('indices' , 'assigned_user_id'),
            array('relationships' , 'reports_assigned_user'),

            // reports
            array('fields', 'module'),
            array('indices' , 'idx_savedreport_module'),
            array('relationships' , 'reports_last_run_date'),

            // team_security
            array('fields', 'team_id'),
            array('indices' , 'team_set_saved_reports'),
            array('relationships' , 'reports_team_count_relationship'),
        );
    }

    /**
     * to test that the definitions from various templates exist in the report dictionary
     * @dataProvider reportBeanVardefsProvider
     */
    public function testReportBeanVardefs($type, $name)
    {
        $def = $this->getReportVardef();
        $this->assertArrayHasKey($name, $def[$type], 'Missing ' . $type . ':' . $name);
    }

    /**
     * To get vardefs of Reports
     * @return array
     */
    protected function getReportVardef()
    {
        static $def = null;
        if (empty($def)) {
            global $dictionary;
            unset($dictionary['SavedReport']);
            require 'modules/Reports/vardefs.php';
            $def = $dictionary['SavedReport'];
        }
        return $def;
    }
}

