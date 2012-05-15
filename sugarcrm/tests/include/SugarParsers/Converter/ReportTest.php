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

require_once("include/SugarCharts/ReportBuilder.php");
require_once("include/SugarParsers/Converter/Report.php");
require_once("include/SugarParsers/Filter.php");
class SugarParsers_Converter_ReportTest extends Sugar_PHPUnit_Framework_TestCase
{
    /**
     * @var SugarParsers_Filter;
     */
    protected $filter;

    public function setUp()
    {
        $this->filter = new SugarParsers_Filter();
    }

    public function tearDown()
    {
        unset($this->obj);
        unset($this->filter);
        $filterDict = new FilterDictionary();
        $filterDict->resetCache();
    }

    /**
     * @group SugarParser
     */
    public function testEqualFilterConvert()
    {
        $obj = json_decode('{"billing_postalcode":"90210"}');
        $this->filter->parse($obj);

        $actual = $this->filter->convert(new SugarParsers_Converter_Report());

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

    /**
     * @group SugarParser
     */
    public function testNotEqualFilterConvert()
    {
        $obj = json_decode('{"billing_postalcode": { "$not" : "90210" } }');
        $this->filter->parse($obj);

        $actual = $this->filter->convert(new SugarParsers_Converter_Report());

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

    /**
     * @group SugarParser
     */
    public function testInFilterConvert()
    {

        $obj = json_decode('{"billing_postalcode": { "$in" : ["90210", "46052"] }}');

        $this->filter->parse($obj);
        $actual = $this->filter->convert(new SugarParsers_Converter_Report());

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

    /**
     * @group SugarParser
     */
    public function testNotInFilterConvert()
    {

        $obj = json_decode('{"billing_postalcode": { "$not" : { "$in" : ["90210", "46052"] }}}');

        $this->filter->parse($obj);
        $actual = $this->filter->convert(new SugarParsers_Converter_Report());

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

    /**
     * @group SugarParser
     */
    public function testAndFilterConvert()
    {
        $obj = json_decode('{"$and" : [{"first_name":"William"},{"last_name":"Williamson"}] }');

        $this->filter->parse($obj);
        $actual = $this->filter->convert(new SugarParsers_Converter_Report());

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

    /**
     * @group SugarParser
     */
    public function testOrFilterConvert()
    {
        $obj = json_decode('{"$or" : [{"first_name":"William"},{"first_name":"Jon"}] }');

        $this->filter->parse($obj);
        $actual = $this->filter->convert(new SugarParsers_Converter_Report());

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

    /**
     * @group SugarParser
     */
    public function testEmptyFilterConvert()
    {
        $obj = json_decode('{"billing_postalcode":"$empty"}');
        $this->filter->parse($obj);

        $actual = $this->filter->convert(new SugarParsers_Converter_Report());

        $expected = array("Filter_1" => array(
            'operator' => 'AND',
            0 => array(
                'name' => 'billing_postalcode',
                'table_key' => 'self',
                'qualifier_name' => 'empty',
                'input_name0' => null
            )
        ));

        $this->assertSame($expected, $actual);
    }

    /**
     * @group SugarParser
     */
    public function testNotEmptyFilterConvert()
    {
        $obj = json_decode('{"billing_postalcode": { "$not" : "$empty" }}');
        $this->filter->parse($obj);

        $actual = $this->filter->convert(new SugarParsers_Converter_Report());

        $expected = array("Filter_1" => array(
            'operator' => 'AND',
            0 => array(
                'name' => 'billing_postalcode',
                'table_key' => 'self',
                'qualifier_name' => 'not_empty',
                'input_name0' => null
            )
        ));

        $this->assertSame($expected, $actual);
    }

    /**
     * @group SugarParser
     */
    public function testInvalidFieldFilterConvert()
    {
        $obj = json_decode('{"postalcode": { "$not" : "$empty" }}');
        $this->filter->parse($obj);

        $converter = new SugarParsers_Converter_Report();
        $converter->setReportBuilder($this->createTestReportBuilder());
        $actual = $this->filter->convert($converter);

        $expected = array("Filter_1" => array(
            'operator' => 'AND',
        ));

        $this->assertSame($expected, $actual);
    }

    public function testLinkFilterConvert()
    {
        $obj = json_decode('{"member_of":{"$and":[{"billing_address_state":"UT"},{"billing_address_country":"USA"}]}}');

        $this->filter->parse($obj);

        $converter = new SugarParsers_Converter_Report();
        $converter->setReportBuilder($this->createTestReportBuilder());

        $actual = $this->filter->convert($converter);

        $expected = array("Filter_1" => array(
            'operator' => 'AND',
            0 => array(
                'name' => 'billing_address_state',
                'table_key' => 'Accounts:member_of',
                'qualifier_name' => 'equals',
                'input_name0' => 'UT'
            ),
            1 => array(
                'name' => 'billing_address_country',
                'table_key' => 'Accounts:member_of',
                'qualifier_name' => 'equals',
                'input_name0' => 'USA'
            )
        ));

        $this->assertSame($expected, $actual);

    }

    protected function createTestReportBuilder()
    {
        $rb = new ReportBuilder('Accounts');
        return $rb;
    }
}