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

require_once('modules/Reports/Report.php');

/**
 * Test aggregate functions if NULL fields are present
 *
 * @author avucinci@sugarcrm.com
 */
class Bug63673Test extends Sugar_PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        SugarTestHelper::setUp('beanFiles');
        SugarTestHelper::setUp('beanList');
        SugarTestHelper::setUp('current_user');
    }

    public function tearDown()
    {
        SugarTestOpportunityUtilities::removeAllCreatedOpportunities();
        SugarTestHelper::tearDown();
    }

    /**
     * Test if aggregate functions return proper values if NULL fields are present
     *
     * @param $reportDef - Report definition
     * @param $create - array for call_user_func() for generating test data
     * @param $avg - expected AVG result
     * @param $count - expected COUNT result
     * @param $max - expected MAX result
     * @param $sum - expected SUM result
     *
     * @dataProvider aggregateDataProvider
     */
    public function testAggregateFunctions($reportDef, $create, $avg, $count, $max, $sum)
    {
        call_user_func($create);

        $report = new Report($reportDef);
        $report->run_summary_query();
        $row = $report->get_summary_next_row();

        $this->assertNotEmpty($row['cells'], 'Empty summary results');
        $this->assertEquals($avg, $row['cells'][0], 'AVG result wrong');
        $this->assertEquals($count, $row['cells'][1], 'COUNT result wrong');
        $this->assertEquals($max, $row['cells'][2], 'MAX result wrong');
        $this->assertEquals($sum, $row['cells'][3], 'SUM result wrong');
    }

    /**
     * Creates 3 opportunities, setting the name for filtering
     * and setting probabilities to 10, 20, NULL so we can test aggregate functions
     */
    private static function setUpOpportunities()
    {
        $opp = SugarTestOpportunityUtilities::createOpportunity();
        $opp->name = 'Bug 63673 Test Opp 1';
        $opp->probability = 10;
        $opp->save();

        $opp = SugarTestOpportunityUtilities::createOpportunity();
        $opp->name = 'Bug 63673 Test Opp 2';
        $opp->probability = 20;
        $opp->save();

        $opp = SugarTestOpportunityUtilities::createOpportunity();
        $opp->name = 'Bug 63673 Test Opp 3';
        $opp->save();
        $GLOBALS['db']->query("UPDATE opportunities SET probability = NULL WHERE id = '{$opp->id}'");
    }

    public static function aggregateDataProvider()
    {
        return array(
            array(
                '{
                    "display_columns":[
                        {
                            "name":"name",
                            "label":"Opportunity Name",
                            "table_key":"self"
                        },
                        {
                            "name":"probability",
                            "label":"Probability (%)",
                            "table_key":"self"
                        }
                    ],
                    "module":"Opportunities",
                    "group_defs":[],
                    "summary_columns":[
                        {
                            "name":"probability",
                            "label":"AVG: Probability (%)",
                            "field_type":"int",
                            "group_function":"avg",
                            "table_key":"self"
                        },
                        {
                            "name":"count",
                            "label":"Count",
                            "field_type":"",
                            "group_function":"count",
                            "table_key":"self"
                        },
                        {
                            "name":"probability",
                            "label":"MAX: Probability (%)",
                            "field_type":"int",
                            "group_function":"max",
                            "table_key":"self"
                        },
                        {
                            "name":"probability",
                            "label":"SUM: Probability (%)",
                            "field_type":"int",
                            "group_function":"sum",
                            "table_key":"self"
                        }
                    ],
                    "report_name":"Bug 63673",
                    "chart_type":"none",
                    "do_round":1,
                    "chart_description":"",
                    "numerical_chart_column":"self:probability:avg",
                    "numerical_chart_column_type":"",
                    "assigned_user_id":"1",
                    "report_type":"summary",
                    "full_table_list":{
                        "self":{
                            "value":"Opportunities",
                            "module":"Opportunities",
                            "label":"Opportunities"
                        }
                    },
                    "filters_def":{
                        "Filter_1":{
                            "operator":"AND",
                            "0":{
                                "name":"name",
                                "table_key":"self",
                                "qualifier_name":"starts_with",
                                "input_name0":"Bug 63673",
                                "input_name1":"on"
                            }
                        }
                    }
                }',
                array('Bug63673Test', 'setUpOpportunities'),
                '15',
                '3',
                '20',
                '30',
            ),
        );
    }
}
