<?php
/*
 * Your installation or use of this SugarCRM file is subject to the applicable
 * terms available at
 * http://support.sugarcrm.com/Resources/Master_Subscription_Agreements/.
 * If you do not agree to all of the applicable terms or do not have the
 * authority to bind the entity as an authorized representative, then do not
 * install or use this SugarCRM file.
 *
 * Copyright (C) SugarCRM Inc. All rights reserved.
 */

/**
 * This class is meant to test everything SOAP
 */
class SOAPAPI1Test extends SOAPTestCase
{
    private $contact;
    private $meeting;

    /**
     * Create test user
     */
    protected function setUp() : void
    {
        $this->soapURL = $GLOBALS['sugar_config']['site_url'].'/soap.php';
        parent::setUp();
        $this->login(); // Logging in just before the SOAP call as this will also commit any pending DB changes
        $this->setupTestContact();
        $this->meeting = SugarTestMeetingUtilities::createMeeting();
    }

    /**
     * Remove anything that was used during this test
     */
    protected function tearDown() : void
    {
        SugarTestContactUtilities::removeCreatedContactsUsersRelationships();
        $this->contact = null;
        SugarTestMeetingUtilities::removeAllCreatedMeetings();
        SugarTestMeetingUtilities::removeMeetingContacts();
        $this->meeting = null;
        parent::tearDown();
    }

    /**
     * Ensure we can create a session on the server.
     */
    public function testCanLogin()
    {
        $result = $this->login();
        $this->assertTrue(
            !empty($result['id']) && $result['id'] != -1,
            'SOAP Session not created. Error ('.$result['error']['number'].'): '.$result['error']['name'].': '.$result['error']['description'].'. HTTP Response: '.$this->soapClient->response
        );
    }

    public function testSearchContactByEmail()
    {
        $result = $this->soapClient->call('contact_by_email', ['user_name' => 'admin', 'password' => md5('asdf'), 'email_address' => $this->contact->email1]);
        $this->assertTrue(!empty($result) && count($result) > 0, 'Incorrect number of results returned. HTTP Response: '.$this->soapClient->response);
        $this->assertEquals($result[0]['name1'], $this->contact->first_name, 'Incorrect result found');
    }

    public function testSearchByModule()
    {
        $modules = ['Contacts'];
        $result = $this->soapClient->call('search_by_module', ['user_name' => 'admin', 'password' => md5('asdf'), 'search_string' => $this->contact->email1, 'modules' => $modules, 'offset' => 0, 'max_results' => 10]);
        $this->assertTrue(!empty($result) && count($result['entry_list']) > 0, 'Incorrect number of results returned. HTTP Response: '.$this->soapClient->response);
        $this->assertEquals('first_name', $result['entry_list'][0]['name_value_list'][1]['name'], 'Incorrect field returned');
        $this->assertEquals($this->contact->first_name, $result['entry_list'][0]['name_value_list'][1]['value'], 'Incorrect result returned');
    }

    public function testGetModifiedEntries()
    {
        $ids = [$this->contact->id];
        $result = $this->soapClient->call('get_modified_entries', ['session' => $this->sessionId, 'module_name' => 'Contacts', 'ids' => $ids, 'select_fields' => []]);
        $decoded = base64_decode($result['result']);
        $decoded = simplexml_load_string($decoded);
        $this->assertEquals($this->contact->id, $decoded->item->id, 'Incorrect entry returned.');
    }

    public function testGetAttendeeList()
    {
        $this->meeting->load_relationship('contacts');
        $this->meeting->contacts->add($this->contact->id);
        $GLOBALS['db']->commit();
        $result = $this->soapClient->call('get_attendee_list', ['session' => $this->sessionId, 'module_name' => 'Meetings', 'id' => $this->meeting->id]);
        $decoded = base64_decode($result['result']);
        $decoded = simplexml_load_string($decoded);
        $this->assertTrue(!empty($result['result']), 'Results not returned. HTTP Response: '.$this->soapClient->response);
        $this->assertEquals(urldecode($decoded->attendee->first_name), $this->contact->first_name, 'Incorrect Result returned expected: '.$this->contact->first_name.' Found: '.urldecode($decoded->attendee->first_name));
    }

    public function testSyncGetModifiedRelationships()
    {
        $ids = [$this->contact->id];
        $yesterday = date('Y-m-d', strtotime('last year'));
        $tomorrow = date('Y-m-d', mktime(0, 0, 0, date("m"), date("d") + 1, date("Y")));
        $result = $this->soapClient->call('sync_get_modified_relationships', ['session' => $this->sessionId, 'module_name' => 'Users', 'related_module' => 'Contacts', 'from_date' => $yesterday, 'to_date' => $tomorrow, 'offset' => 0, 'max_results' => 10, 'deleted' => 0, 'module_id' => $GLOBALS['current_user']->id, 'select_fields'=> [], 'ids' => $ids, 'relationship_name' => 'contacts_users', 'deletion_date' => $yesterday, 'php_serialize' => 0]);
        $this->assertTrue(!empty($result['entry_list']), 'Results not returned. HTTP Response: '.$this->soapClient->response);
        $decoded = base64_decode($result['entry_list']);
        $decoded = simplexml_load_string($decoded);
        if (isset($decoded->item[0])) {
            $this->assertEquals(urlencode($decoded->item->name_value_list->name_value[1]->name), 'contact_id', "testSyncGetModifiedRelationships - could not retrieve contact_id column name");
            $this->assertEquals(urlencode($decoded->item->name_value_list->name_value[1]->value), $this->contact->id, "vlue of contact id is not same as returned via SOAP");
        }
    }

    /**********************************
     * HELPER PUBLIC FUNCTIONS
     **********************************/
    private function setupTestContact()
    {
        $this->contact = SugarTestContactUtilities::createContact();
        $this->contact->contacts_users_id = $GLOBALS['current_user']->id;
        $this->contact->save();
        $GLOBALS['db']->commit(); // Making sure these changes are committed to the database
    }
}
