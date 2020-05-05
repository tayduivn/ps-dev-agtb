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
class SOAPAPI2Test extends SOAPTestCase
{
    private static $contactId;
    private static $opportunities = [];

    public static function setUpBeforeClass() : void
    {
        SugarTestHelper::setUp('beanFiles');
        SugarTestHelper::setUp('beanList');
        parent::setUpBeforeClass();
        $contact = SugarTestContactUtilities::createContact();
        self::$contactId = $contact->id;
    }

    protected function setUp() : void
    {
        $this->soapURL = $GLOBALS['sugar_config']['site_url'].'/service/v2/soap.php';
        parent::setUp();
        $this->login();
    }

    protected function tearDown() : void
    {
        $GLOBALS['db']->query("DELETE FROM accounts WHERE name like 'UNIT TEST%' ");
        $GLOBALS['db']->query("DELETE FROM contacts WHERE first_name like 'UNIT TEST%' ");
        parent::tearDown();
    }

    public static function tearDownAfterClass(): void
    {
        if (!empty(self::$opportunities)) {
            $GLOBALS['db']->query('DELETE FROM opportunities WHERE id IN (\'' . implode("', '", self::$opportunities) . '\')');
        }
        parent::tearDownAfterClass();
        SugarTestHelper::tearDown();
    }

    /**
     * Ensure we can create a session on the server.
     */
    public function testCanLogin()
    {
        $result = $this->login();
        $this->assertTrue(
            !empty($result['id']) && $result['id'] != -1,
            'SOAP Session not created. Error ('.$this->soapClient->faultcode.'): '.$this->soapClient->faultstring.': '.$this->soapClient->faultdetail
        );
    }

    public function testSetEntryForContact()
    {
        $result = $this->setEntryForContact();
        $this->assertTrue(
            !empty($result['id']) && $result['id'] != -1,
            'Can not create new contact. Error ('.$this->soapClient->faultcode.'): '.$this->soapClient->faultstring.': '.$this->soapClient->faultdetail
        );
    } // fn

    public function testGetEntryForContact()
    {
        $setresult = $this->setEntryForContact();
        $result = $this->getEntryForContact($setresult['id']);
        if (empty($this->soapClient->faultcode)) {
            if (($result['entry_list'][0]['name_value_list'][2]['value'] == 1) &&
                ($result['entry_list'][0]['name_value_list'][3]['value'] == "Cold Call")) {
                $this->assertEquals($result['entry_list'][0]['name_value_list'][2]['value'], 1, "testGetEntryForContact method - Get Entry For contact is not same as Set Entry");
            } // else
        } else {
            $this->fail('Can not retrieve newly created contact. Error ('.$this->soapClient->faultcode.'): '.$this->soapClient->faultstring.': '.$this->soapClient->faultdetail);
        }
    } // fn

    /**
     * @ticket 38986
     */
    public function testGetEntryForContactNoSelectFields()
    {
        $result = $this->soapClient->call('get_entry', ['session'=>$this->sessionId,'module_name'=>'Contacts','id'=>self::$contactId,'select_fields'=>[], 'link_name_to_fields_array' => []]);
        $this->assertTrue(!empty($result['entry_list'][0]['name_value_list']), "testGetEntryForContactNoSelectFields returned no field data");
    }

    public function testSetEntriesForAccount()
    {
        $result = $this->setEntriesForAccount();
        $this->assertTrue(
            !empty($result['ids']) && $result['ids'][0] != -1,
            'Can not create new account using testSetEntriesForAccount. Error ('.$this->soapClient->faultcode.'): '.$this->soapClient->faultstring.': '.$this->soapClient->faultdetail
        );
    } // fn

    public function testSetEntryForOpportunity()
    {
        $result = $this->setEntryForOpportunity();
        $this->assertTrue(
            !empty($result['id']) && $result['id'] != -1,
            'Can not create new account using testSetEntryForOpportunity. Error ('.$this->soapClient->faultcode.'): '.$this->soapClient->faultstring.': '.$this->soapClient->faultdetail
        );
    } // fn

    public function testSetRelationshipForOpportunity()
    {
        $setresult = $this->setEntryForOpportunity();
        $result = $this->setRelationshipForOpportunity($setresult['id']);
        $this->assertTrue(($result['created'] > 0), 'testSetRelationshipForOpportunity method - Relationship for opportunity to Contact could not be created');
    } // fn


    public function testGetRelationshipForOpportunity()
    {
        $setresult = $this->setEntryForOpportunity();
        $this->setRelationshipForOpportunity($setresult['id']);
        $result = $this->getRelationshipForOpportunity($setresult['id']);
        $this->assertEquals(
            $result['entry_list'][0]['id'],
            self::$contactId,
            "testGetRelationshipForOpportunity - Get Relationship of Opportunity to Contact failed"
        );
    } // fn

    public function testSearchByModule()
    {
        $result = $this->searchByModule();
        $this->assertTrue(($result['entry_list'][0]['records'] > 0 && $result['entry_list'][1]['records'] && $result['entry_list'][2]['records']), "testSearchByModule - could not retrieve any data by search");
    } // fn

    /**********************************
     * HELPER PUBLIC FUNCTIONS
     **********************************/

    private function setEntryForContact()
    {
        global $timedate;
        $current_date = $timedate->nowDb();
        $time = mt_rand();
        $first_name = 'SugarContactFirst' . $time;
        $last_name = 'SugarContactLast';
        $email1 = 'contact@sugar.com';
        $result = $this->soapClient->call('set_entry', ['session'=>$this->sessionId,'module_name'=>'Contacts', 'name_value_list'=>[['name'=>'last_name' , 'value'=>"$last_name"], ['name'=>'first_name' , 'value'=>"$first_name"], ['name'=>'do_not_call' , 'value'=>"1"], ['name'=>'birthdate' , 'value'=>"$current_date"], ['name'=>'lead_source' , 'value'=>"Cold Call"], ['name'=>'email1' , 'value'=>"$email1"]]]);
        SugarTestContactUtilities::setCreatedContact([$result['id']]);
        return $result;
    } // fn

    private function getEntryForContact($id)
    {
        $result = $this->soapClient->call('get_entry', ['session'=>$this->sessionId,'module_name'=>'Contacts','id'=>$id,
            'select_fields'=>['last_name', 'first_name', 'do_not_call', 'lead_source', 'email1'],
            'link_name_to_fields_array' => [['name' =>  'email_addresses', 'value' => ['id', 'email_address', 'opt_out', 'primary_address']]],
        ]);
        return $result;
    }

    private function setEntriesForAccount()
    {
        $this->login();
        $time = mt_rand();
        $name = 'SugarAccount' . $time;
        $email1 = 'account@'. $time. 'sugar.com';
        $result = $this->soapClient->call('set_entries', ['session'=>$this->sessionId,'module_name'=>'Accounts', 'name_value_lists'=>[[['name'=>'name' , 'value'=>"$name"], ['name'=>'email1' , 'value'=>"$email1"]]]]);
        $soap_version_test_accountId = $result['ids'][0];
        SugarTestAccountUtilities::setCreatedAccount([$soap_version_test_accountId]);
        return $result;
    } // fn

    private function setEntryForOpportunity()
    {
        $time = mt_rand();
        $name = 'SugarOpportunity' . $time;
        $account = SugarTestAccountUtilities::createAccount();
        $sales_stage = 'Prospecting';
        $probability = 10;
        $amount = 1000;
        $result = $this->soapClient->call('set_entry', ['session'=>$this->sessionId,'module_name'=>'Opportunities',
            'name_value_lists'=>[['name'=>'name' , 'value'=>"$name"], ['name'=>'amount' , 'value'=>"$amount"],
                ['name'=>'probability' , 'value'=>"$probability"], ['name'=>'sales_stage' , 'value'=>"$sales_stage"],
                ['name'=>'account_id' , 'value'=>$account->id],
            ],
        ]);
        self::$opportunities[] = $result['id'];
        return $result;
    } // fn

    private function setRelationshipForOpportunity($id)
    {
        $result = $this->soapClient->call('set_relationship', ['session'=>$this->sessionId,'module_name' => 'Opportunities',
            'module_id' => $id, 'link_field_name' => 'contacts',
            'related_ids' =>[self::$contactId], 'name_value_list' => [['name' => 'contact_role', 'value' => 'testrole']], 'delete'=>0,
        ]);
        return $result;
    } // fn

    private function getRelationshipForOpportunity($id)
    {
        $result = $this->soapClient->call(
            'get_relationships',
            [
                'session' => $this->sessionId,
                'module_name' => 'Opportunities',
                'module_id' => $id,
                'link_field_name' => 'contacts',
                'related_module_query' => '',
                'related_fields' => ['id'],
                'related_module_link_name_to_fields_array' => [['name' =>  'contacts', 'value' => ['id', 'first_name', 'last_name']]],
                'deleted'=>0,
            ]
        );
        return $result;
    } // fn

    private function searchByModule()
    {
        $result = $this->soapClient->call(
            'search_by_module',
            [
                'session' => $this->sessionId,
                'search_string' => 'Sugar',
                'modules' => ['Accounts', 'Contacts', 'Opportunities'],
                'offset' => '0',
                'max_results' => '10',
            ]
        );

        return $result;
    } // fn
}
