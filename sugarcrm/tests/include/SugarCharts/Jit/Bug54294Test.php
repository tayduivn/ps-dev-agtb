<?php
//FILE SUGARCRM flav=pro ONLY
/*********************************************************************************
 * The contents of this file are subject to the SugarCRM Master Subscription
 * Agreement ("License") which can be viewed at
 * http://www.sugarcrm.com/crm/master-subscription-agreement
 * By installing or using this file, You have unconditionally agreed to the
 * terms and conditions of the License, and You may not use this file except in
 * compliance with the License.  Under the terms of the license, You shall not,
 * among other things: 1) sublicense, resell, rent, lease, redistribute, assign
 * or otherwise transfer Your rights to the Software, and 2) use the Software
 * for timesharing or service bureau purposes such as hosting the Software for
 * commercial gain and/or for the benefit of a third party.  Use of the Software
 * may be subject to applicable fees and any use of the Software without first
 * paying applicable fees is strictly prohibited.  You do not have the right to
 * remove SugarCRM copyrights from the source code or user interface.
 *
 * All copies of the Covered Code must include on each user interface screen:
 *  (i) the "Powered by SugarCRM" logo and
 *  (ii) the SugarCRM copyright notice
 * in the same form as they appear in the distribution.  See full license for
 * requirements.
 *
 * Your Warranty, Limitations of liability and Indemnity are expressly stated
 * in the License.  Please refer to the License for the specific language
 * governing these rights and limitations under the License.  Portions created
 * by SugarCRM are Copyright (C) 2004-2012 SugarCRM, Inc.; All Rights Reserved.
 ********************************************************************************/

require_once 'modules/Reports/Report.php';
require_once 'modules/Reports/templates/templates_chart.php';

/**
 * Bug #54294
 * Reports Do Not Format Currency Fields on Charts
 *
 * @author mgusev@sugarcrm.com
 * @ticked 54294
 */
class Bug54294Test extends Sugar_PHPUnit_Framework_TestCase
{
    /**
     * @var Currency
     */
    protected $currency = null;

    /**
     * @var TimeDate
     */
    protected $timeDate = null;

    /**
     * @var Account
     */
    protected $account = null;

    /**
     * @var Opportunity
     */
    protected $opportunity = null;

    /**
     * @var SavedReport
     */
    protected $savedReport = null;

    protected function setUp()
    {
        SugarTestHelper::setUp('beanFiles');
        SugarTestHelper::setUp('beanList');
        SugarTestHelper::setUp('mod_strings', array('Opportunities'));
        SugarTestHelper::setUp('app_list_strings');
        SugarTestHelper::setUp('current_user');

        $this->currency = new Currency();
        $this->timeDate = new TimeDate($GLOBALS['current_user']);

        $this->account = SugarTestAccountUtilities::createAccount();

        $this->opportunity = new Opportunity();
        $this->opportunity->name = __CLASS__;
        $this->opportunity->currency_id = -99;
        $this->opportunity->amount = 1000000;
        $this->opportunity->sales_stage = 'Prospecting';
        $this->opportunity->account_id = $this->account->id;
        $this->opportunity->account_name = $this->account->name;
        $this->opportunity->date_closed = $this->timeDate->asUser(new DateTime('+7 days'));
        $this->opportunity->assigned_user_id = $GLOBALS['current_user']->id;
        $this->opportunity->assigned_user_name = $GLOBALS['current_user']->name;
        $this->opportunity->save();

        $reportDef = '
            {
                "display_columns":[],
                "module":"Opportunities",
                "group_defs":
                [
                    {
                        "name":"sales_stage",
                        "label":"Sales Stage",
                        "table_key":"self",
                        "type":"enum"
                    }
                ],
                "summary_columns":
                [
                    {
                        "name":"sales_stage",
                        "label":"Sales Stage",
                        "table_key":"self"
                    },
                    {
                        "name":"amount_usdollar",
                        "label":"SUM: Amount",
                        "field_type":"currency",
                        "group_function":"sum",
                        "table_key":"self"
                    }
                ],
                "report_name":"' . __CLASS__ . '",
                "chart_type":"vBarF",
                "do_round":0,"chart_description":"",
                "numerical_chart_column":"self:amount_usdollar:sum",
                "numerical_chart_column_type":"currency",
                "assigned_user_id":"' . $GLOBALS['current_user']->id . '",
                "report_type":"summary",
                "full_table_list":
                {
                    "self":
                    {
                        "value":"Opportunities",
                        "module":"Opportunities",
                        "label":"Opportunities"
                    },
                    "Opportunities:accounts":
                    {
                        "name":"Opportunities  >  Accounts",
                        "parent":"self",
                        "link_def":
                        {
                            "name":"accounts",
                            "relationship_name":"accounts_opportunities",
                            "bean_is_lhs":false,
                            "link_type":"many",
                            "label":"Accounts",
                            "module":"Accounts",
                            "table_key":"Opportunities:accounts"
                        },
                        "dependents":
                        [
                            "Filter.1_table_filter_row_1"
                        ],
                        "module":"Accounts",
                        "label":"Accounts"
                    }
                },
                "filters_def":
                {
                    "Filter_1":
                    {
                        "operator":"AND",
                        "0":
                        {
                            "name":"id",
                            "table_key":"Opportunities:accounts",
                            "qualifier_name":"is",
                            "input_name0":"' . $this->account->id . '",
                            "input_name1":"' . addslashes($this->account->name) .'"
                        }
                    }
                }
            }
        ';

        $this->savedReport = new SavedReport();
        $this->savedReport->assigned_user_id = $GLOBALS['current_user']->id;
        $this->savedReport->assigned_user_name = $GLOBALS['current_user']->name;
        $this->savedReport->chart_type = 'vBarF';
        $this->savedReport->team_id = '1';
        $this->savedReport->save_report(-1, $GLOBALS['current_user']->id, __CLASS__, 'Opportunities', 'summary', $reportDef, 0, 1, 'vBarF');
    }

    protected function tearDown()
    {
        $this->opportunity->mark_deleted($this->opportunity->id);
        $this->savedReport->mark_deleted($this->savedReport->id);
        SugarTestAccountUtilities::removeAllCreatedAccounts();
        SugarTestHelper::tearDown();
    }

    /**
     * @outputBuffering enabled
     * @group 54294
     * @return void
     */
    public function testCurrencySymbolInChart()
    {
        $report = new Report($this->savedReport->content);
        $report->is_saved_report = true;
        $report->saved_report = &$this->savedReport;
        $report->saved_report_id = $this->savedReport->id;

        $report->run_summary_query();
        while ($report->get_summary_next_row() != false)
        {
            // grabbing records
        }

        if ($report->has_summary_columns()) {
            $report->run_total_query();
            $report->get_summary_header_row();
            $report->get_summary_total_row();
        }

        template_chart($report, '');
        $jsonFile = str_replace(".xml",".js", get_cache_file_name($report));
        $jsonObject = sugar_file_get_contents($jsonFile);
        $json = getJSONobj();
        $jsonObject = $json->decode($jsonObject);

        $this->assertEquals($this->opportunity->amount, $jsonObject['values'][0]['values'][0], 'Value in chart should be equal to opportunity amount');
        $this->assertStringStartsWith(
            currency_format_number($this->opportunity->amount, array('currency_symbol' => print_currency_symbol($report->report_def))),
            $jsonObject['values'][0]['valuelabels'][0],
            'Label in chart should be localized'
        );
    }
}
