<?php
if (!defined('sugarEntry') || !sugarEntry) die('Not A Valid Entry Point');

require_once('include/MVC/View/views/view.detail.php');

/**
 * @author Jim Bartek
 * @project moofcart
 * @tasknum 4
 * Custom detail view to populate the correct product categories
 */


class DiscountCodesViewDetail extends ViewDetail {
    function DiscountCodesViewDetail() {
        parent::ViewDetail();
    }

    function display() {
        global $current_user;
	global $app_list_strings;
        require_once("modules/DiscountCodes/DiscountCodes.php"); 
        // instantiate the object
	$discount_code_bean = new DiscountCodes();
	// overwrite the existing drop down list (which is empty) with the db values
        $app_list_strings['product_category_c_list'] = $discount_code_bean->getProductCategories();

	$app_list_strings['discount_when_product_cat_list'] = $discount_code_bean->getProductCategories();
        
	parent::display();
    }

}
