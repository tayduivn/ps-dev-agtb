<?php

/* * *******************************************************************************
 * The contents of this file are subject to the SugarCRM Master Subscription
 * Agreement ("License") which can be viewed at
 * http://www.sugarcrm.com/crm/master-subscription-agreement
 * By installing or using this file, You have unconditionally agreed to the
 * terms and conditions of the License, and You may not use this file except in
 * compliance with the License. Under the terms of the license, You shall not,
 * among other things: 1) sublicense, resell, rent, lease, redistribute, assign
 * or otherwise transfer Your rights to the Software, and 2) use the Software
 * for timesharing or service bureau purposes such as hosting the Software for
 * commercial gain and/or for the benefit of a third party. Use of the Software
 * may be subject to applicable fees and any use of the Software without first
 * paying applicable fees is strictly prohibited. You do not have the right to
 * remove SugarCRM copyrights from the source code or user interface.
 *
 * All copies of the Covered Code must include on each user interface screen:
 * (i) the "Powered by SugarCRM" logo and
 * (ii) the SugarCRM copyright notice
 * in the same form as they appear in the distribution. See full license for
 * requirements.
 *
 * Your Warranty, Limitations of liability and Indemnity are expressly stated
 * in the License. Please refer to the License for the specific language
 * governing these rights and limitations under the License. Portions created
 * by SugarCRM are Copyright (C) 2004-2012 SugarCRM, Inc.; All Rights Reserved.
 * ****************************************************************************** */


require_once('tests/service/SOAPTestCase.php');

require_once('tests/SugarTestProductBundleUtilities.php');
require_once('tests/SugarTestQuoteUtilities.php');
require_once('tests/SugarTestProductUtilities.php');

/**
 * Bug #32064
 * Setting a relationship between ProductBundles and Quotes or Products and ProductBundles results in a PHP fatal error
 *
 * @ticket 32064
 */
class Bug32064Test extends SOAPTestCase
{

    private $_prodBundle = null;
    private $_prodBundleForQuoteCase = null;
    private $_quote = null;
    private $_product = null;

    public function setUp()
    {
        $this->_soapURL = $GLOBALS['sugar_config']['site_url'] . '/service/v4_1/soap.php';
        parent::setUp();

        // create anon user for login
        $this->_setupTestUser();
        $this->_login();

        $this->_prodBundle = SugarTestProductBundleUtilities::createProductBundle();
        $this->_prodBundleForQuoteCase = SugarTestProductBundleUtilities::createProductBundle();
        $this->_quote = SugarTestQuoteUtilities::createQuote();
        $this->_product = SugarTestProductUtilities::createProduct();

        // Commit setUp records for DB2.
        $GLOBALS['db']->commit();
    }

    public function tearDown()
    {
        $this->_tearDownTestUser();

        SugarTestProductBundleUtilities::removeAllCreatedProductBundles();
        SugarTestQuoteUtilities::removeAllCreatedQuotes();
        SugarTestProductUtilities::removeAllCreatedProducts();

        parent::tearDown();
    }

    /**
     * Setting a relationship between ProductBundles and Quotes or Products and ProductBundles results in a PHP fatal error
     *
     * @group 32064
     */
    public function testProductBundlesRelationsWithProductsAndQuotes()
    {

        $this->_soapClient->call('set_relationship', array(
            'session' => $this->_sessionId,
            'module_name' => 'ProductBundles',
            'module_id' => $this->_prodBundle->id,
            'link_field_name' => 'products',
            'related_ids' => array($this->_product->id),
            'name_value_list' => array(),
            'deleted' => 0
                )
        );

        $this->_soapClient->call('set_relationship', array(
            'session' => $this->_sessionId,
            'module_name' => 'Quotes',
            'module_id' => $this->_quote->id,
            'link_field_name' => 'product_bundles',
            'related_ids' => array($this->_prodBundle->id),
            'name_value_list' => array(),
            'deleted' => 0
                )
        );

        $this->_soapClient->call('set_relationship', array(
            'session' => $this->_sessionId,
            'module_name' => 'Quotes',
            'module_id' => $this->_quote->id,
            'link_field_name' => 'product_bundles',
            'related_ids' => array($this->_prodBundleForQuoteCase->id),
            'name_value_list' => array(),
            'deleted' => 0
                )
        );

        $assertProductsRel = $this->_soapClient->call('get_relationships', array(
            'session' => $this->_sessionId,
            'module_name' => 'ProductBundles',
            'module_id' => $this->_prodBundle->id,
            'link_field_name' => 'products',
            'related_module_query' => '',
            'related_fields' => array('id'),
            'related_module_link_name_to_fields_array' => array(),
            'deleted' => 0,
                )
        );

        $assertProdBundleRel = $this->_soapClient->call('get_relationships', array(
            'session' => $this->_sessionId,
            'module_name' => 'Quotes',
            'module_id' => $this->_quote->id,
            'link_field_name' => 'product_bundles',
            'related_module_query' => '',
            'related_fields' => array('id'),
            'related_module_link_name_to_fields_array' => array(),
            'deleted' => 0,
                )
        );

        $this->assertEquals($this->_product->id, $assertProductsRel['entry_list'][0]['id']);

        $expectedIds = array($assertProdBundleRel['entry_list'][0]['id'], $assertProdBundleRel['entry_list'][1]['id']);
        $this->assertContains($this->_prodBundle->id, $expectedIds);
        $this->assertContains($this->_prodBundleForQuoteCase->id, $expectedIds);

        // can't find norman interface.
        $db = $this->_quote->db;
        $rows = array();
        $sql = "SELECT bundle_id, bundle_index FROM product_bundle_quote
        WHERE bundle_id IN ({$db->quoted($this->_prodBundle->id)}, {$db->quoted($this->_prodBundleForQuoteCase->id)})
        AND quote_id = {$db->quoted($this->_quote->id)} ORDER BY bundle_index DESC";
        $result = $db->query($sql);

        // = fetchAll
        while ($rows[] = $db->fetchByAssoc($result));

        $this->assertGreaterThan($rows[1]['bundle_index'], $rows[0]['bundle_index']);
    }

}

