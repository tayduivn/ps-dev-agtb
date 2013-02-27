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

require_once('include/ListView/ListViewData.php');

/**
 * Bug #58890
 * ListView Does Not Retain Sort Order
 *
 * @author mgusev@sugarcrm.com
 * @ticked 58890
 */
class Bug58890Test extends Sugar_PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        SugarTestHelper::setUp('beanFiles');
        SugarTestHelper::setUp('beanList');
        SugarTestHelper::setUp('current_user');
    }

    public function tearDown()
    {
        SugarTestHelper::tearDown();
    }

    /**
     * Test asserts order by value
     *
     * @group 58890
     * @return void
     */
    public function testOrderBy()
    {
        $bean = new SugarBean58890();
        $listViewData = new ListViewData();
        $listViewData->listviewName = $bean->module_name;

        $listViewData->getListViewData($bean, '', -1, -1, array('name' => array()));
        $this->assertEquals('date_entered DESC', $bean->orderByString58890, 'Order by date_entered DESC should be used');

        $GLOBALS['current_user']->setPreference('listviewOrder', array(
            'orderBy' => 'name',
            'sortOrder' => 'ASC'
        ), 0, $listViewData->var_name);

        $listViewData->getListViewData($bean, '', -1, -1, array('name' => array()));
        $this->assertEquals('name ASC', $bean->orderByString58890, 'User\'s preference should be used');
    }
}

class SugarBean58890 extends Account
{
    /**
     * @var string
     */
    public $orderByString58890 = '';

    public function create_new_list_query($order_by, $where, $filter = array(), $params = array(), $show_deleted = 0, $join_type = '', $return_array = false, $parentbean = null, $singleSelect = false, $ifListForExport = false)
    {
        $this->orderByString58890 = $order_by;
        return parent::create_new_list_query($order_by, $where, $filter, $params, $show_deleted, $join_type, $return_array, $parentbean, $singleSelect, $ifListForExport);
    }
}
