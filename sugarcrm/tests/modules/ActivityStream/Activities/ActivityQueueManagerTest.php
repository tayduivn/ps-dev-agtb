<?php

require_once 'modules/ActivityStream/Activities/ActivityQueueManager.php';
require_once 'modules/ActivityStream/Activities/Activity.php';

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

    public function dataProviderForActivityMessageCreation()
    {
        return array(
            array(true, 'after_save', 'createOrUpdate'),
            array(false, 'after_save', null),
            array(true, 'before_save', null),
            array(true, 'after_relationship_add', 'link'),
            array(true, 'after_relationship_delete', 'unlink'),
        );
    }

    /**
     * @dataProvider dataProviderForActivityMessageCreation
     */
    public function testEventDispatcher_ActivityMessageCreation($activityEnabled, $event, $expectedAction)
    {
        $actions = array(
            'createOrUpdate',
            'link',
            'unlink',
        );
        $contact = BeanFactory::getBean('Contacts');
        $contact->id = create_guid();

        $save_enabled = Activity::$enabled;
        Activity::enable();

        if (!$activityEnabled) {
            Activity::disable();
        }
        $actManager = self::getMock(
            "TestActivityQueueManager",
            array('isValidLink', 'createOrUpdate', 'link', 'unlink', 'processSubscriptions')
        );
        $actManager->expects($this->any())->method('isValidLink')->will($this->returnValue(true));
        foreach ($actions as $action) {
            if ($action === $expectedAction) {
                $actManager->expects($this->once())->method($action)->will($this->returnValue(false));
            } else {
                $actManager->expects($this->never())->method($action);
            }
        }
        $actManager->eventDispatcher($contact, $event, array());

        Activity::$enabled = $save_enabled;
    }
}

class TestActivityQueueManager extends ActivityQueueManager {
    public function exec_processParentAttributes($bean) {
        $this->processParentAttributes($bean);
    }
    public function eventDispatcher(SugarBean $bean, $event, $args) {
        parent::eventDispatcher($bean, $event, $args);
    }
}
