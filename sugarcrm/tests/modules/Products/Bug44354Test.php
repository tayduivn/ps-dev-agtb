<?php
//FILE SUGARCRM flav=pro ONLY
/*********************************************************************************
 *The contents of this file are subject to the SugarCRM Professional End User License Agreement 
 *("License") which can be viewed at http://www.sugarcrm.com/EULA.  
 *By installing or using this file, You have unconditionally agreed to the terms and conditions of the License, and You may 
 *not use this file except in compliance with the License. Under the terms of the license, You 
 *shall not, among other things: 1) sublicense, resell, rent, lease, redistribute, assign or 
 *otherwise transfer Your rights to the Software, and 2) use the Software for timesharing or 
 *service bureau purposes such as hosting the Software for commercial gain and/or for the benefit 
 *of a third party.  Use of the Software may be subject to applicable fees and any use of the 
 *Software without first paying applicable fees is strictly prohibited.  You do not have the 
 *right to remove SugarCRM copyrights from the source code or user interface. 
 * All copies of the Covered Code must include on each user interface screen:
 * (i) the "Powered by SugarCRM" logo and 
 * (ii) the SugarCRM copyright notice 
 * in the same form as they appear in the distribution.  See full license for requirements.
 *Your Warranty, Limitations of liability and Indemnity are expressly stated in the License.  Please refer 
 *to the License for the specific language governing these rights and limitations under the License.
 *Portions created by SugarCRM are Copyright (C) 2004 SugarCRM, Inc.; All Rights Reserved.  
 ********************************************************************************/


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