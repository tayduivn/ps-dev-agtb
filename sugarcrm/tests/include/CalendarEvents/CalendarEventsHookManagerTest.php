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
 * Class CalendarEventsHookManagerTest
 *
 * @covers \CalendarEventsHookManager
 */
class CalendarEventsHookManagerTest extends Sugar_PHPUnit_Framework_TestCase
{
    /** @var CallCRYS1415TestMock */
    protected $call;

    /** @var User */
    protected $user;

    /** @var array */
    protected $args;

    /** @var \User */
    protected $currentUser = null;

    /** @var string */
    protected $currentUserPrimaryEmail = '';

    /**
     * {@inheritdoc}
     */
    public function setUp()
    {
        BeanFactory::setBeanClass('Users', 'UserCRYS1579');
        $this->currentUserPrimaryEmail = 'mail' . rand(1000, 1999) . '@domain.test';

        $emailAddress = $this->getMock('EmailAddress');
        $emailAddress->method('getPrimaryAddress')->willReturn($this->currentUserPrimaryEmail);

        $this->user = new UserCRYS1579();
        $this->user->id = create_guid();
        $this->user->emailAddress = $emailAddress;
        $this->user->name = 'User' . rand(1000, 1999);
        $this->currentUser = $GLOBALS['current_user'];
        $GLOBALS['current_user'] = $this->user;
        UserCRYS1579::$currentUser = $this->user;

        CallCRYS1415TestMock::$calDavHandler =
            $this->getMock('Sugarcrm\Sugarcrm\Dav\Cal\Hook\Handler', array('export'));
        $this->call = new CallCRYS1415TestMock();
        $this->call->name = 'Call' . mt_rand();
        $this->call->date_start = TimeDate::getInstance()->getNow()->asDb();
        $this->call->assigned_user_id = $this->user->id;
        $this->call->save();

        $this->call->set_accept_status($this->user, 'none');

        $this->args = array(
            'id' => $this->call->id,
            'related_id' => $this->user->id,
            'name' => $this->call->name,
            'related_name' => $this->user->name,
            'module' => 'Calls',
            'related_module' => 'Users',
            'link' => 'users',
            'relationship' => 'calls_users',
        );
    }

    /**
     * {@inheritdoc}
     */
    public function tearDown()
    {
        $this->call->mark_deleted($this->call->id);
        $GLOBALS['current_user'] = $this->currentUser;
        SugarTestUserUtilities::removeAllCreatedAnonymousUsers();
    }

    /**
     * @covers \CalendarEventsHookManager::afterRelationshipAdd
     */
    public function testTriggerCalDavExportAfterRelationshipAdd()
    {
        $calDavHandler = $this->getMock('Sugarcrm\Sugarcrm\Dav\Cal\Hook\Handler', array('export'));
        $calDavHandler->expects($this->at(0))
            ->method('export')
            ->with(
                $this->anything(),
                $this->equalTo(
                    array(
                        'update',
                        array(),
                        array(
                            'added' => array(
                                array(
                                    'Users',
                                    $this->user->id,
                                    $this->currentUserPrimaryEmail,
                                    'none',
                                    $GLOBALS['locale']->formatName($this->user),
                                ),
                            ),
                        ),
                    )
                )
            );
        CallCRYS1415TestMock::$calDavHandler = $calDavHandler;

        $hookManager = new CalendarEventsHookManager();
        $hookManager->afterRelationshipAdd($this->call, 'after_relationship_add', $this->args);
    }

    /**
     * @covers \CalendarEventsHookManager::afterRelationshipUpdate
     */
    public function testTriggerCalDavExportAfterRelationshipUpdate()
    {
        $this->call->set_accept_status($this->user, 'accept');

        $calDavHandler = $this->getMock('Sugarcrm\Sugarcrm\Dav\Cal\Hook\Handler', array('export'));
        $calDavHandler->expects($this->at(0))
            ->method('export')
            ->with(
                $this->anything(),
                $this->equalTo(
                    array(
                        'update',
                        array(),
                        array(
                            'changed' => array(
                                array(
                                    'Users',
                                    $this->user->id,
                                    $this->currentUserPrimaryEmail,
                                    'accept',
                                    $GLOBALS['locale']->formatName($this->user),
                                ),
                            ),
                        ),
                    )
                )
            );
        CallCRYS1415TestMock::$calDavHandler = $calDavHandler;

        $hookManager = new CalendarEventsHookManager();
        $hookManager->afterRelationshipUpdate($this->call, 'after_relationship_update', $this->args);
    }

    /**
     * @covers \CalendarEventsHookManager::afterRelationshipDelete
     */
    public function testTriggerCalDavExportAfterRelationshipDelete()
    {
        $calDavHandler = $this->getMock('Sugarcrm\Sugarcrm\Dav\Cal\Hook\Handler', array('export'));
        $calDavHandler->expects($this->at(0))
            ->method('export')
            ->with(
                $this->anything(),
                $this->equalTo(
                    array(
                        'update',
                        array(),
                        array(
                            'deleted' => array(
                                array(
                                    'Users',
                                    $this->user->id,
                                    $this->currentUserPrimaryEmail,
                                    'none',
                                    $GLOBALS['locale']->formatName($this->user),
                                ),
                            ),
                        ),
                    )
                )
            );
        CallCRYS1415TestMock::$calDavHandler = $calDavHandler;

        $hookManager = new CalendarEventsHookManager();
        $hookManager->afterRelationshipDelete($this->call, 'after_relationship_delete', $this->args);
    }
}

/**
 * Class is used to setup mocked CalDavHook.
 */
class CallCRYS1415TestMock extends Call
{
    public static $calDavHandler = null;

    public function getCalDavHook()
    {
        return self::$calDavHandler;
    }
}

/**
 * Class UserCRYS1579
 */
class UserCRYS1579 extends User
{
    /** @var User */
    public static $currentUser = null;

    /**
     * {@inheritdoc}
     */
    public function retrieve($id = '-1', $encode = true, $deleted = true)
    {
        $this->id = static::$currentUser->id;
        $this->name = static::$currentUser->name;
        $this->emailAddress = static::$currentUser->emailAddress;
        return $this;
    }
}
