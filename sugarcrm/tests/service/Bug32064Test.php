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

    private $_prodBundleId = null;
    private $_qouteId = null;
    private $_productId = null;

    public function setUp()
    {
        $this->_soapURL = $GLOBALS['sugar_config']['site_url'] . '/service/v4_1/soap.php';
        parent::setUp();

        // create anon user for login
        $this->_setupTestUser();
        $this->_login();

        $this->_prodBundleId = SugarTestProductBundleUtilities::createProductBundle()->id;
        $this->_qouteId = SugarTestQuoteUtilities::createQuote()->id;
        $this->_productId = SugarTestProductUtilities::createProduct()->id;
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
            'module_id' => $this->_prodBundleId,
            'link_field_name' => 'products',
            'related_ids' => array($this->_productId),
            'name_value_list' => array(),
            'deleted' => 0
                )
        );

        $this->_soapClient->call('set_relationship', array(
            'session' => $this->_sessionId,
            'module_name' => 'Quotes',
            'module_id' => $this->_qouteId,
            'link_field_name' => 'product_bundles',
            'related_ids' => array($this->_prodBundleId),
            'name_value_list' => array(),
            'deleted' => 0
                )
        );

        $assertProductsRel = $this->_soapClient->call('get_relationships', array(
            'session' => $this->_sessionId,
            'module_name' => 'ProductBundles',
            'module_id' => $this->_prodBundleId,
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
            'module_id' => $this->_qouteId,
            'link_field_name' => 'product_bundles',
            'related_module_query' => '',
            'related_fields' => array('id'),
            'related_module_link_name_to_fields_array' => array(),
            'deleted' => 0,
                )
        );

        $this->assertTrue(isset($assertProductsRel['entry_list'][0]['id']));
        $this->assertEquals($this->_productId, $assertProductsRel['entry_list'][0]['id']);

        $this->assertTrue(isset($assertProdBundleRel['entry_list'][0]['id']));
        $this->assertEquals($this->_prodBundleId, $assertProdBundleRel['entry_list'][0]['id']);
    }

}

