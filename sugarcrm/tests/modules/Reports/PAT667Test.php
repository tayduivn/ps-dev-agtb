<?php
/*********************************************************************************
 * By installing or using this file, you are confirming on behalf of the entity
 * subscribed to the SugarCRM Inc. product ("Company") that Company is bound by
 * the SugarCRM Inc. Master Subscription Agreement (“MSA”), which is viewable at:
 * http://www.sugarcrm.com/master-subscription-agreement
 *
 * If Company is not bound by the MSA, then by installing or using this file
 * you are agreeing unconditionally that Company will be bound by the MSA and
 * certifying that you have authority to bind Company accordingly.
 *
 * Copyright (C) 2004-2014 SugarCRM Inc.  All rights reserved.
 ********************************************************************************/

require_once 'modules/Reports/Report.php';

/**
 * Filtering Report on Multiselect field with "Is One Of" returns "false positives"
 * @ticket PAT-667
 * @author bsitnikovski@sugarcrm.com
 */
class BugPAT667Test extends Sugar_PHPUnit_Framework_TestCase
{
    private $report;
    private static $custom_field_def = array(
        'name'        => 'test_bugPAT667',
        'type'        => 'multienum',
        'module'      => 'ModuleBuilder',
        'view_module' => 'Accounts',
        'options'     => 'aaa_list',
        'default'     => '^Consultants^,^International Consultants^',
    );

    public function setUp()
    {
        if ($GLOBALS['db']->dbType == 'oci8')
        {
            $this->markTestSkipped('Oracle is skipped; need to revisit it later');
        }

        SugarTestHelper::setUp('current_user', array(true, 1));

        // Create the custom field
        $mbc = new ModuleBuilderController();
        $_REQUEST = self::$custom_field_def;
        $mbc->action_saveField();
        // Update field name, all custom field have _c appended
        self::$custom_field_def['name'] .= '_c';

        $this->report = new Report();
        $this->report->layout_manager->setAttribute("context", "Filter");
    }

    public function tearDown()
    {
        if ($GLOBALS['db']->dbType == 'oci8')
        {
            $this->markTestSkipped('Oracle is skipped; need to revisit it later');
        }

        $mbc = new ModuleBuilderController();

        // Delete the custom field
        $_REQUEST = self::$custom_field_def;
        $mbc->action_DeleteField();
    }

    public function testReportsRelatedField()
    {
        $data = array(
            "operator" => "AND",
            0 => array(
                "name" => "test_bugPAT667_c",
                "table_key" => "self",
                "qualifier_name" => "one_of",
                "input_name0" => array("Consultants")
            )
        );

        $expected = "(((accounts_cstm.test_bugPAT667_c LIKE '%^Consultants^%')))";

        $this->report->filtersIterate($data, $res);
        $this->assertEquals($res, $expected);
    }
}
