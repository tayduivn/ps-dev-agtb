<?PHP
/*********************************************************************************
 * The contents of this file are subject to the SugarCRM Enterprise Subscription
 * Agreement ("License") which can be viewed at
 * http://www.sugarcrm.com/crm/products/sugar-enterprise-eula.html
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
 * by SugarCRM are Copyright (C) 2004-2010 SugarCRM, Inc.; All Rights Reserved.
 ********************************************************************************/
/**
 * THIS CLASS IS FOR DEVELOPERS TO MAKE CUSTOMIZATIONS IN
 */
require_once('modules/DiscountCodes/DiscountCodes_sugar.php');
class DiscountCodes extends DiscountCodes_sugar {
	
	function DiscountCodes(){	
		parent::DiscountCodes_sugar();
	}

/**
 * @author Jim Bartek
 * @project moofcart
 * @tasknum 4
 * This function is the for the product_category drop-down.  It is called in custom/modules/DiscountCodes/view/view.edit.php and view.detail.php
 */

        function getProductCategories() {
                $db = &DBManagerFactory::getInstance();
                $product_categories = array();
		// get the product categories
                $result = $db->query("SELECT id,name FROM product_categories WHERE deleted = 0");
		// set the first one to blank
                $product_categories[''] = '';
		while($row = $db->fetchByAssoc($result)) {
                        $product_categories[ $row['id'] ] = $row[ 'name' ];
                }

		// return the properly formatted product categories array
                return $product_categories;
        }

	
}
?>
