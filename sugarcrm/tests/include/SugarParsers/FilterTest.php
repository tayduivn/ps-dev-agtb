<?php


require_once("include/SugarParsers/Filter.php");
class SugarParsers_FilterTest extends Sugar_PHPUnit_Framework_TestCase
{

    /**
     * @var SugarParsers_Filter
     */
    protected $obj;

    public function setUp()
    {
        $this->obj = new SugarParsers_Filter();
    }

    public function tearDown()
    {
        $filterDict = new FilterDictionary();
        $filterDict->resetCache();
        unset($this->obj);
    }

    /**
     * @group SugarParser
     */
    public function testParseEqual()
    {
        $obj = json_decode('{"billing_postalcode":"90210"}');
        $this->obj->parse($obj);
        $pFilter = $this->obj->getParsedFilter();
        $this->assertInstanceOf("SugarParsers_Filter_Equal", $pFilter['billing_postalcode']);
    }

    /**
     * @group SugarParser
     */
    public function testParseNotEqual()
    {
        $obj = json_decode('{"billing_postalcode": { "$not" : "90210" } }');
        $this->obj->parse($obj);
        $pFilter = $this->obj->getParsedFilter();
        $this->assertInstanceOf("SugarParsers_Filter_Not", $pFilter['billing_postalcode']);
        $equalObject = $pFilter['billing_postalcode']->getValue();
        $this->assertInstanceOf("SugarParsers_Filter_Equal", $equalObject);
        $this->assertSame("90210", $equalObject->getValue());

    }

    /**
     * @group SugarParser
     */
    public function testInFilterParse()
    {

        $obj = json_decode('{"billing_postalcode": { "$in" : ["90210", "46052"] }}');

        $this->obj->parse($obj);
        $pFilter = $this->obj->getParsedFilter();
        $this->assertInstanceOf("SugarParsers_Filter_In", $pFilter['billing_postalcode']);
        $inValue = $pFilter['billing_postalcode']->getValue();
        $this->assertSame(array('90210', '46052'), $inValue);

    }

    /**
     * @group SugarParser
     */
    public function testNotInFilterParse()
    {

        $obj = json_decode('{"billing_postalcode": { "$not" : { "$in" : ["90210", "46052"] }}}');

        $this->obj->parse($obj);
        $pFilter = $this->obj->getParsedFilter();
        $this->assertInstanceOf("SugarParsers_Filter_Not", $pFilter['billing_postalcode']);
        $inFilterObj = $pFilter['billing_postalcode']->getValue();
        $this->assertInstanceOf("SugarParsers_Filter_In", $inFilterObj);
        $inValue = $inFilterObj->getValue();
        $this->assertSame(array('90210', '46052'), $inValue);

    }

    /**
     * @group SugarParser
     */
    public function testParseWithAnd()
    {
        $obj = json_decode('{"$and" : [{"first_name":"William"},{"last_name":"Williamson"}] }');

        $this->obj->parse($obj);

        $pFilter = $this->obj->getParsedFilter();
        $this->assertInstanceOf("SugarParsers_Filter_And", $pFilter['0']);
        $andFilterObject = $pFilter['0']->getValue();
        $this->assertInstanceOf("SugarParsers_Filter_Equal", $andFilterObject['first_name']);
        $this->assertEquals("William", $andFilterObject['first_name']->getValue());
        $this->assertInstanceOf("SugarParsers_Filter_Equal", $andFilterObject['last_name']);
        $this->assertEquals("Williamson", $andFilterObject['last_name']->getValue());

    }
}