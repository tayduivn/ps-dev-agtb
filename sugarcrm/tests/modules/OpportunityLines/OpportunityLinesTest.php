<?php
//FILE SUGARCRM flav=ent ONLY
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

require_once('modules/OpportunityLines/OpportunityLine.php');

class OpportunityLinesTest  extends Sugar_PHPUnit_Framework_TestCase {

    public function setUp()
    {
        global $current_user, $beanFiles, $beanList;
        include('include/modules.php');
        $current_user = SugarTestUserUtilities::createAnonymousUser();
    }

    public function tearDown()
    {
        SugarTestUserUtilities::removeAllCreatedAnonymousUsers();
        SugarTestOppLineItemUtilities::removeAllCreatedLines();
        SugarTestProductUtilities::removeAllCreatedProducts();
        SugarTestProductCategoryUtilities::removeAllCreatedProductCategories();
        unset($GLOBALS['current_user']);
    }

    public function testGetExperts()
    {
        $this->markTestIncomplete("Not fully implemented yet.");
        $category = SugarTestProductCategoryUtilities::createProductCategory();
        $user_1 = SugarTestUserUtilities::createAnonymousUser();
        $category->assigned_user_id = $user_1->id;
        $category->save();

        $sub_category = SugarTestProductCategoryUtilities::createProductCategory();
        $user_2 = SugarTestUserUtilities::createAnonymousUser();
        $sub_category->assigned_user_id = $user_2->id;
        $sub_category->parent_id = $category->id;
        $sub_category->save();

        $sub_sub_category = SugarTestProductCategoryUtilities::createProductCategory();
        $user_3 = SugarTestUserUtilities::createAnonymousUser();
        $sub_sub_category->assigned_user_id = $user_3->id;
        $sub_sub_category->parent_id = $sub_category->id;
        $sub_sub_category->save();

        $product = SugarTestProductUtilities::createProduct();
        $product->category_id = $sub_sub_category->id;
        $product->save();

        $opp_line = SugarTestOppLineItemUtilities::createLine();
        $opp_line->product_id = $product->id;
        $opp_line->save();

        $opp_line->getExperts();
        $this->assertContains($user_1->id, $opp_line->experts);
        $this->assertContains($user_2->id, $opp_line->experts);
        $this->assertContains($user_3->id, $opp_line->experts);
    }


    public function testGetProductOwners()
    {
        $this->markTestIncomplete("Not fully implemented yet.");
        $product_category = SugarTestProductCategoryUtilities::createProductCategory();
        $product_category->assigned_user_id = $GLOBALS['current_user']->id;
        $product_category->save();

        $product = SugarTestProductUtilities::createProduct();
        $product->category_id = $product_category->id;
        $product->save();

        $opp_line = SugarTestOppLineItemUtilities::createLine();
        $opp_line->product_id = $product->id;
        $opp_line->save();
        $opp_line->getProductOwners();
        $this->assertEquals($GLOBALS['current_user']->id, $opp_line->product_owner_id);
    }

}