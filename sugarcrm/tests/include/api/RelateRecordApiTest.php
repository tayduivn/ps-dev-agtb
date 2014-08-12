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

require_once 'include/api/RestService.php';
require_once 'clients/base/api/RelateRecordApi.php';

class RelateRecordApiTest extends Sugar_PHPUnit_Framework_TestCase
{
    protected $createdBeans = array();

    public function setUp()
    {
        SugarTestHelper::setUp('beanList');
        SugarTestHelper::setUp('beanFiles');
        SugarTestHelper::setUp('current_user');
    }

    public function tearDown()
    {
        foreach($this->createdBeans as $bean)
        {
            $bean->retrieve($bean->id);
            $bean->mark_deleted($bean->id);
        }
        unset($_SESSION['ACL']);
        SugarTestAccountUtilities::removeAllCreatedAccounts();
        SugarTestProspectListsUtilities::removeAllCreatedProspectLists();
        SugarTestCallUtilities::removeAllCreatedCalls();
        SugarTestContactUtilities::removeAllCreatedContacts();
        SugarTestHelper::tearDown();
    }

    public function testCreateRelatedNote() {
        $contact = BeanFactory::getBean("Contacts");
        $contact->last_name = "Related Record Unit Test Contact";
        $contact->save();
        // Get the real data that is in the system, not the partial data we have saved
        $contact->retrieve($contact->id);
        $this->createdBeans[] = $contact;
        $noteName = "Related Record Unit Test Note";

        $api = new RestService();
        //Fake the security
        $api->user = $GLOBALS['current_user'];


        $args = array(
            "module" => "Contacts",
            "record" => $contact->id,
            "link_name" => "notes",
            "name" => $noteName,
            "assigned_user_id" => $GLOBALS['current_user']->id,
        );
        $apiClass = new RelateRecordApi();
        $result = $apiClass->createRelatedRecord($api, $args);

        $this->assertNotEmpty($result['record']);
        $this->assertNotEmpty($result['related_record']['id']);
        $this->assertEquals($noteName, $result['related_record']['name']);

        $note = BeanFactory::getBean("Notes", $result['related_record']['id']);
        // Get the real data that is in the system, not the partial data we have saved
        $note->retrieve($note->id);
        $this->createdBeans[] = $note;

        $contact->load_relationship("notes");
        $relatedNoteIds = $contact->notes->get();
        $this->assertNotEmpty($relatedNoteIds);
        $this->assertEquals($note->id, $relatedNoteIds[0]);
    }

    public function testViewNoneCreate() {
        $this->markTestIncomplete('This is getting following and _module on the array. FRM team will fix it');
        // setup ACL
        unset($_SESSION['ACL']);
        $_SESSION['ACL'][$GLOBALS['current_user']->id]['Contacts']['module']['admin']['aclaccess'] = 99;
        $_SESSION['ACL'][$GLOBALS['current_user']->id]['Notes']['module']['access']['aclaccess'] = 90;
        $_SESSION['ACL'][$GLOBALS['current_user']->id]['Notes']['module']['edit']['aclaccess'] = 90;
        // create a record
        $contact = BeanFactory::getBean("Contacts");
        $contact->last_name = "Related Record Unit Test Contact";
        $contact->save();
        // Get the real data that is in the system, not the partial data we have saved
        $contact->retrieve($contact->id);
        $this->createdBeans[] = $contact;
        $noteName = "Related Record Unit Test Note";

        $api = new RestService();
        //Fake the security
        $api->user = $GLOBALS['current_user'];


        $args = array(
            "module" => "Contacts",
            "record" => $contact->id,
            "link_name" => "notes",
            "name" => $noteName,
            "assigned_user_id" => $GLOBALS['current_user']->id,
        );
        $apiClass = new RelateRecordApi();
        $result = $apiClass->createRelatedRecord($api, $args);
        $this->assertEquals(count($result['related_record']), 1, "More than one field was returned");
        $this->assertNotEmpty($result['related_record']['id'], "ID was empty");
        unset($_SESSION['ACL']);
        $this->createdBeans[] = BeanFactory::getBean("Notes", $result['related_record']['id']);
    }

    /**
     * @group createRelatedLinksFromRecordList
     */
    public function testCreateRelatedLinksFromRecordList_AllRelationshipsAddedSuccessfully()
    {
        $prospectList = SugarTestProspectListsUtilities::createProspectLists();

        $account1 = SugarTestAccountUtilities::createAccount();
        $account2 = SugarTestAccountUtilities::createAccount();

        $records = array ($account1->id, $account2->id);
        $recordListId = RecordListFactory::saveRecordList($records, 'Reports');

        $mockAPI = self::getMock("RelateRecordApi", array("loadBean", "requireArgs"));
        $mockAPI->expects(self::once())
            ->method("loadBean")
            ->will(self::returnValue($prospectList));
        $mockAPI->expects(self::once())
            ->method("requireArgs")
            ->will(self::returnValue(true));

        $api = new RestService();
        $api->user = $GLOBALS['current_user'];

        $args = array(
            "module"    => "ProspectLists",
            "record"    => $prospectList->id,
            "link_name" => "accounts",
            "remote_id" => $recordListId,
        );

        $result = $mockAPI->createRelatedLinksFromRecordList($api,$args);
        $this->assertNotEmpty($result['record']);
        $this->assertNotEmpty($result['record']['id']);
        $this->assertEquals(2, count($result['related_records']['success']));
        $this->assertEquals(0, count($result['related_records']['error']));

        RecordListFactory::deleteRecordList($recordListId);
    }

    /**
     * @group createRelatedLinksFromRecordList
     */
    public function testCreateRelatedLinksFromRecordList_RelationshipsFailedToAdd()
    {
        $prospectList = SugarTestProspectListsUtilities::createProspectLists();

        $account1 = SugarTestAccountUtilities::createAccount();
        $account2 = SugarTestAccountUtilities::createAccount();

        $records = array ($account1->id, $account2->id);
        $recordListId = RecordListFactory::saveRecordList($records, 'Reports');


        $relationshipStub = $this->getMockRelationship();
        $relationshipStub->expects($this->once())
            ->method('add')
            ->will($this->returnValue(array($account1->id)));

        $stub = $this->getMock(BeanFactory::getObjectName('ProspectLists'));
        $stub->accounts = $relationshipStub;

        $mockAPI = self::getMock("RelateRecordApi", array("loadBean", "requireArgs", "checkRelatedSecurity"));
        $mockAPI->expects(self::once())
            ->method("loadBean")
            ->will(self::returnValue($stub));
        $mockAPI->expects(self::once())
            ->method("requireArgs")
            ->will(self::returnValue(true));
        $mockAPI->expects(self::once())
            ->method("checkRelatedSecurity")
            ->will(self::returnValue(array('accounts')));

        $api = new RestService();
        $api->user = $GLOBALS['current_user'];

        $args = array(
            "module"    => "ProspectLists",
            "record"    => $prospectList->id,
            "link_name" => "accounts",
            "remote_id" => $recordListId,
        );

        $result = $mockAPI->createRelatedLinksFromRecordList($api,$args);

        $this->assertNotEmpty($result['record']);
        $this->assertEquals(1, count($result['related_records']['success']));
        $this->assertEquals(1, count($result['related_records']['error']));

        RecordListFactory::deleteRecordList($recordListId);
    }

    /**
     * Helper to get a mock relationship
     * @return mixed
     */
    protected function getMockRelationship()
    {
        return $this->getMockBuilder('Link2')
            ->disableOriginalConstructor()
            ->getMock();
    }

    public function testGetRelatedFieldsReturnsOnlyFieldsForPassedInLink()
    {
        $opp = $this->getMock('Opportunity', array('save'));
        $contact = $this->getMock('Contact', array('save'));

        $rr_api = new RelateRecordApi();

        $api = new RestService();
        $api->user = $GLOBALS['current_user'];

        $fields = SugarTestReflection::callProtectedMethod(
            $rr_api,
            'getRelatedFields',
            array(
                $api,
                // all of the below fields contain a rname_link.
                array(
                    'accept_status_calls' => '',
                    'accept_status_meetings' => '',
                    'opportunity_role' => 'Unit Test'
                ),
                $opp,
                'contacts',
                $contact
            )
        );

        // this should only contain one field as opportunity_role is the only valid one for the contacts link
        $this->assertCount(1, $fields);
    }

    public function testDeleteRelatedLink()
    {
        $call = SugarTestCallUtilities::createCall();
        $contact = SugarTestContactUtilities::createContact();

        $this->assertTrue($call->load_relationship('contacts'), 'Relationship is not loaded');
        $call->contacts->add($contact);

        $call = BeanFactory::retrieveBean('Calls', $call->id, array('use_cache' => false));
        $this->assertEquals($contact->id, $call->contact_id, 'Contact is not linked to call');

        // unregister bean in order to make sure API won't take it from cache
        // where the call is stored w/o linked contact
        BeanFactory::unregisterBean('Calls', $call->id);

        $api = new RelateRecordApi();
        $service = SugarTestRestUtilities::getRestServiceMock();
        $response = $api->deleteRelatedLink($service, array(
            'module' => 'Calls',
            'record' => $call->id,
            'link_name' => 'contacts',
            'remote_id' => $contact->id,
        ));

        $this->assertArrayHasKey('record', $response);
        $this->assertEquals($call->id, $response['record']['id'], 'Call is not returned by API');
        $this->assertEmpty($response['record']['contact_id'], 'Contact is not unlinked from call');
    }

    /**
     * Before Save hook should be called only once.
     * @ticket PAT-769
     */
    public function testBeforeSaveOnCreateRelatedRecord()
    {
        LogicHook::refreshHooks();
        $hook = array(
            'Notes',
            'before_save',
            Array(1, 'Notes::before_save', __FILE__, 'SugarBeanBeforeSaveTestHook', 'beforeSave')
        );
        call_user_func_array('check_logic_hook_file', $hook);

        $contact = SugarTestContactUtilities::createContact();

        $api = new RestService();
        $api->user = $GLOBALS['current_user'];

        $args = array(
            'module' => 'Contacts',
            'record' => $contact->id,
            'link_name' => 'notes',
            'name' => 'Test Note',
            'assigned_user_id' => $api->user->id,
        );
        $apiClass = new RelateRecordApi();
        $result = $apiClass->createRelatedRecord($api, $args);

        call_user_func_array('remove_logic_hook', $hook);
        $this->createdBeans[] = BeanFactory::getBean('Notes', $result['related_record']['id']);
        $expectedCount = SugarBeanBeforeSaveTestHook::$callCounter;
        SugarBeanBeforeSaveTestHook::$callCounter = 0;

        $this->assertEquals(1, $expectedCount);
    }
}

class SugarBeanBeforeSaveTestHook
{
    static public $callCounter = 0;

    public function beforeSave($bean, $event, $arguments)
    {
        self::$callCounter++;
    }
}
