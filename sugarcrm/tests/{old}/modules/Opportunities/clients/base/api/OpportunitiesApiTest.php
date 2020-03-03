<?php
// FILE SUGARCRM flav=ent ONLY
/*
 * Your installation or use of this SugarCRM file is subject to the applicable
 * terms available at
 * http://support.sugarcrm.com/Resources/Master_Subscription_Agreements/.
 * If you do not agree to all of the applicable terms or do not have the
 * authority to bind the entity as an authorized representative, then do not
 * install or use this SugarCRM file.
 *
 * Copyright (C) SugarCRM Inc. All rights reserved.
 */

use PHPUnit\Framework\TestCase;

class OpportunitiesApiTest extends TestCase
{
    /**
     * @var OpportunitiesApi
     */
    protected $api;

    protected function setUp() : void
    {
        $this->service = SugarTestRestUtilities::getRestServiceMock();
        $this->api = new OpportunitiesApi();
    }

    public static function setUpBeforeClass() : void
    {
        SugarTestForecastUtilities::setUpForecastConfig(array(
            'sales_stage_won' => array('Closed Won'),
            'sales_stage_lost' => array('Closed Lost'),
        ));
    }

    public static function tearDownAfterClass(): void
    {
        SugarTestForecastUtilities::tearDownForecastConfig();
        SugarTestHelper::tearDown();
    }

    protected function tearDown() : void
    {
        SugarTestOpportunityUtilities::removeAllCreatedOpportunities();
        SugarTestAccountUtilities::removeAllCreatedAccounts();
        SugarTestRevenueLineItemUtilities::removeAllCreatedRevenueLineItems();

        Opportunity::$settings = array();
    }

    /**
     * @covers ::updateReords
     * @covers ::updateRevenueLineItems
     */
    public function testPutOpportunity_OppportunityMode_DoesNotUpdateRLIs()
    {
        Opportunity::$settings = array(
            'opps_view_by' => 'Opportunities',
        );

        $opp = SugarTestOpportunityUtilities::createOpportunity();

        $args['module'] = 'Opportunities';
        $args['record'] = $opp->id;

        $api = $this->createPartialMock('OpportunitiesApi', array('updateRevenueLineItems', 'loadBean'));
        $api->expects($this->never())
            ->method('updateRevenueLineItems');
        $api->expects($this->any())
            ->method('loadBean')
            ->willReturn($opp);

        $result = $api->updateRecord($this->service, $args);
    }

    public function dataProviderPutOpportunity_OppportunityRliMode_UpdateRLIs()
    {
        return array(
            // sales stage, date closed
            array(
                array(
                    'sales_stage' => 'Proposed',
                    'date_closed' => '2019-01-01',
                    'other_field' => 'test',
                ),
                1,
            ),
            // sales stage
            array(
                array(
                    'sales_stage' => 'Proposed',
                    'other_field' => 'test',
                ),
                1,
            ),
            // date closed
            array(
                array(
                    'date_closed' => '2019-01-01',
                    'other_field' => 'test',
                ),
                1,
            ),
            // other fields
            array(
                array(
                    'other_field' => 'test',
                ),
                0,
            ),
        );
    }

    /**
     * @dataProvider dataProviderPutOpportunity_OppportunityRliMode_UpdateRLIs
     *
     * @covers ::updateReords
     * @covers ::updateRevenueLineItems
     */
    public function testPutOpportunity_OppportunityRliMode_UpdateRLIs($args, $expected)
    {
        $opp = SugarTestOpportunityUtilities::createOpportunity();

        $args['module'] = 'Opportunities';
        $args['record'] = $opp->id;

        Opportunity::$settings = array(
            'opps_view_by' => 'RevenueLineItems',
        );

        $api = $this->createPartialMock('OpportunitiesApi', array('updateRevenueLineItems', 'loadBean'));
        $api->expects($this->exactly($expected))
            ->method('updateRevenueLineItems');
        $api->expects($this->any())
            ->method('loadBean')
            ->willReturn($opp);

        $result = $api->updateRecord($this->service, $args);
    }

    /**
     * @covers ::updateReords
     * @covers ::updateRevenueLineItems
     */
    public function testPutOpportunity_UpdateRevenueLineItems_CalledWithCorrectArgs()
    {
        $opp = SugarTestOpportunityUtilities::createOpportunity();
        $opp->sales_stage = 'Closed Lost';

        $args['module'] = 'Opportunities';
        $args['record'] = $opp->id;
        $args['sales_stage'] = 'Prospecting';
        $args['date_closed'] = '2019-01-01';

        Opportunity::$settings = array(
            'opps_view_by' => 'RevenueLineItems',
        );

        $api = $this->createPartialMock('OpportunitiesApi', array('updateRevenueLineItems', 'loadBean'));
        $api->expects($this->once())
            ->method('updateRevenueLineItems')
            ->with($opp, array('sales_stage' => 'Prospecting', 'date_closed' => '2019-01-01'));
        $api->expects($this->any())
            ->method('loadBean')
            ->willReturn($opp);

        $result = $api->updateRecord($this->service, $args);
    }

    /**
     * @covers ::updateRevenueLineItems
     */
    public function testUpdateRevenueLineItems_RlisUpdated()
    {
        $opp = SugarTestOpportunityUtilities::createOpportunity();
        $opp->save();

        $rli1 = SugarTestRevenueLineItemUtilities::createRevenueLineItem();
        $rli1->opportunity_id = $opp->id;
        $rli1->sales_stage = 'Closed Won';
        $rli1->save();

        $rli2 = SugarTestRevenueLineItemUtilities::createRevenueLineItem();
        $rli2->opportunity_id = $opp->id;
        $rli2->sales_stage = 'Closed Lost';
        $rli2->save();

        $rli3 = SugarTestRevenueLineItemUtilities::createRevenueLineItem();
        $rli3->opportunity_id = $opp->id;
        $rli3->sales_stage = 'Prospecting';
        $rli3->save();

        $data = array(
            'sales_stage' => 'Qualification',
            'date_closed' => '2017-01-01',
        );

        SugarTestReflection::callProtectedMethod(
            $this->api,
            'updateRevenueLineItems',
            array(
                $opp,
                $data,
            )
        );

        $rli1 = BeanFactory::retrieveBean('RevenueLineItems', $rli1->id);
        $rli2 = BeanFactory::retrieveBean('RevenueLineItems', $rli2->id);
        $rli3 = BeanFactory::retrieveBean('RevenueLineItems', $rli3->id);

        $this->assertSame($rli1->sales_stage, $data['sales_stage']);
        $this->assertSame($rli1->date_closed, $data['date_closed']);

        $this->assertSame($rli2->sales_stage, $data['sales_stage']);
        $this->assertSame($rli2->date_closed, $data['date_closed']);

        $this->assertSame($rli3->sales_stage, $data['sales_stage']);
        $this->assertSame($rli3->date_closed, $data['date_closed']);
    }
}
