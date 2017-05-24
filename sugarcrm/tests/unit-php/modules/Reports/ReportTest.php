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

namespace Sugarcrm\SugarcrmTestUnit\modules\Reports;

/**
 * @coversDefaultClass \Report
 */
class ReportTest extends \PHPUnit_Framework_TestCase
{
    public function filterQueryProvider()
    {
        return array(
            // has filter
            array(
                // @codingStandardsIgnoreStart
                'reportDef' => '{"display_columns":[],"module":"Accounts","group_defs":[{"name":"industry","label":"Industry","table_key":"self","type":"enum"}],"summary_columns":[{"name":"count","label":"Count","field_type":"","group_function":"count","table_key":"self"},{"name":"industry","label":"Industry","table_key":"self"}],"report_name":"test1","chart_type":"vBarF","do_round":1,"chart_description":"","numerical_chart_column":"self:count","numerical_chart_column_type":"","assigned_user_id":"1","report_type":"summary","full_table_list":{"self":{"value":"Accounts","module":"Accounts","label":"Accounts","dependents":[]},"Accounts:contacts":{"name":"Accounts  \u003E  Contacts ","parent":"self","link_def":{"name":"contacts","relationship_name":"accounts_contacts","bean_is_lhs":true,"link_type":"many","label":"Contacts","module":"Contacts","table_key":"Accounts:contacts"},"dependents":["Filter.1_table_filter_row_2","Filter.1_table_filter_row_2","Filter.1_table_filter_row_2","Filter.1_table_filter_row_2","Filter.1_table_filter_row_2","Filter.1_table_filter_row_2","Filter.1_table_filter_row_2","Filter.1_table_filter_row_2","Filter.1_table_filter_row_2","Filter.1_table_filter_row_2","Filter.1_table_filter_row_2","Filter.1_table_filter_row_5","Filter.1_table_filter_row_2","Filter.1_table_filter_row_2","Filter.1_table_filter_row_2","Filter.1_table_filter_row_5","Filter.1_table_filter_row_2","Filter.1_table_filter_row_2","Filter.1_table_filter_row_2"],"module":"Contacts","label":"Contacts"}},"filters_def":{"Filter_1":{"operator":"AND","0":{"name":"name","table_key":"self","qualifier_name":"not_empty","input_name0":"not_empty","input_name1":"on"},"1":{"name":"primary_address_state","table_key":"Accounts:contacts","qualifier_name":"equals","input_name0":"CA","input_name1":"on"}}}}',
                'queryPiece' => 'SELECT DISTINCT accounts.id FROM accounts
 INNER JOIN  accounts_contacts l1_1 ON accounts.id=l1_1.account_id AND l1_1.deleted=0

 INNER JOIN  contacts l1 ON l1.id=l1_1.contact_id AND l1.deleted=0

 WHERE ((((coalesce(LENGTH(accounts.name), 0) <> 0)) AND (l1.primary_address_state=\'CA\'
))) '
                // @codingStandardsIgnoreEnd
            ),
            // no filter
            array(
                // @codingStandardsIgnoreStart
                'reportDef' => '{"display_columns":[],"module":"Accounts","group_defs":[{"name":"industry","label":"Industry","table_key":"self","type":"enum"}],"summary_columns":[{"name":"industry","label":"Industry","table_key":"self"},{"name":"count","label":"Count","field_type":"","group_function":"count","table_key":"self"}],"report_name":"test1","chart_type":"vBarF","do_round":1,"chart_description":"","numerical_chart_column":"self:count","numerical_chart_column_type":"","assigned_user_id":"1","report_type":"summary","full_table_list":{"self":{"value":"Accounts","module":"Accounts","label":"Accounts"}},"filters_def":{"Filter_1":{"operator":"AND"}}}',
                'queryPiece' => ''
                // @codingStandardsIgnoreEnd
            ),
        );
    }

    /**
     * @covers ::getFilterQuery
     * @dataProvider filterQueryProvider
     * @param $reportDef
     * @param $queryPiece
     */
    public function testGetFilterQuery($reportDef, $queryPiece)
    {
        $report = new Report($reportDef);
        $this->assertSame($queryPiece, $report->getFilterQuery());
    }
}
