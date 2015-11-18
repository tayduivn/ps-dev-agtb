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

namespace Sugarcrm\SugarcrmTests\Trigger\ReminderManager;

use Sugarcrm\Sugarcrm\Trigger\Client;
use Sugarcrm\Sugarcrm\Trigger\ReminderManager\TriggerServer;

/**
 * Class TriggerServer
 * @package Sugarcrm\SugarcrmTests\Trigger\ReminderManager
 * @coversDefaultClass \Sugarcrm\Sugarcrm\Trigger\ReminderManager\TriggerServer
 */
class TriggerServerTest extends \Sugar_PHPUnit_Framework_TestCase
{
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|TriggerServer
     */
    protected $triggerServerManager;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|Client
     */
    protected $triggerClient;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|\Call|\Meeting|\SugarBean
     */
    protected $bean;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|\User
     */
    protected $user;

    public function setUp()
    {
        parent::setUp();
        $this->triggerClient = $this->getMock(
            'Sugarcrm\\Sugarcrm\\Trigger\\Client',
            array('push', 'delete', 'deleteByTags')
        );
    }

    /**
     * @covers ::deleteReminders
     */
    public function testDeleteRemindersDeletesTriggersOnServer()
    {
        $this->mockBean('SugarBean');
        $this->bean->object_name = 'bean';

        $tag = 'bean-' . $this->bean->id;

        $this->triggerClient->expects($this->once())
            ->method('deleteByTags')
            ->with(array($tag));

        $this->mockTriggerServerManager(array(
            'getTriggerClient'
        ));

        $this->triggerServerManager->method('getTriggerClient')->willReturn($this->triggerClient);

        $this->triggerServerManager->deleteReminders($this->bean);
    }

    /**
     * @covers ::addReminderForUser
     * @covers ::prepareTags
     */
    public function testAddReminderForUser()
    {
        $this->mockBean('SugarBean');
        $this->mockUser();
        $reminderTime = new \DateTime();
        $triggerArgs = array(
            'module' => $this->bean->module_name,
            'beanId' => $this->bean->id,
            'userId' => $this->user->id
        );
        $tags = array(
            'bean' => 'bean-tag',
            'user' => 'user-tag',
        );

        $this->mockTriggerServerManager(array('prepareTriggerArgs', 'makeTag', 'getTriggerClient'));

        $this->triggerServerManager->expects($this->once())
            ->method('prepareTriggerArgs')
            ->with($this->equalTo($this->bean), $this->equalTo($this->user))
            ->willReturn($triggerArgs);

        $this->triggerServerManager->expects($this->exactly(2))
            ->method('makeTag')
            ->with($this->logicalOr($this->equalTo($this->bean), $this->equalTo($this->user)))
            ->will($this->returnValueMap(array(
                array($this->bean, $tags['bean']),
                array($this->user, $tags['user']),
            )));

        $this->triggerServerManager->expects($this->once())
            ->method('prepareTriggerArgs')
            ->with($this->equalTo($this->bean), $this->equalTo($this->user))
            ->willReturn($triggerArgs);

        $this->triggerServerManager->expects($this->atLeastOnce())->method('getTriggerClient')
            ->willReturn($this->triggerClient);

        $this->triggerClient->expects($this->any())
            ->method('push')
            ->with(
                $this->equalTo($this->bean->id.'-'.$this->user->id),
                $this->equalTo($reminderTime->format('Y-m-d\TH:i:s')),
                $this->equalTo('post'),
                $this->equalTo(TriggerServer::CALLBACK_URL),
                $this->equalTo($triggerArgs),
                $this->logicalAnd($this->contains($tags['bean']), $this->contains($tags['user']))
            );

        $this->triggerServerManager->addReminderForUser($this->bean, $this->user, $reminderTime);
    }

    /**
     * @param string $module
     */
    protected function mockBean($module)
    {
        $this->bean = $this->getMock($module);
        $this->bean->module_name = $module;
        $this->bean->id = 'dummy-bean-id';
        $this->bean->name = 'dummy bean name';
    }

    protected function mockUser($reminderTime = 60)
    {
        $this->user = $this->getMock('User', array('getPreference'));
        $this->user->id = 'dummy-user-id';
        $this->user->method('getPreference')->willReturn($reminderTime);
    }

    protected function mockTriggerServerManager($methods = array())
    {
        $this->triggerServerManager = $this->getMock(
            'Sugarcrm\\Sugarcrm\\Trigger\\ReminderManager\\TriggerServer',
            $methods
        );
    }
}
