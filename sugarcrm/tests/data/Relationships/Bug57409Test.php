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

/**
 * Bug #57409
 * It takes 1.4 min to load Contact record edit view
 *
 * @author mgusev@sugarcrm.com
 * @ticked 57409
 */
class Bug57409Test extends Sugar_PHPUnit_Framework_TestCase
{
    /**
     * @var Contact
     */
    protected $contact = null;

    public function setUp()
    {
        SugarTestHelper::setUp('beanFiles');
        SugarTestHelper::setUp('beanList');
        SugarTestHelper::setUp('current_user');

        SugarTestOpportunityUtilities::createOpportunity();
        $opp1 = SugarTestOpportunityUtilities::createOpportunity();

        $this->contact = SugarTestContactUtilities::createContact();
        $this->contact->load_relationship('opportunities');
        $this->contact->opportunities->add($opp1->id);
    }

    public function tearDown()
    {
        SugarTestOpportunityUtilities::removeAllCreatedOpportunities();
        SugarTestContactUtilities::removeAllCreatedContacts();

        SugarTestHelper::tearDown();
    }

    /**
     * Test asserts that query returns correct number of records
     *
     * @group 57409
     * @return void
     */
    public function testGetQuery()
    {
        $query = $this->contact->opportunities->relationship->getQuery($this->contact->opportunities, array(
            'enforce_teams' => true
        ));

        $actual = 0;
        $result = $GLOBALS['db']->query($query);
        while ($GLOBALS['db']->fetchByAssoc($result, FALSE)) {
            $actual++;
        }

        $this->assertEquals(1, $actual, 'Number of fetched opportunities is incorrect');
    }
}