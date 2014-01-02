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

require_once 'clients/base/api/FilterApi.php';

/**
 * Tests RevenueLineItemsApiTest.
 */
class RS21Test extends Sugar_PHPUnit_Framework_TestCase
{
	/**
     * @var SugarApi
     */
    protected $api;

    /**
     * @var array
     */
    protected $user;


    protected function setUp()
    {
        parent::setUp();

        SugarTestHelper::setUp('app_list_strings');
        SugarTestHelper::setUp('beanFiles');
        SugarTestHelper::setUp('beanList');

        $this->user = SugarTestHelper::setUp('current_user', array(true, false));
        $this->api = new FilterApi();
    }


    protected function tearDown()
    {
        SugarTestHelper::tearDown();
        parent::tearDown();
    }

    public function testFilterList()
    {
        $result = $this->api->filterList(
            SugarTestRestUtilities::getRestServiceMock($this->user),
            array(
            	'module' => 'RevenueLineItems',
            	'fields' => 'name,opportunity_name,account_name,sales_stage,
            	probability,date_closed,commit_stage,
            	product_template_name,category_name,quantity,likely_case,
            	best_case,worst_case,quote_name,assigned_user_name,currency_id,base_rate,quote_id,
            	opportunity_id,account_id,product_template_id,category_id,assigned_user_id,my_favorite,following',
            	'max_num' => '20',
            	'order_by' => 'name:desc',
        	)
        );

        $this->assertArrayHasKey('records', $result);
        
    }
}
