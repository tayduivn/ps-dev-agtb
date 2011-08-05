<?php
//FILE SUGARCRM flav=pro ONLY

require_once 'modules/ProductTypes/ProductType.php';

class SugarTestProductTypesUtilities
{
    protected static $_createdTypes = array();

    private function __construct() {}

    public static function createType($id = '', $name = "") 
    {
    	$type = new ProductType();
        $type->name = $name;
        if(!empty($id))
        {
            $type->new_with_id = true;
            $type->id = $id;
        }
        $type->save();
        self::$_createdTypes[] = $type;
        return $type;
    }

    public static function removeAllCreatedtypes() 
    {
        $type_ids = self::getCreatedTypeIds();
        $GLOBALS['db']->query('DELETE FROM product_types WHERE id IN (\'' . implode("', '", $type_ids) . '\')');
    }
        
    public static function getCreatedTypeIds() 
    {
        $type_ids = array();
        foreach (self::$_createdTypes as $type) {
            $type_ids[] = $type->id;
        }
        return $type_ids;
    }
}
?>