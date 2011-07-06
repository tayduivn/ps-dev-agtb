<?php
//FILE SUGARCRM flav=pro ONLY
/**
 * Bug44354Test.php
 * 
 * This is a simple test to check the category_name vardefs.php entry for the Products module.
 * There's some special handling here with the category field since the category_id field uses
 * a function override for display in the EdtiView, DetailView & QuickCreate.  We need to hide
 * visibility for the category_name since placing it in EditView, DetailView or QuickCreate layout will
 * cause confusion.  The category_name field will not work in EditView and QuickCreate because the
 * value will be set from the category_id field when calling the function.  However, the category_name
 * field needs to be available for search and listview layouts since id fields are currently not allowed
 * to be added (see ListLayoutMetaDataParser.php isValidField method).
 * 
 * @author clee
 *
 */
class Bug44354Test extends Sugar_PHPUnit_Framework_TestCase
{

	public function testCategoryNameFieldVisibility() 
	{
    	require_once('include/SugarObjects/VardefManager.php');
        VardefManager::loadVardef('Products', 'Product', true);
        require('cache/modules/Products/Productvardefs.php');	
        $categoryName = $GLOBALS['dictionary']['Product']['fields']['category_name'];
        $this->assertNotEmpty($categoryName['studio']);
        $this->assertTrue(is_array($categoryName), $categoryName['studio']);
        $this->assertFalse($categoryName['studio']['detailview']);
        $this->assertFalse($categoryName['studio']['editview']);
        $this->assertFalse($categoryName['studio']['quickcreate']);
	}
	

}