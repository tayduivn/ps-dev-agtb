<?php
//FILE SUGARCRM flav=pro ONLY
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
 * by SugarCRM are Copyright (C) 2004-2012 SugarCRM, Inc.; All Rights Reserved.
 ********************************************************************************/


require_once 'include/api/RestService.php';
require_once 'clients/base/api/RelateRecordApi.php';
/**
 * Bug #57888
 * REST API: Create related quote must populate billing/shipping contact and account
 *
 * @ticket 57888
 */
class RelateRecordQuoteApiTest extends Sugar_PHPUnit_Framework_TestCase
{
    private $_api;
    private $_contact;
    private $_account;
    private $_args;
    private $_quoteName;
    private $_apiClass;
    private $_address_fields = array('address_street', 'address_city', 'address_state', 'address_street', 'address_street');

    public function setUp(){
        SugarTestHelper::setUp('beanList');
        SugarTestHelper::setUp('beanFiles');
        SugarTestHelper::setUp('current_user', array(true, 1));

        $this->_contact = SugarTestContactUtilities::createContact();
        $this->_api = new RestService();
        $this->_api->user = $GLOBALS['current_user'];

        $this->_quoteName = 'RelateRecordQuoteApiTestQuote'.time();
        $this->_args = array(
            "module" => "Contacts",
            "record" => $this->_contact->id,
            "link_name" => "quotes",
            "name" => $this->_quoteName,
            "assigned_user_id" => $GLOBALS['current_user']->id,
            "date_quote_expected_closed" => TimeDate::getInstance()->getNow()->asDbDate(),
        );

        $this->_apiClass = new RelateRecordApi();
    }

    public function tearDown()
    {
        SugarTestContactUtilities::removeAllCreatedContacts();
        SugarTestQuoteUtilities::removeAllCreatedQuotes();
        SugarTestAccountUtilities::removeAllCreatedAccounts();
        SugarTestHelper::tearDown();

        unset( $this->_api,  $this->_contact, $this->_account, $this->_args, $this->_quoteName, $this->_apiClass);
    }

    private function fillAddressArgs()
    {
        $address_types = array('shipping', 'billing');
        $time = time();

        foreach ( $address_types as $_type )
        {
            foreach ( $this->_address_fields as $_field )
            {
                $this->_args[$_type.'_'.$_field] = $_type.'_'.$_field.$time;
            }
        }
    }

    private function fillAddressForContact($address = 'primary')
    {
        $address = in_array($address, array('primary', 'alt')) ? $address : 'primary';
        $time = time();
        foreach ( $this->_address_fields as $_field )
        {
            $_field = $address.'_'.$_field;
            $this->_contact->$_field = $_field.$time;
        }
        $this->_contact->save(false);
    }

    private function createAccountForContact()
    {
        $this->_account = SugarTestAccountUtilities::createAccount();
        $this->_contact->account_id = $this->_account->id;
        $this->_contact->save(false);
    }

    private function assertRelatedItemExists($result)
    {
        $this->assertNotEmpty($result['record']);
        $this->assertNotEmpty($result['related_record']['id']);
        $this->assertEquals($this->_quoteName, $result['related_record']['name']);

        $quote = new Quote();
        $quote->retrieve($result['related_record']['id']);
        SugarTestQuoteUtilities::setCreatedQuote(array($result['related_record']['id']));

        $this->_contact->load_relationship("quotes");
        $relatedIds = $this->_contact->quotes->get();
        $this->assertNotEmpty($relatedIds);
        $this->assertEquals($quote->id, $relatedIds[0]);

        $this->assertArrayHasKey('shipping_contact_name', $result['related_record']);
        $this->assertEquals($this->_contact->name, $result['related_record']['shipping_contact_name']);

        $this->assertArrayHasKey('shipping_contact_id', $result['related_record']);
        $this->assertEquals($this->_contact->id, $result['related_record']['shipping_contact_id']);
    }
    /**
     * test case when there are NOT request params and contact has NOT primary and alt address
     */
    public function testCreateRelatedQuoteToContact()
    {

        $result = $this->_apiClass->createRelatedRecord($this->_api, $this->_args);
        $this->assertRelatedItemExists($result);

        // contact has not address and there are not request data to populate - all address fields should be empty
        $address_types = array('shipping', 'billing');
        foreach ( $address_types as $_type )
        {
            foreach ( $this->_address_fields as $_field )
            {
                $_field = $_type.'_'.$_field;
                $this->assertArrayHasKey($_field, $result['related_record']);
                $this->assertEquals('', $result['related_record'][$_field]);
            }
        }
    }

    /**
     * test case when there are request params and contact has NOT primary and alt address
     */
    public function testCreateRelatedQuoteToContactWithParams()
    {
        $this->fillAddressArgs();

        $result = $this->_apiClass->createRelatedRecord($this->_api, $this->_args);
        $this->assertRelatedItemExists($result);

        // contact has not address but there are request data to populate - all address fields should be populated form request
        $address_types = array('shipping', 'billing');
        foreach ( $address_types as $_type )
        {
            foreach ( $this->_address_fields as $_field )
            {
                $_field = $_type.'_'.$_field;
                $this->assertArrayHasKey($_field, $result['related_record']);
                $this->assertEquals($this->_args[$_field], $result['related_record'][$_field]);
            }
        }
    }

    /**
     * test case when there are NOT request params and contact has primary address
     */
    public function testCreateRelatedQuoteToContactWithAddress()
    {
        $this->fillAddressForContact();

        $result = $this->_apiClass->createRelatedRecord($this->_api, $this->_args);
        $this->assertRelatedItemExists($result);

        // billing address is populated when contact has account only
        // shipping address is populated from primary address of contact
        $address_types = array('shipping');
        foreach ( $address_types as $_type )
        {
            foreach ( $this->_address_fields as $_field )
            {
                $_field_to_check = 'primary_'.$_field;
                $_field = $_type.'_'.$_field;
                $this->assertArrayHasKey($_field, $result['related_record']);
                $this->assertEquals($this->_contact->$_field_to_check, $result['related_record'][$_field]);
            }
        }
    }

    /**
     * test case when there are request params and contact has primary address
     */
    public function testCreateRelatedQuoteToContactWithAddressAndParams()
    {
        $this->fillAddressArgs();
        $this->fillAddressForContact();

        $result = $this->_apiClass->createRelatedRecord($this->_api, $this->_args);
        $this->assertRelatedItemExists($result);

        // billing address is populated when contact has account only
        // shipping address is populated from request
        $address_types = array('shipping');
        foreach ( $address_types as $_type )
        {
            foreach ( $this->_address_fields as $_field )
            {
                $_field = $_type.'_'.$_field;
                $this->assertArrayHasKey($_field, $result['related_record']);
                $this->assertEquals($this->_args[$_field], $result['related_record'][$_field]);
            }
        }
    }

    /**
     * test case when there are NOT request params and contact has alt address (primary address is empty)
     */
    public function testCreateRelatedQuoteToContactWithAltAddress()
    {
        $this->fillAddressForContact('alt');

        $result = $this->_apiClass->createRelatedRecord($this->_api, $this->_args);
        $this->assertRelatedItemExists($result);

        // billing address is populated when contact has account only
        // shipping address is populated from alt address of contact
        $address_types = array('shipping');
        foreach ( $address_types as $_type )
        {
            foreach ( $this->_address_fields as $_field )
            {
                $_field_to_check = 'alt_'.$_field;
                $_field = $_type.'_'.$_field;
                $this->assertArrayHasKey($_field, $result['related_record']);
                $this->assertEquals($this->_contact->$_field_to_check, $result['related_record'][$_field]);
            }
        }
    }

    /**
     * test case when there are request params and contact has alt address (primary address is empty)
     */
    public function testCreateRelatedQuoteToContactWithAltAddressAndParams()
    {
        $this->fillAddressArgs();
        $this->fillAddressForContact('alt');

        $result = $this->_apiClass->createRelatedRecord($this->_api, $this->_args);
        $this->assertRelatedItemExists($result);

        // billing address is populated when contact has account only
        // shipping address is populated from request
        $address_types = array('shipping');
        foreach ( $address_types as $_type )
        {
            foreach ( $this->_address_fields as $_field )
            {
                $_field = $_type.'_'.$_field;
                $this->assertArrayHasKey($_field, $result['related_record']);
                $this->assertEquals($this->_args[$_field], $result['related_record'][$_field]);
            }
        }
    }

    /**
     * test case when there are NOT request params and contact has related account
     */
    public function testCreateRelatedQuoteToContactWithAccount()
    {
        $this->createAccountForContact();
        $this->fillAddressForContact();

        $result = $this->_apiClass->createRelatedRecord($this->_api, $this->_args);
        $this->assertRelatedItemExists($result);

        // check is account related
        $this->assertArrayHasKey('account_name', $result['related_record']);
        $this->assertEquals($this->_account->name, $result['related_record']['account_name']);
        $this->assertArrayHasKey('account_id', $result['related_record']);
        $this->assertEquals($this->_account->id, $result['related_record']['account_id']);

        // contact has account and billing contact should be populated
        $this->assertArrayHasKey('billing_contact_name', $result['related_record']);
        $this->assertEquals($this->_contact->name, $result['related_record']['billing_contact_name']);
        $this->assertArrayHasKey('billing_contact_id', $result['related_record']);
        $this->assertEquals($this->_contact->id, $result['related_record']['billing_contact_id']);
        
        // contact has account and billing address should be populated
        // shipping and billing address are populated from primary address of contact
        $address_types = array('shipping', 'billing');
        foreach ( $address_types as $_type )
        {
            foreach ( $this->_address_fields as $_field )
            {
                $_field_to_check = 'primary_'.$_field;
                $_field = $_type.'_'.$_field;
                $this->assertArrayHasKey($_field, $result['related_record']);
                $this->assertEquals($this->_contact->$_field_to_check, $result['related_record'][$_field]);
            }
        }
    }

    /**
     * test case when there are request params and contact has related account
     */
    public function testCreateRelatedQuoteToContactWithAccountAndParams()
    {
        $this->createAccountForContact();
        $this->fillAddressArgs();

        $result = $this->_apiClass->createRelatedRecord($this->_api, $this->_args);
        $this->assertRelatedItemExists($result);

        // check is account related
        $this->assertArrayHasKey('account_name', $result['related_record']);
        $this->assertEquals($this->_account->name, $result['related_record']['account_name']);
        $this->assertArrayHasKey('account_id', $result['related_record']);
        $this->assertEquals($this->_account->id, $result['related_record']['account_id']);

        // contact has account and billing contact should be populated
        $this->assertArrayHasKey('billing_contact_name', $result['related_record']);
        $this->assertEquals($this->_contact->name, $result['related_record']['billing_contact_name']);
        $this->assertArrayHasKey('billing_contact_id', $result['related_record']);
        $this->assertEquals($this->_contact->id, $result['related_record']['billing_contact_id']);

        // contact has account and billing address should be populated
        // shipping and billing address are populated from request
        $address_types = array('shipping', 'billing');
        foreach ( $address_types as $_type )
        {
            foreach ( $this->_address_fields as $_field )
            {
                $_field = $_type.'_'.$_field;
                $this->assertArrayHasKey($_field, $result['related_record']);
                $this->assertEquals($this->_args[$_field], $result['related_record'][$_field]);
            }
        }
    }
}
