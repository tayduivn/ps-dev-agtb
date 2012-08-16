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
require_once('install/install_utils.php');

class ForecastTreeSeedDataTest extends Sugar_PHPUnit_Framework_TestCase
{
	public static function setUpBeforeClass()
	{
        $GLOBALS['db']->query("DELETE FROM forecast_tree WHERE hierarchy_type in ('users','products')");
	}

	public function testCreateForecastUserSeedData()
	{
		require_once('install/seed_data/ForecastTreeSeedData.php');
        $forecastSeedData = new ForecastTreeSeedData();
        $forecastSeedData->populateUserSeedData();
        $results = $GLOBALS['db']->query("SELECT id, reports_to_id FROM users WHERE status = 'Active'");
        $users_data = array();

        while(($row = $GLOBALS['db']->fetchByAssoc($results)))
        {
            $users_data[$row['id']] = $row['reports_to_id'];
        }

        $tree_results = $GLOBALS['db']->query("SELECT user_id, parent_id FROM forecast_tree WHERE hierarchy_type = 'users'");
        $tree_data = array();
        while(($row = $GLOBALS['db']->fetchByAssoc($tree_results)))
        {
            $tree_data[$row['user_id']] = $row['parent_id'];
        }
        sort($users_data, SORT_STRING);
        sort($tree_data, SORT_STRING);

        //Assert that the users table entries match the users hierarchy_type entries in forecast_tree
        $this->assertEquals($users_data, $tree_data, 'Forecast tree data for users does not match report to structure of users table');

        $forecastSeedData->populateProductCategorySeedData();
        $product_categories_count = $GLOBALS['db']->getOne("SELECT count(id) AS total FROM product_categories");
        $product_templates_count = $GLOBALS['db']->getOne("SELECT count(id) AS total FROM product_templates");
        $tree_product_count = $GLOBALS['db']->getOne("SELECT count(id) AS total FROM forecast_tree WHERE hierarchy_type = 'products'");

        $this->assertEquals($product_categories_count + $product_templates_count, $tree_product_count, 'Forecast tree data for products does not match hierarchy structure of product_categories and product_templates table');
    }
}