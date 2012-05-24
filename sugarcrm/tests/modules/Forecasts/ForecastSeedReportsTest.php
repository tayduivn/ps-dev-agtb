<?php
//FILE SUGARCRM flav=pro ONLY

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

require_once('modules/Reports/SavedReport.php');
require_once('modules/Reports/Report.php');
require_once('modules/Products/Product.php');
require_once('modules/Users/User.php');

class ForecastSeedReportsTest extends Sugar_PHPUnit_Framework_TestCase
{
    /**
     * List of the default reports
     *
     * @var array
     */
    protected static $report_defs;

    public static function setUpBeforeClass()
    {
        global $beanFiles, $beanList, $current_user, $app_list_strings, $app_strings, $timedate;
        $timedate = TimeDate::getInstance();
        $app_list_strings = return_app_list_strings_language('en');
        $app_strings = return_application_language('en');

        $current_user = SugarTestUserUtilities::createAnonymousUser();
        $current_user->user_name = 'employee0';
        $current_user->is_admin = 1;
        $current_user->save();
    
        $employee1 = SugarTestUserUtilities::createAnonymousUser();
        $employee1->reports_to_id = $current_user->id;
        $employee1->user_name = 'employee1';
        $employee1->save();
    
        $employee2 = SugarTestUserUtilities::createAnonymousUser();
        $employee2->reports_to_id = $current_user->id;
        $employee2->user_name = 'employee2';
        $employee2->save();
    
        $employee3 = SugarTestUserUtilities::createAnonymousUser();
        $employee3->reports_to_id = $employee2->id;
        $employee3->user_name = 'employee3';
        $employee3->save();
    
        $employee4 = SugarTestUserUtilities::createAnonymousUser();
        $employee4->reports_to_id = $employee3->id;
        $employee4->user_name = 'employee4';
        $employee4->save();

        $products = $current_user->build_related_list("SELECT id FROM products WHERE deleted = 0", new Product(), 0, 10);

        $opp1 = SugarTestOpportunityUtilities::createOpportunity();
        $opp1->date_closed = $timedate->getNow()->modify('-4 month')->asDbDate();
        $opp1->assigned_user_id = $current_user->id;
        $opp1->probability = '85';
        $opp1->best_case = 1300;
        $opp1->likely_case = 1200;
        $opp1->worst_case = 1100;
        $opp1->team_id = '1';
        $opp1->team_set_id = '1';
        $opp1->save();

        $line_bundle_1 = SugarTestOppLineBundleUtilities::createLineBundle();
        $line_1 = SugarTestOppLineItemUtilities::createLine();
        $line_1->name = $opp1->name;
        $line_1->opportunity_id = $opp1->id;
        $line_1->product_id = $products[array_rand($products)]->id;
        $line_1->team_set_id = '1';
        $line_1->team_id = '1';
        $line_1->best_case = 1300;
        $line_1->likely_case = 1200;
        $line_1->worst_case = 1100;
        $line_1->save();
        
        $line_bundle_1->set_opportunitylinebundle_opportunity_relationship($opp1->id, '', '1');
        $line_bundle_1->set_opportunitylinebundle_opportunityline_relationship($line_1->id, '1', '');
    
        $opp2 = SugarTestOpportunityUtilities::createOpportunity();
        $opp2->date_closed = $timedate->getNow()->asDbDate();
        $opp2->assigned_user_id = $employee1->id;
        $opp2->probability = '75';
        $opp2->best_case = 1300;
        $opp2->likely_case = 1200;
        $opp2->worst_case = 1100;
        $opp2->team_id = '1';
        $opp2->team_set_id = '1';
        $opp2->save();

        $line_bundle_2 = SugarTestOppLineBundleUtilities::createLineBundle();
        $line_2 = SugarTestOppLineItemUtilities::createLine();
        $line_2->name = $opp2->name;
        $line_2->opportunity_id = $opp2->id;
        $line_2->product_id = $products[array_rand($products)]->id;
        $line_2->team_set_id = '1';
        $line_2->team_id = '1';
        $line_2->best_case = 1300;
        $line_2->likely_case = 1200;
        $line_2->worst_case = 1100;        
        $line_2->save();
        $line_bundle_2->set_opportunitylinebundle_opportunity_relationship($opp2->id, '', '1');
        $line_bundle_2->set_opportunitylinebundle_opportunityline_relationship($line_2->id, '1', '');
    
        $opp3 = SugarTestOpportunityUtilities::createOpportunity();
        $opp3->date_closed = $timedate->getNow()->modify('+4 month')->asDbDate();
        $opp3->assigned_user_id = $employee2->id;
        $opp3->probability = '75';
        $opp3->best_case = 1300;
        $opp3->likely_case = 1200;
        $opp3->worst_case = 1100;
        $opp3->team_id = '1';
        $opp3->team_set_id = '1';
        $opp3->save();

        $line_bundle_3 = SugarTestOppLineBundleUtilities::createLineBundle();
        $line_3 = SugarTestOppLineItemUtilities::createLine();
        $line_3->name = $opp3->name;
        $line_3->opportunity_id = $opp3->id;
        $line_3->product_id = $products[0]->id;
        $line_3->team_set_id = '1';
        $line_3->team_id = '1';
        $line_3->best_case = 1300;
        $line_3->likely_case = 1200;
        $line_3->worst_case = 1100;        
        $line_3->save();
        $line_bundle_3->set_opportunitylinebundle_opportunity_relationship($opp3->id, '', '1');
        $line_bundle_3->set_opportunitylinebundle_opportunityline_relationship($line_3->id, '1', '');
    
        $opp4 = SugarTestOpportunityUtilities::createOpportunity();
        $opp4->date_closed = $timedate->getNow()->modify('+4 month')->asDbDate();
        $opp4->assigned_user_id = $employee3->id;
        $opp4->probability = '80';
        $opp4->best_case = 1300;
        $opp4->likely_case = 1200;
        $opp4->worst_case = 1100;
        $opp4->team_id = '1';
        $opp4->team_set_id = '1';
        $opp4->save();

        $line_bundle_4 = SugarTestOppLineBundleUtilities::createLineBundle();
        $line_4 = SugarTestOppLineItemUtilities::createLine();
        $line_4->name = $opp4->name;
        $line_4->opportunity_id = $opp4->id;
        $line_4->product_id = $products[1]->id;
        $line_4->team_set_id = '1';
        $line_4->team_id = '1';
        $line_4->best_case = 1300;
        $line_4->likely_case = 1200;
        $line_4->worst_case = 1100;        
        $line_4->save();
        $line_bundle_4->set_opportunitylinebundle_opportunity_relationship($opp4->id, '', '1');
        $line_bundle_4->set_opportunitylinebundle_opportunityline_relationship($line_4->id, '1', '');
    
        $opp5 = SugarTestOpportunityUtilities::createOpportunity();
        $opp5->date_closed = $timedate->getNow()->modify('+4 month')->asDbDate();
        $opp5->assigned_user_id = $employee4->id;
        $opp5->probability = '90';
        $opp5->best_case = 1300;
        $opp5->likely_case = 1200;
        $opp5->worst_case = 1100;
        $opp5->team_id = '1';
        $opp5->team_set_id = '1';
        $opp5->save();

        $line_bundle_5 = SugarTestOppLineBundleUtilities::createLineBundle();
        $line_5 = SugarTestOppLineItemUtilities::createLine();
        $line_5->name = $opp5->name;
        $line_5->opportunity_id = $opp5->id;
        $line_5->product_id = $products[2]->id;
        $line_5->team_set_id = '1';
        $line_5->team_id = '1';
        $line_5->best_case = 1300;
        $line_5->likely_case = 1200;
        $line_5->worst_case = 1100;        
        $line_5->save();
        $line_bundle_5->set_opportunitylinebundle_opportunity_relationship($opp5->id, '', '1');
        $line_bundle_5->set_opportunitylinebundle_opportunityline_relationship($line_5->id, '1', '');        

        self::$report_defs = array();
        self::$report_defs['ForecastSeedReport1'] = array('Opportunities', 'ForecastSeedReport1', '{"display_columns":[{"name":"name","label":"Name","table_key":"Opportunities:opportunity_lines"},{"name":"user_name","label":"User Name","table_key":"Opportunities:assigned_user_link"},{"name":"price","label":"Price","table_key":"Opportunities:opportunity_lines"},{"name":"quantity","label":"Quantity","table_key":"Opportunities:opportunity_lines"},{"name":"best_case","label":"Best case","table_key":"Opportunities:opportunity_lines"},{"name":"likely_case","label":"Likely case","table_key":"Opportunities:opportunity_lines"}],"module":"Opportunities","group_defs":[{"name":"date_closed","label":"Month: Expected Close Date","column_function":"month","qualifier":"month","table_key":"self","type":"date"},{"name":"name","label":"Product","table_key":"Opportunities:opportunity_lines:products","type":"name"}],"summary_columns":[{"name":"date_closed","label":"Month: Expected Close Date","column_function":"month","qualifier":"month","table_key":"self"},{"name":"name","label":"Product","table_key":"Opportunities:opportunity_lines:products"},{"name":"likely_case","label":"SUM: Likely case","field_type":"currency","group_function":"sum","table_key":"Opportunities:opportunity_lines"}],"report_name":"Test 1","chart_type":"vBarF","do_round":1,"chart_description":"","numerical_chart_column":"Opportunities:opportunity_lines:likely_case:sum","numerical_chart_column_type":"","assigned_user_id":"1","report_type":"summary","full_table_list":{"self":{"value":"Opportunities","module":"Opportunities","label":"Opportunities"},"Opportunities:timeperiods":{"name":"Opportunities  >  Time Periods","parent":"self","link_def":{"name":"timeperiods","relationship_name":"opportunities_timeperiods","bean_is_lhs":false,"link_type":"one","label":"TimePeriods","module":"TimePeriods","table_key":"Opportunities:timeperiods"},"dependents":[null,null,null,null,null,null,null,null,null,null,null,null,null,null,null,null,null,null,null,null,null,null,null,null,null,null,null,null,null,null,"Filter.1_table_filter_row_3","Filter.1_table_filter_row_2",null,null,"Filter.1_table_filter_row_2",null,null,"Filter.1_table_filter_row_2",null,null,"Filter.1_table_filter_row_2",null,null,"Filter.1_table_filter_row_2",null,null,"Filter.1_table_filter_row_1",null,null,"Filter.1_table_filter_row_1",null,null,"Filter.1_table_filter_row_1",null,null,"Filter.1_table_filter_row_1","group_by_row_2","display_summaries_row_group_by_row_2","Filter.1_table_filter_row_1",null,null,"Filter.1_table_filter_row_1","Filter.1_table_filter_row_1"],"module":"TimePeriods","label":"TimePeriods"},"Opportunities:assigned_user_link":{"name":"Opportunities  >  Assigned to User","parent":"self","link_def":{"name":"assigned_user_link","relationship_name":"opportunities_assigned_user","bean_is_lhs":false,"link_type":"one","label":"Assigned to User","module":"Users","table_key":"Opportunities:assigned_user_link"},"dependents":["Filter.1_table_filter_row_2","Filter.1_table_filter_row_2","Filter.1_table_filter_row_2","Filter.1_table_filter_row_2","Filter.1_table_filter_row_2","Filter.1_table_filter_row_2","Filter.1_table_filter_row_2","Filter.1_table_filter_row_2","Filter.1_table_filter_row_2","Filter.1_table_filter_row_2",null,null,null,null,null,"Filter.1_table_filter_row_3","Filter.1_table_filter_row_2","Filter.1_table_filter_row_2","Filter.1_table_filter_row_2","Filter.1_table_filter_row_2","Filter.1_table_filter_row_2","Filter.1_table_filter_row_2","Filter.1_table_filter_row_2","display_cols_row_8"],"module":"Users","label":"Assigned to User"},"Opportunities:opportunity_lines":{"name":"Opportunities  >  Opportunity Line Items ","parent":"self","link_def":{"name":"opportunity_lines","relationship_name":"opportunity_lines","bean_is_lhs":true,"link_type":"many","label":"Opportunity Line Items","module":"OpportunityLines","table_key":"Opportunities:opportunity_lines"},"dependents":["group_by_row_2","display_summaries_row_group_by_row_2","display_cols_row_4","display_cols_row_5","display_cols_row_6","display_cols_row_7","group_by_row_2","display_summaries_row_group_by_row_2","display_cols_row_4","display_cols_row_5","display_cols_row_6","display_cols_row_7","group_by_row_3","display_summaries_row_group_by_row_3","group_by_row_2","display_summaries_row_group_by_row_2","display_cols_row_4","display_cols_row_5","display_cols_row_6","display_cols_row_7","group_by_row_2","display_summaries_row_group_by_row_2","display_cols_row_4","display_cols_row_5","display_cols_row_6","display_cols_row_7","group_by_row_2","display_summaries_row_group_by_row_2","display_cols_row_4","display_cols_row_5","display_cols_row_6","display_cols_row_7","group_by_row_2","display_summaries_row_group_by_row_2","display_cols_row_4","display_cols_row_5","display_cols_row_6","display_cols_row_7","group_by_row_2","display_summaries_row_group_by_row_2","display_cols_row_4","display_cols_row_5","display_cols_row_6","display_cols_row_7","group_by_row_2","display_summaries_row_group_by_row_2","display_cols_row_4","display_cols_row_5","display_cols_row_6","display_cols_row_7","group_by_row_2","display_summaries_row_group_by_row_2","display_cols_row_4","display_cols_row_5","display_cols_row_6","display_cols_row_7","group_by_row_2","display_summaries_row_group_by_row_2","display_cols_row_4","display_cols_row_5","display_cols_row_6","display_cols_row_7","group_by_row_2","display_summaries_row_group_by_row_2","display_cols_row_4","display_cols_row_5","display_cols_row_6","display_cols_row_7","group_by_row_2","display_summaries_row_group_by_row_2","display_cols_row_4","display_cols_row_5","display_cols_row_6","display_cols_row_7","group_by_row_2","display_summaries_row_group_by_row_2","display_cols_row_4","display_cols_row_5","display_cols_row_6","display_cols_row_7","group_by_row_2","display_summaries_row_group_by_row_2","display_cols_row_4","display_cols_row_5","display_cols_row_6","display_cols_row_7","group_by_row_2","display_summaries_row_group_by_row_2","display_cols_row_4","display_cols_row_5","display_cols_row_6","display_cols_row_7","display_summaries_row_8","group_by_row_2","display_summaries_row_group_by_row_2","display_summaries_row_3","display_cols_row_4","display_cols_row_5","display_cols_row_6","display_cols_row_7","group_by_row_2","display_summaries_row_group_by_row_2","display_summaries_row_3","display_cols_row_4","display_cols_row_5","display_cols_row_6","display_cols_row_7","group_by_row_2","display_summaries_row_group_by_row_2","display_summaries_row_3","display_cols_row_4","display_cols_row_5","display_cols_row_6","display_cols_row_7","group_by_row_1","display_summaries_row_group_by_row_1","display_summaries_row_3","display_cols_row_4","display_cols_row_5","display_cols_row_6","display_cols_row_7","group_by_row_2","display_summaries_row_group_by_row_2","display_summaries_row_3","display_cols_row_4","display_cols_row_5","display_cols_row_6","display_cols_row_7","group_by_row_2","display_summaries_row_group_by_row_2","display_summaries_row_3","display_cols_row_4","display_cols_row_5","display_cols_row_6","display_cols_row_7","group_by_row_2","display_summaries_row_group_by_row_2","display_summaries_row_3","display_cols_row_4","display_cols_row_5","display_cols_row_6","display_cols_row_7","display_cols_row_10"],"module":"OpportunityLines","label":"Opportunity Line Items"},"Opportunities:opportunity_lines:products":{"name":"Opportunities  >  Opportunity Line Items  >  Products","parent":"Opportunities:opportunity_lines","link_def":{"name":"products","relationship_name":"opportunity_lines_products","bean_is_lhs":false,"link_type":"one","label":"Products","module":"Products","table_key":"Opportunities:opportunity_lines:products"},"dependents":["group_by_row_3","display_summaries_row_group_by_row_3","group_by_row_2","display_summaries_row_group_by_row_2","group_by_row_2","display_summaries_row_group_by_row_2","group_by_row_2","display_summaries_row_group_by_row_2","group_by_row_2","display_summaries_row_group_by_row_2","group_by_row_2","display_summaries_row_group_by_row_2","group_by_row_2","display_summaries_row_group_by_row_2","group_by_row_2","display_summaries_row_group_by_row_2","group_by_row_2","display_summaries_row_group_by_row_2","group_by_row_2","display_summaries_row_group_by_row_2","group_by_row_2","display_summaries_row_group_by_row_2","group_by_row_2","display_summaries_row_group_by_row_2","group_by_row_2","display_summaries_row_group_by_row_2","group_by_row_2","display_summaries_row_group_by_row_2","group_by_row_2","display_summaries_row_group_by_row_2","group_by_row_2","display_summaries_row_group_by_row_2","group_by_row_2","display_summaries_row_group_by_row_2","group_by_row_1","display_summaries_row_group_by_row_1","group_by_row_2","display_summaries_row_group_by_row_2","group_by_row_2","display_summaries_row_group_by_row_2","group_by_row_2","display_summaries_row_group_by_row_2"],"module":"Products","label":"Products"}},"filters_def":{"Filter_1":{"operator":"AND","0":{"name":"name","table_key":"Opportunities:timeperiods","qualifier_name":"is","runtime":1,"input_name0":["last_current_next"]},"1":{"name":"id","table_key":"Opportunities:assigned_user_link","qualifier_name":"reports_to","runtime":1,"input_name0":["Current User"]},"2":{"name":"probability","table_key":"self","qualifier_name":"greater","runtime":1,"input_name0":"25","input_name1":"on"}}}}', 'detailed_summary', 'vBarF');
        self::$report_defs['ForecastSeedReport2'] = array('Opportunities', 'ForecastSeedReport2', '{"display_columns":[{"name":"name","label":"Opportunity Name","table_key":"self"},{"name":"date_closed","label":"Expected Close Date","table_key":"self"},{"name":"sales_stage","label":"Sales Stage","table_key":"self"},{"name":"probability","label":"Probability (%)","table_key":"self"},{"name":"amount","label":"Opportunity Amount","table_key":"self"},{"name":"best_case","label":"Best case","table_key":"self"},{"name":"likely_case","label":"Likely case","table_key":"self"}],"module":"Opportunities","group_defs":[{"name":"date_closed","label":"Month: Expected Close Date","column_function":"month","qualifier":"month","table_key":"self","type":"date"},{"name":"sales_stage","label":"Sales Stage","table_key":"self","type":"enum"},{"name":"amount","label":"Opportunity Amount","table_key":"self","type":"currency"}],"summary_columns":[{"name":"date_closed","label":"Month: Expected Close Date","column_function":"month","qualifier":"month","table_key":"self"},{"name":"sales_stage","label":"Sales Stage","table_key":"self"},{"name":"amount","label":"Opportunity Amount","table_key":"self"},{"name":"amount","label":"SUM: Opportunity Amount","field_type":"currency","group_function":"sum","table_key":"self"}],"report_name":"Test 4","chart_type":"vBarF","do_round":1,"chart_description":"","numerical_chart_column":"self:amount:sum","numerical_chart_column_type":"currency","assigned_user_id":"1","report_type":"summary","full_table_list":{"self":{"value":"Opportunities","module":"Opportunities","label":"Opportunities"},"Opportunities:timeperiods":{"name":"Opportunities  >  Time Periods","parent":"self","link_def":{"name":"timeperiods","relationship_name":"opportunities_timeperiods","bean_is_lhs":false,"link_type":"one","label":"TimePeriods","module":"TimePeriods","table_key":"Opportunities:timeperiods"},"dependents":["Filter.1_table_filter_row_1"],"module":"TimePeriods","label":"TimePeriods"},"Opportunities:assigned_user_link":{"name":"Opportunities  >  Assigned to User","parent":"self","link_def":{"name":"assigned_user_link","relationship_name":"opportunities_assigned_user","bean_is_lhs":false,"link_type":"one","label":"Assigned to User","module":"Users","table_key":"Opportunities:assigned_user_link"},"dependents":["Filter.1_table_filter_row_2"],"module":"Users","label":"Assigned to User"}},"filters_def":{"Filter_1":{"operator":"AND","0":{"name":"name","table_key":"Opportunities:timeperiods","qualifier_name":"is","runtime":1,"input_name0":["last_current_next"]},"1":{"name":"id","table_key":"Opportunities:assigned_user_link","qualifier_name":"reports_to","runtime":1,"input_name0":["Current User"]},"2":{"name":"probability","table_key":"self","qualifier_name":"greater","runtime":1,"input_name0":"70","input_name1":"on"}}}}', 'detailed_summary', 'vBarF');
        parent::setUpBeforeClass();
    }

    public static function tearDownAfterClass()
    {
        $GLOBALS['db']->query("DELETE FROM saved_reports WHERE name IN ('ForecastSeedReport1', 'ForecastSeedReport2', 'ForecastSeedReport3')");
        SugarTestUserUtilities::removeAllCreatedAnonymousUsers();
        SugarTestOppLineItemUtilities::removeAllCreatedLines();
        SugarTestOppLineBundleUtilities::removeAllCreatedLineBundles();
        SugarTestOpportunityUtilities::removeAllCreatedOpps();
        parent::tearDownAfterClass();
    }


    /**
     * @outputBuffering disabled
     *
     */
    public function testForecastSeedReport1()
    {
        global $current_user, $mod_strings;
        $mod_strings = return_module_language('en', 'Opportunities');
        $saved_report = new SavedReport();
        $result = $saved_report->save_report(-1, $current_user->id, self::$report_defs['ForecastSeedReport1'][1], self::$report_defs['ForecastSeedReport1'][0], self::$report_defs['ForecastSeedReport1'][3], self::$report_defs['ForecastSeedReport1'][2], 1, '1', self::$report_defs['ForecastSeedReport1'][4]);
        //Assert that we have created the report
        $this->assertNotEmpty($result);
        $report = new Report($saved_report->content);
        $report->run_query();
        $report->run_summary_query();
        $this->assertEquals(2, count($report->query_list));

        /**
         * These Queries should give you the data you need to begin building the chart
         *
         */
        /*
        foreach($report->query_list as $query)
        {
            $result = $GLOBALS['db']->query($query);
            while(($row = $GLOBALS['db']->fetchByAssoc($result)))
            {
                echo var_export($row, true);
            }
        }
        */
    }

    public function testForecastSeedReport2()
    {
        global $current_user, $mod_strings;
        $mod_strings = return_module_language('en', 'Opportunities');
        $saved_report = new SavedReport();
        $result = $saved_report->save_report(-1, $current_user->id, self::$report_defs['ForecastSeedReport2'][1], self::$report_defs['ForecastSeedReport2'][0], self::$report_defs['ForecastSeedReport2'][3], self::$report_defs['ForecastSeedReport2'][2], 1, '1', self::$report_defs['ForecastSeedReport2'][4]);
        //Assert that we have created the report
        $this->assertNotEmpty($result);
        $report = new Report($saved_report->content);
        $report->run_query();
        $report->run_summary_query();
        $this->assertEquals(2, count($report->query_list));
        $report_data = json_decode($saved_report->content, true);

        //Now re-create the report and then json_encode
        /*
        $report_data = array();
        $report_data['display_columns'] = array (
           0 =>
           array (
             'name' => 'name',
             'label' => 'Opportunity Name',
             'table_key' => 'self',
           ),
           1 =>
           array (
             'name' => 'date_closed',
             'label' => 'Expected Close Date',
             'table_key' => 'self',
           ),
           2 =>
           array (
             'name' => 'sales_stage',
             'label' => 'Sales Stage',
             'table_key' => 'self',
           ),
           3 =>
           array (
             'name' => 'probability',
             'label' => 'Probability (%)',
             'table_key' => 'self',
           ),
           4 =>
           array (
             'name' => 'amount',
             'label' => 'Opportunity Amount',
             'table_key' => 'self',
           ),
           5 =>
           array (
             'name' => 'best_case',
             'label' => 'Best case',
             'table_key' => 'self',
           ),
           6 =>
           array (
             'name' => 'likely_case',
             'label' => 'Likely case',
             'table_key' => 'self',
           ),
         );

        $report_data['module'] = 'Opportunities';
        $report_data['group_defs'] = array (
            0 =>
            array (
              'name' => 'date_closed',
              'label' => 'Month: Expected Close Date',
              'column_function' => 'month',
              'qualifier' => 'month',
              'table_key' => 'self',
              'type' => 'date',
            ),
            1 =>
            array (
              'name' => 'sales_stage',
              'label' => 'Sales Stage',
              'table_key' => 'self',
              'type' => 'enum',
            ),
            2 =>
            array (
              'name' => 'amount',
              'label' => 'Opportunity Amount',
              'table_key' => 'self',
              'type' => 'currency',
            ),
          );

        $report_data['summary_columns'] = array (
            0 =>
            array (
              'name' => 'date_closed',
              'label' => 'Month: Expected Close Date',
              'column_function' => 'month',
              'qualifier' => 'month',
              'table_key' => 'self',
            ),
            1 =>
            array (
              'name' => 'sales_stage',
              'label' => 'Sales Stage',
              'table_key' => 'self',
            ),
            2 =>
            array (
              'name' => 'amount',
              'label' => 'Opportunity Amount',
              'table_key' => 'self',
            ),
            3 =>
            array (
              'name' => 'amount',
              'label' => 'SUM: Opportunity Amount',
              'field_type' => 'currency',
              'group_function' => 'sum',
              'table_key' => 'self',
            ),
          );

        $report_data['report_name'] = 'ForecastSeedReport3';
        $report_data['chart_type'] = 'vBarF';
        $report_data['do_round'] = '1';
        $report_data['chart_description'] => '';
        $report_data['numerical_chart_column'] = 'self:amount:sum';
        $report_data['numerical_chart_column_type'] = 'currency';
        $report_data['assigned_user_id'] = $current_user->id;
        $report_data['report_type'] = 'summary';

        $report_data['full_table_list'] = array (
            'self' =>
            array (
              'value' => 'Opportunities',
              'module' => 'Opportunities',
              'label' => 'Opportunities',
            ),
            'Opportunities:timeperiods' =>
            array (
              'name' => 'Opportunities  >  Time Periods',
              'parent' => 'self',
              'link_def' =>
              array (
                'name' => 'timeperiods',
                'relationship_name' => 'opportunities_timeperiods',
                'bean_is_lhs' => false,
                'link_type' => 'one',
                'label' => 'TimePeriods',
                'module' => 'TimePeriods',
                'table_key' => 'Opportunities:timeperiods',
              ),
              'dependents' =>
              array (
                0 => 'Filter.1_table_filter_row_1',
              ),
              'module' => 'TimePeriods',
              'label' => 'TimePeriods',
            ),
            'Opportunities:assigned_user_link' =>
            array (
              'name' => 'Opportunities  >  Assigned to User',
              'parent' => 'self',
              'link_def' =>
              array (
                'name' => 'assigned_user_link',
                'relationship_name' => 'opportunities_assigned_user',
                'bean_is_lhs' => false,
                'link_type' => 'one',
                'label' => 'Assigned to User',
                'module' => 'Users',
                'table_key' => 'Opportunities:assigned_user_link',
              ),
              'dependents' =>
              array (
                0 => 'Filter.1_table_filter_row_2',
              ),
              'module' => 'Users',
              'label' => 'Assigned to User',
            ),
          );
        */
        //This is the core of the test.  Our idea is perhaps that we load the defined seed report and then pass in these dynamic filters
        $report_data['filters_def'] = array(
            'Filter_1' =>
            array(
                'operator' => 'AND',
                0 =>
                array(
                    'name' => 'name',
                    'table_key' => 'Opportunities:timeperiods',
                    'qualifier_name' => 'is',
                    'runtime' => 1,
                    'input_name0' =>
                    array(
                        0 => 'last_current_next',
                    ),
                ),
                1 =>
                array(
                    'name' => 'id',
                    'table_key' => 'Opportunities:assigned_user_link',
                    'qualifier_name' => 'reports_to',
                    'runtime' => 1,
                    'input_name0' =>
                    array(
                        0 => 'Current User',
                    ),
                ),
                2 =>
                array(
                    'name' => 'probability',
                    'table_key' => 'self',
                    'qualifier_name' => 'greater',
                    'runtime' => 1,
                    'input_name0' => '70',
                    'input_name1' => 'on',
                ),
            ),
        );

        $report = new Report(json_encode($report_data));
        $report->run_query();
        $report->run_summary_query();
        $this->assertEquals(2, count($report->query_list));

    }

}