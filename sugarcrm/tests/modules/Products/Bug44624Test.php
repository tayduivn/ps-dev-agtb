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


class Bug44624Test extends Sugar_PHPUnit_Framework_TestCase
{

	private $_product;

	public function setUp() 
	{
		global $current_user;
	    $current_user = SugarTestUserUtilities::createAnonymousUser();
        $current_user->is_admin = 1;
        $current_user->setPreference('dec_sep', '.', 0, 'global');
        $current_user->setPreference('num_grp_sep', ',', 0, 'global');

		require('include/modules.php');
		$GLOBALS['beanList'] = $beanList;
		$GLOBALS['beanFiles'] = $beanFiles;

	    $GLOBALS['module'] = "Products";
		SugarTestProductTypesUtilities::createType(false, '1');
		$this->_product = SugarTestProductUtilitiesWithTypes2::createProduct("1");
        $this->_product->disable_row_level_security = true;
        //Clear out the products_audit table
        $GLOBALS['db']->query("DELETE FROM products_audit WHERE parent_id = '{$this->_product->id}'");
        //$this->useOutputBuffering = false;

	}

	public function tearDown()
	{
		$GLOBALS['db']->query("DELETE FROM products_audit WHERE parent_id = '{$this->_product->id}'");
		SugarTestProductUtilitiesWithTypes2::removeAllCreatedProducts();
		SugarTestProductTypesUtilities::removeAllCreatedtypes();
        SugarTestUserUtilities::removeAllCreatedAnonymousUsers();
        unset($this->_product);
	}

    public function testProductListPriceChanges() {
        $this->_product->list_price = 0;
        $this->_product->save();
        $this->_product->retrieve();

        $this->_product->list_price = 0.00;
        $this->_product->save(); 
        $this->_product->retrieve();

        $this->_product->list_price = "";
        $this->_product->save(); 
        $this->_product->retrieve();

        $id = $this->_product->id;
        $query = "SELECT * from products_audit where parent_id='{$id}'";
        $result = $GLOBALS['db']->query($query);

        $list_of_changes = array();

          while ($row = $GLOBALS['db']->fetchByAssoc($result)) {
              $list_of_changes[] = 'a' . $row['created_by'] . ' = ' . $row["field_name"] . ',' . $row['before_value_string'] . ',' . $row['after_value_string'];

          }

        //echo var_export($list_of_changes, true);
        // list of audited changes should be empty
        $this->assertEmpty($list_of_changes);
    }


    public function testProductCostPriceChanges() {

        $this->_product->cost_price = 1;    // original cost price is 1
        $this->_product->save();
        $this->_product->retrieve();

        $this->_product->cost_price = 1.00;
        $this->_product->save(); 
        $this->_product->retrieve();

        $this->_product->cost_price = "1";
        $this->_product->save(); 
        $this->_product->retrieve();

        $id = $this->_product->id;
        $query = "SELECT * from products_audit where parent_id='$id'";
        $result = $GLOBALS['db']->query($query);

        $list_of_changes = array();

          while ($row = $GLOBALS['db']->fetchByAssoc($result)) {
              $list_of_changes[] = 'b' . $row['created_by'] . ' = ' . $row["field_name"] . ',' . $row['before_value_string'] . ',' . $row['after_value_string'];

          }

        //echo var_export($list_of_changes, true);
        
        // list of audited changes should be empty
        $this->assertEmpty($list_of_changes);
    }

    public function testProductDiscountPriceChanges() {

 

          $this->_product->discount_price = 3.33;    // original cost price is 3.33
          $this->_product->save();
          $this->_product->retrieve();

          $this->_product->discount_price = "3.33";
          $this->_product->save();
          $this->_product->retrieve();

          $id = $this->_product->id;
          $query = "SELECT * from products_audit where parent_id='$id'";
          $result = $GLOBALS['db']->query($query);

          $list_of_changes = array();

          while ($row = $GLOBALS['db']->fetchByAssoc($result)) {
              $list_of_changes[] = 'c' . $row['created_by'] . ' = ' . $row["field_name"] . ',' . $row['before_value_string'] . ',' . $row['after_value_string'];

          }

          //echo var_export($list_of_changes, true);
          //list of audited changes should be empty
          $this->assertEmpty($list_of_changes);


      }


}
 
/**
 * Create a products with type 
 * @author alex
 *
 */
class SugarTestProductUtilitiesWithTypes2 extends SugarTestProductUtilities 
{

	/**
	 * Get type id by type name
	 * @param string $typeName type name
	 */
	public static function getTypeId($typeName)
	{
		static $typesList;

		if (!$typesList)
		{
			$productType = new ProductType();
			$typesList = $productType->get_product_types();
		}

		return array_search($typeName, $typesList);
	}

	/**
	 *
	 * Create a product
	 * @param string $typeName type of created product will be
	 * @param int $id id of created product
	 */
	public static function createProduct($typeName, $id = '', $prodName="SugarProduct")
	{
		$time = mt_rand();
		$name = $prodName;
		$product = new Product();
		$product->name = $name . $time;
		$product->tax_class = 'Taxable';
		$product->cost_price = 1;
		$product->list_price = 0;
		$product->discount_price = 3.33;
		$product->quantity = '100';
        $product->status = 'Ship';
		$product->type_id = self::getTypeId($typeName);
		if(!empty($id))
		{
			$product->new_with_id = true;
			$product->id = $id;
		}
		$product->save();
        $product->retrieve();
		self::$_createdProducts[] = $product;
		return $product;
	}
}


