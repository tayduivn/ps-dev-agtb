<?php
if(!defined('sugarEntry') || !sugarEntry) die('Not A Valid Entry Point');
/*********************************************************************************
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


require_once "include/generic/LayoutManager.php";
require_once "include/generic/SugarWidgets/SugarWidgetFieldcurrency.php";
class SugarWidgetFieldcurrencyTest extends Sugar_PHPUnit_Framework_TestCase
{
    /**
     * @var SugarWidgetFieldcurrency
     */
    protected $widgetField;

    public function setUp()
    {
        global $current_user;
        $this->widgetField = new SugarWidgetFieldcurrency(new LayoutManager());
        $current_user = SugarTestUserUtilities::createAnonymousUser();
    }

    public function tearDown()
    {
        unset($this->widgetField);
        SugarTestUserUtilities::removeAllCreatedAnonymousUsers();
    }

    /**
     * @group SugarWidgetField
     * @group LessEqual
     */
    public function testQueryFilterLess_Equal()
    {
        $layout_def =  array ('name' => 'donotinvoiceuntil_c', 'table_key' => 'self', 'qualifier_name' => 'Less_Equal', 'input_name0' => '$1.12', 'input_name1' => 'on', 'table_alias' => 'pordr_purchaseorders_cstm', 'column_key' => 'self:donotinvoiceuntil_c', 'type' => 'int');
        $filter = $this->widgetField->queryFilterLess_Equal($layout_def);

        $this->assertEquals("pordr_purchaseorders_cstm.donotinvoiceuntil_c <= 1.12\n", $filter);
    }

    /**
     * @group SugarWidgetField
     * @group GreaterEqual
     */
    public function testQueryFilterGreater_Equal()
    {
        $layout_def =  array ('name' => 'donotinvoiceuntil_c', 'table_key' => 'self', 'qualifier_name' => 'Greater_Equal', 'input_name0' => '$1.12', 'input_name1' => 'on', 'table_alias' => 'pordr_purchaseorders_cstm', 'column_key' => 'self:donotinvoiceuntil_c', 'type' => 'int');
        $filter = $this->widgetField->queryFilterGreater_Equal($layout_def);

        $this->assertEquals("pordr_purchaseorders_cstm.donotinvoiceuntil_c >= 1.12\n", $filter);
    }
}
