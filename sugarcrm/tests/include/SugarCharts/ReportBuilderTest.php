<?php
/********************************************************************************
 *The contents of this file are subject to the SugarCRM Professional End User License Agreement
 *("License") which can be viewed at http://www.sugarcrm.com/EULA.
 *By installing or using this file, You have unconditionally agreed to the terms and conditions of the License, and You may
 *not use this file except in compliance with the License. Under the terms of the license, You
 *shall not, among other things: 1) sublicense, resell, rent, lease, redistribute, assign or
 *otherwise transfer Your rights to the Software, and 2) use the Software for timesharing or
 *service bureau purposes such as hosting the Software for commercial gain and/or for the benefit
 *of a third party.  Use of the Software may be subject to applicable fees and any use of the
 *Software without first paying applicable fees is strictly prohibited.  You do not have the
 *right to remove SugarCRM copyrights from the source code or user interface.
 * All copies of the Covered Code must include on each user interface screen:
 * (i) the "Powered by SugarCRM" logo and
 * (ii) the SugarCRM copyright notice
 * in the same form as they appear in the distribution.  See full license for requirements.
 *Your Warranty, Limitations of liability and Indemnity are expressly stated in the License.  Please refer
 *to the License for the specific language governing these rights and limitations under the License.
 *Portions created by SugarCRM are Copyright (C) 2004 SugarCRM, Inc.; All Rights Reserved.
 ********************************************************************************/
require_once('include/SugarCharts/ReportBuilder.php');

class ReportBuilderTest extends Sugar_PHPUnit_Framework_TestCase
{
    /**
     * setUpBeforeClass
     *
     * Override setupBeforeClass to instantiate global beanFile and beanList variables
     */
    public static function setUpBeforeClass()
    {
        global $beanList, $beanFiles;
        require('include/modules.php');
    }

    public static function tearDownAfterClass()
    {
        $GLOBALS['db']->query("DELETE FROM saved_reports WHERE name = 'TestReport'");
    }

    /**
     * @group ReportBuilder
     * @group SugarCharts
     */
    public function testConstructorSetsModule()
    {
        $rb = new ReportBuilder('Accounts');
        $actual_json = $rb->toJson();
        $actual = json_decode($actual_json, true);

        $this->assertEquals('Accounts', $actual['module']);
    }

    /**
     * @group ReportBuilder
     * @group SugarCharts
     */
    public function testConstructorSetsSelfTable()
    {
        $rb = new ReportBuilder('Accounts');
        $actual_json = $rb->toJson();
        $actual = json_decode($actual_json, true);

        $this->assertSame(array('self' => array(
            'value' => 'Accounts',
            'module' => 'Accounts',
            'label' => 'Accounts',
            'parent' => '',
            'children' => array())), $actual['full_table_list']);
    }

    /**
     * @group ReportBuilder
     * @group SugarCharts
     */
    public function testToJson()
    {
        $rb = new ReportBuilder('Accounts');
        $test = json_decode($rb->toJson());

        $this->assertNotNull($test);
    }

    /**
     * @group ReportBuilder
     * @group SugarCharts
     */
    public function testToArray()
    {
        $rb = new ReportBuilder('Accounts');
        $this->assertTrue(is_array($rb->toArray()));
    }

    /**
     * @group ReportBuilder
     * @group SugarCharts
     */
    public function testAddModuleWithKey()
    {
        $rb = new ReportBuilder('Accounts');
        $rb->addModule('Contacts', 'contacts');
        $actual_json = $rb->toJson();
        $actual = json_decode($actual_json, true);

        $this->assertSame(array(
            'value' => 'Contacts',
            'module' => 'Contacts',
            'label' => 'Contacts',
            'parent' => '',
            'children' => array()), $actual['full_table_list']['contacts']);
    }

    /**
     * @group ReportBuilder
     * @group SugarCharts
     */
    public function testGetTableKeyWithModuleReturnsString()
    {
        $rb = new ReportBuilder('Accounts');
        $this->assertEquals('self', $rb->getKeyTable('Accounts'));
    }

    /**
     * @group ReportBuilder
     * @group SugarCharts
     */
    public function testGetTableKeyWithNotModuleReturnsArray()
    {
        $rb = new ReportBuilder('Accounts');
        $this->assertSame(array('self' => array('module' => 'Accounts', 'key' => 'self')), $rb->getKeyTable());
    }

    /**
     * @group ReportBuilder
     * @group SugarCharts
     */
    public function testGetBeanReturnsAccountSugarBeanFromCache()
    {
        $rb = new ReportBuilder('Accounts');
        $this->assertInstanceOf('Account', $rb->getBean('Accounts'));
    }

    /**
     * @group ReportBuilder
     * @group SugarCharts
     */
    public function testGetBeanReturnsContactSugarBeanAfterCreate()
    {
        $rb = new ReportBuilder('Accounts');
        $this->assertInstanceOf('Contact', $rb->getBean('Contacts'));
    }

    /**
     * @group ReportBuilder
     * @group SugarCharts
     */
    public function testGetDefaultModuleAsString()
    {
        $rb = new ReportBuilder('Accounts');
        $this->assertEquals('Accounts', $rb->getDefaultModule());
    }

    /**
     * @group ReportBuilder
     * @group SugarCharts
     */
    public function testGetDefaultModuleAsAccountBean()
    {
        $rb = new ReportBuilder('Accounts');
        $this->assertInstanceOf('Account', $rb->getDefaultModule(true));
    }

    /**
     * @group ReportBuilder
     * @group SugarCharts
     */
    public function testAddSummaryCount()
    {
        $rb = new ReportBuilder('Accounts');
        $rb->addSummaryCount();
        $actual_json = $rb->toJson();
        $actual = json_decode($actual_json, true);

        $this->assertSame(array(
            'name' => 'count',
            'label' => 'Count',
            'table_key' => 'self',
            'group_function' => "count",
            'field_type' => ''
        ), $actual['summary_columns'][0]);
    }

    /**
     * @group ReportBuilder
     * @group SugarCharts
     */
    public function testAddSummaryColumnWithoutModule()
    {
        $rb = new ReportBuilder('Accounts');
        $rb->addSummaryColumn('name');
        $actual_json = $rb->toJson();
        $actual = json_decode($actual_json, true);

        $this->assertSame(array(
            'name' => "name",
            'label' => "Name:",
            'field_type' => 'name',
            'table_key' => "self",
        ), $actual['summary_columns'][0]);
    }

    /**
     * @group ReportBuilder
     * @group SugarCharts
     */
    public function testAddSummaryColumnWithModuleAsString()
    {
        $rb = new ReportBuilder('Accounts');
        $rb->addSummaryColumn('name', 'Accounts');
        $actual_json = $rb->toJson();
        $actual = json_decode($actual_json, true);

        $this->assertSame(array(
            'name' => "name",
            'label' => "Name:",
            'field_type' => 'name',
            'table_key' => "self",
        ), $actual['summary_columns'][0]);
    }

    /**
     * @group ReportBuilder
     * @group SugarCharts
     */
    public function testAddSummaryColumnWithModuleAsSugarBean()
    {
        $rb = new ReportBuilder('Accounts');
        $rb->addSummaryColumn('name', $rb->getBean('Accounts'));
        $actual_json = $rb->toJson();
        $actual = json_decode($actual_json, true);

        $this->assertSame(array(
            'name' => "name",
            'label' => "Name:",
            'field_type' => 'name',
            'table_key' => "self",
        ), $actual['summary_columns'][0]);
    }

    /**
     * @group ReportBuilder
     * @group SugarCharts
     */
    public function testAddGroupByWithModule()
    {
        $rb = new ReportBuilder('Accounts');
        $rb->addGroupBy('name', 'Accounts');
        $actual_json = $rb->toJson();
        $actual = json_decode($actual_json, true);

        $this->assertSame(array(
            'name' => "name",
            'label' => "Name:",
            'table_key' => "self",
            'type' => 'name',
        ), $actual['group_defs'][0]);
    }

    /**
     * @group ReportBuilder
     * @group SugarCharts
     */
    public function testAddGroupByWithoutModule()
    {
        $rb = new ReportBuilder('Accounts');
        $rb->addGroupBy('name');
        $actual_json = $rb->toJson();
        $actual = json_decode($actual_json, true);

        $this->assertSame(array(
            'name' => "name",
            'label' => "Name:",
            'table_key' => "self",
            'type' => 'name',
        ), $actual['group_defs'][0]);
    }

    /**
     * @group ReportBuilder
     * @group SugarCharts
     */
    public function testAddLinkSetsTableInList()
    {
        $rb = new ReportBuilder('Accounts');
        $rb->addLink('contacts', 'name');
        $actual_json = $rb->toJson();
        $actual = json_decode($actual_json, true);

        $this->assertTrue(isset($actual['full_table_list']['Accounts:contacts']));
    }

    /**
     * @group ReportBuilder
     * @group SugarCharts
     */
    public function testAddLinkSetsFieldInSummaryColumns()
    {
        $rb = new ReportBuilder('Accounts');
        $rb->addLink('contacts', 'name');
        $actual_json = $rb->toJson();
        $actual = json_decode($actual_json, true);

        $this->assertEquals('Accounts:contacts', $actual['summary_columns'][0]['table_key']);
    }

    /**
     * @group ReportBuilder
     * @group SugarCharts
     */
    public function testAddLinkSetsFieldInGroupDefs()
    {
        $rb = new ReportBuilder('Accounts');
        $rb->addLink('contacts', 'name');
        $actual_json = $rb->toJson();
        $actual = json_decode($actual_json, true);

        $this->assertEquals('Accounts:contacts', $actual['group_defs'][0]['table_key']);
    }

    /**
     * @group ReportBuilder
     * @group SugarCharts
     */
    public function testAddLinkToAccountModule()
    {
        $rb = new ReportBuilder('Accounts');
        $rb->addLink('member_of', 'name');
        $actual_json = $rb->toJson();
        $actual = json_decode($actual_json, true);

        $this->assertEquals('Accounts:member_of', $actual['group_defs'][0]['table_key']);
    }

    /**
     * @group ReportBuilder
     * @group SugarCharts
     */
    public function testAddFilter()
    {

        $filter = array("Filter_1" => array(
            'operator' => 'AND',
            0 => array(
                'name' => 'billing_postalcode',
                'table_key' => 'self',
                'qualifier_name' => 'not_empty',
                'input_name0' => null
            )
        ));

        $rb = new ReportBuilder('Accounts');
        $rb->addFilter($filter);
        $actual_json = $rb->toJson();
        $actual = json_decode($actual_json, true);

        $this->assertSame($filter['Filter_1'], $actual['filters_def']['Filter_1'][0]);
    }

    /**
     * @group ReportBuilder
     * @group SugarCharts
     */
    public function testGetBeanFromTableKeyReturnsFalse()
    {
        $rb = new ReportBuilder('Accounts');
        $return = $rb->getBeanFromTableKey('asdfasdf');

        $this->assertFalse($return);
    }

    /**
     * @group ReportBuilder
     * @group SugarCharts
     */
    public function testGetBeanFromTableKeyReturnsAccountBean()
    {
        $rb = new ReportBuilder('Accounts');
        $return = $rb->getBeanFromTableKey('self');

        $this->assertInstanceOf('Account', $return);
    }

    /**
     * @group ReportBuilder
     * @group SugarCharts
     */
    public function testGetLinkTableReturnsArrayWhenLinkDoesntExist()
    {
        $rb = new ReportBuilder('Accounts');
        $return = $rb->getLinkTable('asdf');

        $this->assertTrue(is_array($return));
    }

    /**
     * @group ReportBuilder
     * @group SugarCharts
     */
    public function testGetLinkTableReturnsStringWhenLinkExist()
    {
        $rb = new ReportBuilder('Accounts');
        $rb->addLink('member_of');
        $return = $rb->getLinkTable('member_of');

        $this->assertEquals('Accounts:member_of', $return);
    }

    /**
     * @group ReportBuilder
     * @group SugarCharts
     */
    public function testMultiLevelLink()
    {
        $rb = new ReportBuilder('Accounts');
        $rb->addLink('assigned_user_link', 'user_name', array('Accounts', 'contacts'));
        $return = $rb->getLinkTable();
        $this->assertEquals('Accounts:contacts:assigned_user_link', $return['assigned_user_link']);
    }

    /**
     * @group ReportBuilder
     * @group SugarCharts
     */
    public function testSetSetValidChartType()
    {
        $rb = new ReportBuilder('Accounts');
        $rb->setChartType('funnelF');

        $this->assertEquals('funnelF', $rb->getChartType());
    }

    /**
     * @group ReportBuilder
     * @group SugarCharts
     */
    public function testSetInvalidChartTypeEqualshBarF()
    {
        $rb = new ReportBuilder('Accounts');
        $rb->setChartType('SomeInvalidChartTypeF');

        $this->assertEquals('hBarF', $rb->getChartType());
    }

    /**
     * @group ReportBuilder
     * @group SugarCharts
     */
    public function testLoadSavedReportReturnsFalseWithNonValidGuid()
    {
        $rb = new ReportBuilder('Accounts');
        $return = $rb->loadSavedReport('this is only a test');

        $this->assertFalse($return);
    }

    /**
     * @group ReportBuilder
     * @group SugarCharts
     */
    public function testLoadSavedReportReturnsFalseWhenSavedReportModuleDoesntMatchParentModule()
    {
        $saved_report = $this->createTestReport();

        $rb = new ReportBuilder('Accounts');
        $return = $rb->loadSavedReport($saved_report->id);

        $this->assertFalse($return);

        $this->removeTestReport($saved_report->id);
    }

    /**
     * @group ReportBuilder
     * @group SugarCharts
     */
    public function testLoadSavedReportReturnsTrue()
    {
        $saved_report = $this->createTestReport();

        $rb = new ReportBuilder('Opportunities');
        $return = $rb->loadSavedReport($saved_report->id);

        $this->assertTrue($return);

        $this->removeTestReport($saved_report->id);
    }

    /**
     * @group ReportBuilder
     * @group SugarCharts
     */
    public function testLoadSavedReportWithAdditionalFiltersContainsDefaultFilter()
    {
        $saved_report = $this->createTestReport();

        $filter = array("Filter_1" => array(
            'operator' => 'AND',
            0 => array(
                'name' => 'billing_postalcode',
                'table_key' => 'self',
                'qualifier_name' => 'not_empty',
                'input_name0' => null
            )
        ));

        $og_filter = array(
            'operator' => 'AND',
            0 => array(
                'name' => 'user_name',
                'table_key' => 'Opportunities:assigned_user_link',
                'qualifier_name' => 'reports_to',
                'input_name0' => array('seed_chris_id')
            )
        );

        $rb = new ReportBuilder('Opportunities');
        $rb->loadSavedReport($saved_report->id);
        $rb->addFilter($filter);

        $filters = $rb->getFilters();

        $this->assertSame($og_filter, $filters['Filter_1'][0]);

        $this->removeTestReport($saved_report->id);
    }

    /**
     * @group ReportBuilder
     * @group SugarCharts
     */
    public function testLoadSavedReportWithAdditionalFiltersContainsNewFilter()
    {
        $saved_report = $this->createTestReport();

        $filter = array("Filter_1" => array(
            'operator' => 'AND',
            0 => array(
                'name' => 'billing_postalcode',
                'table_key' => 'self',
                'qualifier_name' => 'not_empty',
                'input_name0' => null
            )
        ));

        $rb = new ReportBuilder('Opportunities');
        $rb->loadSavedReport($saved_report->id);
        $rb->addFilter($filter);

        $filters = $rb->getFilters();

        $this->assertSame($filter['Filter_1'], $filters['Filter_1'][1]);

        $this->removeTestReport($saved_report->id);
    }

    /**
     * @group ReportBuilder
     * @group SugarCharts
     */
    public function testGetGroupByReturnsFullList()
    {
        $rb = new ReportBuilder('Accounts');
        $rb->addGroupBy('name', 'Accounts');
        $rb->addGroupBy('billing_address_city', 'Accounts');

        $actual = $rb->getGroupBy();

        $expected = array(
            array(
                'name' => 'name',
                'label' => 'Name:',
                'table_key' => 'self',
                'type' => 'name',
            ),
            array(
                'name' => 'billing_address_city',
                'label' => 'Billing City:',
                'table_key' => 'self',
                'type' => 'varchar',
            ),
        );

        $this->assertSame($expected, $actual);
    }

    /**
     * @group ReportBuilder
     * @group SugarCharts
     */
    public function testGetGroupByWithFieldReturnsField()
    {
        $rb = new ReportBuilder('Accounts');
        $rb->addGroupBy('name', 'Accounts');
        $rb->addGroupBy('billing_address_city', 'Accounts');

        $actual = $rb->getGroupBy('billing_address_city');

        $expected = array(
            'name' => 'billing_address_city',
            'label' => 'Billing City:',
            'table_key' => 'self',
            'type' => 'varchar',
        );

        $this->assertSame($expected, $actual);
    }

    /**
     * @group ReportBuilder
     * @group SugarCharts
     */
    public function testRemoveGroupByReturnsTrue()
    {
        $rb = new ReportBuilder('Accounts');
        $rb->addGroupBy('name', 'Accounts');
        $rb->addGroupBy('billing_address_city', 'Accounts');

        $group_by = array(
            'name' => 'billing_address_city',
            'label' => 'Billing City:',
            'table_key' => 'self',
            'type' => 'varchar',
        );

        $this->assertTrue($rb->removeGroupBy($group_by));

    }

    /**
     * @group ReportBuilder
     * @group SugarCharts
     */
    public function testRemoveGroupByReturnsFalseWithInvalidGroupByDef()
    {
        $rb = new ReportBuilder('Accounts');
        $rb->addGroupBy('name', 'Accounts');
        $rb->addGroupBy('billing_address_city', 'Accounts');

        $this->assertFalse($rb->removeGroupBy('billing_address_city'));
    }

    /**
     * @group ReportBuilder
     * @group SugarCharts
     */
    public function testGetSummaryColumns()
    {
        $rb = new ReportBuilder('Opportunities');
        $rb->addSummaryColumn('amount');
        $rb->addSummaryColumn('best_case', null, null, array('group_function' => 'sum'));

        $expected = array(
            array(
                'name' => 'amount',
                'label' => 'Opportunity Amount:',
                'field_type' => 'currency',
                'table_key' => 'self',
            ),
            array(
                'name' => 'best_case',
                'label' => 'SUM: Best case',
                'field_type' => 'currency',
                'table_key' => 'self',
                'group_function' => 'sum',
            ),
        );

        $this->assertSame($expected, $rb->getSummaryColumns());
    }

    /**
     * @group ReportBuilder
     * @group SugarCharts
     */
    public function testGetSummaryColumnByFieldName()
    {
        $rb = new ReportBuilder('Opportunities');
        $rb->addSummaryColumn('likely_case');
        $rb->addSummaryColumn('best_case', null, null, array('group_function' => 'sum'));

        $expected = array(
            'name' => 'best_case',
            'label' => 'SUM: Best case',
            'field_type' => 'currency',
            'table_key' => 'self',
            'group_function' => 'sum',
        );

        $this->assertSame($expected, $rb->getSummaryColumns('best_case'));
    }

    /**
     * @group ReportBuilder
     * @group SugarCharts
     */
    public function testRemoveSummaryColumnReturnsTrue()
    {
        $rb = new ReportBuilder('Opportunities');
        $rb->addSummaryColumn('amount');
        $rb->addSummaryColumn('best_case', null, null, array('group_function' => 'sum'));

        $summary = array(
            'name' => 'best_case',
            'label' => 'SUM: Best case',
            'field_type' => 'currency',
            'table_key' => 'self',
            'group_function' => 'sum',
        );

        $this->assertTrue($rb->removeSummaryColumn($summary));
    }

    /**
     * @group ReportBuilder
     * @group SugarCharts
     */
    public function testRemoveSummaryColumnDoesntExist()
    {
        $rb = new ReportBuilder('Opportunities');
        $rb->addSummaryColumn('amount');
        $rb->addSummaryColumn('best_case', null, null, array('group_function' => 'sum'));

        $expected = array(
            array(
                'name' => 'amount',
                'label' => 'Opportunity Amount:',
                'field_type' => 'currency',
                'table_key' => 'self',
            )
        );

        $remove = array(
            'name' => 'best_case',
            'label' => 'SUM: Best case',
            'field_type' => 'currency',
            'table_key' => 'self',
            'group_function' => 'sum',
        );

        $rb->removeSummaryColumn($remove);

        $this->assertSame($expected, $rb->getSummaryColumns());
    }

    /**
     * @group ReportBuilder
     * @group SugarCharts
     */
    public function testSetChartColumnViaFieldName()
    {
        $rb = new ReportBuilder('Opportunities');
        $rb->addSummaryColumn('likely_case');
        $rb->addSummaryColumn('best_case', null, null, array('group_function' => 'sum'));

        $rb->setChartColumn('best_case');

        $this->assertEquals('self:best_case:sum', $rb->getChartColumn());
    }

    /**
     * @group ReportBuilder
     * @group SugarCharts
     */
    public function testSetChartColumnViaSummaryColumn()
    {
        $rb = new ReportBuilder('Opportunities');
        $rb->addSummaryColumn('likely_case');
        $rb->addSummaryColumn('best_case', null, null, array('group_function' => 'sum'));

        $rb->setChartColumn($rb->getSummaryColumns('best_case'));

        $this->assertEquals('self:best_case:sum', $rb->getChartColumn());
    }

    /**
     * @group ReportBuilder
     * @group SugarCharts
     */
    public function testGetChartColumnType()
    {
        $rb = new ReportBuilder('Opportunities');
        $rb->addSummaryColumn('likely_case');
        $rb->addSummaryColumn('best_case', null, null, array('group_function' => 'sum'));

        $rb->setChartColumn('best_case');

        $this->assertEquals('currency', $rb->getChartColumnType());
    }

    /**
     * Create A Test Report
     *
     * @return SavedReport|SugarBean
     */
    protected function createTestReport()
    {
        $report = '{"display_columns":[],"module":"Opportunities","group_defs":[{"name":"opportunity_type","label":"Type","table_key":"self","type":"enum","force_label":"Type"}],"summary_columns":[{"name":"opportunity_type","label":"Type","table_key":"self"},{"name":"count","label":"Count","field_type":"","group_function":"count","table_key":"self"}],"report_name":"UnitTestReport","chart_type":"pieF","do_round":1,"chart_description":"","numerical_chart_column":"self:count","numerical_chart_column_type":"","assigned_user_id":"1","report_type":"summary","full_table_list":{"self":{"value":"Opportunities","module":"Opportunities","label":"Opportunities"},"Opportunities:assigned_user_link":{"name":"Opportunities  >  Assigned to User","parent":"self","link_def":{"name":"assigned_user_link","relationship_name":"opportunities_assigned_user","bean_is_lhs":false,"link_type":"one","label":"Assigned to User","module":"Users","table_key":"Opportunities:assigned_user_link"},"dependents":["Filter.1_table_filter_row_1","Filter.1_table_filter_row_1"],"module":"Users","label":"Assigned to User"}},"filters_def":{"Filter_1":{"operator":"AND","0":{"name":"user_name","table_key":"Opportunities:assigned_user_link","qualifier_name":"reports_to","input_name0":["seed_chris_id"]}}}}';
        /* @var $saved_report SavedReport */
        $saved_report = BeanFactory::getBean('Reports');
        $saved_report->save_report(-1, 1, 'TestReport', 'Opportunities', 'summary', $report, 1, 1, 'pieF');

        return $saved_report;
    }

    protected function removeTestReport($report_id)
    {
        $GLOBALS['db']->query("DELETE FROM saved_reports WHERE name IN ('" . $report_id . "')");
    }
}