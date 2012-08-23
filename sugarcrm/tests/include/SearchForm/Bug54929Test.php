<?php
/*********************************************************************************
 * The contents of this file are subject to the SugarCRM Master Subscription
 * Agreement ("License") which can be viewed at
 * http://www.sugarcrm.com/crm/en/msa/master_subscription_agreement_11_April_2011.pdf
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
 * by SugarCRM are Copyright (C) 2004-2011 SugarCRM, Inc.; All Rights Reserved.
 ********************************************************************************/

require_once('include/SearchForm/SearchForm2.php');

/**
 * Bug #54929
 * Search Filtering is Broken when using Numbers as the Item Names in the Sales Stage Dropdown Menu
 *
 * @author vromanenko@sugarcrm.com
 * @ticked 54929
 */
class Bug54929Test extends Sugar_PHPUnit_Framework_TestCase
{
    /**
     * @var SearchForm
     */
    protected $searchForm;

    /**
     * @var Opportunity
     */
    protected $seed;

    protected $module;

    protected $action;

    protected $normalAppListStringsOfSalesStageDom;

    protected function setUp()
    {
        SugarTestHelper::setUp('app_list_strings');
        $this->seed = new Opportunity();
        $this->module = 'Opportunities';
        $this->action = 'index';

        $this->normalAppListStringsOfSalesStageDom = $GLOBALS['app_list_strings']['sales_stage_dom'];
        $GLOBALS['app_list_strings']['sales_stage_dom'] = array(
            ''      => '',
            '00'    => '0-zero',
            '10'    => '10-ten',
            '100'   => '100-hundred'
        );
    }

    protected function tearDown()
    {
        $GLOBALS['app_list_strings']['sales_stage_dom'] = $this->normalAppListStringsOfSalesStageDom;
    }

    /**
     * Test that indexes of the sales stage field options has not been changed.
     * @group bug54929
     */
    public function testIntegerIndexesOfMultiSelectFieldOptionsOnTheAdvancedSearch()
    {
        $searchMetaData = SearchForm::retrieveSearchDefs($this->module);
        $this->searchForm = new SearchForm($this->seed, $this->module, $this->action);
        $this->searchForm->setup(
            $searchMetaData['searchdefs'],
            $searchMetaData['searchFields'],
            'SearchFormGeneric.tpl',
            'advanced_search',
            array()
        );
        $result = $this->searchForm->fieldDefs['sales_stage_advanced']['options'];

        $this->assertArrayHasKey('', $result);
        $this->assertArrayHasKey('00', $result);
        $this->assertArrayHasKey(10, $result);
        $this->assertArrayHasKey(100, $result);
        $this->assertEquals(4, count($result));
    }

}