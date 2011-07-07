<?php
//FILE SUGARCRM flav=pro ONLY
require_once 'modules/Products/Product.php';

class SugarTestProductUtilities
{
    private static $_createdProducts = array();

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