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

require_once 'include/SubPanel/SubPanelDefinitions.php';

/**
 * Bug #52361
 * Relate field data is not displayed in subpanel
 * part 2
 * @author arymarchik@sugarcrm.com
 * @ticked 52361
 */
class Bug52361p2Test extends Sugar_PHPUnit_Framework_TestCase
{
    /**
     * @var Quote
     */
    protected $_quote;

    /**
     * @var Contact
     */
    protected $_contact;

    /**
     * @var Account
     */
    protected $_account;

    public function setUp()
    {
        SugarTestHelper::setUp('current_user', array(true, 1));
        SugarTestHelper::setUp('app_list_strings');
        SugarTestHelper::setUp('beanList');
        SugarTestHelper::setUp('beanFiles');
        $user = $GLOBALS['current_user'];
        parent::setUp();

        $this->_quote = SugarTestQuoteUtilities::createQuote();

        $this->_account = SugarTestAccountUtilities::createAccount();
        $this->_quote->account_id = $this->_account->id;
        $this->_quote->shipping_account_id = $this->_account->id;
        $this->_quote->billing_account_id = $this->_account->id;

        $this->_contact = SugarTestContactUtilities::createContact();
        $this->_quote->shipping_contact_id = $this->_contact->id;
        $this->_quote->billing_contact_id = $this->_contact->id;

        $this->_quote->team_id = $user->team_id;
        $this->_quote->team_set_id = $user->team_set_id;
        $this->_quote->assigned_user_id = $user->id;
        $this->_quote->save();

        $bundle = SugarTestProductBundleUtilities::createProductBundle();
        $bundle->team_id = $this->_quote->team_id;
        $bundle->team_set_id = $this->_quote->team_set_id;
        $bundle->save();
        $bundle->set_productbundle_quote_relationship($this->_quote->id, $bundle->id);

        for($i = 0; $i < rand(2,5); $i++)
        {
            $product = SugarTestProductUtilities::createProduct();
            $product->team_id = $this->_quote->team_id;
            $product->team_set_id = $this->_quote->team_set_id;
            $product->quote_id = $this->_quote->id;
            $product->account_id = $this->_quote->billing_account_id;
            $product->contact_id = $this->_quote->billing_contact_id;
            $product->modified_user_id = $user->id;
            $product->created_by = $user->id;
            $product->assigned_user_id = $user->id;
            $product->save();
            $bundle->set_productbundle_product_relationship($product->id, 1, $bundle->id );
        }
    }

    public function tearDown()
    {
        foreach($this->_quote->get_product_bundles() as $bundle)
        {
            foreach($bundle->get_products() as $product)
            {
                $product->mark_deleted($product->id);
            }
        }
        $this->_quote->mark_deleted($this->_quote->id);
        $this->_contact->mark_deleted($this->_contact->id);
        $this->_account->mark_deleted($this->_account->id);
        parent::tearDown();
    }

    /**
     * Test product counts in product subpanel in contacts
     *
     * @group 52361
     * @return void
     */
    public function testContactSubPanel()
    {
        if(!file_exists('modules/Contacts/metadata/subpaneldefs.php'))
        {
            $this->markTestSkipped('Can\'t find subpanel definitions');
        }
        include_once 'modules/Contacts/metadata/subpaneldefs.php';
        if(!isset($layout_defs['Contacts']['subpanel_setup']['products']))
        {
            $this->markTestSkipped('Can\'t find subpanel definitions for products');
        }
        $sum_products = 0;
        foreach($this->_quote->get_product_bundles() as $bundle)
        {
            $sum_products += count($bundle->get_products());
        }
        $panel = new aSubPanel('', $layout_defs['Contacts']['subpanel_setup']['products'], $this->_contact, true);
        $response = SugarBean::get_union_related_list($this->_contact, '', '', '', 0, $sum_products * 2, $sum_products * 2, '',$panel);
        $this->assertEquals($sum_products, $response['row_count']);
        $this->assertEquals($sum_products, count($response['list']));

        $this->_quote->quote_stage = (rand(0,1) == 1) ? 'Closed Lost' : 'Closed Dead';
        $this->_quote->save();
        $response = SugarBean::get_union_related_list($this->_contact, '', '', '', 0, $sum_products * 2, $sum_products * 2, '',$panel);
        $this->assertEquals(0, $response['row_count']);
        $this->assertEquals(0, count($response['list']));
    }

    /**
     * Test product counts in product subpanel in account
     *
     * @group 52361
     * @return void
     */
    public function testAccountSubPanel()
    {
        if(!file_exists('modules/Accounts/metadata/subpaneldefs.php'))
        {
            $this->markTestSkipped('Can\'t find subpanel definitions');
        }
        include_once 'modules/Accounts/metadata/subpaneldefs.php';
        if(!isset($layout_defs['Accounts']['subpanel_setup']['products']))
        {
            $this->markTestSkipped('Can\'t find subpanel definitions for account');
        }
        $sum_products = 0;
        foreach($this->_quote->get_product_bundles() as $bundle)
        {
            $sum_products += count($bundle->get_products());
        }
        $panel = new aSubPanel('', $layout_defs['Accounts']['subpanel_setup']['products'], $this->_account, true);
        $response = SugarBean::get_union_related_list($this->_account, '', '', '', 0, $sum_products * 2, $sum_products * 2, '',$panel);
        $this->assertEquals($sum_products, $response['row_count']);
        $this->assertEquals($sum_products, count($response['list']));

        $this->_quote->quote_stage = (rand(0,1) == 1) ? 'Closed Lost' : 'Closed Dead';
        $this->_quote->save();
        $response = SugarBean::get_union_related_list($this->_account, '', '', '', 0, $sum_products * 2, $sum_products * 2, '',$panel);
        $this->assertEquals(0, $response['row_count']);
        $this->assertEquals(0, count($response['list']));
    }
}