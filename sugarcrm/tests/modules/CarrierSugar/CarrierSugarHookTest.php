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

require_once 'modules/CarrierSugar/Hook.php';

use Sugarcrm\Sugarcrm\Socket\Client as SocketServerClient;

/**
 * @coversDefaultClass \CarrierSugarHook
 *
 * Class CarrierSugarHookTest
 */
class CarrierSugarHookTest extends Sugar_PHPUnit_Framework_TestCase
{

    const NS_CLIENT = 'Sugarcrm\\Sugarcrm\\Socket\\Client';

    public function testHookOnUpdate()
    {
        $notification = new Notifications();
        $arguments = array('isUpdate' => true);
        $hook = $this->getMock('CarrierSugarHook', array('send'));
        $hook->expects($this->never())->method('send');

        $hook->hook($notification, 'after_save', $arguments);
    }

    public function testHookOnInsert()
    {
        $notification = new Notifications();
        $arguments = array('isUpdate' => false);
        $hook = $this->getMock('CarrierSugarHook', array('send'));

        $hook->expects($this->once())->method('send')->with($this->equalTo($notification));

        $hook->hook($notification, 'after_save', $arguments);
    }

    public function testGetServiceBase()
    {
        $apiUser = SugarTestUserUtilities::createAnonymousUser();
        $hook = new CarrierSugarHook();

        $service = SugarTestReflection::callProtectedMethod($hook, 'getServiceBase', array($apiUser));

        $this->assertInstanceOf('RestService', $service);
        $this->assertEquals($apiUser, $service->user);
    }

    public function testPrepareMessage()
    {
        $fieldDefs = array(
            'id' => 'field Id Definitions',
            'name' => 'field Name Definitions',
            'description' => 'field Description Definitions',
            'assigned_user_id' => 'field Assigned user id Definitions'
        );

        $notificationArr = array('title' => 'title Message', 'text' => 'text Message', '_module' => 'Notifications');

        $recipient = SugarTestUserUtilities::createAnonymousUser();

        $notification = $this->getMock('Notifications', array('getFieldDefinitions'));
        $notification->expects($this->once())->method('getFieldDefinitions')
            ->willReturn($fieldDefs);
        $notification->assigned_user_id = $recipient->id;

        $restService = $this->getMock('RestService');

        $hook = $this->getMock('CarrierSugarHook', array('formatBean', 'getServiceBase'));

        $currentUserOld = SugarTestUserUtilities::createAnonymousUser(true);
        $GLOBALS['current_user'] = $currentUserOld;


        $hook->expects($this->atLeastOnce())->method('getServiceBase')
            ->with($this->callback(function ($user) use ($recipient) {
                return $user->id == $recipient->id;
            }))
            ->willReturn($restService);

        $hook->expects($this->once())->method('formatBean')
            ->with(
                $this->equalTo($restService),
                $this->equalTo(array('fields' => array('id', 'name', 'description', 'assigned_user_id'))),
                $this->equalTo($notification)
            )->willReturn($notificationArr);

        SugarTestReflection::callProtectedMethod($hook, 'prepareMessage', array($notification));

        $this->assertEquals($currentUserOld, $GLOBALS['current_user']);
    }

    public function testSocketNotConfigured()
    {
        $hook = $this->getMock(
            'CarrierSugarHook',
            array('isSocketConfigured', 'prepareMessage', 'getSocketServerClient')
        );

        $hook->expects($this->once())->method('isSocketConfigured')->willReturn(false);
        $hook->expects($this->never())->method('prepareMessage');
        $hook->expects($this->never())->method('getSocketServerClient');

        $notification = new Notifications();

        SugarTestReflection::callProtectedMethod($hook, 'send', array($notification));
    }

    public function testSendingNotification()
    {
        $notification = new Notifications();
        $notification->assigned_user_id = 'some-assigned-user-id';
        $notificationArr = array('assigned_user_id' => 'some-assigned-user-id', 'name' => 'some-name');
        $isSent = true;

        $client = $this->getMock(self::NS_CLIENT, array('recipient', 'send'));
        $client->expects($this->once())->method('recipient')
            ->with(
                $this->equalTo(SocketServerClient::RECIPIENT_USER_ID),
                $this->equalTo($notification->assigned_user_id)
            )->willReturn($client);

        $client->expects($this->once())->method('send')
            ->with($this->equalTo('notification'), $this->equalTo($notificationArr))
            ->willReturn($isSent);

        $hook = $this->getMock(
            'CarrierSugarHook',
            array('isSocketConfigured', 'prepareMessage', 'getSocketServerClient')
        );

        $hook->expects($this->once())->method('isSocketConfigured')->willReturn(true);

        $hook->expects($this->once())->method('prepareMessage')
            ->with($this->equalTo($notification))
            ->willReturn($notificationArr);

        $hook->expects($this->once())->method('getSocketServerClient')->willReturn($client);

        SugarTestReflection::callProtectedMethod($hook, 'send', array($notification));
    }

    public function webSocketUrls()
    {
        return array(
            array('http://some.host', true),
            array('', false),
        );
    }

    /**
     * @dataProvider webSocketUrls
     * @param $url
     * @param $expected
     */
    public function testIsSocketConfigured($url, $expected)
    {
        $config = $this->getMock('SugarConfig', array('get'));

        $config->expects($this->once())->method('get')
            ->with($this->equalTo('websockets.server.url'))
            ->willReturn($url);

        $hook = $this->getMock('CarrierSugarHook', array('getSugarConfig'));
        $hook->expects($this->once())->method('getSugarConfig')
            ->willReturn($config);

        $res = SugarTestReflection::callProtectedMethod($hook, 'isSocketConfigured');

        $this->assertEquals($expected, $res);
    }

    protected function setUp()
    {
        parent::setUp();
        SugarTestHelper::setUp('current_user');
    }

    protected function tearDown()
    {
        SugarTestUserUtilities::removeAllCreatedAnonymousUsers();
        SugarTestHelper::tearDown();
        parent::tearDown();
    }
}
