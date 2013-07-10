<?php
//FILE SUGARCRM flav=pro ONLY
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


class RevenueLineItemTest extends Sugar_PHPUnit_Framework_TestCase
{
    /**
     * @group revenuelineitems
     * @covers RevenueLineItem::convertToQuotedLineItem()
     */
    public function testConvertToQuotedLineItem()
    {
        /* @var $rli RevenueLineItem */
        $rli = $this->getMock('RevenueLineItem', array('save'));
        $rli->likely_case = '100.00';
        $rli->sales_stage = 'Test';
        $product = $rli->convertToQuotedLineItem();

        $this->assertEquals($rli->likely_case, $product->discount_price);
        $this->assertEquals($rli->id, $product->revenuelineitem_id, 'RLI to QLI Link is not Set');
        $this->assertEquals('Test', $product->sales_stage, "Product does not match RevenueLineItem");
    }
}


