<?php
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

require_once('include/SugarCharts/Jit/JitReports.php');

class Bug52901Test extends Sugar_PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        SugarTestHelper::setUp('current_user');
    }

    public function tearDown()
    {
        SugarTestHelper::tearDown();
    }


    /**
     * DataProvider function for test
     * @static
     * @return array
     */
    public static function dataFeed()
    {
        $dataSeed = array(
            // Accounts:
            // 1. type = '', industry = '';
            // 2. type = 'Bar', industry = 'Foo'
            'emptyTypeAndIndustry' => array(
                array(
                    '' =>
                    array(
                        '' =>
                        array(
                            'numerical_value' => 1,
                            'group_text' => '',
                            'group_key' => 'self:account_type',
                            'count' => '',
                            'group_label' => 'someHtml',
                            'numerical_label' => 'someHtml',
                            'numerical_key' => 'count',
                            'module' => 'Accounts',
                            'group_base_text' => '',
                        ),
                    ),
                    'Bar' =>
                    array(
                        'Foo' =>
                        array(
                            'numerical_value' => 1,
                            'group_text' => 'Bar',
                            'group_key' => 'self:account_type',
                            'count' => '',
                            'group_label' => 'someHtml',
                            'numerical_label' => 'someHtml',
                            'numerical_key' => 'count',
                            'module' => 'Accounts',
                            'group_base_text' => 'Foo',
                        ),
                    ),
                ),
                array('', 'Foo'),
                array('type', 'industry'),
                array('', 'Foo', '', 'Foo'),
            ),
            // Accounts:
            // 1. type = 'Foo', industry = 'Bar';
            // 2. type = 'Bar', industry = 'Foo'
            'bothDifferentTypeAndIndustry' => array(
                array(
                    'Foo' =>
                    array(
                        'Bar' =>
                        array(
                            'numerical_value' => 1,
                            'group_text' => 'Foo',
                            'group_key' => 'self:account_type',
                            'count' => '',
                            'group_label' => 'someHtml',
                            'numerical_label' => 'someHtml',
                            'numerical_key' => 'count',
                            'module' => 'Accounts',
                            'group_base_text' => 'Bar',
                        ),
                    ),
                    'Bar' =>
                    array(
                        'Foo' =>
                        array(
                            'numerical_value' => 1,
                            'group_text' => 'Bar',
                            'group_key' => 'self:account_type',
                            'count' => '',
                            'group_label' => 'someHtml',
                            'numerical_label' => 'someHtml',
                            'numerical_key' => 'count',
                            'module' => 'Accounts',
                            'group_base_text' => 'Foo',
                        ),
                    ),
                ),
                array('Foo', 'Bar'),
                array('type', 'industry'),
                array('Foo', 'Bar', 'Foo', 'Bar'),
            ),
            // Accounts:
            // 1. type = 'Foo', industry = 'Foo';
            // 2. type = 'Bar', industry = 'Bar'
            'bothEqualTypeAndIndustry' => array(
                array(
                    'Bar' =>
                    array(
                        'Bar' =>
                        array(
                            'numerical_value' => 1,
                            'group_text' => 'Bar',
                            'group_key' => 'self:account_type',
                            'count' => '',
                            'group_label' => 'someHtml',
                            'numerical_label' => 'someHtml',
                            'numerical_key' => 'count',
                            'module' => 'Accounts',
                            'group_base_text' => 'Bar',
                        ),
                    ),
                    'Foo' =>
                    array(
                        'Foo' =>
                        array(
                            'numerical_value' => 1,
                            'group_text' => 'Foo',
                            'group_key' => 'self:account_type',
                            'count' => '',
                            'group_label' => 'someHtml',
                            'numerical_label' => 'someHtml',
                            'numerical_key' => 'count',
                            'module' => 'Accounts',
                            'group_base_text' => 'Foo',
                        ),
                    ),
                ),
                array('Foo', 'Bar'),
                array('type', 'industry'),
                array('Foo', 'Bar', 'Foo', 'Bar'),
            ),
            // Accounts: Single group by. only by type
            // 1. type = 'Foo'
            // 2. type = 'Bar'
            'onlyByType' => array(
                array(
                    'Bar' =>
                    array(
                        'Bar' =>
                        array(
                            'numerical_value' => 1,
                            'group_text' => 'Bar',
                            'group_key' => 'self:account_type',
                            'count' => '',
                            'group_label' => 'someHtml',
                            'numerical_label' => 'someHtml',
                            'numerical_key' => 'count',
                            'module' => 'Accounts',
                            'group_base_text' => 'Bar',
                        ),
                    ),
                    'Foo' =>
                    array(
                        'Foo' =>
                        array(
                            'numerical_value' => 1,
                            'group_text' => 'Foo',
                            'group_key' => 'self:account_type',
                            'count' => '',
                            'group_label' => 'someHtml',
                            'numerical_label' => 'someHtml',
                            'numerical_key' => 'count',
                            'module' => 'Accounts',
                            'group_base_text' => 'Foo',
                        ),
                    ),
                ),
                array('Foo', 'Bar'),
                array('type'),
                array(),
            ),
        );

        return $dataSeed;
    }

    /**
     * Test that <subgroups> is filled properly
     *
     * @param $dataSet  array dataSet for JitReports
     * @param $superSet array super_set for JitReports
     * @param $groupBy array array of group_by levels
     * @param $expectedSubgroupNodesTitles array expected list of values of node <title> in each node <subgroups>
     *
     * @dataProvider dataFeed
     * @group 52901
     */
    public function testXMLIsGeneratedProperly($dataSet, $superSet, $groupBy, $expectedSubgroupNodesTitles)
    {
        $JR = new JitReports();
        $JR->setData($dataSet);
        $JR->super_set = $superSet;
        $JR->setDisplayProperty('thousands', false);
        $JR->group_by = $groupBy;

        // We do this because the function which is under the test (xmlDataReportChart()) returns XML without root node and thus causes XML parse error
        $actualXML = '<data>' . $JR->xmlDataReportChart() . '</data>';

        // Get the list of <title> node value elements of each <subgroup>
        $dom = new DomDocument();
        $dom->loadXML($actualXML);
        $xpath = new DomXPath($dom);
        $nodes = $xpath->query('group/subgroups/group/title');
        $actualSubgroupNodesTitlesArray = array();
        foreach ($nodes as $node)
        {
            $actualSubgroupNodesTitlesArray[] = $node->nodeValue;
        }

        $this->assertEquals($expectedSubgroupNodesTitles, $actualSubgroupNodesTitlesArray);
    }

}