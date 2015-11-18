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

/**
 * Class BaseTest
 * @package Sugarcrm\SugarcrmTests\Trigger\ReminderManager
 * @coversDefaultClass \Sugarcrm\Sugarcrm\Trigger\ReminderManager\Base
 */
class BaseTest extends \Sugar_PHPUnit_Framework_TestCase
{
    /**
     * @covers ::makeTag
     */
    public function testMakeTag()
    {
        $bean = $this->getMock('Call');
        $bean->id = 'dummy-bean-id';
        $bean->object_name = 'Call';

        $base = $this->getMock(
            'Sugarcrm\\Sugarcrm\\Trigger\\ReminderManager\\Base',
            array('deleteReminders', 'addReminderForUser')
        );

        $this->assertEquals(
            strtolower($bean->object_name) . '-' . $bean->id,
            \SugarTestReflection::callProtectedMethod($base, 'makeTag', array($bean))
        );
    }

    /**
     * @covers ::prepareTriggerArgs
     */
    public function testPrepareTriggerArgs()
    {
        $bean = $this->getMock('Call');
        $bean->id = 'dummy-bean-id';
        $bean->module_name = 'Calls';
        $user = $this->getMock('User');
        $user->id = 'dummy-user-id';

        $base = $this->getMock(
            'Sugarcrm\\Sugarcrm\\Trigger\\ReminderManager\\Base',
            array('deleteReminders', 'addReminderForUser')
        );

        $args = \SugarTestReflection::callProtectedMethod($base, 'prepareTriggerArgs', array($bean, $user));
        $this->assertArrayHasKey('module', $args);
        $this->assertEquals($bean->module_name, $args['module']);
        $this->assertArrayHasKey('beanId', $args);
        $this->assertEquals($bean->id, $args['beanId']);
        $this->assertArrayHasKey('userId', $args);
        $this->assertEquals($user->id, $args['userId']);
    }
}
