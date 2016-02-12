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

    /**
     * {@inheritdoc}
     */
    public function setUp()
    {
        $this->user = SugarTestUserUtilities::createAnonymousUser();
        CallCRYS1415TestMock::$calDavHandler =
            $this->getMock('Sugarcrm\Sugarcrm\Dav\Cal\Hook\Handler', array('export'));
        $this->call = new CallCRYS1415TestMock();
        $this->call->name = 'Call' . mt_rand();
        $this->call->date_start = TimeDate::getInstance()->getNow()->asDb();
        $this->call->assigned_user_id = $GLOBALS['current_user']->id;
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
                                    $this->user->emailAddress->getPrimaryAddress($this->user),
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
                                    $this->user->emailAddress->getPrimaryAddress($this->user),
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
                                    $this->user->emailAddress->getPrimaryAddress($this->user),
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
