<?php
//FILE SUGARCRM flav=pro ONLY
require_once('include/nusoap/nusoap.php');
require_once 'tests/service/SOAPTestCase.php';

class SOAPAPI4Test extends SOAPTestCase
{
    /**
     * Create test user
     *
     */
	public function setUp()
    {
    	$this->_soapURL = $GLOBALS['sugar_config']['site_url'].'/service/v4/soap.php';

		parent::setUp();
    }
    
    public function tearDown()
    {
    	SugarTestContactUtilities::removeAllCreatedContacts();

		parent::tearDown();
    }
    
    public function testGetEntryList()
    {
        $contact = SugarTestContactUtilities::createContact();
        
        $this->_login();
        $result = $this->_soapClient->call(
            'get_entry_list',
            array(
                'session' => $this->_sessionId,
                'module_name' => 'Contacts',
                'query' => "contacts.id = '{$contact->id}'",
                'order_by' => '',
                'offset' => 0,
                'select_fields' => array('last_name', 'first_name', 'do_not_call', 'lead_source', 'email1'),
                'link_name_to_fields_array' => array(array('name' =>  'email_addresses', 'value' => array('id', 'email_address', 'opt_out', 'primary_address'))),
                'max_results' => 1,
                'deleted' => 0,
                )
            );		
		
        $this->assertEquals(
            $contact->email1,
            $result['relationship_list'][0]['link_list'][0]['records'][0]['link_value'][1]['value']
            );
    }

    public function testGetEntries()
    {
        $contact = SugarTestContactUtilities::createContact();
        
        $this->_login();
        $result = $this->_soapClient->call(
            'get_entries',
            array(
                'session' => $this->_sessionId,
                'module_name' => 'Contacts',
                'ids' => array($contact->id),
                'select_fields' => array('last_name', 'first_name', 'do_not_call', 'lead_source', 'email1'),
                'link_name_to_fields_array' => array(array('name' =>  'email_addresses', 'value' => array('id', 'email_address', 'opt_out', 'primary_address'))),
                )
            );		
		
        $this->assertEquals(
            $contact->email1,
            $result['relationship_list'][0]['link_list'][0]['records'][0]['link_value'][1]['value']
            );
    }
}
