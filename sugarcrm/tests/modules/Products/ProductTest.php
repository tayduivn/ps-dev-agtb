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
        $product->fetched_row = array();

        foreach($product->getFieldDefinitions() as $field) {
            $product->fetched_row[$field['name']] = $product->$field['name'];
        }

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
