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

class SimpleQueryTest extends Sugar_PHPUnit_Framework_TestCase
{
    /**
     * @var DBManager
     */
    private $_db;
    protected $created = array();

    protected $backupGlobals = FALSE;

    protected $contacts = array();
    protected $accounts = array();
    protected $notes = array();

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

        if ( !empty($this->notes) ) {
            $notesList = array();
            foreach ( $this->notes as $note ) {
                $notesList[] = $note->id;
            }

            $this->_db->query("DELETE FROM notes WHERE id IN ('".implode("','",$notesList)."')");
            $this->_db->query("DELETE FROM notes_cstm WHERE id_c IN ('".implode("','",$notesList)."')");
        }        

    }

    public function testSelect()
    {
    	// create a new contact
    	$contact = BeanFactory::getBean('Contacts');
    	$contact->first_name = 'Test';
    	$contact->last_name = 'McTester';
    	$contact->save();
        $this->contacts[] = $contact;
    	$id = $contact->id;
    	// don't need the contact bean anymore, get rid of it
    	unset($contact);
    	// get the new contact
    	
    	$sq = new SugarQuery();
    	$sq->select(array("first_name","last_name"));
    	$sq->from(BeanFactory::getBean('Contacts'));
    	$sq->where()->equals("id",$id);
        
    	$result = $sq->execute();
    	// only 1 record
    	$result = reset($result);

    	$this->assertEquals($result['first_name'], 'Test', 'The First Name Did Not Match');
    	$this->assertEquals($result['last_name'], 'McTester', 'The Last Name Did Not Match');


    }


    public function testSelectWithJoin()
    {
        // create a new contact
        $contact = BeanFactory::getBean('Contacts');
        $contact->first_name = 'Test';
        $contact->last_name = 'McTester';
        $contact->save();
        $contact_id = $contact->id;


        $account = BeanFactory::getBean('Accounts');
        $account->name = 'Awesome';
        $account->save();
        
        $account->load_relationship('contacts');
        $account->contacts->add($contact->id);

        $this->accounts[] = $account;
        $this->contacts[] = $contact;

        // don't need the contact bean anymore, get rid of it
        unset($contact);
        unset($account);
        // get the new contact



        $sq = new SugarQuery();
        $sq->select(array("first_name","last_name", array("accounts.name", 'aname')));
        $sq->from(BeanFactory::getBean('Contacts'));
        $sq->join('accounts');
        $sq->where()->equals("id",$contact_id);

        $result = $sq->execute();
        // only 1 record
        $result = reset($result);

        $this->assertEquals($result['first_name'], 'Test', 'The First Name Did Not Match');
        $this->assertEquals($result['last_name'], 'McTester', 'The Last Name Did Not Match');
        $this->assertEquals($result['aname'], 'Awesome', 'The Account Name Did Not Match');
    }

    public function testSelectWithJoinToSelf()
    {

        $account = BeanFactory::getBean('Accounts');
        $account->name = 'Awesome';
        $account->save();
        $account_id = $account->id;

        $account2 = BeanFactory::getBean('Accounts');
        $account2->name = 'Awesome 2';
        $account2->save();
        
        $account->load_relationship('members');
        $account->members->add($account2->id);

        $this->accounts[] = $account;
        $this->accounts[] = $account2;

        // don't need the accounts beans anymore, get rid of'em
        unset($account2);
        unset($account);
        


        // lets try a query
        $sq = new SugarQuery();
        $sq->select(array(array("accounts.name", 'aname')));
        $sq->from(BeanFactory::getBean('Accounts'));
        $sq->join('members');
        $sq->where()->equals("id",$account_id);
        
        $result = $sq->execute();
        // only 1 record
        $result = reset($result);

        $this->assertEquals('Awesome', $result['aname'], "Account doesn't match");

    }

    public function testSelectManyToMany()
    {
        global $current_user;

        $current_user->load_relationship('email_addresses');

        $email_address = BeanFactory::getBean('EmailAddresses');
        $email_address->email_address = 'test@test.com';
        $email_address->deleted = 0;
        $email_address->save();

        $current_user->email_addresses->add($email_address->id, array('deleted' => 0));


        // lets try a query
        $sq = new SugarQuery();
        $sq->select(array(array("users.first_name", 'fname')));
        $sq->from(BeanFactory::getBean('Users'));
        $sq->join('email_addresses');
        $sq->where()->starts("email_addresses.email_address","test");
        $sq->where()->equals('users.id', $current_user->id);

        $result = $sq->execute();
        $result = reset($result);
        $this->assertEquals($current_user->first_name, $result['fname'], "Wrong Email Address Result Returned");

    }

    public function testSelectOneToManyWithRole()
    {
        $account = BeanFactory::getBean('Accounts');
        $account->name = 'Test Account';
        $account->save();
        $account_id = $account->id;

        // create a new note
        $note = BeanFactory::getBean('Notes');
        $note->name = 'Test Note';
        $note->parent_type = 'Accounts';
        $note->parent_id = $account_id;
        $note->save();
        $note_id = $note->id;

        $this->accounts[] = $account;
        $this->notes[] = $note;

        // don't need the contact bean anymore, get rid of it
        unset($note);
        unset($account);
        // get the new contact
        $sq = new SugarQuery();
        $sq->select(array('accounts.name', 'accounts.id'));
        $sq->from(BeanFactory::getBean('Notes'));
        $sq->join('accounts');
        $sq->where()->equals("id",$note_id);

        $result = $sq->execute();
        // only 1 record
        $result = reset($result);

        $this->assertEquals($result['name'], 'Test Account', 'The Name Did Not Match');
        $this->assertEquals($result['id'], $account_id, 'The ID Did Not Match');

    }    

}
