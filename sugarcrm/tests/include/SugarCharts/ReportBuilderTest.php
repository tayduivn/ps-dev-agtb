<?php

require_once('include/SugarCharts/ReportBuilder.php');

class ReportBuilderTest extends Sugar_PHPUnit_Framework_TestCase
{
    public function setUp()
    {
    }

    public function tearDown()
    {
    }

    public function testConstructorSetsModule()
    {
        $rb = new ReportBuilder('Accounts');
        $actual_json = $rb->toJson();
        $actual = $this->objectToArray(json_decode($actual_json));

        $this->assertEquals('Accounts', $actual['module']);
    }

    public function testConstructorSetsSelfTable()
    {
        $rb = new ReportBuilder('Accounts');
        $actual_json = $rb->toJson();
        $actual = $this->objectToArray(json_decode($actual_json));

        $this->assertSame(array('self' => array(
            'value' => 'Accounts',
            'module' => 'Accounts',
            'label' => 'Accounts',
            'parent' => '',
            'children' => array())), $actual['full_table_list']);
    }

    public function testAddModuleWithKey()
    {
        $rb = new ReportBuilder('Accounts');
        $rb->addModule('Contacts', 'contacts');
        $actual_json = $rb->toJson();
        $actual = $this->objectToArray(json_decode($actual_json));

        $this->assertSame(array(
            'value' => 'Contacts',
            'module' => 'Contacts',
            'label' => 'Contacts',
            'parent' => '',
            'children' => array()), $actual['full_table_list']['contacts']);
    }

    public function testAddModuleWithoutKey()
    {
        $rb = new ReportBuilder('Accounts');
        $rb->addModule('Contacts');
        $actual_json = $rb->toJson();
        $actual = $this->objectToArray(json_decode($actual_json));

        $this->assertSame(array(
            'value' => 'Contacts',
            'module' => 'Contacts',
            'label' => 'Contacts',
            'parent' => '',
            'children' => array()), $actual['full_table_list']['Contacts']);
    }

    public function testGetTableKeyWithModuleReturnsString()
    {
        $rb = new ReportBuilder('Accounts');
        $this->assertEquals('self', $rb->getKeyTable('Accounts'));
    }

    public function testGetTableKeyWithNotModuleReturnsArray()
    {
        $rb = new ReportBuilder('Accounts');
        $this->assertSame(array('Accounts' => 'self'), $rb->getKeyTable());
    }

    public function testGetBeanReturnsAccountSugarBeanFromCache()
    {
        $rb = new ReportBuilder('Accounts');
        $this->assertInstanceOf('Account', $rb->getBean('Accounts'));
    }

    public function testGetBeanReturnsContactSugarBeanAfterCreate()
    {
        $rb = new ReportBuilder('Accounts');
        $this->assertInstanceOf('Contact', $rb->getBean('Contacts'));
    }

    public function testGetDefaultModuleAsString()
    {
        $rb = new ReportBuilder('Accounts');
        $this->assertEquals('Accounts', $rb->getDefaultModule());
    }

    public function testGetDefaultModuleAsAccountBean()
    {
        $rb = new ReportBuilder('Accounts');
        $this->assertInstanceOf('Account', $rb->getDefaultModule(true));
    }

    public function testAddSummaryCount()
    {
        $rb = new ReportBuilder('Accounts');
        $rb->addSummaryCount();
        $actual_json = $rb->toJson();
        $actual = $this->objectToArray(json_decode($actual_json));

        $this->assertSame(array(
            'name' => 'count',
            'label' => 'Count',
            'table_key' => 'self',
            'group_function' => "count",
            'field_type' => ''
        ), $actual['summary_columns'][0]);
    }

    public function testAddSummaryColumnWithoutModule()
    {
        $rb = new ReportBuilder('Accounts');
        $rb->addSummaryColumn('name');
        $actual_json = $rb->toJson();
        $actual = $this->objectToArray(json_decode($actual_json));

        $this->assertSame(array(
            'name' => "name",
            'label' => "LBL_NAME",
            'table_key' => "self",
        ), $actual['summary_columns'][0]);
    }

    public function testAddSummaryColumnWithModuleAsString()
    {
        $rb = new ReportBuilder('Accounts');
        $rb->addSummaryColumn('name', 'Accounts');
        $actual_json = $rb->toJson();
        $actual = $this->objectToArray(json_decode($actual_json));

        $this->assertSame(array(
            'name' => "name",
            'label' => "LBL_NAME",
            'table_key' => "self",
        ), $actual['summary_columns'][0]);
    }

    public function testAddSummaryColumnWithModuleAsSugarBean()
    {
        $rb = new ReportBuilder('Accounts');
        $rb->addSummaryColumn('name', $rb->getBean('Accounts'));
        $actual_json = $rb->toJson();
        $actual = $this->objectToArray(json_decode($actual_json));

        $this->assertSame(array(
            'name' => "name",
            'label' => "LBL_NAME",
            'table_key' => "self",
        ), $actual['summary_columns'][0]);
    }

    public function testAddGroupByWithModule()
    {
        $rb = new ReportBuilder('Accounts');
        $rb->addGroupBy('name', 'Accounts');
        $actual_json = $rb->toJson();
        $actual = $this->objectToArray(json_decode($actual_json));

        $this->assertSame(array(
            'name' => "name",
            'label' => "LBL_NAME",
            'table_key' => "self",
            'type' => 'name',
        ), $actual['group_defs'][0]);
    }

    public function testAddGroupByWithoutModule()
    {
        $rb = new ReportBuilder('Accounts');
        $rb->addGroupBy('name');
        $actual_json = $rb->toJson();
        $actual = $this->objectToArray(json_decode($actual_json));

        $this->assertSame(array(
            'name' => "name",
            'label' => "LBL_NAME",
            'table_key' => "self",
            'type' => 'name',
        ), $actual['group_defs'][0]);
    }

    public function testAddLinkSetsTableInList()
    {
        $rb = new ReportBuilder('Accounts');
        $rb->addLink('contacts', 'name');
        $actual_json = $rb->toJson();
        $actual = $this->objectToArray(json_decode($actual_json));

        $this->assertTrue(isset($actual['full_table_list']['Accounts:contacts']));
    }

    public function testAddLinkSetsFieldInSummaryColumns()
    {
        $rb = new ReportBuilder('Accounts');
        $rb->addLink('contacts', 'name');
        $actual_json = $rb->toJson();
        $actual = $this->objectToArray(json_decode($actual_json));

        $this->assertEquals('Accounts:contacts', $actual['summary_columns'][0]['table_key']);
    }

    public function testAddLinkSetsFieldInGroupDefs()
    {
        $rb = new ReportBuilder('Accounts');
        $rb->addLink('contacts', 'name');
        $actual_json = $rb->toJson();
        $actual = $this->objectToArray(json_decode($actual_json));

        $this->assertEquals('Accounts:contacts', $actual['group_defs'][0]['table_key']);
    }

    public function testAddLinkToAccountModule()
    {
        $rb = new ReportBuilder('Accounts');
        $rb->addLink('member_of', 'name');
        $actual_json = $rb->toJson();
        $actual = $this->objectToArray(json_decode($actual_json));

        $this->assertEquals('Accounts:member_of', $actual['group_defs'][0]['table_key']);
    }

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
        $actual = $this->objectToArray(json_decode($actual_json));

        $this->assertSame($filter, $actual['filters_def']);
    }

    protected function objectToArray($d)
    {
        if (is_object($d)) {
            // Gets the properties of the given object
            // with get_object_vars function
            $d = get_object_vars($d);
        }

        if (is_array($d)) {
            /**
             * Return array converted to object
             * Using __FUNCTION__ (Magic constant)
             * for recursive call
             */
            return array_map(array(__CLASS__, __FUNCTION__), $d);
        }
        else {
            // Return array
            return $d;
        }
    }
}