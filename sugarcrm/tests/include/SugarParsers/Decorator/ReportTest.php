<?php


require_once("include/SugarParsers/Decorator/Report.php");
require_once("include/SugarParsers/Filter.php");
class SugarParsers_Decorator_ReportTest extends Sugar_PHPUnit_Framework_TestCase
{

    /**
     * @var SugarParsers_Decorator_Report
     */
    protected $obj;

    /**
     * @var SugarParsers_Filter;
     */
    protected $filter;

    public function setUp()
    {
        $this->obj = new SugarParsers_Decorator_Report();
        $this->filter = new SugarParsers_Filter();
    }

    public function tearDown()
    {
        unset($this->obj);
        unset($this->filter);
        $filterDict = new FilterDictionary();
        $filterDict->resetCache();
    }

    public function testEqualFilterConvert()
    {
        $obj = json_decode('{"billing_postalcode":"90210"}');
        $this->filter->parse($obj);

        $actual = $this->filter->convert(new SugarParsers_Decorator_Report());

        $expected = array("Filter_1" => array(
            'operator' => 'AND',
            0 => array(
                'name' => 'billing_postalcode',
                'table_key' => 'self',
                'qualifier_name' => 'equals',
                'input_name0' => '90210'
            )
        ));

        $this->assertSame($expected, $actual);
    }

    public function testNotEqualFilterConvert()
    {
        $obj = json_decode('{"billing_postalcode": { "$not" : "90210" } }');
        $this->filter->parse($obj);

        $actual = $this->filter->convert(new SugarParsers_Decorator_Report());

        $expected = array("Filter_1" => array(
            'operator' => 'AND',
            0 => array(
                'name' => 'billing_postalcode',
                'table_key' => 'self',
                'qualifier_name' => 'not_equals',
                'input_name0' => '90210'
            )
        ));

        $this->assertSame($expected, $actual);
    }

    public function testInFilterConvert()
    {

        $obj = json_decode('{"billing_postalcode": { "$in" : ["90210", "46052"] }}');

        $this->filter->parse($obj);
        $actual = $this->filter->convert(new SugarParsers_Decorator_Report());

        $expected = array("Filter_1" => array(
            'operator' => 'AND',
            0 => array(
                'name' => 'billing_postalcode',
                'table_key' => 'self',
                'qualifier_name' => 'one_of',
                'input_name0' => array(
                    '90210',
                    '46052'
                )
            )
        ));

        $this->assertSame($expected, $actual);
    }

    public function testNotInFilterConvert()
    {

        $obj = json_decode('{"billing_postalcode": { "$not" : { "$in" : ["90210", "46052"] }}}');

        $this->filter->parse($obj);
        $actual = $this->filter->convert(new SugarParsers_Decorator_Report());

        $expected = array("Filter_1" => array(
            'operator' => 'AND',
            0 => array(
                'name' => 'billing_postalcode',
                'table_key' => 'self',
                'qualifier_name' => 'not_one_of',
                'input_name0' => array(
                    '90210',
                    '46052'
                )
            )
        ));

        $this->assertSame($expected, $actual);
    }

    public function testAndFilterConvert()
    {
        $obj = json_decode('{"$and" : [{"first_name":"William"},{"last_name":"Williamson"}] }');

        $this->filter->parse($obj);
        $actual = $this->filter->convert(new SugarParsers_Decorator_Report());

        $expected = array("Filter_1" => array(
            'operator' => 'AND',
            0 => array(
                'name' => 'first_name',
                'table_key' => 'self',
                'qualifier_name' => 'equals',
                'input_name0' => 'William'
            ),
            1 => array(
                'name' => 'last_name',
                'table_key' => 'self',
                'qualifier_name' => 'equals',
                'input_name0' => 'Williamson'
            )
        ));

        $this->assertSame($expected, $actual);

    }

    public function testOrFilterConvert()
    {
        $obj = json_decode('{"$or" : [{"first_name":"William"},{"first_name":"Jon"}] }');

        $this->filter->parse($obj);
        $actual = $this->filter->convert(new SugarParsers_Decorator_Report());

        $expected = array("Filter_1" => array(
            'operator' => 'OR',
            0 => array(
                'name' => 'first_name',
                'table_key' => 'self',
                'qualifier_name' => 'equals',
                'input_name0' => 'William'
            ),
            1 => array(
                'name' => 'first_name',
                'table_key' => 'self',
                'qualifier_name' => 'equals',
                'input_name0' => 'Jon'
            )
        ));

        $this->assertSame($expected, $actual);

    }
}