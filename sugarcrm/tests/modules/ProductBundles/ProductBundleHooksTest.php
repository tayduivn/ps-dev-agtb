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

require_once 'modules/ProductBundles/ProductBundleHooks.php';

class ProductBundleHooksTest extends Sugar_PHPUnit_Framework_TestCase
{
    /**
     * @dataProvider dataProviderInvalidLinkName
     * @param string $link_name
     */
    public function testWhenLinkNotEqualToProductsReturnsFalse($link_name)
    {
        $hook = new ProductBundleHooks();
        /* @var $bean ProductBundle */
        $bean = $this->getMock('ProductBundle', array('save'));
        $args = array('link' => $link_name);

        $this->assertFalse($hook->afterProductRelationship($bean, 'after_relationship_delete', $args));
    }

    public function dataProviderInvalidLinkName()
    {
        return array(
            array(''),
            array('test_string')
        );
    }

    public function testWhenLinkNotNotSetReturnsFalse()
    {
        $hook = new ProductBundleHooks();
        /* @var $bean ProductBundle */
        $bean = $this->getMock('ProductBundle', array('save'));
        $args = array();

        $this->assertFalse($hook->afterProductRelationship($bean, 'after_relationship_delete', $args));
    }

    /**
     * @dataProvider dataProviderInvalidEvent
     * @param string $event
     */
    public function testWhenInvalidEventNameReturnsFalse($event)
    {
        $hook = new ProductBundleHooks();
        /* @var $bean ProductBundle */
        $bean = $this->getMock('ProductBundle', array('save'));
        $args = array('link' => 'products');

        $this->assertFalse($hook->afterProductRelationship($bean, $event, $args));
    }

    public function dataProviderInvalidEvent()
    {
        return array(
            array(''),
            array('test_string')
        );
    }

    public function testAfterProductRelationship()
    {
        $hook = new ProductBundleHooks();

        $bean = $this->getMock('ProductBundle', array('save'));
        $bean->expects($this->once())
            ->method('save');

        $link2 = $this->getMockBuilder('Link2')
            ->disableOriginalConstructor()
            ->setMethods(array('getBeans'))
            ->getMock();

        $link2->expects($this->once())
            ->method('getBeans')
            ->will($this->returnValue(array()));

        $bean->quotes = $link2;

        $args = array('link' => 'products');
        /* @var $bean ProductBundle */
        $this->assertTrue($hook->afterProductRelationship($bean, 'after_relationship_delete', $args));
    }
}
