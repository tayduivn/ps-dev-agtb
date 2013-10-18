<?php
/*
 * By installing or using this file, you are confirming on behalf of the entity
 * subscribed to the SugarCRM Inc. product ("Company") that Company is bound by
 * the SugarCRM Inc. Master Subscription Agreement ("MSA"), which is viewable at:
 * http://www.sugarcrm.com/master-subscription-agreement
 *
 * If Company is not bound by the MSA, then by installing or using this file
 * you are agreeing unconditionally that Company will be bound by the MSA and
 * certifying that you have authority to bind Company accordingly.
 *
 * Copyright  2004-2013 SugarCRM Inc.  All rights reserved.
 */

class ProductTest extends Sugar_PHPUnit_Framework_TestCase
{
    /**
     * @dataProvider productDataProvider
     */
    public function testConvertProductToRLI($amount, $quantity, $discount, $discount_select)
    {
        /* @var $product Product */
        $product = $this->getMock('Product', array('save'));

        $product->expects($this->any())
            ->method('save')
            ->will($this->returnValue(true));

        $product->name = 'Hello World';
        $product->total_amount = $amount;
        $product->discount_price = $amount;
        $product->quantity = $quantity;
        $product->discount_amount = $discount;
        $product->discount_select = $discount_select;

        SugarTestReflection::callProtectedMethod($product, 'calculateDiscountPrice');

        $rli = $product->convertToRevenueLineItem();

        $this->assertEquals($product->revenuelineitem_id, $rli->id);
        $this->assertEquals($product->name, $rli->name);
        $this->assertEquals(
            SugarMath::init()
                ->exp(
                    '(?+?)-(?*?)',
                    array(
                        $amount,
                        $discount,
                        $discount,
                        $quantity
                    )
                )
                ->result(),
            $rli->likely_case
        );
        // lets make sure that the discount_amount is correct
        $this->assertEquals(
            SugarMath::init()
                ->exp(
                    '(?*?)',
                    array(
                        $product->deal_calc,
                        $quantity
                    )
                )
                ->result(),
            $rli->discount_amount
        );
    }

    /**
     * productDataProvider
     */
    public function productDataProvider()
    {
        return array(
            array('100.00', '1', '0', null),
            array('100.00', '10', '0', null),
            array('100.00', '10', '1', null),
            array('100.00', '1', '0', 1),
            array('100.00', '1', '10', 1),
            array('100.00', '2', '10', 1),
        );
    }
}
