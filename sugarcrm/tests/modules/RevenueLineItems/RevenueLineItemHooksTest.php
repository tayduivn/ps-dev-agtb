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

require_once('modules/RevenueLineItems/RevenueLineItemHooks.php');
class RevenueLineItemHooksTest extends Sugar_PHPUnit_Framework_TestCase
{

    /**
     * @var RevenueLineItem
     */
    protected $rli;

    public function setUp()
    {
        parent::setUp();
        SugarTestHelper::setUp('moduleList');
        SugarTestHelper::setUp('beanList');

        $this->rli = $this->getMock('RevenueLineItem', array('save'));
    }

    public function tearDown()
    {
        $this->rli = null;
        SugarTestHelper::tearDown();
        parent::tearDown();
    }

    /**
     * @covers RevenueLineItemHooks::afterRelationshipDelete
     * @dataProvider dataAfterRelationshipDelete
     */
    public function testAfterRelationshipDelete($event, $link, $result)
    {
        $hook = new RevenueLineItemHooks();
        $ret = $hook->afterRelationshipDelete($this->rli, $event, $link);
        $this->assertEquals($result, $ret);
        
    }

    public function dataAfterRelationshipDelete()
    {
        return array(
            array('after_relationship_delete', array('link' => 'account_link'), true),
            array('after_relationship_delete', array('link' => 'foo'), false),
            array('foo', array('link' => 'account_link'), false),
            array('foo', array('link' => 'foo'), false ),
        );
    }
}
