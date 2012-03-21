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
 * by SugarCRM are Copyright (C) 2004-2011 SugarCRM, Inc.; All Rights Reserved.
 ********************************************************************************/

require_once 'tests/service/SOAPTestCase.php';
/**
 * Bug50838Test.php
 * This test is to check the use of the related_module_query parameter for get_relationships web services calls.  This
 * was a regression bug introduced by Link2.php changes in 6.3.
 *
 */
class Bug50838Test extends SOAPTestCase
{

    /**
     * Create test user
     *
     */
    public function setUp()
    {
        $this->markTestIncomplete('This was a regression bug that will be addressed for 6.5.1');
        $this->_soapURL = $GLOBALS['sugar_config']['site_url'].'/service/v2/soap.php';
        parent::setUp();
        $this->_login();
    }

    /**
     * Remove anything that was used during this test
     *
     */
    public function tearDown()
    {
        parent::tearDown();
    }

    /**
     * @outputBuffering disabled
     */
    public function testGetRelationshipsFilter()
    {

        $result = $this->_soapClient->call('set_entry',
            array(
                'session' => $this->_sessionId,
                'module' => 'Accounts',
                'name_value_list' => array(
                    array('name' => 'name', 'value' => 'New Account'),
                    array('name' => 'description', 'value' => 'This is an account created from a REST web services call'),
                    ),
                )
            );

        $this->assertTrue(!empty($result['id']) && $result['id'] != -1);

        $accountId = $result['id'];

        $result = $this->_soapClient->call('set_entry',
            array(
                'session' => $this->_sessionId,
                'module' => 'Contacts',
                'name_value_list' => array(
                    array('name' => 'last_name', 'value' => 'New Contact 1'),
                    array('name' => 'description', 'value' => 'This is a contact created from a SOAP web services call'),
                    ),
                )
            );

        $this->assertTrue(!empty($result['id']) && $result['id'] != -1);

        $contactId1 = $result['id'];

        $result = $this->_soapClient->call('set_entry',
            array(
                'session' => $this->_sessionId,
                'module' => 'Contacts',
                'name_value_list' => array(
                    array('name' => 'last_name', 'value' => 'New Contact 2'),
                    array('name' => 'description', 'value' => 'This is a contact created from a SOAP web services call'),
                    ),
                )
            );

        $this->assertTrue(!empty($result['id']) && $result['id'] != -1);

        $contactId2 = $result['id'];

        // now relate them together
        $result = $this->_soapClient->call('set_relationship',
            array(
                'session' => $this->_sessionId,
                'module' => 'Accounts',
                'module_id' => $accountId,
                'link_field_name' => 'contacts',
                'related_ids' => array($contactId1,$contactId2),
                )
            );

        $this->assertEquals($result['created'],1);

        // retrieve only one relation specified with $related_module_query parameters
        $result = $this->_soapClient->call('get_relationships',
            array(
                'session' => $this->_sessionId,
                'module' => 'Accounts',
                'module_id' => $accountId,
                'link_field_name' => 'contacts',
                'related_module_query' => '',
                //'related_module_query' => 'contacts.last_name = "New Contact 2"',
                'related_fields' => array('id','last_name','description'),
                'related_module_link_name_to_fields_array' => array(),
                'deleted' => false,
                )
            );

        $this->assertEquals(2, count($result['entry_list']));

        // retrieve only one relation specified with $related_module_query parameters
        $result = $this->_soapClient->call('get_relationships',
            array(
                'session' => $this->_sessionId,
                'module' => 'Accounts',
                'module_id' => $accountId,
                'link_field_name' => 'contacts',
                'related_module_query' => "contacts.last_name = 'New Contact 2'",
                'related_fields' => array('id','last_name','description'),
                'related_module_link_name_to_fields_array' => array(),
                'deleted' => false,
                )
            );

        echo var_export($result, true);
        $this->assertEquals(1, count($result['entry_list']));

        $GLOBALS['db']->query("DELETE FROM accounts WHERE id= '{$accountId}'");
        $GLOBALS['db']->query("DELETE FROM contacts WHERE id= '{$contactId1}'");
        $GLOBALS['db']->query("DELETE FROM contacts WHERE id= '{$contactId2}'");
        $GLOBALS['db']->query("DELETE FROM accounts_contacts WHERE account_id= '{$accountId}'");

    }
}
