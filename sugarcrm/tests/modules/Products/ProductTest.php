<?php
/*
 * By installing or using this file, you are confirming on behalf of the entity
 * subscribed to the SugarCRM Inc. product ("Company") that Company is bound by
 * the SugarCRM Inc. Master Subscription Agreement (“MSA”), which is viewable at:
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
    public function testConvertProductToRLI($amount, $quantity, $discount)
    {
        /* @var $product Product */
        $product = $this->getMock('Product', array('save'));

        $product->expects($this->any())
            ->method('save')
            ->will($this->returnValue(true));

        $product->name = 'Hello World';
        $product->total_amount = $amount;
        $product->quantity = $quantity;
        $product->discount_amount = $discount;

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
    }
    
    /**
     * productDataProvider
     */
    public function productDataProvider()
    {
        return array(
           array('100.00', '1', '0'),
           array('100.00', '10', '0'),
           array('100.00', '10', '1')
        );
    }
}
