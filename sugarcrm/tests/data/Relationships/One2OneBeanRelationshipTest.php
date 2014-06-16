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
 * Copyright (C) 2004-2014 SugarCRM Inc.  All rights reserved.
 */

class One2OneBeanRelationshipTest extends Sugar_PHPUnit_Framework_TestCase
{

    public function testbuildJoinSugarQuery()
    {
        $relDef = array(
            'name' => 'products_revenuelineitems',
            'lhs_module' => 'Products',
            'lhs_table' => 'products',
            'lhs_key' => 'revenuelineitem_id',
            'rhs_module' => 'RevenueLineItems',
            'rhs_table' => 'revenue_line_items',
            'rhs_key' => 'id',
            'relationship_type' => 'one-to-one',
        );
        $rel = new One2OneBeanRelationship($relDef);

        /* @var $product Product */
        $product = $this->getMock('Product', array('save'));
        $product->id = 'unit_test_id';

        $link2 = $this->getMockBuilder('Link2')
            ->setMethods(array('getSide', 'getRelatedModuleName', 'getFocus'))
            ->disableOriginalConstructor()
            ->getMOck();
        $link2->expects($this->any())
            ->method('getSide')
            ->willReturn(REL_RHS);
        $link2->expects($this->any())
            ->method('getRelatedModuleName')
            ->willReturn($relDef['rhs_module']);
        $link2->expects($this->never())
            ->method('getFocus');
        $sq = new SugarQuery();
        $sq->select('id');
        $sq->from(BeanFactory::getBean('RevenueLineItems'));

        /** @var Link2 $link2 */
        $ret = $rel->buildJoinSugarQuery($link2, $sq, array('ignoreRole' => true));

        /** @var SugarQuery_Builder_Join $ret */
        $this->assertEquals('revenue_line_items', $ret->on['and']->conditions[0]->field->table);
        $this->assertEquals('id', $ret->on['and']->conditions[0]->field->field);
        $this->assertEquals('products.revenuelineitem_id', $ret->on['and']->conditions[0]->values);
    }
}
