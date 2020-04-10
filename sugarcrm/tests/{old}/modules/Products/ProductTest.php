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
 * Class ProductTest
 * @coversDefaultClass Product
 */
class ProductTest extends TestCase
{
    public static function setUpBeforeClass() : void
    {
        SugarTestHelper::setUp('beanList');
    }

    public static function tearDownAfterClass(): void
    {
        SugarTestProductUtilities::removeAllCreatedProducts();
        SugarTestHelper::tearDown();
    }

    /**
     * @param String $amount
     * @param String $quantity
     * @param String $discount
     * @param String $discount_select
     * @param String $likely_expected
     * @throws SugarMath_Exception
     * @dataProvider productDataProvider
     * @covers ::convertToRevenueLineItem
     */
    public function testConvertProductToRLI($amount, $quantity, $discount, $discount_select, $likely_expected)
    {
        /* @var $product Product */
        $product = $this->getMockBuilder('Product')->setMethods(array('save'))->getMock();

        $product->expects($this->any())
            ->method('save')
            ->will($this->returnValue(true));

        $discount_amount = $discount;
        if ($discount_select === 1) {
            $discount_amount = SugarMath::init()->exp('(?*?)*(?/100)', array($amount, $quantity, $discount))->result();
        }

        $product->name = 'Hello World';
        $product->total_amount = SugarMath::init()->exp('((?*?)-?)', array($amount, $quantity, $discount_amount))->result();
        $product->discount_price = $amount;
        $product->quantity = $quantity;
        $product->discount_amount = $discount;
        $product->discount_select = $discount_select;
        $product->fetched_row = array();

        foreach ($product->getFieldDefinitions() as $field) {
            $product->fetched_row[$field['name']] = $product->{$field['name']};
        }

        SugarTestReflection::callProtectedMethod($product, 'calculateDiscountPrice');

        $rli = $product->convertToRevenueLineItem();

        $this->assertEquals($product->revenuelineitem_id, $rli->id);
        $this->assertEquals($product->name, $rli->name);
        $this->assertEquals(
            $likely_expected,
            $rli->likely_case,
            'Likely Case Is Wrong'
        );
        // lets make sure that the discount_amount is correct
        $this->assertEquals(
            $discount_amount,
            $rli->discount_amount,
            'Discount Amount Is Wrong'
        );
    }

    /**
     * productDataProvider
     */
    public function productDataProvider()
    {
        // $amount, $quantity, $discount, $discount_select, $likely_expected
        return array(
            array('100.00', '1', '0', null, '100.000000'),
            array('1000.00', '10', '0', null, '10000.000000'),
            array('100.00', '10', '1', null, '999.000000'),
            array('100.00', '1', '0', 1, '100.000000'),
            array('100.00', '1', '10', 1, '90.000000'),
            array('100.00', '2', '20', 1, '160.000000'),
            array('0.13', '1000', '10', 1, '117.000000'),
            array('0.25', '89765', '21456.00', null, '985.250000'),
        );
    }

    /**
     * @dataProvider dataProviderUpdateCurrencyBaseRate
     * @param string $stage
     * @param boolean $expected
     * @covers ::updateCurrencyBaseRate
     */
    public function testUpdateCurrencyBaseRate($stage, $expected)
    {
        $product = $this->createPartialMock('Product', array('save', 'load_relationship'));
        $product->expects($this->once())
            ->method('load_relationship')
            ->with('product_bundles')
            ->willReturn(true);

        $bundle = $this->createPartialMock('ProductBundle', array('save', 'load_relationship'));

        $bundle->expects($this->once())
            ->method('load_relationship')
            ->with('quotes')
            ->willReturn(true);

        /* @var $quote Quote */
        $quote = $this->createPartialMock('Quote', array('save'));

        $quote->quote_stage = $stage;

        $quote_link2 = $this->getMockBuilder('Link2')
            ->disableOriginalConstructor()
            ->setMethods(array('getBeans'))
            ->getMock();

        $quote_link2->expects($this->once())
            ->method('getBeans')
            ->willReturn(
                array(
                    $quote
                )
            );

        /* @var $product Product */
        $bundle->quotes = $quote_link2;

        $bundle_link2 = $this->getMockBuilder('Link2')
            ->disableOriginalConstructor()
            ->setMethods(array('getBeans'))
            ->getMock();

        $bundle_link2->expects($this->once())
            ->method('getBeans')
            ->willReturn(
                array(
                    $bundle
                )
            );

        /* @var $product Product */
        $product->product_bundles = $bundle_link2;

        $this->assertEquals($expected, $product->updateCurrencyBaseRate());
    }

    public function dataProviderUpdateCurrencyBaseRate()
    {
        return array(
            array('Draft', true),
            array('Negotiation', true),
            array('Delivered', true),
            array('On Hold', true),
            array('Confirmed', true),
            array('Closed Accepted', false),
            array('Closed Lost', false),
            array('Closed Dead', false)
        );
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
        $product = SugarTestProductUtilities::createProduct();
        $product->quantity = $quantity;
        $product->discount_price = $discount_price;
        $product->discount_amount = $discount_amount;
        $product->discount_select = $discount_select;
        $product->save();

        // lets make sure the totals are correct
        $this->assertEquals(
            $total_amount,
            $product->total_amount,
            'Total amount Is Wrong'
        );
    }

    /**
     * totalAmountDataProvider
     */
    public function totalAmountDataProvider()
    {
        // $quantity, $discount_price, $discount_amount, $discount_select, $total_amount
        return array(
            array('-2', '100.000000', '10.000000', '0', '-190.000000'),
            array('-2', '100.000000', '10.000000', '1', '-180.000000'),
            array('2', '100.000000', '10.000000', '0', '190.000000'),
            array('2', '100.000000', '10.000000', '1', '180.000000'),
        );
    }

    /**
     * @covers ::updateCurrencyBaseRate
     */
    public function testUpdateCurrencyBaseRateWithNotQuoteReturnTrue()
    {
        $product = $this->createPartialMock('Product', array('save', 'load_relationship'));
        $product->expects($this->once())
            ->method('load_relationship')
            ->with('product_bundles')
            ->willReturn(true);

        $link2 = $this->getMockBuilder('Link2')
            ->disableOriginalConstructor()
            ->setMethods(array('getBeans'))
            ->getMock();

        $link2->expects($this->once())
            ->method('getBeans')
            ->willReturn(
                array()
            );

        /* @var $product Product */
        $product->product_bundles = $link2;

        $this->assertTrue($product->updateCurrencyBaseRate());
    }

    /**
     * @covers ::get_summary_text
     */
    public function testGetSummaryText()
    {
        $product = $this->createPartialMock('Product', array('save', 'load_relationship'));
        $product->name = 'test';

        $this->assertEquals('test', $product->get_summary_text());
    }

    public static function dataProviderSetAccountIdForOpportunity()
    {
        return array(
            array(
                array(
                    'test_account_id'
                ),
                true
            ),
            array(
                array(),
                false
            )
        );
    }

    /**
     * @dataProvider dataProviderSetAccountIdForOpportunity
     * @covers ::setAccountIdForOpportunity
     */
    public function testSetAccountIdForOpportunity($accounts, $expected)
    {
        $product = $this->createPartialMock('Product', array('save', 'load_relationship'));

        $opp = $this->getMockBuilder('Opportunity')
            ->setMethods(array('save', 'load_relationship'))
            ->getMock();

        $opp->id = 'test_opp_id';

        $opp->expects($this->once())
            ->method('load_relationship')
            ->with('accounts')
            ->willReturn(true);

        $link2 = $this->getMockBuilder('Link2')
            ->setMethods(array('get'))
            ->disableOriginalConstructor()
            ->getMock();

        $link2->expects($this->once())
            ->method('get')
            ->willReturn($accounts);


        $opp->accounts = $link2;

        BeanFactory::registerBean($opp);

        $actual = SugarTestReflection::callProtectedMethod($product, 'setAccountIdForOpportunity', array($opp->id));

        $this->assertEquals($expected, $actual);

        BeanFactory::unregisterBean($opp, $opp->id);
    }

    public static function dataProviderPopulateFromTemplateWillReturnFalse()
    {
        return array(
            array(null),
            array('one_id')
        );
    }

    /**
     * @dataProvider dataProviderPopulateFromTemplateWillReturnFalse
     * @param string $template_id
     * @covers ::populateFromTemplate
     */
    public function testPopulateFromTemplateWillReturnFalse($template_id)
    {
        $product = $this->createPartialMock('Product', array('save'));

        $product->product_template_id = $template_id;
        $product->fetched_row = array(
            'product_template_id' => $template_id
        );

        $actual = SugarTestReflection::callProtectedMethod($product, 'populateFromTemplate');

        $this->assertFalse($actual);
    }

    /**
     * @covers ::calculateDiscountPrice
     */
    public function testCalculateDiscountPriceDoesNotRunIfFieldEmpty()
    {
        $fields = array(
            'pricing_formula',
            'cost_price',
            'list_price',
            'discount_price',
            'pricing_factor'
        );

        $product = $this->createPartialMock('Product', array('save', 'getPriceFormula'));

        $product->expects($this->never())
            ->method('getPriceFormula');

        foreach($fields as $field) {
            $product->$field = null;
        }

        SugarTestReflection::callProtectedMethod($product, 'calculateDiscountPrice');
    }

    /**
     * @covers ::calculateDiscountPrice
     */
    public function testCalculateDiscountPrice()
    {
        $product = $this->createPartialMock('Product', array('save', 'getPriceFormula'));
        $product->pricing_formula = 'PercentageDiscount';
        $product->cost_price = '100.000000';
        $product->list_price = '150.000000';
        $product->discount_price = '25.000000';
        $product->pricing_factor = '12.00';


        SugarAutoLoader::load('modules/ProductTemplates/formulas/price_list_discount.php');

        $formula = $this->getMockBuilder('PercentageDiscount')
            ->setMethods(array('calculate_price'))
            ->getMock();

        $formula->expects($this->once())
            ->method('calculate_price')
            ->with(
                $product->cost_price,
                $product->list_price,
                $product->discount_price,
                $product->pricing_factor
            );

        $product->expects($this->once())
            ->method('getPriceFormula')
            ->willReturn($formula);

        SugarTestReflection::callProtectedMethod($product, 'calculateDiscountPrice');
    }

    public function dataProviderGetPriceFormula()
    {
        return array(
            array('Fixed'),
            array('ProfitMargin'),
            array('PercentageMarkup'),
            array('PercentageDiscount'),
            array('IsList')
        );
    }

    /**
     * @dataProvider dataProviderGetPriceFormula
     * @covers ::getPriceFormula
     * @param string $formula
     */
    public function testGetPriceFormula($formula)
    {
        $product = $this->createPartialMock('Product', array('save'));

        $actual = SugarTestReflection::callProtectedMethod($product, 'getPriceFormula', array($formula));

        $this->assertInstanceOf($formula, $actual);

        unset($GLOBALS['price_formulas']);
    }

    /**
     * @dataProvider dataProviderCheckQuantity
     * @covers ::checkQuantity
     * @param mixed $actual
     * @param integer $expected
     */
    public function testQuantityNotDefaulted($actual, $expected)
    {
        $product = $this->getMockBuilder('Product')
            ->setMethods(array('save'))
            ->getMock();

        $product->quantity = $actual;

        SugarTestReflection::callProtectedMethod($product, 'checkQuantity');

        $this->assertEquals($expected, $product->quantity);
    }

    public static function dataProviderCheckQuantity()
    {
        return array(
            array('', 0),
            array(null, 0),
            array(0, 0),
            array(1, 1),
            array(42,42),
        );
    }

    /**
     * Tests to make sure product_bundles always saves before quotes
     *
     * @covers ::getRelatedCalcFields
     */
    public function testGetRelatedCalcFields()
    {
        global $dictionary;

        $product = $this->getMockBuilder('Product')
            ->setMethods(array('save'))
            ->getMock();

        $initialState = $dictionary['Product']['related_calc_fields'];

        // set dictionary for test
        $dictionary['Product']['related_calc_fields'] = ['quotes', 'product_bundles'];

        $result = SugarTestReflection::callProtectedMethod($product, 'getRelatedCalcFields');

        $this->assertEquals($result, ['product_bundles', 'quotes']);

        // test if both product_bundles or quotes is missing from the list
        $dictionary['Product']['related_calc_fields'] = ['test', 'quotes'];

        $result = SugarTestReflection::callProtectedMethod($product, 'getRelatedCalcFields');

        $this->assertEquals($result, ['test', 'quotes']);

        // restore dictionary
        $dictionary['Product']['related_calc_fields'] = $initialState;
    }

    //BEGIN SUGARCRM flav=ent ONLY
    /**
     * @covers ::setServiceEndDate
     * @dataProvider providerTestSetServiceEndDate
     */
    public function testSetServiceEndDate(array $serviceFields, ?string $expectedEndDate, $shouldClearFields)
    {
        $mockProduct = $this->getMockBuilder('Product')
            ->disableOriginalConstructor()
            ->getMock();
        // Set the service fields accordingly
        $mockProduct->service = $serviceFields['service'];
        $mockProduct->service_start_date = $serviceFields['service_start_date'];
        $mockProduct->service_duration_value = $serviceFields['service_duration_value'];
        $mockProduct->service_duration_unit = $serviceFields['service_duration_unit'];
        SugarTestReflection::callProtectedMethod($mockProduct, 'setServiceEndDate');
        $this->assertSame($expectedEndDate, $mockProduct->service_end_date);
        if ($shouldClearFields) {
            $this->assertSame(false, $mockProduct->service);
            $this->assertSame(null, $mockProduct->service_start_date);
            $this->assertSame(null, $mockProduct->service_duration_value);
            $this->assertSame(null, $mockProduct->service_duration_unit);
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
                true,
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
    //END SUGARCRM flav=ent ONLY
}
