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

namespace Sugarcrm\SugarcrmTestUnit\modules\ProductBundles;

/**
 * Class ProductBundleHooksTest
 * @package Sugarcrm\SugarcrmTestUnit\modules\ProductBundles
 * @coversDefaultClass \ProductBundleHooks
 */
class ProductBundleHooksTest extends \PHPUnit_Framework_TestCase
{
    public function setup()
    {
        \SugarAutoLoader::load('../../modules/ProductBundles/ProductBundleHooks.php');
        \SugarAutoLoader::load('../../modules/ProductBundles/ProductBundle.php');
        parent::setup();
    }

    /**
     * @covers ::setQLIQuoteLink
     */
    public function testSetQLIQuoteLink()
    {
        $mock = new \ProductBundleHooks();

        $genericBeanMock = $this->getMockBuilder('\ProductBundleTest_genericBeanMock')
            ->setMethods(['save'])
            ->disableOriginalConstructor()
            ->getMock();

        $genericBeanMock->id = 'foo';

        $genericBeanMock->expects($this->once())
            ->method('save');

        $productBundle = $this->getMockBuilder('\ProductBundle')
            ->setMethods(['get_linked_beans'])
            ->disableOriginalConstructor()
            ->getMock();

        $productBundle->expects($this->exactly(2))
            ->method('get_linked_beans')
            ->will($this->returnValue(array($genericBeanMock)));

        $mock->setQLIQuoteLink($productBundle, 'foo', 'bar');
    }
}

class ProductBundleTest_genericBeanMock
{
    public function save()
    {

    }
}
