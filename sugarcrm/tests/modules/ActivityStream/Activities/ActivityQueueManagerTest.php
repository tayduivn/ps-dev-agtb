<?php

require_once('modules/ActivityStream/Activities/ActivityQueueManager.php');

class ActivityQueueManagerTest extends Sugar_PHPUnit_Framework_TestCase
{
    public function testprocessParentAttributes_noParent_NoAction()
    {
        $contact = BeanFactory::getBean('Contacts');
        $contact->parent_type = null;
        $contact->parent_id   = null;
        $actManager = self::getMock("TestActivityQueueManager", array("getRelationshipDefinition","unlink","link","processSubscriptions"));

        $actManager->expects($this->never())
            ->method('unlink');
        $actManager->expects($this->never())
            ->method('link');
        $actManager->expects($this->never())
            ->method('processSubscriptions');
        $actManager->expects($this->never())
            ->method('getRelationshipDefinition');

        $actManager->exec_processParentAttributes($contact);
    }

    public function testprocessParentAttributes_OldParentIdMatches_NoAction()
    {
        $contact = BeanFactory::getBean('Contacts');
        $contact->parent_type = 'X-Y-Z';
        $contact->parent_id   = create_guid();
        $contact->fetched_row = array('parent_id' => $contact->parent_id);
        $actManager = self::getMock("TestActivityQueueManager", array("getRelationshipDefinition","unlink","link","processSubscriptions"));

        $actManager->expects($this->never())
            ->method('unlink');
        $actManager->expects($this->never())
            ->method('link');
        $actManager->expects($this->never())
            ->method('processSubscriptions');
        $actManager->expects($this->once())
            ->method('getRelationshipDefinition');

        $actManager->exec_processParentAttributes($contact);
    }

    public function testprocessParentAttributes_OldParentExists_OldParentNoMatch_UnlinkCalled()
    {
        $contact = BeanFactory::getBean('Contacts');
        $contact->parent_type = 'X-Y-Z';
        $contact->parent_id   = create_guid();
        $contact->fetched_row = array('parent_id' => create_guid(), 'parent_type' => 'Accounts');
        $actManager = self::getMock("TestActivityQueueManager", array("getRelationshipDefinition","unlink","link","processSubscriptions"));

        $actManager->expects($this->once())
            ->method('unlink');
        $actManager->expects($this->once())
            ->method('link');
        $actManager->expects($this->exactly(2))
            ->method('processSubscriptions');
        $actManager->expects($this->once())
            ->method('getRelationshipDefinition');

        $actManager->exec_processParentAttributes($contact);
    }

    public function testprocessParentAttributes_Parent_RelationshipNotFound_LinkandUnlinkCalled()
    {
        $contact = BeanFactory::getBean('Contacts');
        $contact->parent_type = 'X-Y-Z';
        $contact->parent_id   = create_guid();
        $actManager = self::getMock("TestActivityQueueManager", array("unlink","link","processSubscriptions"));

        $actManager->expects($this->never())
            ->method('unlink');
        $actManager->expects($this->once())
            ->method('link');
        $actManager->expects($this->once())
            ->method('processSubscriptions');

        $actManager->exec_processParentAttributes($contact);
    }

    public function testprocessParentAttributes_Parent_RelationshipFound_NoAction()
    {
        $contact = BeanFactory::getBean('Contacts');
        $contact->parent_type = 'Accounts';
        $contact->parent_id   = create_guid();
        $actManager = self::getMock("TestActivityQueueManager", array("unlink","link","processSubscriptions"));

        $actManager->expects($this->never())
            ->method('unlink');
        $actManager->expects($this->never())
            ->method('link');
        $actManager->expects($this->never())
            ->method('processSubscriptions');

        $actManager->exec_processParentAttributes($contact);
    }
}

class TestActivityQueueManager extends ActivityQueueManager {
    public function exec_processParentAttributes($bean) {
        $this->processParentAttributes($bean);
    }
}
