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
