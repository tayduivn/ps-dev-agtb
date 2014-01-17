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

require_once 'modules/RevenueLineItems/clients/base/api/RevenueLineItemsGlobeChartApi.php';
require_once 'SugarTestForecastUtilities.php';

/**
 * Tests RevenueLineItemsGlobeChartApiTest.
 */
class RevenueLineItemsGlobeChartApiTest extends Sugar_PHPUnit_Framework_TestCase
{
    /**
     * @var SugarApi
     */
    protected $api;

    /**
     * @var User
     */
    protected $current_user;

    protected function setUp()
    {
        parent::setUp();
        SugarTestHelper::setUp('app_list_strings');
        SugarTestHelper::setUp('beanFiles');
        SugarTestHelper::setUp('beanList');
        $this->current_user = SugarTestHelper::setUp('current_user', array(true, false));
        $this->api = new RevenueLineItemsGlobeChartApi();
    }

    protected function tearDown()
    {
        SugarTestAccountUtilities::removeAllCreatedAccounts();
        SugarTestOpportunityUtilities::removeAllCreatedOpportunities();
        SugarTestRevenueLineItemUtilities::removeAllCreatedRevenueLineItems();
        SugarTestHelper::tearDown();
        parent::tearDown();
    }

    public function testSalesByCountry()
    {
        $account = SugarTestAccountUtilities::createAccount();
        $account->billing_address_country = 'TestCountryName';
        $account->billing_address_state = 'TestStateName';
        $account->save();

        $opp = SugarTestOpportunityUtilities::createOpportunity(null, $account);
        $opp->teams->replace(array($this->current_user->team_id));
        $opp->save();

        $rli1 = SugarTestRevenueLineItemUtilities::createRevenueLineItem();
        $rli1->opportunity_id  = $opp->id;
        $rli1->sales_stage = 'Closed Won';
        $rli1->teams->replace(array($this->current_user->team_id));
        $rli1->save();

        $rli2 = SugarTestRevenueLineItemUtilities::createRevenueLineItem();
        $rli2->opportunity_id  = $opp->id;
        $rli2->sales_stage = 'Closed Won';
        $rli2->teams->replace(array($this->current_user->team_id));
        $rli2->save();

        $result = $this->api->salesByCountry(
            SugarTestRestUtilities::getRestServiceMock($this->current_user),
            array('module' => 'RevenueLineItems')
        );

        $this->assertArrayHasKey('TestCountryName', $result);

        $countryGroup = $result['TestCountryName'];
        $this->assertArrayHasKey('TestStateName', $countryGroup);
        $this->assertArrayHasKey('_total', $countryGroup);

        $stateGroup = $countryGroup['TestStateName'];
        $this->assertArrayHasKey('_total', $stateGroup);
    }

}
