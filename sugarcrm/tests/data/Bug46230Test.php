<?php
//FILE SUGARCRM flav=pro ONLY
/* * *******************************************************************************
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
 * by SugarCRM are Copyright (C) 2004-2011 SugarCRM, Inc.; All Rights Reserved.
 * ****************************************************************************** */

/**
 * Bug #46230
 * Dependent Field values are not refreshed in subpanels & listviews
 * @ticket 49878
 */
class Bug46230Test extends Sugar_PHPUnit_Framework_TestCase
{
    private $account;
    private $stored_service_object;
	private $account2;

    public function setUp()
    {
        $GLOBALS['current_user'] = SugarTestUserUtilities::createAnonymousUser();
        $this->account = SugarTestAccountUtilities::createAccount();

        //Unset global service_object variable so that the code in updateDependencyBean is run in SugarBean.php
        if(isset($GLOBALS['service_object'])) {
            $this->stored_service_object = $GLOBALS['service_object'];
            unset($GLOBALS['service_object']);
        }

		$this->account2 = SugarTestAccountUtilities::createAccount();
        $this->account2->account_type = 'Analyst';
        $this->account2->industry = 'Energy';
        $this->account2->field_defs['industry']['dependency'] = 'or(equal($account_type,"Analyst"),equal($account_type,"Customer"))';
        $this->account2->save();
    }

    public function tearDown()
    {
        SugarTestAccountUtilities::removeAllCreatedAccounts();
        SugarTestUserUtilities::removeAllCreatedAnonymousUsers();
        unset($GLOBALS['current_user']);
        if(!empty($this->stored_service_object)) {
            $GLOBALS['service_object'] = $this->stored_service_object;
        }
    }

    public function providerData()
    {
        return array(
            array('Partner', 'Banking', '1'),
            array('Analyst', 'Energy', '0'),
            array('Customer', 'Education', '0'),
            );
    }
    /**
     * @dataProvider providerData
     * @group 46230
     */
    public function testGetListViewArray($type, $industry, $is_industry_hidden)
    {
        $this->account->account_type = $type;
        $this->account->industry = $industry;
        $dependency = 'or(equal($account_type,"Analyst"),equal($account_type,"Customer"))';
        $this->account->field_defs['industry']['dependency'] = $dependency;

        $this->account->updateDependentFieldForListView();

        $res = $this->account->get_list_view_array();

        if ($is_industry_hidden == '1')
        {
            $this->assertEmpty($res['INDUSTRY']);
        }
        else
        {
            $this->assertNotEmpty($res['INDUSTRY']);
        }
		
		$this->account->updateDependentField();

        if ($is_industry_hidden == '1')
        {
            $this->assertEmpty($res['INDUSTRY']);
        }
        else
        {
            $this->assertNotEmpty($res['INDUSTRY']);
        }
    }
	
	/**
     * @group 54042
     */
    function testRetrieveBeanUpdateDependentFields()
    {
       $this->account->retrieve($this->account2->id);
       $res = $this->account->get_list_view_array();
       $this->assertNotEmpty($res['INDUSTRY']);
    }

    /**
     * @group 54042
     */
    function testRetrieveByStringFieldsBeanUpdateDependentFields()
    {
       $this->account->retrieve_by_string_fields(array('id'=>$this->account2->id));
       $res = $this->account->get_list_view_array();
       $this->assertNotEmpty($res['INDUSTRY']);
    }
}