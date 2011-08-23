<?php
//FILE SUGARCRM flav=pro ONLY
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

require_once('include/nusoap/nusoap.php');
require_once 'tests/service/SOAPTestCase.php';
require_once('tests/service/APIv3Helper.php');


class SOAPAPI4Test extends SOAPTestCase
{
    private static $helperObject;
    private $cleanup;

    /**
     * Create test user
     *
     */
	public function setUp()
    {
    	$this->_soapURL = $GLOBALS['sugar_config']['site_url'].'/service/v4/soap.php';
		parent::setUp();
		self::$helperObject = new APIv3Helper();
        $this->_login();
        $this->cleanup = false;
    }

    public function tearDown()
    {
        if(!empty($this->cleanup)) {
            $GLOBALS['db']->query("DELETE FROM accounts WHERE name like 'UNIT TEST%' ");
            $GLOBALS['db']->query("DELETE FROM opportunities WHERE name like 'UNIT TEST%' ");
            $GLOBALS['db']->query("DELETE FROM contacts WHERE first_name like 'UNIT TEST%' ");
        }
        parent::tearDown();
    }

    public function testGetEntryList()
    {
        $contact = SugarTestContactUtilities::createContact();

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
                'favorites' => false,
                )
            );

        $this->assertEquals(
            $contact->email1,
            $result['relationship_list'][0]['link_list'][0]['records'][0]['link_value'][1]['value']
            );
    }


    public function testGetEntryListWithFavorites()
    {
        $contact = SugarTestContactUtilities::createContact();
        $sf = new SugarFavorites();
        $sf->module = 'Contacts';
        $sf->record_id = $contact->id;
        $sf->save(FALSE);
        $GLOBALS['db']->commit();

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
                'favorites' => true,
                )
            );

        $this->assertEquals(
            $contact->email1,
            $result['relationship_list'][0]['link_list'][0]['records'][0]['link_value'][1]['value']
            );
    }


    public function testSearchByModule()
    {

        $seedData = self::$helperObject->populateSeedDataForSearchTest($GLOBALS['current_user']->id);
        $this->cleanup = true;
        $returnFields = array('name','id','deleted');
        $searchModules = array('Accounts','Contacts','Opportunities');
        $searchString = "UNIT TEST";
        $offSet = 0;
        $maxResults = 10;

        $results = $this->_soapClient->call('search_by_module',
                        array(
                            'session' => $this->_sessionId,
                            'search'  => $searchString,
                            'modules' => $searchModules,
                            'offset'  => $offSet,
                            'max'     => $maxResults,
                            'user'    => $GLOBALS['current_user']->id,
                            'fields'  => $returnFields,
                            'unified_only' => TRUE,
                            'favorites' => FALSE)
                        );
        $this->assertEquals($seedData[0]['fieldValue'], self::$helperObject->findFieldByNameFromEntryList($results['entry_list'],$seedData[0]['id'],'Accounts', $seedData[0]['fieldName']));
        $this->assertFalse(self::$helperObject->findFieldByNameFromEntryList($results['entry_list'],$seedData[1]['id'],'Accounts', $seedData[1]['fieldName']));
        $this->assertEquals($seedData[2]['fieldValue'], self::$helperObject->findFieldByNameFromEntryList($results['entry_list'],$seedData[2]['id'],'Contacts', $seedData[2]['fieldName']));
        $this->assertEquals($seedData[3]['fieldValue'], self::$helperObject->findFieldByNameFromEntryList($results['entry_list'],$seedData[3]['id'],'Opportunities', $seedData[3]['fieldName']));
        $this->assertFalse(self::$helperObject->findFieldByNameFromEntryList($results['entry_list'],$seedData[4]['id'],'Opportunities', $seedData[4]['fieldName']));
    }

    public function testSearchByModuleWithFavorites()
    {

        $seedData = self::$helperObject->populateSeedDataForSearchTest($GLOBALS['current_user']->id);
        $this->cleanup = true;
        $sf = new SugarFavorites();
        $sf->module = 'Accounts';
        $sf->record_id = $seedData[0]['id'];
        $sf->save(FALSE);

        $sf = new SugarFavorites();
        $sf->module = 'Contacts';
        $sf->record_id = $seedData[2]['id'];
        $sf->save(FALSE);

        $returnFields = array('name','id','deleted');
        $searchModules = array('Accounts','Contacts','Opportunities');
        $searchString = "UNIT TEST";
        $offSet = 0;
        $maxResults = 10;

        $results = $this->_soapClient->call('search_by_module',
                        array(
                            'session' => $this->_sessionId,
                            'search'  => $searchString,
                            'modules' => $searchModules,
                            'offset'  => $offSet,
                            'max'     => $maxResults,
                            'user'    => $GLOBALS['current_user']->id,
                            'fields'  => $returnFields,
                            'unified_only' => TRUE,
                            'favorites' => TRUE)
                        );
        $this->assertEquals($seedData[0]['fieldValue'], self::$helperObject->findFieldByNameFromEntryList($results['entry_list'],$seedData[0]['id'],'Accounts', $seedData[0]['fieldName']));
        $this->assertFalse(self::$helperObject->findFieldByNameFromEntryList($results['entry_list'],$seedData[1]['id'],'Accounts', $seedData[1]['fieldName']));
        $this->assertEquals($seedData[2]['fieldValue'], self::$helperObject->findFieldByNameFromEntryList($results['entry_list'],$seedData[2]['id'],'Contacts', $seedData[2]['fieldName']));
        $this->assertFalse(self::$helperObject->findFieldByNameFromEntryList($results['entry_list'],$seedData[3]['id'],'Opportunities', $seedData[3]['fieldName']));
        $this->assertFalse(self::$helperObject->findFieldByNameFromEntryList($results['entry_list'],$seedData[4]['id'],'Opportunities', $seedData[4]['fieldName']));
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

    /**
     * Test get avaiable modules call
     *
     */
    function testGetAllAvailableModules()
    {
        $soap_data = array('session' => $this->_sessionId);

        $result = $this->_soapClient->call('get_available_modules', $soap_data);
        $actual = $result['modules'][0];
        $this->assertArrayHasKey("module_key", $actual);
        $this->assertArrayHasKey("module_label", $actual);
        $this->assertArrayHasKey("acls", $actual);
        $this->assertArrayHasKey("favorite_enabled", $actual);

        $soap_data = array('session' => $this->_sessionId, 'filter' => 'all');

        $result = $this->_soapClient->call('get_available_modules', $soap_data);
        $actual = $result['modules'][0];
        $this->assertArrayHasKey("module_key", $actual);
        $this->assertArrayHasKey("module_label", $actual);
        $this->assertArrayHasKey("acls", $actual);
        $this->assertArrayHasKey("favorite_enabled", $actual);
    }

    /**
     * Test get avaiable modules call
     *
     */
    function testGetAvailableModules()
    {
        global $beanList, $beanFiles;
        $soap_data = array('session' => $this->_sessionId,'filter' => 'mobile');
        $result = $this->_soapClient->call('get_available_modules', $soap_data);

        foreach ( $result['modules'] as $tmpModEntry)
        {
            $tmpModEntry['module_key'];
            $this->assertTrue( isset($tmpModEntry['acls']) );
            $this->assertTrue( isset($tmpModEntry['module_key']) );

            $class_name = $beanList[$tmpModEntry['module_key']];
            require_once($beanFiles[$class_name]);
            $mod = new $class_name();
            $this->assertEquals( $mod->isFavoritesEnabled(), $tmpModEntry['favorite_enabled']);
        }
    }

}
