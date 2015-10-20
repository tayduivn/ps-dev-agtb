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

require_once 'modules/CarrierSugar/Transport.php';

/**
 * @coversDefaultClass \CarrierSugarTransport
 *
 * Class CarrierSugarTransportTest
 */
class CarrierSugarTransportTest extends Sugar_PHPUnit_Framework_TestCase
{

    public function emptyMessages()
    {
        return array(
            array(array()),
            array(array('title' => '')),
            array(array('text' => '')),
            array(array('html' => '')),
            array(array('title' => '', 'text' => '', 'html' => '')),
        );
    }

    /**
     * @dataProvider emptyMessages
     * @param $message
     */
    public function testEmptyMessage($message)
    {
        $transport = $this->getMock('CarrierSugarTransport', array('newNotification'));

        $transport->expects($this->never())->method('newNotification');

        $transport->send('some-user-id', $message);
    }

    public function testSaveMessage()
    {
        $isSaved = true;
        $assignedUserId = 'some-assigned-user-id';

        $notification = $this->getMock('Notifications', array('save'));
        $notification->expects($this->once())->method('save')->willReturn($isSaved);

        $transport = $this->getMock('CarrierSugarTransport', array('newNotification'));
        $transport->expects($this->once())->method('newNotification')->willReturn($notification);

        $message = array('title' => 'someTitle', 'text' => 'someText', 'html' => 'someText');

        $res = $transport->send($assignedUserId, $message);

        $this->assertEquals($isSaved, $res);
        $this->assertEquals($assignedUserId, $notification->assigned_user_id);
        $this->assertEquals($message['title'], $notification->name);
        $this->assertEquals($message['html'], $notification->description);
    }

    public function testSaveMessageVsEmptyHtml()
    {
        $isSaved = true;
        $assignedUserId = 'some-assigned-user-id';

        $notification = $this->getMock('Notifications', array('save'));
        $notification->expects($this->once())->method('save')->willReturn($isSaved);

        $transport = $this->getMock('CarrierSugarTransport', array('newNotification'));
        $transport->expects($this->once())->method('newNotification')->willReturn($notification);

        $message = array('title' => 'someTitle', 'text' => '>some >Text', 'html' => '');

        $res = $transport->send($assignedUserId, $message);

        $this->assertEquals($isSaved, $res);
        $this->assertEquals($assignedUserId, $notification->assigned_user_id);
        $this->assertEquals($message['title'], $notification->name);
        $this->assertEquals(to_html($message['text']), $notification->description);
    }
}
