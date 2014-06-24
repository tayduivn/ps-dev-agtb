<?php
/*
 * Your installation or use of this SugarCRM file is subject to the applicable
 * terms available at
 * http://support.sugarcrm.com/06_Customer_Center/10_Master_Subscription_Agreements/.
 * If you do not agree to all of the applicable terms or do not have the
 * authority to bind the entity as an authorized representative, then do not
 * install or use this SugarCRM file.
 *
 * Copyright (C) SugarCRM Inc. All rights reserved.
 */

require_once 'modules/Opportunities/clients/base/api/OpportunitiesApi.php';

/**
 * Tests RS11Test.
 */
class RS11Test extends Sugar_PHPUnit_Framework_TestCase
{
	/**
     * @var SugarApi
     */
    protected $api;

    /**
     * @var User
     */
    protected $current_user;

    /**
     * @var Opportunity
     */
    protected $opportunity;

    /**
     * @var Account
     */
    protected $account;

    protected function setUp()
    {
        parent::setUp();
        SugarTestHelper::setUp('app_list_strings');
        SugarTestHelper::setUp('beanFiles');
        SugarTestHelper::setUp('beanList');

        $this->current_user = SugarTestHelper::setUp('current_user', array(true, false));
        $this->api = new OpportunitiesApi();
        $this->account = SugarTestAccountUtilities::createAccount();
        $this->opportunity = SugarTestOpportunityUtilities::createOpportunity(null, $this->account);
    }

    protected function tearDown()
    {
        SugarTestAccountUtilities::removeAllCreatedAccounts();
        SugarTestOpportunityUtilities::removeAllCreatedOpportunities();
        SugarTestHelper::tearDown();
        parent::tearDown();
    }

    public function testInfluencers()
    {
        $result = $this->api->influencers(
            SugarTestRestUtilities::getRestServiceMock($this->current_user),
            array('module' => 'Opportunities', 'record' => $this->opportunity->id)
        );

        $this->assertEquals(array(), $result);
    }

    public function testRecommendExperts()
    {
        $result = $this->api->recommendExperts(
            SugarTestRestUtilities::getRestServiceMock($this->current_user),
            array('module' => 'Opportunities', 'record' => $this->opportunity->id)
        );

        $this->assertEquals(array(), $result);
    }

    public function testRecommendExpertsTypeahead()
    {
        $result = $this->api->recommendExpertsTypeahead(
            SugarTestRestUtilities::getRestServiceMock($this->current_user),
            array('module' => 'Opportunities', 'record' => $this->opportunity->id)
        );

        $this->assertEquals(array(), $result);
    }

    public function testSimilarDeals()
    {
        $result = $this->api->similarDeals(
            SugarTestRestUtilities::getRestServiceMock($this->current_user),
            array('module' => 'Opportunities', 'record' => $this->opportunity->id)
        );

        $this->assertEquals(array(), $result);
    }
}
