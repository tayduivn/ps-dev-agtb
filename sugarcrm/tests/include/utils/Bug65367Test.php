<?php
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

/**
 * @ticket 65367
 */
class Bug65367Test extends Sugar_PHPUnit_Framework_TestCase
{
    /** @var Account */
    private $account;
    /** @var Contact */
    private $contact1;
    /** @var Contact */
    private $contact2;

    protected function setUp()
    {
        parent::setUp();

        SugarTestHelper::setUp('current_user');
        SugarTestHelper::setUp('beanList');
        SugarTestHelper::setUp('beanFiles');

        $account = $this->account = SugarTestAccountUtilities::createAccount();
        $contact1 = $this->contact1 = SugarTestContactUtilities::createContact();
        $contact2 = $this->contact2 = SugarTestContactUtilities::createContact();

        // this trick is not related to the fix but is needed because of how Contact::$report_to_name is implemented
        $contact2->first_name = '';
        $contact2->save();

        $contact1->load_relationship('accounts');
        $contact1->accounts->add($account);

        $contact1->load_relationship('reports_to_link');
        $contact1->reports_to_link->add($contact2);
    }

    protected function tearDown()
    {
        SugarTestContactUtilities::removeAllCreatedContacts();
        SugarTestAccountUtilities::removeAllCreatedAccounts();
        SugarTestHelper::tearDown();
        parent::tearDown();
    }

    public function testAllTablesAreJoined()
    {
        $contact = BeanFactory::getBean('Contacts');
        $contact->retrieve($this->contact1->id);

        $searchFields = array(
            'account_name' => array(
                'value' => true,
            ),
            'report_to_name' => array(
                'value' => true,
            ),
        );

        $params = array(
            'select' => 'SELECT contacts.id',
            'from'   => 'FROM contacts',
            'where'  => 'WHERE'
                . ' account_name = ' . $contact->db->quoted($contact->account_name)
                . ' AND'
                . ' report_to_name = ' . $contact->db->quoted($contact->report_to_name),
        );

        $patch = create_export_query_relate_link_patch('Contacts', $searchFields, $params['where']);

        $query = implode(' ', array(
            $params['select'],
            $params['from'],
            $patch['join'],
            $patch['where'],
        ));

        $id = $contact->db->getOne($query);

        $this->assertEquals($contact->id, $id);
    }
}
