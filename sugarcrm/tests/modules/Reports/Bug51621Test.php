<?php
/*********************************************************************************
 * The contents of this file are subject to the SugarCRM Professional End User
 * License Agreement ("License") which can be viewed at
 * http://www.sugarcrm.com/EULA.  By installing or using this file, You have
 * unconditionally agreed to the terms and conditions of the License, and You may
 * not use this file except in compliance with the License. Under the terms of the
 * license, You shall not, among other things: 1) sublicense, resell, rent, lease,
 * redistribute, assign or otherwise transfer Your rights to the Software, and 2)
 * use the Software for timesharing or service bureau purposes such as hosting the
 * Software for commercial gain and/or for the benefit of a third party.  Use of
 * the Software may be subject to applicable fees and any use of the Software
 * without first paying applicable fees is strictly prohibited.  You do not have
 * the right to remove SugarCRM copyrights from the source code or user interface.
 * All copies of the Covered Code must include on each user interface screen:
 * (i) the "Powered by SugarCRM" logo and (ii) the SugarCRM copyright notice
 * in the same form as they appear in the distribution.  See full license for
 * requirements.  Your Warranty, Limitations of liability and Indemnity are
 * expressly stated in the License.  Please refer to the License for the specific
 * language governing these rights and limitations under the License.
 * Portions created by SugarCRM are Copyright (C) 2004 SugarCRM, Inc.;
 * All Rights Reserved.
 ********************************************************************************/

/**
 * @group Bug51621
 */
class Bug51621Test extends Sugar_PHPUnit_Framework_TestCase
{

    public function setUp()
    {
        $GLOBALS['current_user'] = SugarTestUserUtilities::createAnonymousUser();
    }

    public function tearDown()
    {
        unset($GLOBALS['current_user']);
        SugarTestUserUtilities::removeAllCreatedAnonymousUsers();
    }

    /**
     * @dataProvider savedReportContentTestData
     * @param $dirtyContent test json string of dirty content going in
     * @param $cleanedContent expected json string of clean content coming out
     */
    public function testCleanBeanForSavedReportDoesNotCorruptReportContents($dirtyContent, $cleanedContent) {
        $report = new SavedReport();
        $report->content = $dirtyContent;
        $report->cleanBean();
        $this->assertSame($cleanedContent, $report->content);
    }

    /**
     * @return array
     */
    public function savedReportContentTestData() {
        return array(
            array('{"display_columns":[{"name":"billing_address_city","label":"Billing City","table_key":"self"}],"module":"Accounts","group_defs":[],"summary_columns":[],"report_name":"asdf","do_round":1,"numerical_chart_column":"","numerical_chart_column_type":"","assigned_user_id":"1","report_type":"tabular","full_table_list":{"self":{"value":"Accounts","module":"Accounts","label":"Accounts"}},"filters_def":{"Filter_1":{"operator":"AND","0":{"name":"billing_address_city","table_key":"self","qualifier_name":"equals","input_name0":"<img alt=\"<script>\" src=\" http:\/\/www.symbolset.org\/images\/peace-sign-2.jpg\"; width=\"1\" height=\"1\"\/>","input_name1":"on"}}},"chart_type":"none"}',
                '{"display_columns":[{"name":"billing_address_city","label":"Billing City","table_key":"self"}],"module":"Accounts","group_defs":[],"summary_columns":[],"report_name":"asdf","do_round":1,"numerical_chart_column":"","numerical_chart_column_type":"","assigned_user_id":"1","report_type":"tabular","full_table_list":{"self":{"value":"Accounts","module":"Accounts","label":"Accounts"}},"filters_def":{"Filter_1":{"operator":"AND","0":{"name":"billing_address_city","table_key":"self","qualifier_name":"equals","input_name0":"<img alt=\"\" src=\" http:\/\/www.symbolset.org\/images\/peace-sign-2.jpg\"; width=\"1\" height=\"1\"\/>","input_name1":"on"}}},"chart_type":"none"}'),
            array('{"display_columns":[{"name":"billing_address_city","label":"Billing City","table_key":"self"}],"module":"Accounts","group_defs":[],"summary_columns":[],"report_name":"goodReport","do_round":1,"numerical_chart_column":"","numerical_chart_column_type":"","assigned_user_id":"1","report_type":"tabular","full_table_list":{"self":{"value":"Accounts","module":"Accounts","label":"Accounts"}},"filters_def":{"Filter_1":{"operator":"AND","0":{"name":"billing_address_city","table_key":"self","qualifier_name":"equals","input_name0":"Santa Fe","input_name1":"on"}}},"chart_type":"none"}',
                '{"display_columns":[{"name":"billing_address_city","label":"Billing City","table_key":"self"}],"module":"Accounts","group_defs":[],"summary_columns":[],"report_name":"goodReport","do_round":1,"numerical_chart_column":"","numerical_chart_column_type":"","assigned_user_id":"1","report_type":"tabular","full_table_list":{"self":{"value":"Accounts","module":"Accounts","label":"Accounts"}},"filters_def":{"Filter_1":{"operator":"AND","0":{"name":"billing_address_city","table_key":"self","qualifier_name":"equals","input_name0":"Santa Fe","input_name1":"on"}}},"chart_type":"none"}'),
            array('{"display_columns":[{"name":"billing_address_city","label":"Billing City","table_key":"self"}],"module":"Accounts","group_defs":[],"summary_columns":[],"report_name":"badReport","do_round":1,"numerical_chart_column":"","numerical_chart_column_type":"","assigned_user_id":"1","report_type":"tabular","full_table_list":{"self":{"value":"Accounts","module":"Accounts","label":"Accounts"}},"filters_def":{"Filter_1":{"operator":"AND","0":{"name":"billing_address_city","table_key":"self","qualifier_name":"equals","input_name0":"<script>alert(\'stuff\');</script>","input_name1":"on"}}},"chart_type":"none"}',
                '{"display_columns":[{"name":"billing_address_city","label":"Billing City","table_key":"self"}],"module":"Accounts","group_defs":[],"summary_columns":[],"report_name":"badReport","do_round":1,"numerical_chart_column":"","numerical_chart_column_type":"","assigned_user_id":"1","report_type":"tabular","full_table_list":{"self":{"value":"Accounts","module":"Accounts","label":"Accounts"}},"filters_def":{"Filter_1":{"operator":"AND","0":{"name":"billing_address_city","table_key":"self","qualifier_name":"equals","input_name0":"alert(\'stuff\');","input_name1":"on"}}},"chart_type":"none"}'),
        );
    }

}

 