<?php

require_once 'modules/ActivityStream/Activities/ActivityQueueManager.php';
require_once 'modules/ActivityStream/Activities/Activity.php';

/**
 * @group activities
 * @group ActivityStream
 * @group activities_queue
 */
class ActivityQueueManagerTest extends Sugar_PHPUnit_Framework_TestCase
{
    const BOGUS_USER  = '0';
    const USER_ONE    = '1';
    const USER_TWO    = '2';
    const PORTAL_USER = '3';

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
        $actManager->prepareChanges($contact, $activityData);

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
        $actManager->prepareChanges($lead, $activityData);

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
        $actManager->prepareChanges($account1, $activityData);

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
        $actManager->prepareChanges($account1, $activityData);

        $this->assertEquals($expectedData, $activityData);
    }

    public function testPrepareChanges_FieldChangesIncludeActivityDisabledField_OnlyNonDisabledFieldsReturned()
    {
        $contact = BeanFactory::getBean('Contacts');

        //mock out field defs
        $originalFieldDefs = $contact->field_defs;
        $contact->field_defs = array(
            'foo' => array(
                'name' => 'foo',
                'activity_enabled' => false,
            ),
            'bar' => array(
                'name' => 'bar',
                'activity_enabled' => true,
            ),
            'baz' => array(
                'name' => 'baz',
            ),
        );

        $activityData = array(
            'changes' => array(
                'foo' => array(
                    'field_name' => 'foo',
                    'data_type'  => 'varchar',
                    'before'     => 'fooval1',
                    'after'      => 'fooval2',
                ),
                'bar' => array(
                    'field_name' => 'bar',
                    'data_type'  => 'varchar',
                    'before'     => 'barval1',
                    'after'      => 'barval2',
                ),
                'baz' => array(
                    'field_name' => 'baz',
                    'data_type'  => 'varchar',
                    'before'     => 'bazval1',
                    'after'      => 'bazval2',
                ),
            ),
        );

        $expectedData = array(
            'changes' => array(
                'bar' => array(
                    'field_name' => 'bar',
                    'data_type'  => 'varchar',
                    'before'     => 'barval1',
                    'after'      => 'barval2',
                ),
                'baz' => array(
                    'field_name' => 'baz',
                    'data_type'  => 'varchar',
                    'before'     => 'bazval1',
                    'after'      => 'bazval2',
                ),
            ),
        );

        $actManager = new TestActivityQueueManager();
        $actManager->prepareChanges($contact, $activityData);

        $this->assertEquals($expectedData, $activityData);

        //restore contact field defs
        $contact->field_defs = $originalFieldDefs;
    }

    public function dataProviderForAddSubscriptions()
    {
        return array(
            /*  1 */  array(self::USER_ONE,    self::USER_ONE,    false, 1),
            /*  2 */  array(self::USER_ONE,    self::USER_TWO,    false, 2),
            /*  3 */  array(self::USER_ONE,    self::USER_TWO,    true,  1),
            /*  4 */  array(self::USER_ONE,    self::PORTAL_USER, false, 1),
            /*  5 */  array(self::USER_ONE,    self::BOGUS_USER,  false, 1),
            /*  6 */  array(self::PORTAL_USER, self::USER_TWO,    false, 1),
            /*  7 */  array(self::PORTAL_USER, self::USER_TWO,    true,  0),
            /*  8 */  array(self::BOGUS_USER,  self::USER_TWO,    false, 1),
            /*  9 */  array(self::BOGUS_USER,  self::USER_TWO,    true,  0),
            /* 10 */  array(self::BOGUS_USER,  self::BOGUS_USER,  false, 0),
            /* 11 */  array(self::PORTAL_USER, self::PORTAL_USER, false, 0),
        );
    }

    /**
     * @dataProvider dataProviderForAddSubscriptions
     */
    public function testAddSubscribers(
        $arg_assigned_user,
        $arg_createdby_user,
        $isUpdate,
        $subscriptions
    ) {
        $bean = SugarTestContactUtilities::createContact();

        if ($arg_assigned_user == self::BOGUS_USER) {
            $assignedUser     = new User();
            $assignedUser->id = '000A';
        } else {
            $assignedUser = SugarTestUserUtilities::createAnonymousUser();
            if ($arg_assigned_user == self::PORTAL_USER) {
                $assignedUser->portal_only=true;
                $assignedUser->save();
            }
        }

        if ($arg_createdby_user == $arg_assigned_user) {
            $createdByUser = $assignedUser;
        } elseif ($arg_createdby_user == self::BOGUS_USER) {
            $createdByUser     = new User();
            $createdByUser->id = '000B';
        } else {
            $createdByUser = SugarTestUserUtilities::createAnonymousUser();
            if ($arg_createdby_user == self::PORTAL_USER) {
                $createdByUser->portal_only=true;
                $createdByUser->save();
            }
        }

        $bean->assigned_user_id = $assignedUser->id;
        $bean->created_by       = $createdByUser->id;

        $save_enabled = Activity::$enabled;
        Activity::enable();

        $args = array(
            'isUpdate'    => $isUpdate,
            'dataChanges' => array("assigned_user_id" => array())
        );

        $mockActivity = self::getMock('Activity', array('save', 'processRecord'));
        $mockActivity->expects($this->once())
            ->method('save');
        $mockActivity->expects($this->once())
            ->method('processRecord');

        $actManager = self::getMock(
            'TestActivityQueueManager',
            array(
                'subscribeUserToRecord',
                'prepareChanges'
            )
        );
        $actManager->expects($this->exactly($subscriptions))
            ->method('subscribeUserToRecord');

        $actManager->createOrUpdate($bean, $args, $mockActivity);

        Activity::$enabled = $save_enabled;
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
            'ActivityQueueManager',
            array('isValidLink', 'createOrUpdate', 'link', 'unlink')
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
            array('ForecastManagerWorksheets', false),
        );
    }

    /**
     * @dataProvider dataProviderAssignedUserChanged
     */
    public function testAssignmentChanged($auditedChanges, $allChanges, $expected, $assertMessage)
    {
        $bean = BeanFactory::getBean('Contacts');
        $allDataChanges = array(
            'dataChanges' => $allChanges,
        );

        //mock out db manager
        $dbManagerClass = get_class($bean->db);
        $dbManager = self::getMock($dbManagerClass, array('getDataChanges'));
        $dbManager->expects($this->any())->method('getDataChanges')->will($this->returnValue($allDataChanges));
        $bean->db = $dbManager;

        $auditedDataChanges = array(
            'dataChanges' => $auditedChanges,
        );

        $actManager = new TestActivityQueueManager();
        $actual = $actManager->assignmentChanged($bean, $auditedDataChanges);
        $this->assertEquals($expected, $actual, $assertMessage);
    }

    public static function dataProviderAssignedUserChanged()
    {
        return array(
            array(
                array('assigned_user_id' => array()),
                array('assigned_user_id' => array(), 'foo' => array()),
                true,
                'Assignment changed when assigned_user_id is in audited changes',
            ),
            array(
                array(),
                array('assigned_user_id' => array(), 'foo' => array()),
                true,
                'Assignment changed when assigned_user_id is not audited, but still changed',
            ),
            array(
                array(),
                array('foo' => array()),
                false,
                'Assignment did not change when assigned_user_id is not changed at all (audited or otherwise)',
            ),
        );
    }

}

class TestActivityQueueManager extends ActivityQueueManager
{
    public function prepareChanges($bean, &$data)
    {
        parent::prepareChanges($bean, $data);
    }
    public function createOrUpdate($bean, $args, $activity)
    {
        return parent::createOrUpdate($bean, $args, $activity);
    }
    public function assignmentChanged($bean, $args)
    {
        return parent::assignmentChanged($bean, $args);
    }
}
