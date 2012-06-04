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
 
require_once 'modules/Products/Product.php';

class SugarTestProductUtilities
{
    protected static $_createdProducts = array();

    private function __construct() {}

    public static function createProduct($id = '') 
    {
        $time = mt_rand();
    	$name = 'SugarProduct';
    	$product = new Product();
        $product->name = $name . $time;
        $product->tax_class = 'Taxable';
        $product->cost_price = '100.00';
        $product->list_price = '100.00';
        $product->discount_price = '100.00';
        $product->quantity = '100';

        //BEGIN SUGARCRM flav=ent ONLY
        $product->best_case = '100.00';
        $product->likely_case = '80.00';
        $product->worst_case = '50.00';
        //END SUGARCRM flav=ent ONLY

        if(!empty($id))
        {
            $product->new_with_id = true;
            $product->id = $id;
        }
        $product->save();
        self::$_createdProducts[] = $product;
        return $product;
    }

    public static function setCreatedProduct($product_ids) {
    	foreach($product_ids as $product_id) {
    		$product = new Product();
    		$product->id = $product_id;
        	self::$_createdProducts[] = $product;
    	} // foreach
    } // fn
    
    public static function removeAllCreatedProducts() 
    {
        $product_ids = self::getCreatedProductIds();
        $GLOBALS['db']->query('DELETE FROM products WHERE id IN (\'' . implode("', '", $product_ids) . '\')');
    }
        
    public static function getCreatedProductIds() 
    {
        $product_ids = array();
        foreach (self::$_createdProducts as $product) {
            $product_ids[] = $product->id;
        }
        return $product_ids;
    }
}
?>