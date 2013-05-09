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

require_once 'include/database/DBManagerFactory.php';
require_once 'modules/Contacts/Contact.php';
require_once 'tests/include/database/TestBean.php';
require_once 'include/SugarQuery/SugarQuery.php';

class AdvancedQueryTest extends Sugar_PHPUnit_Framework_TestCase
{
    /**
     * @var DBManager
     */
    private $_db;
    protected $created = array();

    protected $backupGlobals = FALSE;

    protected $contacts = array();
    protected $accounts = array();

    static public function setupBeforeClass()
    {
        SugarTestHelper::setUp('app_strings');
        SugarTestHelper::setUp('current_user');
        SugarTestHelper::setUp('beanList');
        SugarTestHelper::setUp('beanFiles');
    }

    static public function tearDownAfterClass()
    {
        SugarTestHelper::tearDown();
    }

    public function setUp()
    {
        if(empty($this->_db)){
            $this->_db = DBManagerFactory::getInstance();
        }
    }

    public function tearDown()
    {
        if ( !empty($this->contacts) ) {
            $contactList = array();
            foreach ( $this->contacts as $contact ) {
                $contactList[] = $contact->id;
            }

            $this->_db->query("DELETE FROM contacts WHERE id IN ('".implode("','",$contactList)."')");
            $this->_db->query("DELETE FROM contacts_cstm WHERE id_c IN ('".implode("','",$contactList)."')");
        }
        if ( !empty($this->accounts) ) {
            $accountList = array();
            foreach ( $this->accounts as $account ) {
                $accountList[] = $account->id;
            }

            $this->_db->query("DELETE FROM accounts WHERE id IN ('".implode("','",$accountList)."')");
            $this->_db->query("DELETE FROM accounts_cstm WHERE id_c IN ('".implode("','",$accountList)."')");
        }

        if ( !empty($this->cases) ) {
            $casesList = array();
            foreach ( $this->cases as $case ) {
                $casesList[] = $case->id;
            }

            $this->_db->query("DELETE FROM cases WHERE id IN ('".implode("','",$casesList)."')");
            $this->_db->query("DELETE FROM cases_cstm WHERE id_c IN ('".implode("','",$casesList)."')");
        }

        if ( !empty($this->notes) ) {
            $notesList = array();
            foreach ( $this->notes as $note) {
                $notesList[] = $note->id;
            }

            $this->_db->query("DELETE FROM notes WHERE id IN ('".implode("','",$notesList)."')");
            $this->_db->query("DELETE FROM notes_cstm WHERE id_c IN ('".implode("','",$notesList)."')");
        }


    }

    public function testSelectInWhere()
    {

        $account = BeanFactory::newBean('Accounts');
        $account->name = 'Awesome';
        $account->save();

        // create a new contact
        $case = BeanFactory::newBean('Cases');
        $case->name = 'Test Case';
        $case->account_id = $account->id;
        $case->save();


        $this->accounts[] = $account;
        $this->cases[] = $case;

        $sqWhere = new SugarQuery();
        $sqWhere->select("id");
        $sqWhere->from(BeanFactory::newBean('Accounts'));
        $sqWhere->where()->equals('name','Awesome')->equals('id', $account->id);

        $sq = new SugarQuery();
        $sq->select(array("name"));
        $sq->from(BeanFactory::newBean('Cases'));
        $sq->where()->in('account_id', $sqWhere);


        $result = $sq->execute();


        // only 1 record
        $result = reset($result);

        $this->assertEquals($result['name'], 'Test Case', 'The name Did Not Match it was ' . $result['name']);

        $sqWhere = new SugarQuery();
        $sqWhere->select("id");
        $sqWhere->from(BeanFactory::newBean('Accounts'));
        $sqWhere->where()->equals('name','Awesome')->equals('id', $account->id);

        $sq = new SugarQuery();
        $sq->select(array("name"));
        $sq->from(BeanFactory::newBean('Cases'));
        $sq->where()->equals('account_id', $sqWhere);


        $result = $sq->execute();


        // only 1 record
        $result = reset($result);

        $this->assertEquals($result['name'], 'Test Case', 'The name Did Not Match it was ' . $result['name']);
    }

    public function testSelectUnion()
    {

        $account = BeanFactory::newBean('Accounts');
        $account->name = 'Awesome';
        $account->save();
        $account1 = $account->id;
        $this->accounts[] = $account;
        // create a new contact
        $account = BeanFactory::newBean('Accounts');
        $account->name = 'Not Awesome';
        $account->save();
        $account2 = $account->id;

        $this->accounts[] = $account;

        $sqUnion = new SugarQuery();
        $sqUnion->select(array("id", "name"));
        $sqUnion->from(BeanFactory::newBean('Accounts'));
        $sqUnion->where()->equals('name','Awesome');

        $sq = new SugarQuery();
        $sq->select(array("id", "name"));
        $sq->from(BeanFactory::newBean('Accounts'));
        $sq->where()->equals('name','Not Awesome');

        $sq->union($sqUnion);

        $result = $sq->execute();

        $this->assertEquals(2, count($result), "More than 2 rows were returned.");

    }

    public function testSelectNotes() {
        $account = BeanFactory::newBean('Accounts');
        $account->name = 'Awesome';
        $account->save();
        $account_id = $account->id;
        $this->accounts[] = $account;

        $note = BeanFactory::newBean('Notes');
        $note->name = 'Test note';
        $note->parent_type = 'Accounts';
        $note->parent_id = $account_id;
        $note->save();
        $this->notes[] = $note;

        $sq = new SugarQuery();
        $sq->select(array(array("accounts.name", "a_name"), array("notes.name", "n_name")));
        $sq->from($account);
        $sq->where()->equals("id",$account_id, $account);
        $sq->join('notes');

        $results = $sq->execute();

        $result = reset($results);

        $this->assertEquals('Test note', $result['n_name'], "The note name was: {$result['n_name']}");

    }

    //BEGIN SUGARCRM flav=pro ONLY
    public function testSelectFavorites() {
        $this->cases = array();
        for ( $i = 0 ; $i < 40 ; $i++ ) {
            $aCase = new aCase();
            $aCase->name = "UNIT TEST ".count($this->cases)." - ".create_guid();
            $aCase->billing_address_postalcode = sprintf("%08d",count($this->cases));
            if ( $i > 25 && $i < 36 ) {
                $aCase->assigned_user_id = $GLOBALS['current_user']->id;
            } else {
                // The rest are assigned to admin
                $aCase->assigned_user_id = '1';
            }
            $aCase->save();
            $this->cases[] = $aCase;
            if ( $i > 33 ) {
                // Favorite the last six
                $fav = new SugarFavorites();
                $fav->id = SugarFavorites::generateGUID('Cases',$aCase->id);
                $fav->new_with_id = true;
                $fav->module = 'Cases';
                $fav->record_id = $aCase->id;
                $fav->created_by = $GLOBALS['current_user']->id;
                $fav->assigned_user_id = $GLOBALS['current_user']->id;
                $fav->deleted = 0;
                $fav->save();
            }
        }

        $sq = new SugarQuery();
        $sq->select(array("id", "name"));
        $sq->from($aCase);

        $sf = new SugarFavorites();
        $sfAlias = $sf->addToSugarQuery($sq);

        $results = $sq->execute();

        $this->assertEquals('6', count($results), "The number of rows returned doesn't match the number of favorites created: " . count($results));

        foreach($results AS $case) {
            $fav = SugarFavorites::isUserFavorite('Cases',$case['id'],$GLOBALS['current_user']->id);
            $this->assertEquals($fav, true, "The record: {$case['id']} was not set as a favorite it is marked:" . var_export($fav, true));
        }

    }
    //END SUGARCRM flav=pro ONLY

    /**
     * @ticket 62961
     */
    public function testCustomFields()
    {
        $contact = new Contact_Mock_Bug62961();
        $fields = $contact->field_defs;
        $fields['fields']['bigname_c'] =
        array (
                'calculated' => 'true',
                'formula' => 'strToUpper($last_name)',
                'enforced' => 'true',
                'dependency' => '',
                'required' => false,
                'source' => 'custom_fields',
                'name' => 'bigname_c',
                'vname' => 'LBL_BIGNAME',
                'type' => 'varchar',
                'massupdate' => '0',
                'default' => NULL,
                'no_default' => false,
                'importable' => 'false',
                'duplicate_merge' => 'disabled',
                'audited' => false,
                'reportable' => true,
                'unified_search' => false,
                'merge_filter' => 'disabled',
                'len' => '255',
                'size' => '20',
                'id' => 'Contactsbigname_c',
                'custom_module' => 'Contacts',
        );
        $fields['fields']['report_to_bigname'] =
        array(
        'name' => 'report_to_bigname',
        'rname' => 'bigname_c',
        'id_name' => 'reports_to_id',
        'vname' => 'LBL_REPORTS_TO',
        'type' => 'relate',
        'link' => 'reports_to_link',
        'table' => 'contacts',
        'isnull' => 'true',
        'module' => 'Contacts',
        'dbType' => 'varchar',
        'len' => 'id',
        'reportable' => false,
        'source' => 'non-db',
        );
        unset($contact->field_defs);
        $contact->field_defs = $fields;

        $sq = new SugarQuery();
        $sq->select(array("id", "last_name", "bigname_c", "report_to_bigname"));
        $sq->from($contact);
        $sq->limit(0,1);

        $sql = $sq->compileSql();
        // ensure the query looks good
        $this->assertContains("contacts_cstm.bigname_c", $sql);
        $this->assertContains("_cstm.bigname_c AS report_to_bigname", $sql);
        $this->assertContains("LEFT JOIN contacts_cstm ON contacts_cstm.id_c = contacts.id", $sql);
        $this->assertRegExp('/LEFT JOIN contacts_cstm jt(\d+)_cstm ON \(jt\1_cstm.id_c = jt\1\.id\)/', $sql);
    }

}

class Contact_Mock_Bug62961 extends Contact
{
    public function hasCustomFields()
    {
        return true;
    }
}