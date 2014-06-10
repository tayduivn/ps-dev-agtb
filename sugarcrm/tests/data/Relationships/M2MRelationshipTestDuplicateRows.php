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

class M2MRelationshipTestDuplicateRows extends Sugar_PHPUnit_Framework_TestCase
{
    protected $def;
    protected $origDB;

    public function setUp()
        {

            $this->origDB = $GLOBALS['db'];
            $this->db = new SugarTestDatabaseMock();
            $this->db->setUp();
            $GLOBALS['db'] = $this->db;
            $this->def = array(
                'name' => "accounts_contacts",
                'table' => "accounts_contacts",
                'lhs_module' => 'Accounts',
                'lhs_table' => 'accounts',
                'lhs_key' => 'id',
                'rhs_module' => 'Contacts',
                'rhs_table' => 'contacts',
                'rhs_key' => 'id',
                'relationship_type' => 'many-to-many',
                'join_table' => 'accounts_contacts',
                'join_key_lhs' => 'account_id',
                'join_key_rhs' => 'contact_id',
                'primary_flag_column' => 'primary_account',
                'primary_flag_side' => 'rhs',
                'primary_flag_default' => true,
           );
        }

        public function tearDown()
        {
            $this->db->tearDown();
            $GLOBALS['db'] = $this->origDB;
        }

    /**
     * @dataProvider dupeRowProvider
     */
    public function testM2MDupeRowCheck($row, $accId, $conId, $expected)
    {
        $this->db->queries = array(
            "searchForExisting" => array(
                'match' => "/SELECT.*FROM.*accounts_contacts/i",
                'rows' => array($row)
            )
        );

        $m2mRelationship = new TestDuplicateM2MRel($this->def);
        $account = BeanFactory::getBean("Accounts");
        $account->id = $accId;
        $contact = BeanFactory::getBean("Contacts");
        $contact->id = $conId;

        $m2mRelationship->add($account, $contact);

        $this->assertEquals($expected, $m2mRelationship->addRowCalled);
    }

    public function dupeRowProvider() {
            return array(
                array(
                    array(
                        "id" => "12345",
                        "contact_id" => "contact_1",
                        "account_id" => "account_1",
                        "date_modified" => "2014-06-02 22:14:12",
                        "primary_account" => "1",
                        "deleted" => "0",
                    ),
                    "account_1",
                    "contact_1",
                    false,
                ),
                //Check deleted flag
                array(
                    array(
                        "id" => "1234",
                        "contact_id" => "contact_1",
                        "account_id" => "account_1",
                        "date_modified" => "2014-06-02 22:14:12",
                        "primary_account" => "1",
                        "deleted" => "1",
                    ),
                    "account_1",
                    "contact_1",
                    true,
                ),
                //Check for additional fields (primary_account here)
                array(
                    array(
                        "id" => "12345",
                        "contact_id" => "contact_1",
                        "account_id" => "account_1",
                        "date_modified" => "2014-06-02 22:14:12",
                        "primary_account" => "0",
                        "deleted" => "0",
                    ),
                    "account_1",
                    "contact_1",
                    true,
                ),
                //Check for new related ids
                array(
                    array(
                        "id" => "12345",
                        "contact_id" => "contact_1",
                        "account_id" => "account_2",
                        "date_modified" => "2014-06-02 22:14:12",
                        "primary_account" => "1",
                        "deleted" => "0",
                    ),
                    "account_1",
                    "contact_1",
                    true,
                ),
            );
        }
}

class TestDuplicateM2MRel extends M2MRelationship
{
    public $addRowCalled = false;

    protected function addRow(array $row)
    {
        $this->addRowCalled = true;
    }
}
