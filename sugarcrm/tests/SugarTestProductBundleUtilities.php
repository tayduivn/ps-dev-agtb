<?php
//FILE SUGARCRM flav=pro ONLY
require_once 'modules/ProductBundles/ProductBundle.php';

class SugarTestProductBundleUtilities
{
    private static $_createdProductBundles = array();

    private function __construct() {}

    public static function createProductBundle($id = '') 
    {
        $time = mt_rand();
    	$name = 'SugarProductBundle';
    	$productbundle = new ProductBundle();
        $productbundle->name = $name . $time;
        $productbundle->bundle_stage = 'draft';
        if(!empty($id))
        {
            $productbundle->new_with_id = true;
            $productbundle->id = $id;
        }
        $productbundle->save();
        self::$_createdProductBundles[] = $productbundle;
        return $productbundle;
    }

    public static function setCreatedProductBundle($productbundle_ids) {
    	foreach($productbundle_ids as $productbundle_id) {
    		$productbundle = new ProductBundle();
    		$productbundle->id = $productbundle_id;
        	self::$_createdProductBundles[] = $productbundle;
    	} // foreach
    } // fn
    
    public static function removeAllCreatedProductBundles() 
    {
        $productbundle_ids = self::getCreatedProductBundleIds();
        $GLOBALS['db']->query('DELETE FROM product_bundles WHERE id IN (\'' . implode("', '", $productbundle_ids) . '\')');
    }
        
    public static function getCreatedProductBundleIds() 
    {
        $productbundle_ids = array();
        foreach (self::$_createdProductBundles as $productbundle) {
            $productbundle_ids[] = $productbundle->id;
        }
        return $productbundle_ids;
    }
}
?>