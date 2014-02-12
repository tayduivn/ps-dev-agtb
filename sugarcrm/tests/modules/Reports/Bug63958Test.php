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
* Copyright (C) 2004-2013 SugarCRM Inc. All rights reserved.
********************************************************************************/

require_once('modules/Reports/Report.php');

/**
* Bug #63958
* Use subselect team joins instead of inner joins for non-admin users
*
* @ticket 63958
*/
class Bug63958Test extends Sugar_PHPUnit_Framework_TestCase
{
    /**
* @group 63958
* @return void
*/
    public function testQueryTeamJoin()
    {
        $reportDefs = <<<DEFS
{"display_columns":[{"name":"full_name","label":"Name","table_key":"Accounts:contacts"}],"module":"Accounts","group_defs":[],"summary_columns":[],"report_name":"test report","do_round":1,"numerical_chart_column":"","numerical_chart_column_type":"","assigned_user_id":"seed_will_id","report_type":"tabular","full_table_list":{"self":{"value":"Accounts","module":"Accounts","label":"Accounts"},"Accounts:contacts":{"name":"Accounts > Contacts","parent":"self","link_def":{"name":"contacts","relationship_name":"accounts_contacts","bean_is_lhs":true,"link_type":"many","label":"Contacts","module":"Contacts","table_key":"Accounts:contacts"},"dependents":["display_cols_row_1"],"module":"Contacts","label":"Contacts"}},"filters_def":{"Filter_1":{"operator":"AND"}},"chart_type":"none"}
DEFS;

        $report = new Report($reportDefs);
        $report->run_summary_query();

        $this->assertContains('AND accounts.team_set_id IN', $report->where, 'Verify the team join in the WHERE clause');
        $this->assertNotEmpty($report->summary_result, 'Verify the query was valid by testing for summary results');
    }

    public function setUp()
    {
        SugarTestHelper::setUp('beanList');
        SugarTestHelper::setUp('beanFiles');

        // Execute this test as a non-admin user
        SugarTestHelper::setUp('current_user', array(true, 0));
    }

    public function tearDown()
    {
        SugarTestHelper::tearDown();
    }
}
