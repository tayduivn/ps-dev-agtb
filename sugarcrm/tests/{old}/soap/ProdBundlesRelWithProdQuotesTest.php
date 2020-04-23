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

/**
 * Bug #32064
 * Setting a relationship between ProductBundles and Quotes or Products and ProductBundles results in a PHP fatal error
 *
 * @ticket 32064
 */
class ProdBundlesRelWithProdQuotesTest extends SOAPTestCase
{
    protected $prodBundle = null;
    protected $quote = null;
    protected $product = null;

    protected function setUp() : void
    {
        parent::setUp();

        $this->prodBundle = SugarTestProductBundleUtilities::createProductBundle();
        $this->quote = SugarTestQuoteUtilities::createQuote();
        $this->product = SugarTestProductUtilities::createProduct();

        // Commit setUp records for DB2.
        $GLOBALS['db']->commit();
    }

    protected function tearDown() : void
    {
        $this->tearDownTestUser();

        SugarTestProductBundleUtilities::removeAllCreatedProductBundles();
        SugarTestQuoteUtilities::removeAllCreatedQuotes();
        SugarTestProductUtilities::removeAllCreatedProducts();

        parent::tearDown();
    }

    /**
     * Setting a relationship between ProductBundles and Quotes or
     * Products and ProductBundles results in a PHP fatal error
     *
     * @group 32064
     */
    public function testProductBundlesRelationsWithProductsAndQuotesSoapV4()
    {
        $this->soapURL = $GLOBALS['sugar_config']['site_url'] . '/service/v4_1/soap.php';
        $this->login();

        $this->soapClient->call(
            'set_relationship',
            [
                'session' => $this->sessionId,
                'module_name' => 'ProductBundles',
                'module_id' => $this->prodBundle->id,
                'link_field_name' => 'products',
                'related_ids' => [$this->product->id],
                'name_value_list' => [],
                'deleted' => 0,
            ]
        );

        $this->soapClient->call(
            'set_relationship',
            [
                'session' => $this->sessionId,
                'module_name' => 'ProductBundles',
                'module_id' => $this->prodBundle->id,
                'link_field_name' => 'quotes',
                'related_ids' => [$this->quote->id],
                'name_value_list' => [],
                'deleted' => 0,
            ]
        );

        $assertProductsRel = $this->soapClient->call(
            'get_relationships',
            [
                'session' => $this->sessionId,
                'module_name' => 'ProductBundles',
                'module_id' => $this->prodBundle->id,
                'link_field_name' => 'products',
                'related_module_query' => '',
                'related_fields' => ['id'],
                'related_module_link_name_to_fields_array' => [],
                'deleted' => 0,
            ]
        );

        $assertQuoteRel = $this->soapClient->call(
            'get_relationships',
            [
                'session' => $this->sessionId,
                'module_name' => 'ProductBundles',
                'module_id' => $this->prodBundle->id,
                'link_field_name' => 'quotes',
                'related_module_query' => '',
                'related_fields' => ['id'],
                'related_module_link_name_to_fields_array' => [],
                'deleted' => 0,
            ]
        );

        $this->assertEquals($this->product->id, $assertProductsRel['entry_list'][0]['id']);
        $this->assertEquals($this->quote->id, $assertQuoteRel['entry_list'][0]['id']);
    }
}
