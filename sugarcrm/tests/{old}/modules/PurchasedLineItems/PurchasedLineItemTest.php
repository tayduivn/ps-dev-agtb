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
 * Class PurchasedLineItemTest
 * @coversDefaulPurchasedLineItem
 */
class PurchasedLineItemTest extends TestCase
{
    public static function setUpBeforeClass(): void
    {
        SugarTestHelper::setUp('current_user');
        SugarTestHelper::setUp('app_list_strings');
        SugarTestHelper::setUp('mod_strings', ['PurchasedLineItems']);
    }

    public static function tearDownAfterClass(): void
    {
        SugarTestHelper::tearDown();
    }

    public function tearDown(): void
    {
        SugarTestAccountUtilities::removeAllCreatedAccounts();
        SugarTestPurchasedLineItemUtilities::removeAllCreatedPurchasedLineItems();
    }

    /**
     * @dataProvider dataProviderDiscountCalculation
     * @param $discount_price
     * @param $quantity
     * @param $discount_amount
     * @param $expected_total
     */
    public function testDiscountCalculation($discount_price, $quantity, $discount_amount, $expected_total): void
    {
        $pli = SugarTestPurchasedLineItemUtilities::createPurchasedLineItem();
        $pli->discount_price = $discount_price;
        $pli->discount_amount = $discount_amount;
        $pli->quantity = $quantity;
        $pli->save();
        $this->assertEquals((float) $expected_total, (float) $pli->total_amount);
    }

    public function dataProviderDiscountCalculation(): array
    {
        // values are price, quantity, discount_amount, and expected_total
        return [
            ['100.00', 1,'0', '100'],
            ['200.00', -1, '10', '-190'],
            ['200.00', 1, '10', '190'],
        ];
    }

    /**
     * @dataProvider dataProviderAnnualRevenueCalculation
     * @param $revenue
     * @param $discount_price
     * @param $discount_amount
     * @param $quantity
     * @param $service
     * @param $service_duration_unit
     * @param $service_duration_value
     */
    public function testAnnualRevenueCalculation(
        $revenue,
        $discount_price,
        $discount_amount,
        $quantity,
        $service,
        $service_duration_unit,
        $service_duration_value,
        $annual_revenue
    ): void {
        $pli = SugarTestPurchasedLineItemUtilities::createPurchasedLineItem();
        $pli->revenue = $revenue;
        $pli->discount_price = $discount_price;
        $pli->discount_amount = $discount_amount;
        $pli->quantity = $quantity;
        $pli->service = $service;
        $pli->service_duration_unit = $service_duration_unit;
        $pli->service_duration_value = $service_duration_value;
        $pli->save();
        $this->assertEquals($annual_revenue, round($pli->annual_revenue, 2));
    }

    public function dataProviderAnnualRevenueCalculation(): array
    {
        // values are revenue, discount_price, discount_amount, quantity, service, service_duration_unit,
        // service_duration_value, annual_revenue
        return [
            ['1000.00', '1000.00', 0, 1, false, '', '', '1000.00'],
            ['300.00', '300.00', 0, 1, true, 'year', '3', '100.00'],
            ['-300.00', '-300.00', 0, 1, true, 'year', '3', '-100.00'],
            ['100.00', '100.00', 0, 1, true, 'month', '18', '66.67'],
            ['18.00', '18.00', 0, 1, true, 'day', '18', '365.00'],
            ['1000.00', '1000.00', 0, 1, true, 'day', '545', '669.72'],
        ];
    }

    /**
     * @covers ::updateRelatedPurchase
     * @dataProvider providerTestUpdateRelatedPurchase
     *
     * @param Array $pliDataArray array of PLIs to create
     * @param string $start_date the expected start_date of the Purchase affected
     * @param string $end_date the expected end_date of the Purchase affected
     */
    public function testUpdateRelatedPurchase($pliDataArray, $start_date, $end_date)
    {
        // Create a purchase
        $purchase = SugarTestPurchaseUtilities::createPurchase();

        // Create PLIs that point to the account. On save, they should update
        // that purchases's start_date and end_date fields
        foreach ($pliDataArray as $pliData) {
            $pli = SugarTestPurchasedLineItemUtilities::createPurchasedLineItem();
            $pli->purchase_id = $purchase->id;
            $pli->service = $pliData['service'];
            $pli->service_start_date = $pliData['service_start_date'];
            $pli->service_end_date = $pliData['service_end_date'];
            $pli->save();
        }

        // Check that the purchase's start_date and end_date were correctly calculated
        // on PLI save
        $resultPurchase = BeanFactory::retrieveBean('Purchases', $purchase->id);
        $this->assertEquals($start_date, $resultPurchase->start_date);
        $this->assertEquals($end_date, $resultPurchase->end_date);
    }

    public function providerTestUpdateRelatedPurchase()
    {
        return array(
            // No related PLIs
            array(
                array(
                ),
                '',
                '',
            ),
            // 2 related service PLIs with different start and end dates
            array(
                array(
                    array('service' => 1, 'service_start_date' => '2020-01-01', 'service_end_date' => '2020-11-12'),
                    array('service' => 1, 'service_start_date' => '2020-05-01', 'service_end_date' => '2025-05-01'),
                ),
                '2020-01-01',
                '2025-05-01',
            ),
            // 2 related goods PLIs with the same start and end dates
            array(
                array(
                    array('service' => 0, 'service_start_date' => '2019-10-05', 'service_end_date' => '2019-10-05'),
                    array('service' => 0, 'service_start_date' => '2020-08-08', 'service_end_date' => '2020-08-08'),
                ),
                '2019-10-05',
                '2020-08-08',
            ),
            // 3 related PLIs one of which is service
            array(
                array(
                    array('service' => 1, 'service_start_date' => '2017-01-31', 'service_end_date' => '2050-12-31'),
                    array('service' => 0, 'service_start_date' => '2021-06-01', 'service_end_date' => '2021-06-01'),
                    array('service' => 0, 'service_start_date' => '2060-10-05', 'service_end_date' => '2060-10-05'),
                ),
                '2017-01-31',
                '2060-10-05',
            ),
        );
    }
}
