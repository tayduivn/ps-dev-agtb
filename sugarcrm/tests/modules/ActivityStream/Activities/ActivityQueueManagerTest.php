<?php

require_once 'modules/ActivityStream/Activities/ActivityQueueManager.php';
require_once 'modules/ActivityStream/Activities/Activity.php';

class ActivityQueueManagerTest extends Sugar_PHPUnit_Framework_TestCase
{
    public function tearDown()
    {
        SugarTestAccountUtilities::removeAllCreatedAccounts();
        SugarTestContactUtilities::removeAllCreatedContacts();
        SugarTestLeadUtilities::removeAllCreatedLeads();
        SugarTestTeamUtilities::removeAllCreatedAnonymousTeams();
        SugarTestUserUtilities::removeAllCreatedAnonymousUsers();
    }

    public function testChangeFields_TeamIDsChangedToNames_ChangesOccurredNormally()
    {
        $contact    = SugarTestContactUtilities::createContact();
        $teamBefore = SugarTestTeamUtilities::createAnonymousTeam();
        $teamAfter  = SugarTestTeamUtilities::createAnonymousTeam();

        $activityData = array(
            'object'  => array(
                'name'   => $contact->full_name,
                'type'   => 'Contact',
                'module' => 'Contacts',
                'id'     => $contact->id,
            ),
            'changes' => array(
                'team_id' => array(
                    'field_name' => 'team_id',
                    'data_type'  => 'id',
                    'before'     => $teamBefore->id,
                    'after'      => $teamAfter->id,
                ),
            ),
        );

        $expectedData = array(
            'object'  => array(
                'name'   => $contact->full_name,
                'type'   => 'Contact',
                'module' => 'Contacts',
                'id'     => $contact->id,
            ),
            'changes' => array(
                'team_id' => array(
                    'field_name' => 'team_id',
                    'data_type'  => 'id',
                    'before'     => $teamBefore->name,
                    'after'      => $teamAfter->name,
                ),
            ),
        );

        $actManager = new TestActivityQueueManager();
        $actManager->exec_prepareChanges($contact, $activityData);

        $this->assertEquals($expectedData, $activityData);
    }

    public function testChangeFields_AssignedUserIDsChangedToNames_ChangesOccurredNormally()
    {
        $lead         = SugarTestLeadUtilities::createLead();
        $assignedUser = SugarTestUserUtilities::createAnonymousUser();

        $activityData = array(
            'object'  => array(
                'name'   => $lead->full_name,
                'type'   => 'Lead',
                'module' => 'Leads',
                'id'     => $lead->id,
            ),
            'changes' => array(
                'assigned_user_id' => array(
                    'field_name' => 'assigned_user_id',
                    'data_type'  => 'id',
                    'before'     => '',
                    'after'      => $assignedUser->id,
                ),
            ),
        );

        $expectedData = array(
            'object'  => array(
                'name'   => $lead->full_name,
                'type'   => 'Lead',
                'module' => 'Leads',
                'id'     => $lead->id,
            ),
            'changes' => array(
                'assigned_user_id' => array(
                    'field_name' => 'assigned_user_id',
                    'data_type'  => 'id',
                    'before'     => '',
                    'after'      => $assignedUser->name,
                ),
            ),
        );

        $actManager = new TestActivityQueueManager();
        $actManager->exec_prepareChanges($lead, $activityData);

        $this->assertEquals($expectedData, $activityData);
    }

    public function testChangeFields_AccountParentIdNoParentType_ChangesOccurredNormally()
    {
        $account1 = SugarTestAccountUtilities::createAccount();
        $account2 = SugarTestAccountUtilities::createAccount();

        $activityData = array(
            'object'  => array(
                'name'   => $account1->name,
                'type'   => 'Account',
                'module' => 'Accounts',
                'id'     => $account1->id,
            ),
            'changes' => array(
                'parent_id' =>
                array(
                    'field_name' => 'parent_id',
                    'data_type'  => 'id',
                    'before'     => '',
                    'after'      => $account2->id,
                ),
            ),
        );

        $expectedData = array(
            'object'  => array(
                'name'   => $account1->name,
                'type'   => 'Account',
                'module' => 'Accounts',
                'id'     => $account1->id,
            ),
            'changes' => array(
                'parent_id' => array(
                    'field_name' => 'parent_id',
                    'data_type'  => 'id',
                    'before'     => '',
                    'after'      => $account2->name,
                ),
            ),
        );

        $actManager = new TestActivityQueueManager();
        $actManager->exec_prepareChanges($account1, $activityData);

        $this->assertEquals($expectedData, $activityData);
    }

    public function testChangeFields_BeanParentIdIncludesParentType_ChangesOccurredNormally()
    {
        $account1 = SugarTestAccountUtilities::createAccount();
        $account2 = SugarTestAccountUtilities::createAccount();

        $contact = SugarTestContactUtilities::createContact();

        $contact->parent_type = 'Accounts';
        $contact->parent_id   = $account1;
        $contact->save();

        $activityData = array(
            'object'  => array(
                'name'   => $contact->full_name,
                'type'   => 'Account',
                'module' => 'Accounts',
                'id'     => $contact->id,
            ),
            'changes' => array(
                'parent_id' => array(
                    'field_name' => 'parent_id',
                    'data_type'  => 'id',
                    'before'     => $account1->id,
                    'after'      => $account2->id,
                ),
            ),
        );

        $expectedData = array(
            'object'  => array(
                'name'   => $contact->full_name,
                'type'   => 'Account',
                'module' => 'Accounts',
                'id'     => $contact->id,
            ),
            'changes' => array(
                'parent_id' => array(
                    'field_name' => 'parent_id',
                    'data_type'  => 'id',
                    'before'     => $account1->name,
                    'after'      => $account2->name,
                ),
            ),
        );

        $actManager = new TestActivityQueueManager();
        $actManager->exec_prepareChanges($account1, $activityData);

        $this->assertEquals($expectedData, $activityData);
    }

    public function testprocessParentAttributes_noParent_NoAction()
    {
        $contact              = BeanFactory::getBean('Contacts');
        $contact->parent_type = null;
        $contact->parent_id   = null;
        $actManager           = self::getMock(
            "TestActivityQueueManager",
            array("getRelationshipDefinition", "unlink", "link", "processSubscriptions")
        );

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
        $contact              = BeanFactory::getBean('Contacts');
        $contact->parent_type = 'X-Y-Z';
        $contact->parent_id   = create_guid();
        $contact->fetched_row = array('parent_id' => $contact->parent_id);
        $actManager           = self::getMock(
            "TestActivityQueueManager",
            array("getRelationshipDefinition", "unlink", "link", "processSubscriptions")
        );

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
        $contact              = BeanFactory::getBean('Contacts');
        $contact->parent_type = 'X-Y-Z';
        $contact->parent_id   = create_guid();
        $contact->fetched_row = array('parent_id' => create_guid(), 'parent_type' => 'Accounts');
        $actManager           = self::getMock(
            "TestActivityQueueManager",
            array("getRelationshipDefinition", "unlink", "link", "processSubscriptions")
        );

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
        $contact              = BeanFactory::getBean('Contacts');
        $contact->parent_type = 'X-Y-Z';
        $contact->parent_id   = create_guid();
        $actManager           = self::getMock(
            "TestActivityQueueManager",
            array("unlink", "link", "processSubscriptions")
        );

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
        $contact              = BeanFactory::getBean('Contacts');
        $contact->parent_type = 'Accounts';
        $contact->parent_id   = create_guid();
        $actManager           = self::getMock(
            "TestActivityQueueManager",
            array("unlink", "link", "processSubscriptions")
        );

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
        $actions     = array(
            'createOrUpdate',
            'link',
            'unlink',
        );
        $contact     = BeanFactory::getBean('Contacts');
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

    /**
     * @dataProvider dataProviderForecastModulesAuditable
     */
    public function testForecastModulesAreNotAuditable($module, $expected)
    {
        $aqm = new ActivityQueueManager();

        $bean = BeanFactory::getBean($module);

        $this->assertEquals($expected, SugarTestReflection::callProtectedMethod($aqm, 'isAuditable', array($bean)));
    }

    public static function dataProviderForecastModulesAuditable()
    {
        return array(
            array('Forecasts', false),
            array('ForecastWorksheets', false),
            array('ForecastManagerWorksheets', false)
        );
    }
}

class TestActivityQueueManager extends ActivityQueueManager
{
    public function exec_processParentAttributes($bean)
    {
        $this->processParentAttributes($bean);
    }

    public function eventDispatcher(SugarBean $bean, $event, $args)
    {
        parent::eventDispatcher($bean, $event, $args);
    }

    public function exec_prepareChanges($bean, &$data)
    {
        return $this->prepareChanges($bean, $data);
    }
}
