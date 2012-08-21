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

require_once('include/SubPanel/SubPanelDefinitions.php');
require_once('include/SubPanel/SubPanel.php');

/**
 * Bug #54419
 *
 *
 * @author mgusev@sugarcrm.com
 * @ticked 54419
 */
class Bug54419Test extends Sugar_PHPUnit_Framework_OutputTestCase
{
    /**
     * @var Account
     */
    protected $accountShipping = null;

    /**
     * @var Account
     */
    protected $accountBilling = null;

    /**
     * @var Quote
     */
    protected $quote = null;

    public function setUp()
    {
        SugarTestHelper::setUp('beanFiles');
        SugarTestHelper::setUp('beanList');
        SugarTestHelper::setUp('current_user', array(true, 1));
        SugarTestHelper::setUp('app_list_strings');
        SugarTestHelper::setUp('app_strings');

        parent::setUp();

        $this->accountShipping = SugarTestAccountUtilities::createAccount();
        $this->accountShipping->name = __CLASS__ . 'shipping';
        $this->accountShipping->save();

        $this->accountBilling = SugarTestAccountUtilities::createAccount();
        $this->accountBilling->name = __CLASS__ . 'billing';
        $this->accountBilling->save();

        $this->quote = SugarTestQuoteUtilities::createQuote();
        $this->quote->billing_account_id = $this->accountBilling->id;
        $this->quote->billing_account_name = $this->accountBilling->name;
        $this->quote->shipping_account_id = $this->accountShipping->id;
        $this->quote->shipping_account_name = $this->accountShipping->name;
        $this->quote->save();
    }

    public function tearDown()
    {
        // Restoring $GLOBALS
        parent::tearDown();
        $_REQUEST = array();
        unset($_SERVER['REQUEST_METHOD']);
        unset($GLOBALS['currentModule']);

        // Removing temp data
        SugarTestQuoteUtilities::removeAllCreatedQuotes();
        SugarTestAccountUtilities::removeAllCreatedAccounts();
        SugarTestHelper::tearDown();
    }

    /**
     * Test tries to assert that quote is present in shipping account
     *
     * @group 54419
     * @return void
     */
    public function testShippingAccount()
    {
        $this->quote->shipping_account_id = $this->accountShipping->id;
        $this->quote->shipping_account_name = $this->accountShipping->name;
        $this->quote->save();

        // Getting data of subpanel
        $_REQUEST['module'] = 'Accounts';
        $_REQUEST['action'] = 'DetailView';
        $_REQUEST['record'] = $this->accountShipping->id;
        $_SERVER['REQUEST_METHOD'] = 'GET';
        $GLOBALS['currentModule'] = 'Accounts';
        unset($GLOBALS['focus']);
        $subpanels = new SubPanelDefinitions($this->accountShipping, 'Accounts');
        $subpanelDef = $subpanels->load_subpanel('quotes');
        $subpanel = new SubPanel('Accounts', $this->accountShipping->id, 'quotes', $subpanelDef, 'Accounts');
        $subpanel->setTemplateFile('include/SubPanel/SubPanelDynamic.html');
        $subpanel->display();
        $actual = $this->getActualOutput();

        $this->assertContains($this->quote->name, $actual, 'Quote name is not displayed in subpanel');
    }


    /**
     * Test tries to assert that pagination is correct if billing & shipping accounts are the same
     *
     * @group 51043
     * @return void
     */
    public function testDoublePagination()
    {
        $this->quote->shipping_account_id = $this->accountBilling->id;
        $this->quote->shipping_account_name = $this->accountBilling->name;
        $this->quote->save();

        // Getting data of subpanel
        $_REQUEST['module'] = 'Accounts';
        $_REQUEST['action'] = 'DetailView';
        $_REQUEST['record'] = $this->accountBilling->id;
        $_SERVER['REQUEST_METHOD'] = 'GET';
        $GLOBALS['currentModule'] = 'Accounts';
        unset($GLOBALS['focus']);
        $subpanels = new SubPanelDefinitions($this->accountBilling, 'Accounts');
        $subpanelDef = $subpanels->load_subpanel('quotes');
        $subpanel = new SubPanel('Accounts', $this->accountBilling->id, 'quotes', $subpanelDef, 'Accounts');
        $subpanel->setTemplateFile('include/SubPanel/SubPanelDynamic.html');
        $subpanel->display();
        $actual = $this->getActualOutput();

        $this->assertContains('(1 - 1 of 1)', $actual, 'Number of quotes is incorrect in subpanel');
    }
}
