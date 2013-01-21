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

require_once 'modules/Quotes/Quote.php';
require_once 'include/SearchForm/SearchForm2.php';

class Bug47537Test extends Sugar_PHPUnit_Framework_TestCase {

    var $module = 'Quotes';
    var $action = 'index';
    var $seed;
    var $form;
    var $array;

    public function setUp()
    {
        SugarTestHelper::setUp('beanList');
        SugarTestHelper::setUp('current_user');

        require "modules/".$this->module."/metadata/searchdefs.php";
        require "modules/".$this->module."/metadata/SearchFields.php";
        require "modules/".$this->module."/metadata/listviewdefs.php";

        $this->seed = BeanFactory::newBean($this->module);
        $this->form = new SearchForm($this->seed, $this->module, $this->action);
        $this->form->setup($searchdefs, $searchFields, 'include/SearchForm/tpls/SearchFormGeneric.tpl', "advanced_search", $listViewDefs);

        $this->array = array(
            'module'=>$this->module,
            'action'=>$this->action,
            'searchFormTab'=>'advanced_search',
            'query'=>'true',
            'quote_num_advanced_range_choice'=>'',
            'range_quote_num_advanced' => '',
            'start_quote_num_entered_advanced' => '',
            'end_quote_num_entered_advanced' => '',
        );
        parent::setUp();
    }

    public function tearDown() {
        unset($this->array);
        unset($this->form);
        unset($this->seed);
        SugarTestHelper::tearDown();
        parent::tearDown();
    }

    /**
     * Data provider for single integer range searches
     * @return array data for tests
     */
    function singleIntRangeProvider() {
        return array(
            array("=", "1", array(strtolower($this->module).".quote_num = 1")),
            array("not_equal", "1", array("(". strtolower($this->module).".quote_num IS NULL OR " . strtolower($this->module) . ".quote_num != 1)")),
            array("greater_than", "1", array(strtolower($this->module).".quote_num > 1")),
            array("greater_than_equals", "1", array(strtolower($this->module).".quote_num >= 1")),
            array("less_than", "1", array(strtolower($this->module).".quote_num < 1")),
            array("less_than_equals", "1", array(strtolower($this->module).".quote_num <= 1")),
        );
    }

    /**
     * Tests single integer advanced searches
     * @dataProvider singleIntRangeProvider
     * @param $op operator from dataProvider
     * @param $val values from dataProvider
     * @param $expected expected result from dataProvider
     */
    public function testAdvancedSearchForInt($op, $val, $expected) {
        $this->array['quote_num_advanced_range_choice'] = $op;
        $this->array['range_quote_num_advanced'] = $val;

        $this->form->populateFromArray($this->array);
        $query = $this->form->generateSearchWhere($this->seed, $this->module);
        $this->assertSame($expected, $query);
    }


    public function testAdvancedSearchForIntBetween() {
        $this->array['quote_num_advanced_range_choice'] = 'between';
        $this->array['start_range_quote_num_advanced'] = '1';
        $this->array['end_range_quote_num_advanced'] = '3';
        $expected = array("(". strtolower($this->module).".quote_num >= 1 AND ".strtolower($this->module).".quote_num <= 3)");

        $this->form->populateFromArray($this->array);
        $query = $this->form->generateSearchWhere($this->seed, $this->module);
        $this->assertSame($expected, $query);
    }

}

