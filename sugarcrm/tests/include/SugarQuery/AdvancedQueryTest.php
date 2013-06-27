<?php
/*********************************************************************************
 * By installing or using this file, you are confirming on behalf of the entity
 * subscribed to the SugarCRM Inc. product ("Company") that Company is bound by
 * the SugarCRM Inc. Master Subscription Agreement (“MSA”), which is viewable at:
 * http://www.sugarcrm.com/master-subscription-agreement
 *
 * If Company is not bound by the MSA, then by installing or using this file
 * you are agreeing unconditionally that Company will be bound by the MSA and
 * certifying that you have authority to bind Company accordingly.
 *
 * Copyright (C) 2004-2013 SugarCRM Inc.  All rights reserved.
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
        BeanFactory::setBeanClass('Contacts');

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
        BeanFactory::setBeanClass('Contacts', 'Contact_Mock_Bug62961');
        $contact = BeanFactory::getBean("Contacts");
        $this->assertArrayHasKey("report_to_bigname", $contact->field_defs);
        $this->assertTrue($contact->hasCustomFields());

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

    /**
     * test conditions on related variables
     */
    public function testRelateConditions()
    {
        $contact = BeanFactory::getBean("Contacts");
        // regular query
        $sq = new SugarQuery();
        $sq->select(array("id", "last_name"));
        $sq->from($contact);
        $sq->where()->equals('first_name','Awesome');
        $this->assertRegExp('/WHERE.*contacts\.first_name\s*=\s*\'Awesome\'/',$sq->compileSql());

        $sq = new SugarQuery();
        $sq->select(array("id", "last_name"));
        $sq->from($contact);
        $sq->where()->equals('contacts.last_name','Awesome');
        $this->assertRegExp('/WHERE.*contacts\.last_name\s*=\s*\'Awesome\'/',$sq->compileSql());

        // with related in name
        $sq = new SugarQuery();
        $sq->select(array("id", "last_name", "account_name"));
        $sq->from($contact);
        $sq->where()->equals('account_name','Awesome');
        $this->assertRegExp('/WHERE.*accounts\.name\s*=\s*\'Awesome\'/',$sq->compileSql());

        // without related in name
        $sq = new SugarQuery();
        $sq->select(array("id", "last_name"));
        $sq->from($contact);
        $sq->where()->equals('account_name','Awesome');
        $this->assertRegExp('/WHERE.*accounts\.name\s*=\s*\'Awesome\'/',$sq->compileSql());

        // self-link
        $acc = BeanFactory::getBean('Accounts');
        $sq = new SugarQuery();
        $sq->select(array("id", "name"));
        $sq->from($acc);
        $sq->where()->equals('parent_name','Awesome');
        $this->assertRegExp('/WHERE.*jt\d+\.name\s*=\s*\'Awesome\'/',$sq->compileSql());

        // custom field
        BeanFactory::setBeanClass('Contacts', 'Contact_Mock_Bug62961');
        $contact = BeanFactory::getBean("Contacts");
        $this->assertArrayHasKey("report_to_bigname", $contact->field_defs);
        $this->assertTrue($contact->hasCustomFields());

        // direct custom field
        $sq = new SugarQuery();
        $sq->select(array("id", "last_name"));
        $sq->from($contact);
        $sq->where()->equals('bigname_c','Chuck Norris');
        $this->assertRegExp('/WHERE.*contacts_cstm\.bigname_c\s*=\s*\'Chuck Norris\'/',$sq->compileSql());

        // related custom field
        $sq = new SugarQuery();
        $sq->select(array("id", "last_name"));
        $sq->from($contact);
        $sq->where()->equals('report_to_bigname','Chuck Norris');
        $this->assertRegExp('/WHERE.*jt\d+_cstm\.bigname_c\s*=\s*\'Chuck Norris\'/',$sq->compileSql());

        // compare fields
        $sq = new SugarQuery();
        $sq->select(array("id", "last_name"));
        $sq->from($contact);
        $sq->where()->equalsField('bigname_c','report_to_bigname');
        $this->assertRegExp('/WHERE.*contacts_cstm.bigname_c\s*=\s*jt\d+_cstm.bigname_c/',$sq->compileSql());

        $sq = new SugarQuery();
        $sq->select(array("id", "last_name", 'report_to_bigname'));
        $sq->from($contact);
        $sq->where()->notEqualsField('bigname_c','report_to_bigname');
        $this->assertRegExp('/WHERE.*contacts_cstm.bigname_c\s*!=\s*jt\d+_cstm.bigname_c/',$sq->compileSql());
    }

    /**
     * Test bad conditions
     * @expectedException SugarApiExceptionInvalidParameter
     */
    public function testBadRelateConditions()
    {
        $contact = BeanFactory::getBean("Contacts");
        // will throw because name is composite
        $sq = new SugarQuery();
        $sq->select(array("id", "last_name", "account_name"));
        $sq->from($contact);
        $sq->where()->equals('report_to_name','Awesome');
        $sql = $sq->compileSql();
        $this->fail("Exception expected!");

    }

    public function testRelatedOrderBy()
    {
        BeanFactory::setBeanClass('Contacts', 'Contact_Mock_Bug62961');
        $contact = BeanFactory::getBean("Contacts");
        $this->assertArrayHasKey("report_to_bigname", $contact->field_defs);
        $this->assertTrue($contact->hasCustomFields());

        // by related field
        $sq = new SugarQuery();
        $sq->select(array("id", "last_name"));
        $sq->from($contact);
        $sq->orderBy("account_name");
        $this->assertRegExp('/ORDER BY\s+accounts.name DESC/',$sq->compileSql());

        // by custom field too
        $sq = new SugarQuery();
        $sq->select(array("id", "last_name"));
        $sq->from($contact);
        $sq->orderBy("account_name")->orderBy("bigname_c", "ASC");
        $this->assertRegExp('/ORDER BY\s+accounts.name DESC\s*,\s*contacts_cstm.last_name ASC/',$sq->compileSql());

        // by related custom field
        $sq = new SugarQuery();
        $sq->select(array("id", "last_name"));
        $sq->from($contact);
        $sq->orderBy("report_to_bigname");
        $this->assertRegExp('/ORDER BY\s+jt\d+_cstm.last_name DESC/',$sq->compileSql());

        // skip bad one
        $sq = new SugarQuery();
        $sq->select(array("id", "last_name"));
        $sq->from($contact);
        $sq->orderBy("report_to_name")->orderBy("account_name", "asc");
        $this->assertRegExp('/ORDER BY\s+accounts.name asc/',$sq->compileSql());
    }

}

class Contact_Mock_Bug62961 extends Contact
{
    public function __construct()
    {
        parent::__construct();
        $this->field_defs['bigname_c'] =
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
                'custom_module' => 'Contacts',
                'sort_on' => 'last_name',
        );
        $this->field_defs['report_to_bigname'] =
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

    }

    public function hasCustomFields()
    {
        return true;
    }
}