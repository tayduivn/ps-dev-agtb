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
 * Copyright (C) 2004-2014 SugarCRM Inc. All rights reserved.
 */
class SavedReportTest extends Sugar_PHPUnit_Framework_TestCase
{
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
    }

    protected function tearDown()
    {
        unset($GLOBALS['report_modules']);
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
}
