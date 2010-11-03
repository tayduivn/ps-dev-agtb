<?php
if (!defined('sugarEntry') || !sugarEntry) die('Not A Valid Entry Point');

require_once('include/MVC/View/views/view.edit.php');

/**
 * @author Jim Bartek
 * @project moofcart
 * @tasknum 4
 * Custom edit view to populate the product categories drop down
 */


class DiscountCodesViewEdit extends ViewEdit {
    function DiscountCodesViewEdit() {
        parent::ViewEdit();
    }

    function display() {
        global $current_user;
	global $app_list_strings;
	require_once("modules/DiscountCodes/DiscountCodes.php");
	// instantiate the discount code object
	$discount_code_bean = new DiscountCodes();
	// populate the dropdown list product_category_list, which was created blank, with the correct categories
	$app_list_strings['product_category_c_list'] = $discount_code_bean->getProductCategories();

	$app_list_strings['discount_when_product_cat_list'] = $discount_code_bean->getProductCategories();

        parent::display();
    }

}
