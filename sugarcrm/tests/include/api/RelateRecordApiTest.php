<?php
/*
 * By installing or using this file, you are confirming on behalf of the entity
 * subscribed to the SugarCRM Inc. product ("Company") that Company is bound by
 * the SugarCRM Inc. Master Subscription Agreement ("MSA"), which is viewable at:
 * http://www.sugarcrm.com/master-subscription-agreement
 *
 * If Company is not bound by the MSA, then by installing or using this file
 * you are agreeing unconditionally that Company will be bound by the MSA and
 * certifying that you have authority to bind Company accordingly.
 *
 * Copyright  2004-2013 SugarCRM Inc.  All rights reserved.
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

}
