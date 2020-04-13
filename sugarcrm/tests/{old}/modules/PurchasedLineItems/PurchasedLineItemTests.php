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
}
