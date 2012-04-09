<?php
/*********************************************************************************
 * The contents of this file are subject to the SugarCRM Master Subscription
 * Agreement ("License") which can be viewed at
 * http://www.sugarcrm.com/crm/en/msa/master_subscription_agreement_11_April_2011.pdf
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
 * by SugarCRM are Copyright (C) 2004-2011 SugarCRM, Inc.; All Rights Reserved.
 ********************************************************************************/

require_once 'include/EditView/EditView2.php';

/**
 * Bug #48570
 * Currency always default to US Dollars when you edit an opportunity
 *
 * @author mgusev@sugarcrm.com
 * @ticket 48570
 */
class Bug48570Test extends Sugar_PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        global $current_user;
        $current_user = SugarTestUserUtilities::createAnonymousUser();
	}

    public function tearDown()
    {
        SugarTestUserUtilities::removeAllCreatedAnonymousUsers();
	}


    /**
     * Create new currency
     * Create fake Opportunity with created currency
     * Try to get select for currency field and assert that new currency is selected
     * 
     * @return void
     * @group 48570
     */
    public function testCurrencySelect()
    {
        $currency = new Currency();
        $currency->iso4217 = 'EUR';
        $currency->name = 'Euro';
        $currency->symbol = 'E';
        $currency->conversion_rate = 1.5;
        $currency->status = 'Active';
        $currency->save();

        $focus = new Opportunity();
        $focus->id = __CLASS__;
        $focus->currency_id = $currency->id;
        $focus->team_id = '1';

        $editView = new EditView();
        $editView->showVCRControl = false;
        $editView->view = 'EditView';
        $editView->setup('Opportunities', $focus, 'modules/Opportunities/metadata/editviewdefs.php');
        $editView->process();

        $currency->mark_deleted($currency->id);

        $this->assertRegExp('/<option value="' . $focus->currency_id . '" selected>/sim', $editView->fieldDefs['currency_id']['value'], 'No selected option here');
    }
}