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

namespace Sugarcrm\SugarcrmTestsUnit\Trigger;

use Sugarcrm\Sugarcrm\Trigger\Reminder;

require_once 'modules/Calls/Emitter.php';
require_once 'tests/SugarTestReflection.php';

/**
 * @coversDefaultClass \Sugarcrm\Sugarcrm\Trigger\Reminder
 */
class ReminderTest extends \PHPUnit_Framework_TestCase
{
    const NS_TRIGGER_REMINDER = 'Sugarcrm\\Sugarcrm\\Trigger\\Reminder';

    /**
     * @covers remind
     */
    public function testRemind()
    {
        $user = $emitterRegistry = $this->getMock('User', array(), array(), '', false);
        $user->module_name = 'Users';
        $user->id = 'user-id' . microtime();

        $call = $emitterRegistry = $this->getMock('Call', array(), array(), '', false);
        $call->id = 'call-id' . microtime();
        $call->module_name = 'Calls';

        $emitter = $this->getMock('CallEmitter', array('reminder'));
        $emitter->expects($this->once())->method('reminder')
            ->with($this->equalTo($call), $this->equalTo($user));

        $emitterRegistry = $this->getMock(
            'Sugarcrm\\Sugarcrm\\Notification\\EmitterRegistry',
            array('getModuleEmitter')
        );
        $emitterRegistry->expects($this->atLeastOnce())->method('getModuleEmitter')
            ->with($this->equalTo($call->module_name))
            ->willReturn($emitter);

        $reminder = $this->getMock(self::NS_TRIGGER_REMINDER, array('getEmitterRegistry', 'validate', 'getBean'));

        $reminder->method('getBean')->will(
            $this->returnValueMap(
                array(
                    array($user->module_name, $user->id, $user),
                    array($call->module_name, $call->id, $call)
                )
            )
        );
        $reminder->method('validate')->willReturn(true);
        $reminder->expects($this->atLeastOnce())->method('getEmitterRegistry')
            ->willReturn($emitterRegistry);

        $reminder->remind($call->module_name, $call->id, $user->id);
    }

    public function validateVariants()
    {
        $minute = new \DateTime('+1minute', new \DateTimeZone('UTC'));
        $hour = new \DateTime('+1hour', new \DateTimeZone('UTC'));

        return array(
            'owner remind minute valid' => array(
                true, 'owner-user-id', 'owner-user-id', 60, null, $minute->format('Y-m-d\TH:i:s')
            ),
            'owner remind minute in valid' => array(
                false, 'owner-user-id', 'owner-user-id', 60, null, $hour->format('Y-m-d\TH:i:s')
            ),
            'owner remind hour valid' => array(
                true, 'owner-user-id', 'owner-user-id', 3600, null, $hour->format('Y-m-d\TH:i:s')
            ),
            'owner remind hour in valid' => array(
                false, 'owner-user-id', 'owner-user-id', 3600, null, $minute->format('Y-m-d\TH:i:s')
            ),

            'guest remind minute valid' => array(
                true, 'owner-user-id', 'user-id',  60, 60, $minute->format('Y-m-d\TH:i:s')
            ),
            'guest remind minute in valid' => array(
                false, 'owner-user-id', 'user-id', 60, 60, $hour->format('Y-m-d\TH:i:s')
            ),
            'guest remind hour valid' => array(
                true, 'owner-user-id', 'user-id', 3600, 3600, $hour->format('Y-m-d\TH:i:s')
            ),
            'guest remind hour in valid' => array(
                false, 'owner-user-id', 'user-id', 3600, 3600, $minute->format('Y-m-d\TH:i:s')
            ),
        );
    }

    /**
     * @param $expects
     * @param $assignedUserId
     * @param $userId
     * @param $reminderTime
     * @param $userReminderTime
     * @param $dateStart
     * @covers validate
     * @dataProvider validateVariants
     */
    public function testValidate($expects, $assignedUserId, $userId, $reminderTime, $userReminderTime, $dateStart)
    {
        $user = $emitterRegistry = $this->getMock('User', array('getPreference'), array(), '', false);
        $user->id = $userId;
        if (!is_null($userReminderTime)) {
            $user->expects($this->atLeastOnce())->method('getPreference')
                ->with($this->equalTo('reminder_time'))
                ->willReturn($userReminderTime);
        }

        $call = $emitterRegistry = $this->getMock('Call', array(), array(), '', false);
        $call->assigned_user_id = $assignedUserId;
        $call->reminder_time = $reminderTime;
        $call->date_start = $dateStart;

        $reminder = new Reminder();

        $this->assertEquals(
            $expects,
            \SugarTestReflection::callProtectedMethod($reminder, 'validate', array($call, $user))
        );
    }
}
