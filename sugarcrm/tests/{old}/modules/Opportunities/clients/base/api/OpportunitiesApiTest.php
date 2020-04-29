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

/**
 * @coversDefaultClass OpportunitiesApi
 */
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
     * @covers ::updateRecord
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

        $api->updateRecord($this->service, $args);
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
                0,
            ),
            // sales stage
            array(
                array(
                    'sales_stage' => 'Proposed',
                    'other_field' => 'test',
                ),
                0,
            ),
            // date closed
            array(
                array(
                    'date_closed' => '2019-01-01',
                    'other_field' => 'test',
                ),
                0,
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
     * @covers ::updateRecord
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

        $api->updateRecord($this->service, $args);
    }

    /**
     * @covers ::updateRecord
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
        $api->expects($this->never())
            ->method('updateRevenueLineItems');
        $api->expects($this->any())
            ->method('loadBean')
            ->willReturn($opp);

        $api->updateRecord($this->service, $args);
    }

    public function dataProviderUpdateRevenueLineItems()
    {
        return [
            [
                ['sales_stage' => 'Closed Won', 'date_closed' => '2017-05-05'],
                ['sales_stage' => 'Closed Won', 'date_closed' => '2017-05-05'],
            ],
            [
                ['sales_stage' => 'Closed Lost', 'date_closed' => '2017-06-06'],
                ['sales_stage' => 'Closed Lost', 'date_closed' => '2017-06-06'],
            ],
            [
                ['sales_stage' => 'Prospecting', 'date_closed' => '2017-02-02'],
                ['sales_stage' => 'Qualification', 'date_closed' => '2017-01-01'],
            ],
        ];
    }

    /**
     * @dataProvider dataProviderUpdateRevenueLineItems
     *
     * @covers ::updateRevenueLineItems
     */
    public function testUpdateRevenueLineItems_RlisUpdated($args, $expected)
    {
        $opp = SugarTestOpportunityUtilities::createOpportunity();
        $opp->save();

        $rli = SugarTestRevenueLineItemUtilities::createRevenueLineItem();
        $rli->opportunity_id = $opp->id;
        $rli->sales_stage = $args['sales_stage'];
        $rli->date_closed = $args['date_closed'];
        $rli->save();

        $data = [
            'sales_stage' => 'Qualification',
            'date_closed' => '2017-01-01',
        ];

        SugarTestReflection::callProtectedMethod(
            $this->api,
            'updateRevenueLineItems',
            array(
                $opp,
                $data,
            )
        );

        $rli = BeanFactory::retrieveBean('RevenueLineItems', $rli->id);

        $this->assertSame($rli->sales_stage, $expected['sales_stage']);
        $this->assertSame($rli->date_closed, $expected['date_closed']);
    }
}
