<?php
/*
 * Your installation or use of this SugarCRM file is subject to the applicable
 * terms available at
 * http://support.sugarcrm.com/06_Customer_Center/10_Master_Subscription_Agreements/.
 * If you do not agree to all of the applicable terms or do not have the
 * authority to bind the entity as an authorized representative, then do not
 * install or use this SugarCRM file.
 *
 * Copyright (C) SugarCRM Inc. All rights reserved.
 */



require_once ('include/api/RestService.php');
require_once ("clients/base/api/RelateApi.php");


/**
 * @group ApiTests
 */
class RelateApiFunctionTest extends Sugar_PHPUnit_Framework_TestCase
{
    public $contacts = array();
    public $email;

    /** @var  RelateApi */
    public $relateApi;

    public function setUp()
    {
        SugarTestHelper::setUp("current_user");
        // load up the unifiedSearchApi for good times ahead
        $this->relateApi = new RelateApi();

        $contact = SugarTestContactUtilities::createContact();
        $contact->first_name = 'RelateApiTest setUp';
        $contact->last_name = 'Contact';
        $contact->email1 = 'testrelate@example.com';
        $contact->save();
        $this->contacts[] = $contact;

        $contact = SugarTestContactUtilities::createContact();
        $this->contacts[] = $contact;

        $this->email = SugarTestEmailUtilities::createEmail();
        $this->email->to_addrs = 'testrelate@example.com';
        $this->email->save();
    }

    public function tearDown()
    {
        SugarTestEmailUtilities::removeAllCreatedEmails();
        SugarTestContactUtilities::removeAllCreatedContacts();
        SugarTestHelper::tearDown();
        parent::tearDown();
    }


    /**
     * BR-1558
     */
    public function testRelatedFunctionLink()
    {
        $serviceBase = SugarTestRestUtilities::getRestServiceMock();
        $response = $this->relateApi->filterRelated($serviceBase, array(
                'link_name' => 'email_contacts',
                'module' => 'Emails',
                'record' => $this->email->id,
        ));


        $this->assertArrayHasKey('records', $response);
        $this->assertEquals(1, count($response['records']), "Wrong record count");
        $this->assertEquals($this->contacts[0]->id, $response['records'][0]['id'], "Wrong contact id");
    }
}
