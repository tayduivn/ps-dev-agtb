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

require_once 'vendor/nusoap//nusoap.php';

/**
 * @group bug43196
 */
class GetEntryListOne2ManyTest extends SOAPTestCase
{
    protected function setUp() : void
    {
        parent::setUp();

        SugarTestHelper::setUp("beanList");
        SugarTestHelper::setUp("beanFiles");
    }

    protected function tearDown() : void
    {
        foreach (SugarTestContactUtilities::getCreatedContactIds() as $id) {
            $GLOBALS['db']->query("DELETE FROM accounts_contacts WHERE contact_id = '{$id}'");
        }
        SugarTestContactUtilities::removeAllCreatedContacts();
        SugarTestAccountUtilities::removeAllCreatedAccounts();
        SugarTestHelper::tearDown();
    }

    public function testGetEntryWhenAccountHasMultipleContactsRelationshipsWorks()
    {
        $contact1 = SugarTestContactUtilities::createContact();
        $contact2 = SugarTestContactUtilities::createContact();
        $account = SugarTestAccountUtilities::createAccount();

        $account->load_relationship('contacts');
        $account->contacts->add($contact1->id);
        $account->contacts->add($contact2->id);

        $this->_login();

        $parameters = [
            'session' => $this->_sessionId,
            'module_name' => 'Accounts',
            'query' => "accounts.id = '{$account->id}'",
            'order_by' => '',
            'offset' => 0,
            'select_fields' => ['id', 'contact_id', 'contact_name'],
            'link_name_to_fields_array' => [['name' =>  'contacts', 'value' => ['id', 'name']]],
            'max_results' => 250,
            'deleted' => 0,
        ];

        $result = $this->_soapClient->call('get_entry_list', $parameters);

        $contact_names = [$contact1->name, $contact2->name];
        $contact_ids = [$contact1->id, $contact2->id];

        $actualContact1Name = $result["relationship_list"][0]["link_list"][0]["records"][0]["link_value"][1]["value"];
        $actualContact2Name = $result["relationship_list"][0]["link_list"][0]["records"][1]["link_value"][1]["value"];
        $actualContact1Id = $result["relationship_list"][0]["link_list"][0]["records"][0]["link_value"][0]["value"];
        $actualContact2Id = $result["relationship_list"][0]["link_list"][0]["records"][1]["link_value"][0]["value"];

        $this->assertTrue(in_array($actualContact1Name, $contact_names), 'Contact1s name not returned.');
        $this->assertTrue(in_array($actualContact2Name, $contact_names), 'Contact2s name not returned.');
        $this->assertTrue(in_array($actualContact1Id, $contact_ids), 'Contact1s id not returned.');
        $this->assertTrue(in_array($actualContact2Id, $contact_ids), 'Contact2s id not returned.');
    }
}
