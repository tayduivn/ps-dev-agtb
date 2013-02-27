<?php
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

class Bug56652Test extends Sugar_PHPUnit_Framework_TestCase
{
    /** @var Contact */
    protected $contact;

    /**
     * Account names are randomly sorted in order to make sure that the data is
     * properly sorted by the application
     *
     * @var array
     */
    protected $account_names = array(
        'E', 'G', 'A', 'D', 'B', 'H', 'F', 'C'
    );

    /**
     * Sets up the fixture, for example, open a network connection.
     * This method is called before a test is executed.
     */
    public function setUp()
    {
        SugarTestHelper::setUp('beanFiles');
        SugarTestHelper::setUp('beanList');
        SugarTestHelper::setUp('current_user');

        parent::setUp();
        $this->contact = SugarTestContactUtilities::createContact();
        $this->contact->load_relationship('opportunities');
        foreach ($this->account_names as $account_name)
        {
            $account = SugarTestAccountUtilities::createAccount();
            $account->name = $account_name;
            $account->save(false);

            $opportunity = SugarTestOpportunityUtilities::createOpportunity(null, $account);
            $this->contact->opportunities->add($opportunity);
        }
    }

    /**
     * Tears down the fixture, for example, close a network connection.
     * This method is called after a test is executed.
     */
    public function tearDown()
    {
        SugarTestAccountUtilities::removeAllCreatedAccounts();
        SugarTestOpportunityUtilities::removeAllCreatedOpportunities();
        SugarTestContactUtilities::removeAllCreatedContacts();
        parent::tearDown();

        SugarTestHelper::tearDown();
    }

    /**
     * @param string $order
     * @param string $function
     * @dataProvider getOrders
     */
    public function testSubPanelDataIsSorted($order, $function)
    {
        // create a minimum required subpanel definition
        $subPanel = new aSubPanel(null, array(
            'module'            => 'Opportunities',
            'subpanel_name'     => null,
            'get_subpanel_data' => 'opportunities',
        ), $this->contact);

        // fetch subpanel data
        $response = SugarBean::get_union_related_list(
            $this->contact, 'account_name', $order, '', 0, -1, -1, 0, $subPanel
        );

        $this->assertArrayHasKey('list', $response);

        $account_names = array();

        /** @var Opportunity $opportunity */
        foreach ($response['list'] as $opportunity)
        {
            $account_names[] = $opportunity->account_name;
        }

        $sorted = $account_names;
        $function($sorted);

        // ensure that opportunities are sorted by account name in the needed order
        $this->assertSame($sorted, $account_names);
    }

    /**
     * @return array
     */
    public static function getOrders()
    {
        return array(
            array('asc',  'sort'),
            array('desc', 'rsort'),
        );
    }
}
