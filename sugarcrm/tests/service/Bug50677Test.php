<?php
//FILE SUGARCRM flav=pro ONLY
/*********************************************************************************
 * The contents of this file are subject to the SugarCRM Professional End User
 * License Agreement ("License") which can be viewed at
 * http://www.sugarcrm.com/EULA.  By installing or using this file, You have
 * unconditionally agreed to the terms and conditions of the License, and You may
 * not use this file except in compliance with the License. Under the terms of the
 * license, You shall not, among other things: 1) sublicense, resell, rent, lease,
 * redistribute, assign or otherwise transfer Your rights to the Software, and 2)
 * use the Software for timesharing or service bureau purposes such as hosting the
 * Software for commercial gain and/or for the benefit of a third party.  Use of
 * the Software may be subject to applicable fees and any use of the Software
 * without first paying applicable fees is strictly prohibited.  You do not have
 * the right to remove SugarCRM copyrights from the source code or user interface.
 * All copies of the Covered Code must include on each user interface screen:
 * (i) the "Powered by SugarCRM" logo and (ii) the SugarCRM copyright notice
 * in the same form as they appear in the distribution.  See full license for
 * requirements.  Your Warranty, Limitations of liability and Indemnity are
 * expressly stated in the License.  Please refer to the License for the specific
 * language governing these rights and limitations under the License.
 * Portions created by SugarCRM are Copyright (C) 2004 SugarCRM, Inc.;
 * All Rights Reserved.
 ********************************************************************************/


/**
 * Bug50677Test
 *
 * This test is to make sure that you can add a relationship between Product Bundles and Products via the standard
 * set_relationship method and include in the extra field.
 *
 * @author Jon Whitcraft
 *
 */

require_once 'tests/service/SOAPTestCase.php';

class Bug50677Test extends SOAPTestCase
{
    /**
     * @var Product
     */
    protected $_product;

    /**
     * @var ProductBundle
     */
    protected $_product_bundle;

    /**
     * setUp
     * Override the setup from SoapTestCase to also create the seed search data for Accounts and Contacts.
     */
    public function setUp()
    {
        $this->_soapURL = $GLOBALS['sugar_config']['site_url'].'/service/v3_1/soap.php';
   		parent::setUp();
        $this->_login(); // Logging in just before the SOAP call as this will also commit any pending DB changes

        $this->_product = SugarTestProductUtilities::createProduct();
        $this->_product_bundle = SugarTestProductBundleUtilities::createProductBundle();
        $GLOBALS['db']->commit();
    }

    public function tearDown()
    {
        $GLOBALS['db']->query("DELETE FROM product_bundle_product WHERE bundle_id = '{$this->_product_bundle->id}'");

        SugarTestProductUtilities::removeAllCreatedProducts();
        SugarTestProductBundleUtilities::removeAllCreatedProductBundles();
        parent::tearDown();
    }

    public function testSetRelationshipProductBundleProduct()
    {
        $result = $this->_soapClient->call('set_relationship', array(
            'session' => $this->_sessionId,
            'module_name' => 'ProductBundles',
            'module_id' => $this->_product_bundle->id,
            'link_field_name' => 'products',
            'related_ids' => $this->_product->id,
            'name_value_list' => array(
                array('name' => 'product_index', 'value' => 1)
                ),
            'deleted' => 0
            )
        );
        $this->assertEquals(1, $result['created'], "Failed To Create Product Bundle -> Product Relationship");

        // lets make sure the row is correct since it was created
        // it should have a product_index of 1.
        $db = $GLOBALS['db'];
        $sql = "SELECT id, product_index FROM product_bundle_product WHERE bundle_id = '" . $db->quote($this->_product_bundle->id) . "'
                AND product_id = '" . $db->quote($this->_product->id) . "'";
        $result = $db->query($sql);
        $row = $db->fetchByAssoc($result);

        $this->assertTrue(is_guid($row['id']));
        $this->assertEquals(1, $row['product_index']);

    }
}
