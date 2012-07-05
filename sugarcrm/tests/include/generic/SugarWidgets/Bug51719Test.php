<?php
//FILE SUGARCRM flav=pro ONLY
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

require_once 'modules/Reports/Report.php';

/**
 * Bug #51719
 * [Oracle]: No data display in Summation with Detail report when used with Is Not Empty filter
 *
 * @author mgusev@sugarcrm.com
 * @ticked 51719
 */
class Bug51719Test extends Sugar_PHPUnit_Framework_TestCase
{
    /**
     * @var Account
     */
    protected $account = null;

    public function setUp()
    {
        $this->markTestIncomplete("Disabling test after speaking to Frank, we will introduce empty and nonempty in the db layer to handle this better");
        SugarTestHelper::setUp('beanList');
        SugarTestHelper::setUp('beanFiles');
        SugarTestHelper::setUp('current_user');

        $this->account = SugarTestAccountUtilities::createAccount();
    }

    public function tearDown()
    {
        SugarTestAccountUtilities::removeAllCreatedAccounts();
        SugarTestHelper::tearDown();
    }

    /**
     * Tries to assert that we got correct account by $where part for empty account_type
     *
     * @group 51719
     * @return void
     */
    public function testQueryFilterEmpty()
    {
        $this->account->account_type = '';
        $this->account->industry = '';
        $this->account->save();

        $layout_def = array(
            'column_key' => 'self:account_type',
            'input_name0' => 'empty',
            'input_name1' => 'on',
            'name' => 'account_type',
            'qualifier_name' => 'empty',
            'runtime' => 1,
            'table_alias' => 'accounts',
            'tablekey' => 'self',
            'type' => 'enum'
        );
        $report = new Report();
        $layoutManager = new LayoutManager();
        $layoutManager->setAttributePtr('reporter', $report);
        $sugarWidgetFieldEnum = new SugarWidgetFieldEnum($layoutManager);
        $where = $sugarWidgetFieldEnum->queryFilter($layout_def);
        if ($where != '')
        {
            $where .= " AND accounts.id = '" . $this->account->id . "'";
        }
        $query = $this->account->create_new_list_query('', $where, array('id'));
        $account = $GLOBALS['db']->fetchOne($query);

        $this->assertEquals($this->account->id, $account['id'], 'Returned incorrect account');
    }

    /**
     * Tries to assert that we got correct account by $where part for not empty account_type
     *
     * @group 51719
     * @return void
     */
    public function testQueryFilterNot_Empty()
    {
        $this->account->account_type = 'Analyst';
        $this->account->industry = 'Apparel';
        $this->account->save();

        $layout_def = array(
            'column_key' => 'self:account_type',
            'input_name0' => 'not_empty',
            'input_name1' => 'on',
            'name' => 'account_type',
            'qualifier_name' => 'not_empty',
            'runtime' => 1,
            'table_alias' => 'accounts',
            'tablekey' => 'self',
            'type' => 'enum'
        );
        $report = new Report();
        $layoutManager = new LayoutManager();
        $layoutManager->setAttributePtr('reporter', $report);
        $sugarWidgetFieldEnum = new SugarWidgetFieldEnum($layoutManager);
        $where = $sugarWidgetFieldEnum->queryFilter($layout_def);
        if ($where != '')
        {
            $where .= " AND accounts.id = '" . $this->account->id . "'";
        }
        $query = $this->account->create_new_list_query('', $where, array('id'));
        $account = $GLOBALS['db']->fetchOne($query);

        $this->assertEquals($this->account->id, $account['id'], 'Returned incorrect account');
    }
}
