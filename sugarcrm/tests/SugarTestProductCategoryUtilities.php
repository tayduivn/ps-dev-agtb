<?php
//FILE SUGARCRM flav=pro ONLY
/*********************************************************************************
 * The contents of this file are subject to the SugarCRM Master Subscription
 * Agreement ("License") which can be viewed at
 * http://www.sugarcrm.com/crm/master-subscription-agreement
 * By installing or using this file, You have unconditionally agreed to the
 * terms and conditions of the License, and You may not use this file except in
 * compliance with the License.  Under the terms of the license, You shall not,
 * among other things: 1) sublicense, resell, rent, lease, redistribute, assign
 * or otherwise transfer Your rights to the Software, and 2) use the Software
 * for timesharing or service bureau purposes such as hosting the Software for
 * commercial gain and/or for the benefit of a third party.  Use of the Software
 * may be subject to applicable fees and any use of the Software without first
 * paying applicable fees is strictly prohibited.  You do not have the right to
 * remove SugarCRM copyrights from the source code or user interface.
 *
 * All copies of the Covered Code must include on each user interface screen:
 *  (i) the "Powered by SugarCRM" logo and
 *  (ii) the SugarCRM copyright notice
 * in the same form as they appear in the distribution.  See full license for
 * requirements.
 *
 * Your Warranty, Limitations of liability and Indemnity are expressly stated
 * in the License.  Please refer to the License for the specific language
 * governing these rights and limitations under the License.  Portions created
 * by SugarCRM are Copyright (C) 2004-2012 SugarCRM, Inc.; All Rights Reserved.
 ********************************************************************************/


require_once 'modules/ProductCategories/ProductCategory.php';

class SugarTestProductCategoryUtilities
{
    protected static $_createdProductCategories = array();

    private function __construct() {}

    public static function createProductCategory($id = '') 
    {
        $time = mt_rand();
        $name = 'SugarProductCategory';
        $product_category = new ProductCategory();
        $product_category->name = $name . $time;
        if(!empty($id))
        {
            $product_category->new_with_id = true;
            $product_category->id = $id;
        }
        $product_category->save();
        self::$_createdProductCategories[] = $product_category;
        return $product_category;
    }

    public static function setCreatedProductCategory($product_category_ids)
    {
        foreach($product_category_ids as $product_category_id)
        {
            $product_category_id = new ProductCategory();
            $product_category->id = $product_category_id;
            self::$_createdProductCategories[] = $product_category;
        }
    }

    public static function removeAllCreatedProductCategories() 
    {
        $product_category_ids = self::getCreatedProductCategoryIds();
        $GLOBALS['db']->query('DELETE FROM product_categories WHERE id IN (\'' . implode("', '", $product_category_ids) . '\')');
    }

    public static function getCreatedProductCategoryIds() 
    {
        $product_category_ids = array();
        foreach (self::$_createdProductCategories as $product_category)
        {
            $product_category_ids[] = $product_category->id;
        }
        return $product_category_ids;
    }
}