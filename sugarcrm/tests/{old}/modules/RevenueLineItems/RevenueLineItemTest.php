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
 * Class RevenueLineItemTest
 * @coversDefaultClass RevenueLineItem
 */
class RevenueLineItemTest extends TestCase
{
    public static function setUpBeforeClass() : void
    {
        SugarTestHelper::setUp('current_user');
        SugarTestHelper::setUp('app_list_strings');
        SugarTestHelper::setUp('mod_strings', ['RevenueLineItems']);
    }

    public static function tearDownAfterClass(): void
    {
        SugarTestHelper::tearDown();
    }

    protected function tearDown() : void
    {
        SugarTestAccountUtilities::removeAllCreatedAccounts();
        SugarTestRevenueLineItemUtilities::removeAllCreatedRevenueLineItems();
    }

    /**
     * @group revenuelineitems
     * @covers ::convertToQuotedLineItem
     */
    public function testConvertToQuotedLineItemWithDiscountPriceSet()
    {
        /* @var $rli RevenueLineItem */
        $rli = $this->getMockBuilder('RevenueLineItem')->setMethods(['save'])->getMock();
        $rli->likely_case = '100.00';
        $rli->discount_price = '200.00';
        $rli->sales_stage = 'Test';
        $product = $rli->convertToQuotedLineItem();

        $this->assertEquals($rli->discount_price, $product->discount_price);
        $this->assertEquals($rli->id, $product->revenuelineitem_id, 'RLI to QLI Link is not Set');
        $this->assertEquals('Test', $product->sales_stage, "Product does not match RevenueLineItem");
    }

    /**
     * @group revenuelineitems
     * @covers ::convertToQuotedLineItem
     */
    public function testConvertToQuotedLineItemWithoutDiscountPriceSet()
    {
        /* @var $rli RevenueLineItem */
        $rli = $this->getMockBuilder('RevenueLineItem')->setMethods(['save'])->getMock();
        $rli->likely_case = '100.00';
        $rli->discount_price = '';
        $rli->sales_stage = 'Test';
        $product = $rli->convertToQuotedLineItem();

        $this->assertEquals($rli->likely_case, $product->discount_price);
        $this->assertEquals($rli->id, $product->revenuelineitem_id, 'RLI to QLI Link is not Set');
        $this->assertEquals('Test', $product->sales_stage, "Product does not match RevenueLineItem");
    }

    /**
     * @group revenuelineitems
     * @covers ::convertToQuotedLineItem
     */
    public function testConvertToQuoteLineItemsSetsCorrectDiscountAmount()
    {
        /* @var $rli RevenueLineItem */
        $rli = $this->getMockBuilder('RevenueLineItem')->setMethods(['save'])->getMock();
        $rli->discount_amount = '25.00';
        $rli->quantity = '50';
        $rli->discount_price = '1.00';
        $product = $rli->convertToQuotedLineItem();

        $this->assertEquals('25.00', $product->discount_amount);
    }

    /**
     * @group revenuelineitems
     * @covers ::convertToQuotedLineItem
     */
    public function testConvertToQuoteLineItemsSetCorrectDiscountAmountWhenPercent()
    {
        /* @var $rli RevenueLineItem */
        $rli = $this->getMockBuilder('RevenueLineItem')->setMethods(['save'])->getMock();
        $rli->discount_amount = '25.00';
        $rli->quantity = '50';
        $rli->discount_price = '1.00';
        $rli->discount_select = 1;
        $rli->deal_calc = 0.25; // (discount_amount/100)*discount_price
        $product = $rli->convertToQuotedLineItem();

        $this->assertEquals('25.00', $product->discount_amount);
    }

    /**
     * @dataProvider dataProviderSetDiscountPrice
     * @covers ::setDiscountPrice
     * @param string $likely
     * @param string $quantity
     * @param string $discount_price
     * @param string $expected_discount
     */
    public function testSetDiscountPrice($likely, $quantity, $discount_price, $expected_discount)
    {
        /* @var $rli RevenueLineItem */
        $rli = $this->getMockBuilder('RevenueLineItem')->setMethods(['save'])->getMock();
        $rli->likely_case = $likely;
        $rli->quantity = $quantity;
        $rli->discount_price = $discount_price;

        SugarTestReflection::callProtectedMethod($rli, 'setDiscountPrice');

        $this->assertEquals($expected_discount, $rli->discount_price);
    }

    public function dataProviderSetDiscountPrice()
    {
        // values are likely, quantity, discount_price, expected_discount_price
        return [
            ['100.00', '1', '', '100.000000'],
            ['100.00', '1', '0.00', '0.00'],
            ['100.00', '1', '150.000000', '150.000000'],
        ];
    }

    /**
     * @param String $quantity
     * @param String $discount_price
     * @param String $discount_amount
     * @param String $discount_select
     * @param String $total_amount
     * @dataProvider totalAmountDataProvider
     */
    public function testCalculateTotalAmount(
        $quantity,
        $discount_price,
        $discount_amount,
        $discount_select,
        $total_amount
    ) {
        /* @var $rli RevenueLineItem */
        $rli = SugarTestRevenueLineItemUtilities::createRevenueLineItem();
        $rli->quantity = $quantity;
        $rli->discount_price = $discount_price;
        $rli->discount_amount = $discount_amount;
        $rli->discount_select = $discount_select;
        $rli->save();

        // lets make sure the totals are correct
        $this->assertEquals(
            $total_amount,
            $rli->total_amount,
            'Total amount Is Wrong'
        );
    }

    /**
     * totalAmountDataProvider
     */
    public function totalAmountDataProvider()
    {
        // $quantity, $discount_price, $discount_amount, $discount_select, $total_amount
        return [
            ['-2', '100.000000', '10.000000', '0', '-190.000000'],
            ['-2', '100.000000', '10.000000', '1', '-180.000000'],
            ['2', '100.000000', '10.000000', '0', '190.000000'],
            ['2', '100.000000', '10.000000', '1', '180.000000'],
        ];
    }

    //BEGIN SUGARCRM flav=ent ONLY
    /**
     * @covers ::saveProductWorksheet
     */
    public function testSaveProductWorksheetReturnsFalse()
    {
        Forecast::$settings = [
            'is_setup' => 0,
        ];
        $mock = $this->getMockBuilder('RevenueLineItem')
            ->getMock();

        $actual = SugarTestReflection::callProtectedMethod($mock, 'saveProductWorksheet');
        $this->assertFalse($actual);
        Forecast::$settings = [];
    }

    //END SUGARCRM flav=ent ONLY

    /**
     * @covers ::mapFieldsFromProductTemplate
     */
    public function testMapFieldsProductTemplate()
    {
        $rli = $this->getMockBuilder('RevenueLineItem')->setMethods(['save'])->getMock();

        $arrExpected = [
            'category_id' => 'test_category_id',
            'mft_part_num' => 'test_mft_part_num',
            'list_price' => 'test_list_price',
            'cost_price' => 'test_cost_price',
            'discount_price' => 'test_discount_price',
            'list_usdollar' => 'test_list_usdollar',
            'cost_usdollar' => 'test_cost_usdollar',
            'discount_usdollar' => 'test_discount_usdollar',
            'tax_class' => 'test_tax_class',
            'weight' => 'test_weight',
        ];

        $pt = $this->getMockBuilder('ProductTemplate')
            ->setMethods(['save'])
            ->getMock();

        $pt->id = 'test_pt_id';
        foreach ($arrExpected as $key => $val) {
            $pt->$key = $val;
        }

        $rli->product_template_id = $pt->id;
        BeanFactory::registerBean($pt);

        SugarTestReflection::callProtectedMethod($rli, 'mapFieldsFromProductTemplate');

        foreach ($arrExpected as $key => $expected) {
            $this->assertEquals($expected, $rli->$key);
        }

        BeanFactory::unregisterBean($pt, $pt->id);
    }

    public static function dataProviderSetAccountIdForOpportunity()
    {
        return [
            [
                [
                    'test_account_id',
                ],
                true,
            ],
            [
                [],
                false,
            ],
        ];
    }

    /**
     * @dataProvider dataProviderSetAccountIdForOpportunity
     * @covers ::setAccountIdForOpportunity
     */
    public function testSetAccountIdForOpportunity($accounts, $expected)
    {
        $product = $this->createPartialMock('Product', ['save', 'load_relationship']);

        $opp = $this->getMockBuilder('Opportunity')
            ->setMethods(['save', 'load_relationship'])
            ->getMock();

        $opp->id = 'test_opp_id';

        $opp->expects($this->once())
            ->method('load_relationship')
            ->with('accounts')
            ->willReturn(true);

        $link2 = $this->getMockBuilder('Link2')
            ->setMethods(['get'])
            ->disableOriginalConstructor()
            ->getMock();

        $link2->expects($this->once())
            ->method('get')
            ->willReturn($accounts);


        $opp->accounts = $link2;

        BeanFactory::registerBean($opp);

        $actual = SugarTestReflection::callProtectedMethod($product, 'setAccountIdForOpportunity', [$opp->id]);

        $this->assertEquals($expected, $actual);

        BeanFactory::unregisterBean($opp, $opp->id);
    }

    /**
     * @covers ::mapFieldsFromOpportunity
     */
    public function testMapFieldsFromOpportunity()
    {
        $product = $this->createPartialMock('Product', ['save']);

        $opp = $this->getMockBuilder('Opportunity')
            ->setMethods(['save'])
            ->getMock();

        $opp->id = 'test_opp_id';
        $opp->opportunity_type = 'test_type';

        $product->opportunity_id = $opp->id;

        BeanFactory::registerBean($opp);

        SugarTestReflection::callProtectedMethod($product, 'mapFieldsFromOpportunity');

        $this->assertEquals('test_type', $product->product_type);

        BeanFactory::unregisterBean($opp, $opp->id);
    }

    /**
     * @covers ::setBestWorstFromLikely
     */
    public function testSetBestWorstFromLikelyDoesNotChangeBecauseOfAcl()
    {
        $rli = $this->getMockBuilder('RevenueLineItem')
            ->setMethods(['ACLFieldAccess'])
            ->disableOriginalConstructor()
            ->getMock();

        $rli->expects($this->atLeast(2))
            ->method('ACLFieldAccess')
            ->willReturn(false);

        /* @var $rli RevenueLineItem */
        $rli->likely_case = 500;
        $rli->best_case = '';
        $rli->worst_case = 0;

        SugarTestReflection::callProtectedMethod($rli, 'setBestWorstFromLikely');

        $this->assertEquals(500, $rli->likely_case);
        $this->assertEquals('', $rli->best_case);
        $this->assertEquals(0, $rli->worst_case);
    }

    /**
     * @dataProvider dataProviderBestWorstAutoFill
     * @covers ::setBestWorstFromLikely
     */
    public function testBestWorstAutoFill($value, $likely, $expected)
    {
        $rli = $this->getMockBuilder('RevenueLineItem')
            ->setMethods(['ACLFieldAccess'])
            ->disableOriginalConstructor()
            ->getMock();

        $rli->expects($this->atLeast(2))
            ->method('ACLFieldAccess')
            ->willReturn(true);

        /* @var $rli RevenueLineItem */
        $rli->likely_case = $likely;
        $rli->best_case = $value;
        $rli->worst_case = $value;

        SugarTestReflection::callProtectedMethod($rli, 'setBestWorstFromLikely');

        $this->assertSame($expected, $rli->best_case);
        $this->assertSame($expected, $rli->worst_case);
    }

    public function dataProviderBestWorstAutoFill()
    {
        return [
            [
                '',
                '100',
                '100',
            ],
            [
                null,
                '100',
                '100',
            ],
            [
                '42',
                '100',
                '42',
            ],
            [
                '0',
                '100',
                '0',
            ],
            [
                '0',
                100,
                '0',
            ],
        ];
    }

    /**
     * @dataProvider dataProviderCheckQuantity
     * @group revenuelineitems
     * @covers ::checkQuantity
     * @param mixed $actual
     * @param integer $expected
     */
    public function testQuantityNotDefaulted($actual, $expected)
    {
        $rli = $this->getMockBuilder('RevenueLineItem')
            ->setMethods(['save'])
            ->getMock();

        $rli->quantity = $actual;

        SugarTestReflection::callProtectedMethod($rli, 'checkQuantity');

        $this->assertEquals($expected, $rli->quantity);
    }


    public static function dataProviderCheckQuantity()
    {
        return [
            ['', 0],
            [null, 0],
            [0, 0],
            [1, 1],
            [42,42],
            [-1, -1],
            [-42,-42],
        ];
    }

    /**
     * @dataProvider dataProviderMapProbabilityFromSalesStage
     * @covers ::mapProbabilityFromSalesStage
     * @group revenuelineitems
     */
    public function testMapProbabilityFromSalesStage($sales_stage, $probability)
    {
        $revenuelineitem = $this->createMock('RevenueLineItem');
        $revenuelineitem->sales_stage = $sales_stage;
        // use the Reflection Helper to call the Protected Method
        SugarTestReflection::callProtectedMethod($revenuelineitem, 'mapProbabilityFromSalesStage');

        $this->assertEquals($probability, $revenuelineitem->probability);
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
     * @dataProvider dataProviderCanConvertToQuote
     * @param $fields
     * @param $expected
     */
    public function testCanConvertToQuote($fields, $expected)
    {
        $rli = $this->getMockBuilder('RevenueLineItem')
            ->setMethods(null)
            ->getMock();

        foreach ($fields as $field => $value) {
            $rli->$field = $value;
        }

        $actual = $rli->canConvertToQuote();

        if ($expected === false) {
            // we have to assert not true, since it's returning a language string and testing against that
            // is bad!
            $this->assertNotTrue($actual);
        } else {
            $this->assertTrue($actual);
        }
    }

    public static function dataProviderCanConvertToQuote()
    {
        return [
            [
                [
                    'category_id' => 'test_cat_id',
                ],
                false,
            ],
            [
                [
                    'quote_id' => 'test_quote_id',
                ],
                false,
            ],
            [
                [
                    'id' => 'test',
                ],
                true,
            ],
        ];
    }

    //BEGIN SUGARCRM flav=ent ONLY
    /**
     * @covers ::setServiceEndDate
     * @dataProvider providerTestSetServiceEndDate
     */
    public function testSetServiceEndDate(array $serviceFields, ?string $expectedEndDate, $shouldClearFields)
    {
        $mockRLI = $this->getMockBuilder('RevenueLineItem')
            ->disableOriginalConstructor()
            ->getMock();

        // Set the service fields accordingly
        $mockRLI->service = $serviceFields['service'];
        $mockRLI->service_start_date = $serviceFields['service_start_date'];
        $mockRLI->service_duration_value = $serviceFields['service_duration_value'];
        $mockRLI->service_duration_unit = $serviceFields['service_duration_unit'];

        SugarTestReflection::callProtectedMethod($mockRLI, 'setServiceEndDate');

        $this->assertSame($expectedEndDate, $mockRLI->service_end_date);

        if ($shouldClearFields) {
            $this->assertSame(false, $mockRLI->service);
            $this->assertSame(null, $mockRLI->service_start_date);
            $this->assertSame(null, $mockRLI->service_duration_value);
            $this->assertSame(null, $mockRLI->service_duration_unit);
        }
    }

    public function providerTestSetServiceEndDate()
    {
        return [
            // Test days
            [
                [
                    'service' => true,
                    'service_start_date' => '2019-09-25',
                    'service_duration_value' => 1,
                    'service_duration_unit' => 'day',
                ],
                '2019-09-25',
                false,
            ],
            [
                [
                    'service' => true,
                    'service_start_date' => '2019-09-25',
                    'service_duration_value' => 7,
                    'service_duration_unit' => 'day',
                ],
                '2019-10-01',
                false,
            ],
            // Test months
            [
                [
                    'service' => true,
                    'service_start_date' => '2019-06-30',
                    'service_duration_value' => 1,
                    'service_duration_unit' => 'month',
                ],
                '2019-07-29',
                false,
            ],
            [
                [
                    'service' => true,
                    'service_start_date' => '2019-06-30',
                    'service_duration_value' => 3,
                    'service_duration_unit' => 'month',
                ],
                '2019-09-29',
                false,
            ],
            // Test years
            [
                [
                    'service' => true,
                    'service_start_date' => '2019-09-30',
                    'service_duration_value' => 1,
                    'service_duration_unit' => 'year',
                ],
                '2020-09-29',
                false,
            ],
            [
                [
                    'service' => true,
                    'service_start_date' => '2019-09-30',
                    'service_duration_value' => 3,
                    'service_duration_unit' => 'year',
                ],
                '2022-09-29',
                false,
            ],
            // Test non-service type
            [
                [
                    'service' => false,
                    'service_start_date' => null,
                    'service_duration_value' => null,
                    'service_duration_unit' => null,
                ],
                null,
                false,
            ],
            // Test clearing of service data for non-services
            [
                [
                    'service' => false,
                    'service_start_date' => '2019-09-30',
                    'service_duration_value' => 3,
                    'service_duration_unit' => 'year',
                ],
                null,
                true,
            ],
            // Test clearing of service data for end date calculation errors
            [
                [
                    'service' => true,
                    'service_start_date' => 'Not a real date',
                    'service_duration_value' => 'Not a real number',
                    'service_duration_unit' => null,
                ],
                null,
                true,
            ],
        ];
    }

    /**
     * @covers ::updateRelatedAccount
     * @dataProvider providerTestUpdateRelatedAccount
     *
     * @param Array $rliDataArray array of RLIs to create
     * @param string $expected the expected next_renewal_date of the Account affected
     */
    public function testUpdateRelatedAccount($rliDataArray, $expected)
    {
        // Create an account
        $account = SugarTestAccountUtilities::createAccount();

        // Create RLIs that point to the account. On save, they should update
        // that account's next_renewal_date field
        foreach ($rliDataArray as $rliData) {
            $rli = SugarTestRevenueLineItemUtilities::createRevenueLineItem();
            $rli->product_type = $rliData['product_type'];
            $rli->renewable = $rliData['renewable'];
            $rli->date_closed = $rliData['date_closed'];
            $rli->account_id = $account->id;
            $rli->save();
        }

        // Check that the account's next_renewal_date was correctly calculated
        // on RLI save
        $resultAccount = BeanFactory::retrieveBean('Accounts', $account->id, ['use_cache' => false]);
        $this->assertEquals($expected, $resultAccount->next_renewal_date);
    }

    public function providerTestUpdateRelatedAccount()
    {
        return [
            // No related RLIs
            [
                [
                ],
                '',
            ],
            // 2 related RLIs, but none that fit the criteria
            [
                [
                    ['product_type' => 'New Business', 'renewable' => 1, 'date_closed' => '2020-01-01'],
                    ['product_type' => 'Existing Business', 'renewable' => 0, 'date_closed' => '2019-01-01'],
                ],
                '',
            ],
            // 2 related RLIs, only one of which fits the criteria
            [
                [
                    ['product_type' => 'Existing Business', 'renewable' => 1, 'date_closed' => '2020-01-01'],
                    ['product_type' => 'New Business', 'renewable' => 1, 'date_closed' => '2019-01-01'],
                ],
                '2020-01-01',
            ],
            // 3 related RLIs, all of which fit the criteria but with different close dates
            [
                [
                    ['product_type' => 'Existing Business', 'renewable' => 1, 'date_closed' => '2020-01-01'],
                    ['product_type' => 'Existing Business', 'renewable' => 1, 'date_closed' => '2019-01-01'],
                    ['product_type' => 'Existing Business', 'renewable' => 1, 'date_closed' => '2021-01-01'],
                ],
                '2019-01-01',
            ],
        ];
    }
    //END SUGARCRM flav=ent ONLY
}
