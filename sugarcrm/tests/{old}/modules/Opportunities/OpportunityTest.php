<?php
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
 * Class OpportunityTest
 * @coversDefaultClass Opportunity
 */
class OpportunityTest extends TestCase
{
    protected static $currentUser;

    public static function setUpBeforeClass() : void
    {
        SugarTestHelper::setUp('current_user');
        SugarTestHelper::setUp('app_strings');
        SugarTestHelper::setUp('beanFiles');
        SugarTestHelper::setUp('beanList');
        SugarTestCurrencyUtilities::createCurrency('MonkeyDollars', '$', 'MOD', 2.0);

        SugarTestForecastUtilities::setUpForecastConfig([
            'sales_stage_won' => ['Closed Won'],
            'sales_stage_lost' => ['Closed Lost'],
            //BEGIN SUGARCRM flav!=ent ONLY
            'forecast_by' => 'opportunities',
            //END SUGARCRM flav!=ent ONLY
        ]);
    }

    protected function tearDown() : void
    {
        // Clean up current user if needed
        if (static::$currentUser) {
            $GLOBALS['current_user'] = static::$currentUser;
            static::$currentUser = null;
        }

        SugarTestRevenueLineItemUtilities::removeAllCreatedRevenueLineItems();
        SugarTestOpportunityUtilities::removeAllCreatedOpportunities();
        SugarTestCurrencyUtilities::removeAllCreatedCurrencies();
        SugarTestTimePeriodUtilities::removeAllCreatedTimePeriods();
        SugarTestProductUtilities::removeAllCreatedProducts();
        SugarTestAccountUtilities::removeAllCreatedAccounts();
    }

    public static function tearDownAfterClass() : void
    {
        SugarTestForecastUtilities::tearDownForecastConfig();
        SugarTestHelper::tearDown();
    }

    public function dataProviderCaseFieldEqualsAmountWhenCaseFieldEmpty()
    {
        return [['best_case'], ['worst_case']];
    }

    /**
     * @dataProvider dataProviderCaseFieldEqualsAmountWhenCaseFieldEmpty
     * @group opportunities
     * @covers ::save
     */
    public function testCaseFieldEqualsAmountWhenCaseFieldEmpty($case)
    {
        $id = create_guid();
        $rli = SugarTestRevenueLineItemUtilities::createRevenueLineItem();
        $opportunity = SugarTestOpportunityUtilities::createOpportunity($id);
        $opportunity->revenuelineitems->add($rli);
        $rli->$case = '';
        $rli->opportunity_id = $id;
        $rli->save();
        $opportunity->save();
        $this->assertEquals($opportunity->$case, $opportunity->amount);
    }


    /**
     * @dataProvider dataProviderCaseFieldEqualsAmountWhenCaseFieldEmpty
     * @group opportunities
     * @covers ::save
     */
    public function testCaseFieldEqualsZeroWhenCaseFieldSetToZero($case)
    {
        $id = create_guid();
        $rli = SugarTestRevenueLineItemUtilities::createRevenueLineItem();
        $opportunity = SugarTestOpportunityUtilities::createOpportunity($id);
        $opportunity->revenuelineitems->add($rli);
        $opportunity->sales_stage = "Prospecting";
        $rli->$case = $rli->likely_case = 0;
        $rli->opportunity_id = $id;
        $rli->save();
        $opportunity->$case = 0;
        $opportunity->save();
        $this->assertEquals(0, $opportunity->$case);
    }

    /**
     * Test that the base_rate field is populated with rate of currency_id
     * @group forecasts
     * @group opportunities
     * @covers ::save
     */
    public function testCurrencyRate()
    {
        $opportunity = SugarTestOpportunityUtilities::createOpportunity();
        $currency = SugarTestCurrencyUtilities::getCurrencyByISO('MOD');
        // if Euro does not exist, will use default currency
        $opportunity->currency_id = $currency->id;
        $opportunity->name = "Test Opportunity Delete Me";
        $opportunity->amount = "5000.00";
        $opportunity->date_closed = TimeDate::getInstance()->getNow()->modify("+10 days")->asDbDate();
        $opportunity->best_case = "1000.00";
        $opportunity->worst_case = "600.00";
        $opportunity->save();
        $this->assertEquals(
            sprintf('%.6f', $opportunity->base_rate),
            sprintf('%.6f', $currency->conversion_rate)
        );
    }

    /**
     * Test that base currency exchange rates from EUR are working properly.
     * @group forecasts
     * @group opportunities
     * @covers ::save
     */
    public function testBaseCurrencyAmounts()
    {
        $opportunity = SugarTestOpportunityUtilities::createOpportunity();
        $currency = SugarTestCurrencyUtilities::getCurrencyByISO('MOD');
        // if Euro does not exist, will use default currency
        $opportunity->currency_id = $currency->id;
        $opportunity->name = "Test Opportunity Delete Me";
        $opportunity->amount = "5000.00";
        $opportunity->date_closed = TimeDate::getInstance()->getNow()->modify("+10 days")->asDbDate();
        $opportunity->best_case = "1000.00";
        $opportunity->worst_case = "600.00";
        $opportunity->save();

        $this->assertEquals(
            sprintf('%.6f', $opportunity->base_rate),
            sprintf('%.6f', $currency->conversion_rate)
        );
    }

    //BEGIN SUGARCRM flav!=ent ONLY

    /**
     * @group opportunities
     * @group forecasts
     * @covers ::mark_deleted
     */
    public function testMarkDeleteDeletesForecastWorksheet()
    {
        SugarTestTimePeriodUtilities::createTimePeriod('2013-01-01', '2013-03-31');

        $opp = SugarTestOpportunityUtilities::createOpportunity();
        $opp->date_closed = '2013-01-01';
        $opp->save();

        $worksheet = SugarTestWorksheetUtilities::loadWorksheetForBean($opp);

        // assert that worksheet is not deleted
        $this->assertEquals(0, $worksheet->deleted);

        $opp->mark_deleted($opp->id);

        $this->assertEquals(1, $opp->deleted);

        // fetch the worksheet again
        unset($worksheet);
        $worksheet = SugarTestWorksheetUtilities::loadWorksheetForBean($opp, false, true);
        $this->assertEquals(1, $worksheet->deleted);
    }
    //END SUGARCRM flav!=ent ONLY

    /**
     * @group opportunities
     * @covers ::getClosedStages
     */
    public function testGetClosedStages()
    {
        $opp = $this->getMockBuilder('Opportunity')
            ->setMethods(['save'])
            ->disableOriginalConstructor()
            ->getMock();
        $closedStages = $opp->getClosedStages();
        $this->assertTrue(is_array($closedStages));
    }

    /**
     * @dataProvider dataProviderMapProbabilityFromSalesStage
     * @group opportunities
     * @covers ::mapProbabilityFromSalesStage
     * @param string $sales_stage
     * @param string $probability
     */
    public function testMapProbabilityFromSalesStage($sales_stage, $probability)
    {
        /* @var $oppMock Opportunity */
        $oppMock = $this->createPartialMock('Opportunity', ['save']);
        $oppMock->sales_stage = $sales_stage;
        // use the Reflection Helper to call the Protected Method
        SugarTestReflection::callProtectedMethod($oppMock, 'mapProbabilityFromSalesStage');

        $this->assertEquals($probability, $oppMock->probability);
    }

    public static function dataProviderMapProbabilityFromSalesStage()
    {
        return [
            ['Prospecting', '10'],
            ['Qualification', '20'],
            ['Needs Analysis', '25'],
            ['Value Proposition', '30'],
            ['Id. Decision Makers', '40'],
            ['Perception Analysis', '50'],
            ['Proposal/Price Quote', '65'],
            ['Negotiation/Review', '80'],
            ['Closed Won', '100'],
            ['Closed Lost', '0'],
        ];
    }

    /**
     * @covers ::isCurrencyIdChanged
     * @dataProvider dataProviderIsCurrencyIdChanged
     * @param string $currency_id
     * @param string $fetched_row_id
     * @param bool $expected
     */
    public function testIsCurrencyIdChanged($currency_id, $fetched_row_id, $expected)
    {
        $opp = $this->getMockBuilder('Opportunity')
            ->setMethods(['save'])
            ->disableOriginalConstructor()
            ->getMock();

        $opp->currency_id = $currency_id;
        if (!is_null($fetched_row_id)) {
            $opp->fetched_row = ['currency_id' => $fetched_row_id];
        }

        $actual = SugarTestReflection::callProtectedMethod($opp, 'isCurrencyIdChanged');

        $this->assertSame($expected, $actual);
    }

    public static function dataProviderIsCurrencyIdChanged()
    {
        return [
            [
                'test_currency_id',
                'test_currency_id',
                false,
            ],
            [
                'test_currency_id',
                'test-currency-id',
                true,
            ],
            [
                null,
                'test-currency-id',
                true,
            ],
            [
                'test_currency_id',
                null,
                true,
            ],
        ];
    }

    /**
     * @covers ::updateCurrencyBaseRate
     * @dataProvider dataProviderUpdateCurrencyBaseRate
     * @param string $sales_stage
     * @param array $closed_stages
     * @param bool $expected
     */
    public function testUpdateCurrencyBaseRate($sales_stage, $closed_stages, $expected)
    {
        $opp = $this->getMockBuilder('Opportunity')
            ->setMethods(['save', 'getClosedStages'])
            ->disableOriginalConstructor()
            ->getMock();

        $opp->sales_stage = $sales_stage;

        $opp->expects($this->once())
            ->method('getClosedStages')
            ->willReturn($closed_stages);

        $actual = $opp->updateCurrencyBaseRate();
        $this->assertSame($expected, $actual);
    }

    public static function dataProviderUpdateCurrencyBaseRate()
    {
        return [
            [
                'test_not_in_array',
                [
                    'test_1',
                    'test_2',
                    'test_3',
                    'test_4',
                ],
                true,
            ],
            [
                'test_in_array',
                [
                    'test_1',
                    'test_2',
                    'test_3',
                    'test_4',
                    'test_in_array',
                ],
                false,
            ],
        ];
    }

    /**
     * @covers ::save_relationship_changes
     */
    public function testSaveRelationshipChanges()
    {
        $opp = $this->getMockBuilder('Opportunity')
            ->setMethods(
                [
                    'set_opportunity_contact_relationship',
                    'set_relationship_info',
                    'handle_preset_relationships',
                    'handle_remaining_relate_fields',
                    'update_parent_relationships',
                    'handle_request_relate',
                    'load_relationship',
                ]
            )
            ->disableOriginalConstructor()
            ->getMock();

        $opp->id = 'test_opp_id';
        $opp->account_id = 'test_account_id1';
        $opp->rel_fields_before_value['account_id'] = 'test_account_id2';

        $linkAccounts = $this->getMockBuilder('Link2')
            ->disableOriginalConstructor()
            ->setMethods(['delete'])
            ->getMock();

        $linkAccounts->expects($this->once())
            ->method('delete')
            ->with($opp->id, 'test_account_id2');

        $opp->accounts = $linkAccounts;

        $linkProducts = $this->getMockBuilder('Link2')
            ->disableOriginalConstructor()
            ->setMethods(['getBeans'])
            ->getMock();

        $mockProduct = $this->getMockBuilder('Product')
            ->setMethods(['save'])
            ->getMock();

        $mockProduct->expects($this->once())
            ->method('save');

        $linkProducts->expects($this->once())
            ->method('getBeans')
            ->willReturn([$mockProduct]);

        $opp->products = $linkProducts;

        $linkRLIs = $this->getMockBuilder('Link2')
            ->disableOriginalConstructor()
            ->setMethods(['getBeans'])
            ->getMock();

        $mockRLI = $this->getMockBuilder('RevenueLineItem')
            ->setMethods(['save'])
            ->getMock();

        $mockRLI->expects($this->once())
            ->method('save');

        $linkRLIs->expects($this->once())
            ->method('getBeans')
            ->willReturn([$mockRLI]);

        $opp->revenuelineitems = $linkRLIs;

        $opp->expects($this->exactly(3))
            ->method('load_relationship')
            ->willReturnOnConsecutiveCalls($linkAccounts, $linkProducts, $linkRLIs);


        $opp->save_relationship_changes(true);

        $this->assertEquals('test_account_id1', $mockProduct->account_id);
        $this->assertEquals('test_account_id1', $mockRLI->account_id);
    }

    /**
     * @covers ::build_generic_where_clause
     */
    public function testBuildGenericWhereClause()
    {
        $opp = $this->getMockBuilder('Opportunity')
            ->setMethods(['save'])
            ->disableOriginalConstructor()
            ->getMock();

        $actual = $opp->build_generic_where_clause('test');

        $this->assertEquals("opportunities.name like 'test%' or accounts.name like 'test%'", $actual);
    }

    /**
     * @covers ::set_opportunity_contact_relationship
     */
    public function testSetOpportunityContactRelationship()
    {
        $opp = $this->getMockBuilder('Opportunity')
            ->setMethods(['load_relationship'])
            ->disableOriginalConstructor()
            ->getMock();

        $opp->contacts = $this->getMockBuilder('Link2')
            ->disableOriginalConstructor()
            ->setMethods(['add'])
            ->getMock();

        $opp->contacts->expects($this->once())
            ->method('add')
            ->with('test_contact_id', ['contact_role' => 'default']);

        $opp->expects($this->once())
            ->method('load_relationship');

        $GLOBALS['app_list_strings'] = [
            'opportunity_relationship_type_default_key' => 'default',
        ];

        $opp->set_opportunity_contact_relationship('test_contact_id');
    }

    //BEGIN SUGARCRM flav=ent ONLY
    /**
     * @covers ::updateRLIRollupFields
     * @dataProvider providerTestUpdateRLIRollupFields
     *
     * @param array $rliDataArray array of RLIs to create
     * @param array $expected the array of expected field values for the rollup fields
     */
    public function testUpdateRLIRollupFields($rliDataArray, $expected)
    {
        // Create an Opportunity
        $opportunity = SugarTestOpportunityUtilities::createOpportunity();

        if (empty($rliDataArray)) {
            // There aren't any related RLIs, so test the update function directly
            $opportunity->updateRLIRollupFields();
        } else {
            // Create RLIs related to the opportunity. On save, they should update
            // the rollup fields of the Opportunity
            foreach ($rliDataArray as $rliData) {
                $rli = SugarTestRevenueLineItemUtilities::createRevenueLineItem();
                $rli->sales_stage = $rliData['sales_stage'];
                $rli->service_start_date = $rliData['service_start_date'];
                $rli->date_closed = $rliData['date_closed'];
                $rli->service = $rliData['service'];
                if (!empty($rli->service)) {
                    $rli->service_duration_value = 1;
                    $rli->service_duration_unit = 'year';
                }
                $opportunity->revenuelineitems->add($rli);
            }
        }

        // Check that the Opportunity's rollup fields were correctly calculated
        $this->assertEquals($expected['service_start_date'], $opportunity->service_start_date);
        $this->assertEquals($expected['sales_stage'], $opportunity->sales_stage);
        $this->assertEquals($expected['date_closed'], $opportunity->date_closed);
    }

    public function providerTestUpdateRLIRollupFields()
    {
        return [
            [
                [],
                ['service_start_date' => '', 'sales_stage' => '', 'date_closed' => ''],
            ],
            [
                [
                    ['sales_stage' => 'Prospecting', 'service' => 1, 'service_start_date' => '2020-04-01', 'date_closed' => '2020-03-15'],
                ],
                ['service_start_date' => '2020-04-01', 'sales_stage' => 'Prospecting', 'date_closed' => '2020-03-15'],
            ],
            [
                [
                    ['sales_stage' => 'Closed Won', 'service' => 1, 'service_start_date' => '2019-01-01', 'date_closed' => '2019-10-15'],
                    ['sales_stage' => 'Closed Lost', 'service' => 1, 'service_start_date' => '2020-01-01', 'date_closed' => '2020-01-10'],
                ],
                ['service_start_date' => '2019-01-01', 'sales_stage' => 'Closed Won', 'date_closed' => '2019-10-15'],
            ],
            [
                [
                    ['sales_stage' => 'Closed Won', 'service' => 1, 'service_start_date' => '2020-01-01', 'date_closed' => '2020-03-15'],
                    ['sales_stage' => 'Qualification', 'service' => 1, 'service_start_date' => '2019-01-01', 'date_closed' => '2020-01-20'],
                    ['sales_stage' => 'Prospecting', 'service' => 1, 'service_start_date' => '2019-06-01', 'date_closed' => '2020-03-05'],
                    ['sales_stage' => 'Closed Lost', 'service' => 1, 'service_start_date' => '2018-01-01', 'date_closed' => '2020-03-15'],
                ],
                ['service_start_date' => '2019-01-01', 'sales_stage' => 'Qualification', 'date_closed' => '2020-03-05'],
            ],
        ];
    }

    /**
     * @dataProvider dataProviderCascade
     * @covers::cascade
     */
    public function testCascade($sales_stage, $service_start_date, $date_closed)
    {
        $opp = SugarTestOpportunityUtilities::createOpportunity();

        $rli1 = SugarTestRevenueLineItemUtilities::createRevenueLineItem();
        $rli1->opportunity_id = $opp->id;
        $rli1->sales_stage = 'Closed Won';
        $rli1->date_closed = '2019-10-25';
        $rli1->service = 1;
        $rli1->service_start_date = '2019-09-25';
        $rli1->service_duration_value = 1;
        $rli1->service_duration_unit = 'year';

        $opp->revenuelineitems->add($rli1);

        $rli2 = SugarTestRevenueLineItemUtilities::createRevenueLineItem();
        $rli2->opportunity_id = $opp->id;
        $rli2->sales_stage = 'Closed Lost';
        $rli2->date_closed = '2020-08-20';
        $rli2->service = 1;
        $rli2->service_start_date = '2021-04-28';
        $rli2->service_duration_value = 1;
        $rli2->service_duration_unit = 'year';

        $opp->revenuelineitems->add($rli2);

        $rli3 = SugarTestRevenueLineItemUtilities::createRevenueLineItem();
        $rli3->opportunity_id = $opp->id;
        $rli3->sales_stage = 'Needs Analysis';
        $rli3->date_closed = '2020-08-08';
        $rli3->service = 1;
        $rli3->service_start_date = '2021-04-28';
        $rli3->service_duration_value = 1;
        $rli3->service_duration_unit = 'year';

        $opp->revenuelineitems->add($rli3);

        $rli4 = SugarTestRevenueLineItemUtilities::createRevenueLineItem();
        $rli4->opportunity_id = $opp->id;
        $rli4->sales_stage = 'Needs Analysis';
        $rli4->date_closed = '2019-09-25';
        $rli4->service = 0;
        $rli4->service_start_date = null;

        $opp->revenuelineitems->add($rli4);

        // Opp level RLI management fields  will have been recalculated above, so reset it
        $opp->sales_stage_cascade = $sales_stage;
        $opp->service_start_date_cascade = $service_start_date;
        $opp->date_closed_cascade = $date_closed;
        SugarTestReflection::callProtectedMethod(
            $opp,
            'cascade'
        );

        $this->assertSame($rli1->sales_stage, 'Closed Won');
        $this->assertSame($rli1->service_start_date, '2019-09-25');
        $this->assertSame($rli1->date_closed, '2019-10-25');

        $this->assertSame($rli2->sales_stage, 'Closed Lost');
        $this->assertSame($rli2->service_start_date, '2021-04-28');
        $this->assertSame($rli2->date_closed, '2020-08-20');

        $this->assertSame($rli3->sales_stage, $sales_stage);
        $this->assertSame($rli3->service_start_date, $service_start_date);
        $this->assertSame($rli3->date_closed, $date_closed);

        $this->assertSame($rli4->sales_stage, $sales_stage);
        $this->assertSame($rli4->service_start_date, null);
        $this->assertSame($rli4->date_closed, $date_closed);
    }

    public function dataProviderCascade()
    {
        // $sales_stage, $service_start_date, $date_closed
        return [
            ['Prospecting','2020-02-20','2019-08-25',],
            ['Value Proposition','2020-02-20','2019-09-26',],
        ];
    }

    /**
     * @dataProvider dataProviderCalculate
     * @param string $stages
     * @param string $expected
     * @covers::save
     */
    public function testCalculationDoesNotCascade($stages, $expected)
    {
        $opp = SugarTestOpportunityUtilities::createOpportunity();

        $rlis = [];
        for ($i = 0; $i < count($stages); $i++) {
            // Save a new RLI related to the Opportunity with the given sales stage
            // On save, the Opportunity Sales Stage should be recalculated
            $rli = SugarTestRevenueLineItemUtilities::createRevenueLineItem();
            $rli->sales_stage = $stages[$i];
            $opp->revenuelineitems->add($rli);
            $rlis[] = $rli;

            // Check that the Opportunity Sales Stage was calculated correctly
            $this->assertEquals($expected[$i], $opp->sales_stage);
        }

        // Check that the changes to the Opportunity did not cascade back down to the RLIs
        foreach ($stages as $index => $stage) {
            $rli = $rlis[$index];
            $this->assertEquals($stage, $rli->sales_stage);
        }
    }

    public function dataProviderCalculate()
    {
        return [
            [
                'stages' => ['Prospecting', 'Value Proposition', 'Needs Analysis',],
                'expected' => ['Prospecting', 'Value Proposition', 'Value Proposition',],
            ],
            [
                'stages' => ['Closed Lost', 'Closed Won', 'Value Proposition',],
                'expected' => ['Closed Lost', 'Closed Won', 'Value Proposition',],
            ],
        ];
    }

    /**
     * @dataProvider dataProviderGeneratePurchaseRliIds
     * @param string $generate_purchases The value for the purchase generation flag
     * @param string $sales_stage The sales stage of the opp
     * @param array $licenses Array of licenses for the test user
     * @param int $count The number of expected results
     * @param array $expected The expected result array
     * @throws SugarQueryException
     */
    public function testGetGeneratePurchaseRliIDs($generate_purchase, $sales_stage, $licenses, $count, $expected): void
    {
        $opp = SugarTestOpportunityUtilities::createOpportunity();

        if (!$opp->isLicensedForSell()) {
            $this->markTestSkipped('This test can only run on licensed Sell instances');
        }

        static $idCounter = 1;
        $rliId = 'ut-opp-test-' . $idCounter;
        $idCounter++;

        // Begin mocks for the collector
        $userMock = $this->getMockBuilder('User')
            ->onlyMethods([
                'getLicenseTypes',
                'isAdmin',
            ])
            ->getMock();

        $userMock->expects($this->any())
            ->method('getLicenseTypes')
            ->willReturn($licenses);

        $userMock->expects($this->any())
            ->method('isAdmin')
            ->willReturn(true);

        $userMock->is_admin = '1';
        $userMock->id = 'ut-opp-user';

        if (isset($GLOBALS['current_user'])) {
            static::$currentUser = $GLOBALS['current_user'];
        }

        // Current user needs to be set before the test objects for visibility reasons
        $GLOBALS['current_user'] = $userMock;

        $opp->sales_stage = 'Closed Won';
        $rli = SugarTestRevenueLineItemUtilities::createRevenueLineItem($rliId);
        $rli->generate_purchase = $generate_purchase;
        $rli->sales_stage = $sales_stage;
        $opp->load_relationship('revenuelineitems');
        $opp->revenuelineitems->add($rli);

        $this->assertEquals($rli->opportunity_id, $opp->id);
        $this->assertTrue($rli->isLicensedForSell(), 'RLIs not licensed for sell');
        $this->assertEquals($rli->generate_purchase, $generate_purchase);

        // Used to ensure test data is the same as production data
        $sql = "SELECT id
                FROM revenue_line_items
                WHERE opportunity_id = '$opp->id'
                AND generate_purchase = '$generate_purchase'";
        $rs = $rli->db->query($sql);
        $rows = [];
        while ($row = $rli->db->fetchByAssoc($rs)) {
            $rows[] = $row;
        }

        $this->assertCount(1, $rows);


        $ids = $opp->getGeneratePurchaseRliIds();

        // Begin licensed assertions
        $this->assertCount($count, $ids);

        //var_dump($ids);
        // Ensure IDs returned from DB are trimmed, as some DB2 enforces length
        // on our ID fields
        foreach ($ids as &$row) {
            $row['id'] = trim($row['id']);
        }

        $this->assertEquals($expected, $ids);
    }

    public function dataProviderGeneratePurchaseRliIds(): array
    {
        return [
            ['Yes', 'Closed Won', ['SUGAR_SERVE'], 0, [],],
            ['Yes', 'Closed Won', ['SUGAR_SELL'], 1, [['id' => 'ut-opp-test-2'],],],
            ['No', 'Closed Won', ['SUGAR_SELL'], 0, [],],
            ['Yes', 'Qualification', ['SUGAR_SELL'], 1, [['id' => 'ut-opp-test-4'],],],
            ['Completed', 'Closed Won', ['SUGAR_SELL'], 0, [],],
        ];
    }
    //END SUGARCRM flav=ent ONLY
}
