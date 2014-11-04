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

require_once 'modules/Quotes/QuotesApiHelper.php';
require_once 'modules/ProductBundles/ProductBundlesApiHelper.php';

class ProductBundlesApiHelperTest extends Sugar_PHPUnit_Framework_TestCase
{
    /**
     * @var ProductBundlesApiHelper
     */
    protected $helper;

    /**
     * @var ProductBundle
     */
    protected $product_bundle;

    /**
     * @var Quote
     */
    protected $quote;

    /**
     * @var ServiceBase
     */
    protected $api_service;

    public function setUp()
    {
        parent::setUp();

        $this->api_service = $this->getMockBuilder('ServiceBase')
            ->setMethods(array('execute', 'handleException'))
            ->disableOriginalConstructor()
            ->getMock();
        $this->api_service->user = $this->getMock('User', array('save'));


        $this->helper = $this->getMock('ProductBundlesApiHelper', array('execute'), array($this->api_service));

        $this->product_bundle = $this->getMock('ProductBundle', array('save'));
        $this->quote = $this->getMock('Quote', array('save', 'retrieve', 'ACLAccess', 'mark_deleted'));

    }

    public function tearDown()
    {
        // always clear this out.
        ApiHelper::setHelper('Quotes');
        unset($this->helper);
        SugarTestAccountUtilities::removeAllCreatedAccounts();
        SugarTestHelper::tearDown();
        parent::tearDown();
    }

    /**
     * @expectedException SugarApiExceptionInvalidParameter
     */
    public function testPopulateFromApiWithNoQuoteIdSet()
    {
        $this->helper->populateFromApi($this->product_bundle, array());
    }

    /**
     * @expectedException SugarApiExceptionInvalidParameter
     */
    public function testPopulateFromApiWithEmptyQuoteIdSet()
    {
        $this->helper->populateFromApi($this->product_bundle, array('quote_id'));
    }

    /**
     * @dataProvider dataProviderPopulateFromApi
     */
    public function testPopulateFromApiWithNoBundlePosition($bundlePosition)
    {

        $items = array(
            array(
                'module' => 'ProductBundleNotes'
            ),
            array(
                'module' => 'Products'
            )
        );
        $data = array_merge(array('items' => $items, 'quote_id' => 'unit_test'), $bundlePosition);

        $helper = $this->getMockBuilder('ProductBundlesApiHelper')
            ->setMethods(array('execute', 'getQuoteBean', 'fillInFromQuote', 'processBundleItems', 'linkBundleToQuote'))
            ->setConstructorArgs(array($this->api_service))
            ->getMock();

        $helper->expects($this->once())
            ->method('getQuoteBean')
            ->with('unit_test')
            ->will($this->returnValue($this->quote));

        $helper->expects($this->once())
            ->method('fillInFromQuote')
            ->with($this->product_bundle, $this->quote);

        $helper->expects($this->once())
            ->method('processBundleItems')
            ->with($items, $this->product_bundle, $this->quote);

        $position = array_shift(array_values($bundlePosition));
        $helper->expects($this->once())
            ->method('linkBundleToQuote')
            ->with($this->product_bundle, $this->quote, $position);

        $helper->populateFromApi($this->product_bundle, $data);
    }


    public function dataProviderPopulateFromApi()
    {
        return array(
            array(array('bundle_index' => 0)),
            array(array('position' => 1)),
            array(array())
        );
    }

    /**
     * @expectedException SugarApiExceptionNotFound
     */
    public function testGetQuoteBeanThrowsNotFound()
    {
        SugarTestReflection::callProtectedMethod(
            $this->helper,
            'getQuoteBean',
            array(
                'test_id'
            )
        );
    }

    /**
     * @expectedException SugarApiExceptionNotAuthorized
     */
    public function testGetQuoteBeanThrowsNotAuthorized()
    {
        $this->quote->id = 'unit_test';
        $this->quote->module_name = 'Quotes';

        $this->quote->expects($this->once())
            ->method('ACLAccess')
            ->with('save')
            ->will($this->returnValue(false));

        BeanFactory::registerBean($this->quote);

        SugarTestReflection::callProtectedMethod(
            $this->helper,
            'getQuoteBean',
            array(
                'unit_test'
            )
        );

        BeanFactory::deleteBean('Quotes', 'unit_test');
    }

    public function testGetQuoteBeanReturnsQuoteBean()
    {
        $this->quote->id = 'unit_test';
        $this->quote->module_name = 'Quotes';

        $this->quote->expects($this->once())
            ->method('ACLAccess')
            ->with('save')
            ->will($this->returnValue(true));

        BeanFactory::registerBean($this->quote);

        $bean = SugarTestReflection::callProtectedMethod(
            $this->helper,
            'getQuoteBean',
            array(
                'unit_test'
            )
        );

        $this->assertInstanceOf('Quote', $bean);

        BeanFactory::deleteBean('Quotes', 'unit_test');
    }

    public function testFillInFromQuote()
    {
        $prefill = array(
            'team_id' => 'my_awesome_team',
            'team_set_id' => 'my_awesome_team_set',
            'currency_id' => 'my_awesome_currency',
            'base_rate' => '1.0',
            'taxrate_id' => 'my_awesome_taxrate'
        );

        foreach ($prefill as $key => $value) {
            $this->quote->$key = $value;
        }

        $this->product_bundle->expects($this->once())
            ->method('save');

        SugarTestReflection::callProtectedMethod(
            $this->helper,
            'fillInFromQuote',
            array(
                $this->product_bundle,
                $this->quote
            )
        );

        foreach ($prefill as $key => $value) {
            $this->assertEquals($value, $this->product_bundle->$key);
        }
    }


    public function testProcessBundleItems()
    {
        $quoteHelper = $this->getMockBuilder('QuotesApiHelper')
            ->setMethods(array('execute', 'handleBundleNoteSave', 'handleBundleProductSave'))
            ->setConstructorArgs(array($this->api_service))
            ->getMock();

        $quoteHelper->expects($this->once())
            ->method('handleBundleNoteSave');

        $quoteHelper->expects($this->once())
            ->method('handleBundleProductSave');

        /* @var $quoteHelper QuotesApiHelper */
        ApiHelper::setHelper('Quotes', $quoteHelper);

        SugarTestReflection::callProtectedMethod(
            $this->helper,
            'processBundleItems',
            array(
                array(
                    array(
                        'module' => 'ProductBundleNotes'
                    ),
                    array(
                        'module' => 'Products'
                    )
                ),
                $this->product_bundle,
                $this->quote
            )
        );
    }

    public function testLinkBundleToQuoteWithNoPosition()
    {
        $link2 = $this->getMockBuilder('Link2')
            ->setMethods(array('get', 'add'))
            ->disableOriginalConstructor()
            ->getMock();
        $this->quote->product_bundles = $link2;

        $link2->expects($this->once())
            ->method('get')
            ->will($this->returnValue(array()));

        $link2->expects($this->once())
            ->method('add')
            ->with($this->product_bundle, array('bundle_index' => 0));

        $this->quote->expects($this->once())
            ->method('save');

        SugarTestReflection::callProtectedMethod(
            $this->helper,
            'linkBundleToQuote',
            array(
                $this->product_bundle,
                $this->quote
            )
        );
    }

    public function testLinkBundleToQuoteWithPosition()
    {
        $link2 = $this->getMockBuilder('Link2')
            ->setMethods(array('get', 'add'))
            ->disableOriginalConstructor()
            ->getMock();
        $this->quote->product_bundles = $link2;

        $link2->expects($this->never())
            ->method('get');

        $link2->expects($this->once())
            ->method('add')
            ->with($this->product_bundle, array('bundle_index' => 1));

        $this->quote->expects($this->once())
            ->method('save');

        SugarTestReflection::callProtectedMethod(
            $this->helper,
            'linkBundleToQuote',
            array(
                $this->product_bundle,
                $this->quote,
                1
            )
        );
    }
}
