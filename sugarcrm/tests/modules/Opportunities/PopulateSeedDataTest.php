<?php
//FILE SUGARCRM flav=ent ONLY

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
require_once('modules/Opportunities/OpportunitiesSeedData.php');
require_once('modules/Accounts/Account.php');
require_once('modules/Products/Product.php');
require_once('modules/TimePeriods/TimePeriod.php');
require_once('modules/Users/User.php');

class PopulateOppSeedDataTest extends Sugar_PHPUnit_Framework_TestCase
{

private $createdOpportunities;

function setUp()
{
    SugarTestHelper::setUp('beanFiles');
    SugarTestHelper::setUp('beanList');
    SugarTestHelper::setUp('app_list_strings');
    global $current_user;
    SugarTestHelper::setUp('current_user');
    $current_user->is_admin = 1;
    $current_user->save();
    $GLOBALS['db']->query("UPDATE opportunities SET deleted = 1");
}

function tearDown()
{
    SugarTestUserUtilities::removeAllCreatedAnonymousUsers();
    SugarTestAccountUtilities::removeAllCreatedAccounts();
    SugarTestProductUtilities::removeAllCreatedProducts();
    $GLOBALS['db']->query("UPDATE opportunities SET deleted = 0");
    $ids = "('" . implode("','", $this->createdOpportunities) . "')";
    $GLOBALS['db']->query("DELETE FROM opportunities WHERE id IN $ids");
    $GLOBALS['db']->query("DELETE FROM products WHERE opportunity_id IN $ids");
}

/**
 * @outputBuffering disabled
 */
function testPopulateSeedData()
{
    global $app_list_strings, $current_user;
    $total = 200;
    $account = BeanFactory::getBean('Accounts');
    $product = BeanFactory::getBean('Products');
    $user = new User();
    $account->disable_row_level_security = true;
    $product->disable_row_level_security = true;
    $user->disable_row_level_security = true;

    $accounts = $account->build_related_list("SELECT id FROM accounts WHERE deleted = 0", $account, 0, $total);

    //Accounts may have been deleted by some other tests
    if(count($accounts) < $total)
    {
       $count_accounts = count($accounts);
       while($count_accounts++ < $total) {
             $accounts[] = SugarTestAccountUtilities::createAccount();
       }
    }

    $products = $account->build_related_list("SELECT id FROM products WHERE deleted = 0", $product, 0, $total);

    if(count($products) < $total)
    {
       $count_products = count($products);
       while($count_accounts++ < $total) {
             $products[] = SugarTestProductUtilities::createProduct();
       }
    }

    //echo count($products);
    $result = $GLOBALS['db']->limitQuery("SELECT id FROM users WHERE deleted = 0 AND status = 'Active'", 0, $total);
    $users = array();
    while(($row = $GLOBALS['db']->fetchByAssoc($result)))
    {
        if($row['id'] != $current_user->id)
        {
            $users[$row['id']] = $row['id'];
        }
    }

    $this->createdOpportunities = OpportunitiesSeedData::populateSeedData($total, $app_list_strings, $accounts, $products, $users);
    $this->assertEquals(200, count($this->createdOpportunities));

}


}