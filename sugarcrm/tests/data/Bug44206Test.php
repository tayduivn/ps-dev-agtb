<?php
//FILE SUGARCRM flav=pro ONLY
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

/**
 * @ticket 44206
 */
class Bug44206Test extends Sugar_PHPUnit_Framework_TestCase
{
    /**
     * Temporary opportunity
     *
     * @var Opportunity
     */
    protected $opportunity;

    /**
     * Sets up the fixture, for example, open a network connection.
     * This method is called before a test is executed.
     *
     * Creates a temporary opportunity
     */
    public function setUp()
    {
        $GLOBALS['current_user'] = SugarTestUserUtilities::createAnonymousUser();

        $this->opportunity = new Opportunity();
        $this->opportunity->currency_id = -99;
        $this->opportunity->save();
    }

    /**
     * Tears down the fixture, for example, close a network connection.
     * This method is called after a test is executed.
     *
     * Removes temporary opportunity
     */
    public function tearDown()
    {
        if (!empty($this->opportunity)) {
            $this->opportunity->mark_deleted($this->opportunity->id);
        }
        SugarTestUserUtilities::removeAllCreatedAnonymousUsers();
        unset($GLOBALS['current_user']);
    }

    /**
     * Tests that currency-related properties are filled in at model layer
     * even when opportunity currency is the default one.
     */
    public function testDefaultCurrencyFieldsArePopulated()
    {
        $opportunity = new Opportunity();

        // disable row level security just to simplify the test
        $opportunity->disable_row_level_security = true;
        $list = $opportunity->get_list('', $where = 'opportunities.id = ' . $GLOBALS['db']->quoted($this->opportunity->id));

        $this->assertTrue(is_array($list));
        $this->assertArrayHasKey('list', $list);
        $this->assertTrue(is_array($list['list']));
        $this->assertNotEmpty($list['list']);

        /** @var Opportunity $entry */
        $entry = array_pop($list['list']);
        $this->assertNotEmpty($entry->currency_name);
        $this->assertNotEmpty($entry->currency_symbol);
    }
}
