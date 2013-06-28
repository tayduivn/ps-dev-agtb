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
    public function testConvertProductToRLI()
    {
        /* @var $product Product */
        $product = $this->getMock('Product', array('save'));

        $product->expects($this->any())
            ->method('save')
            ->will($this->returnValue(true));

        $product->name = 'Hello World';
        $product->total_amount = '70.00';

        $rli = $product->convertToRevenueLineItem();

        $this->assertEquals($product->revenuelineitem_id, $rli->id);
        $this->assertEquals($product->name, $rli->name);
        $this->assertEquals($product->total_amount, $rli->likely_case);
    }
}
