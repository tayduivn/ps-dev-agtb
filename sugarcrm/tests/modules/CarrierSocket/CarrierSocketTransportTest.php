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

use Sugarcrm\Sugarcrm\Socket\Client as SocketServerClient;

require_once 'modules/CarrierSocket/Transport.php';

/**
 * @coversDefaultClass \CarrierSocketTransport
 *
 * Class CarrierSocketTransportTest
 */
class CarrierSocketTransportTest extends Sugar_PHPUnit_Framework_TestCase
{
    const NS_CLIENT = 'Sugarcrm\\Sugarcrm\\Socket\\Client';

    public function testSendingFormattedBean()
    {
        $isSent = true;

        $messageArr = array('title' => 'title Message', 'text' => 'text Message');
        $notificationArr = $messageArr + array('_module' => 'Notifications');

        $currentUserOld = SugarTestUserUtilities::createAnonymousUser(true);
        $GLOBALS['current_user'] = $currentUserOld;

        $recipient = SugarTestUserUtilities::createAnonymousUser(true);

        $restService = $this->getMock('RestService');

        $transport = $this->getMock(
            'CarrierSocketTransport',
            array('test', 'formatBean', 'getServiceBase', 'getSocketServerClient')
        );

        $client = $this->getMock(self::NS_CLIENT, array('recipient', 'send'));

        $transport->expects($this->once())->method('test')
            ->willReturn(true);

        $transport->expects($this->atLeastOnce())->method('getServiceBase')
            ->with($this->callback(function ($user) use ($recipient) {
                return true;
                return $user->id == $recipient->id;
            }))
            ->willReturn($restService);

        $transport->expects($this->once())->method('formatBean')
            ->with(
                $this->equalTo($restService),
                $this->equalTo(array('fields' => array('name', 'description', 'assigned_user_id'))),
                $this->callback(function ($bean) use ($messageArr, $recipient) {
                    return $bean->name == $messageArr['title']
                    && $bean->description == $messageArr['text']
                    && $bean->assigned_user_id == $recipient->id;
                })
            )->willReturn($notificationArr);

        $transport->expects($this->atLeastOnce())->method('getSocketServerClient')
            ->willReturn($client);

        $client->expects($this->once())->method('recipient')
            ->with($this->equalTo(SocketServerClient::RECIPIENT_USER_ID), $this->equalTo($recipient->id))
            ->willReturn($client);

        $client->expects($this->once())->method('send')
            ->with($this->equalTo('notification'), $this->equalTo($notificationArr))
            ->willReturn($isSent);

        $sendResult = $transport->send($recipient->id, $messageArr);

        $this->assertEquals($currentUserOld, $GLOBALS['current_user']);
        $this->assertEquals($isSent, $sendResult);
    }

    public function testSocketServerNotAvailable()
    {
        $transport = $this->getMock(
            'CarrierSocketTransport',
            array('test', 'formatBean', 'getServiceBase', 'getSocketServerClient')
        );
        $transport->expects($this->once())->method('test')
            ->willReturn(false);

        $transport->expects($this->never())->method('getSocketServerClient');
        $transport->expects($this->never())->method('formatBean');
        $transport->expects($this->never())->method('getServiceBase');

        $res = $transport->send('some recipient', array('title' => 'some title', 'text' => 'some text'));
        $this->assertFalse($res);
    }

    public function testNoMessage()
    {
        $transport = $this->getMock(
            'CarrierSocketTransport',
            array('test', 'formatBean', 'getServiceBase', 'getSocketServerClient')
        );
        $transport->expects($this->once())->method('test')
            ->willReturn(true);

        $transport->expects($this->never())->method('getSocketServerClient');
        $transport->expects($this->never())->method('formatBean');
        $transport->expects($this->never())->method('getServiceBase');

        $res = $transport->send('some recipient', array());
        $this->assertFalse($res);
    }

    protected function setUp()
    {
        parent::setUp();
        SugarTestHelper::setUp('current_user');
    }

    protected function tearDown()
    {
        SugarTestHelper::tearDown();
        parent::tearDown();
    }
}
