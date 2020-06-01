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
 * Class PurchaseTest
 * @coversDefaultClass Purchase
 */
class PurchaseTest extends TestCase
{
    public function tearDown(): void
    {
        SugarTestPurchaseUtilities::removeAllCreatedPurchases();
        SugarTestPurchasedLineItemUtilities::removeAllCreatedPurchasedLineItems();
    }

    /**
     * @covers ::mapFieldsToPli
     * @dataProvider providerMapFieldsToPli
     */
    public function testMapFieldsToPli($service, $typeName, $categoryName): void
    {
        $purchase = SugarTestPurchaseUtilities::createPurchase();
        $pli = SugarTestPurchasedLineItemUtilities::createPurchasedLineItem();
        $purchase->service = $service;
        $purchase->type_id = Sugarcrm\Sugarcrm\Util\Uuid::uuid1();
        $purchase->type_name = $typeName;
        $purchase->category_id = Sugarcrm\Sugarcrm\Util\Uuid::uuid1();
        $purchase->category_name = $categoryName;

        $purchase->mapFieldsToPli($pli);

        foreach ($purchase->pliCopyFields as $purchField => $pliField) {
            $this->assertEquals($purchase->$purchField, $pli->$pliField);
        }
    }

    public function providerMapFieldsToPli(): array
    {
        return [
            [true, 'Product Type Name 1', 'Product Category Name 1',],
            [false, 'Product Type Name 2', 'Product Category Name 2',],
        ];
    }
}
