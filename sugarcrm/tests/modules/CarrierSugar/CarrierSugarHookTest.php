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

namespace Sugarcrm\SugarcrmTests\modules\CarrierSugar;

require_once 'modules/CarrierSugar/Hook.php';

use CarrierSugarHook;
use Notifications;
use User;
use Sugarcrm\Sugarcrm\Socket\Client as SocketClient;

/**
 * Testing mechanism sending notifications.
 *
 * @covers \CarrierSugarHook
 *
 * Class CarrierSugarHookTest
 */
class CarrierSugarHookTest extends \Sugar_PHPUnit_Framework_TestCase
{
    /** @var CarrierSugarHook */
    protected $hook = null;

    /** @var SocketClient|\PHPUnit_Framework_MockObject_MockObject */
    protected $socketClient = null;

    /** @var \User|\PHPUnit_Framework_MockObject_MockObject */
    protected $user = null;

    /** @var Notifications */
    protected $notification = null;

    /**
     * @inheritDoc
     */
    protected function setUp()
    {
        parent::setUp();
        $this->user = $this->getMockBuilder('User')
            ->disableOriginalConstructor()
            ->getMock();
        $this->user->id = create_guid();
        $GLOBALS['current_user'] = $this->user;

        \BeanFactory::setBeanClass('Users', 'Sugarcrm\SugarcrmTests\modules\CarrierSugar\UserCRYS1267');

        $this->socketClient = $this->getMock('Sugarcrm\Sugarcrm\Socket\Client');
        \SugarTestReflection::setProtectedValue('Sugarcrm\Sugarcrm\Socket\Client', 'instance', $this->socketClient);

        $this->hook = new CarrierSugarHook();
        $this->notification = new \Notifications();
    }

    /**
     * @inheritDoc
     */
    protected function tearDown()
    {
        \SugarTestReflection::setProtectedValue('Sugarcrm\Sugarcrm\Socket\Client', 'instance', null);
        \BeanFactory::setBeanClass('Users');
        unset($GLOBALS['current_user']);
        parent::tearDown();
    }

    /**
     * Data provider for testHook.
     *
     * @see testHook
     * @return array
     */
    public function hookProvider()
    {
        $notificationData = array(
            'assigned_user_id' => 'assigned_user:' . rand(1000, 9999),
            'name' => 'Name ' . rand(1000, 9999),
            'description' => 'Description' . rand(1000, 9999),
        );

        return array(
            'doesNotCallSendWhenSocketServerIsConfiguredAndIsUpdateTrue' => array(
                'notificationData' => array(),
                'arguments' => array(
                    'isUpdate' => true,
                    'dataChanges' => array(
                        'other_field' => array('before' => 'old_value', 'after' => 'new_value'),
                    )
                ),
                'isConfigured' => true,
                'expectRecipient' => null,
                'expectSend' => false,
            ),
            'callSendWhenSocketServerIsConfiguredAndIsUpdateFalse' => array(
                'notificationData' => $notificationData,
                'arguments' => array(
                    'isUpdate' => false,
                ),
                'isConfigured' => true,
                'expectRecipient' => $notificationData['assigned_user_id'],
                'expectSend' => array(
                    'name' => $notificationData['name'],
                    'description' => $notificationData['description'],
                    'assigned_user_id' => $notificationData['assigned_user_id'],
                    '_module' => 'Notifications',
                ),
            ),
            'doesNotCallSendWhenSocketServerIsNotConfiguredAndIsUpdateFalse' => array(
                'notificationData' => $notificationData,
                'arguments' => array(
                    'isUpdate' => false,
                ),
                'isConfigured' => false,
                'expectRecipient' => false,
                'expectSend' => false,
            ),
            'updateStatusAndIsConfigured' => array(
                'notificationData' => $notificationData,
                'arguments' => array(
                    'isUpdate' => false,
                    'dataChanges' => array(
                        'is_read' => array('before' => 'old_value', 'after' => 'new_value'),
                    )
                ),
                'isConfigured' => true,
                'expectRecipient' => $notificationData['assigned_user_id'],
                'expectSend' => array(
                    'name' => $notificationData['name'],
                    'description' => $notificationData['description'],
                    'assigned_user_id' => $notificationData['assigned_user_id'],
                    '_module' => 'Notifications',
                ),
            ),
            'updateOtherFieldAndIsConfigured' => array(
                'notificationData' => $notificationData,
                'arguments' => array(
                    'isUpdate' => true,
                    'dataChanges' => array(
                        'other_field' => array('before' => 'old_value', 'after' => 'new_value'),
                    )
                ),
                'isConfigured' => true,
                'expectRecipient' => false,
                'expectSend' => false,
            ),
        );
    }

    /**
     * Testing mechanism sending notifications.
     *
     * @dataProvider hookProvider
     * @covers       \CarrierSugarHook::hook
     * @param array $notificationData array with data for set into the notification bean.
     * @param array $arguments Arguments about event from logic-hook call.
     * @param boolean $isConfigured is configured socket client.
     * @param false|string $expectRecipient expect recipient of notification or false if not expected seining.
     * @param false|array $expectSend expected prepared notification for sending.
     */
    public function testHook($notificationData, $arguments, $isConfigured, $expectRecipient, $expectSend)
    {

        $this->notification->populateFromRow($notificationData);

        $this->socketClient->method('isConfigured')->willReturn($isConfigured);

        if ($expectSend) {
            $this->socketClient
                ->expects($this->once())
                ->method('recipient')
                ->with($this->equalTo(SocketClient::RECIPIENT_USER_ID), $this->equalTo($expectRecipient))
                ->willReturnSelf();

            $this->socketClient
                ->expects($this->once())
                ->method('send')
                ->with($this->equalTo('notification'), $this->callback(function ($result) use ($expectSend) {
                    foreach ($expectSend as $key => $value) {
                        if ($result[$key] != $value) {
                            return false;
                        }
                    }

                    return true;
                }));

        } else {
            $this->socketClient
                ->expects($this->never())
                ->method('send');
        }

        $this->hook->hook($this->notification, 'after_save', $arguments);
    }
}

/**
 * Mock to override retrieve of user.
 */
class UserCRYS1267 extends User
{
    /**
     * @inheritDoc
     */
    public function retrieve($id, $encode = true, $deleted = true)
    {
        $this->id = $id;
        return $this;
    }
}
