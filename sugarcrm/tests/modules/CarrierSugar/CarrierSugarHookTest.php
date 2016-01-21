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

namespace Sugarcrm\SugarcrmTests\modules\CarrierSugar;

require_once 'modules/CarrierSugar/Hook.php';

use CarrierSugarHook;
use Notifications;
use User;
use Sugarcrm\Sugarcrm\Socket\Client as SocketClient;

/**
 * @coversDefaultClass \CarrierSugarHook
 *
 * Class CarrierSugarHookTest
 */
class CarrierSugarHookTest extends \Sugar_PHPUnit_Framework_TestCase
{
    /** @var CarrierSugarHook */
    protected $hook = null;

    /** @var SocketClient|\PHPUnit_Framework_MockObject_MockObject */
    protected $socketClient = null;

    /** @var Notifications */
    protected $notification = null;

    /** @var array */
    protected $backup = array(
        'SocketClient' => null,
    );

    /**
     * @inheritDoc
     */
    protected function setUp()
    {
        parent::setUp();
        $this->backup['SocketClient'] = \SugarTestReflection::getProtectedValue('Sugarcrm\Sugarcrm\Socket\Client', 'instance');
        \BeanFactory::setBeanClass('Users', 'Sugarcrm\SugarcrmTests\modules\CarrierSugar\UserCRYS1267');

        $this->socketClient = $this->getMock('Sugarcrm\Sugarcrm\Socket\Client');
        \SugarTestReflection::setProtectedValue('Sugarcrm\Sugarcrm\Socket\Client', 'instance', $this->socketClient);
        $this->hook = new CarrierSugarHook();
        $this->notification = new Notifications();
        $this->notification->assigned_user_id = create_guid();
        $this->notification->name = 'Name ' . rand(1000, 9999);
        $this->notification->description = 'Description ' . rand(1000, 9999);
    }

    /**
     * @inheritDoc
     */
    protected function tearDown()
    {
        \SugarTestReflection::setProtectedValue('Sugarcrm\Sugarcrm\Socket\Client', 'instance', $this->backup['SocketClient']);
        \BeanFactory::setBeanClass('Users');
        parent::tearDown();
    }

    /**
     * We should not send notification if it is update
     *
     * @covers CarrierSugarHook::hook
     */
    public function testHookDoesNotSendBeanToSocketIfItIsUpdate()
    {
        $this->socketClient->method('isConfigured')->willReturn(true);
        $this->socketClient->expects($this->never())->method('send');
        $this->hook->hook($this->notification, 'after_save', array(
            'isUpdate' => true,
        ));
    }

    /**
     * We should not send notification if socket is not configured
     *
     * @covers CarrierSugarHook::hook
     */
    public function testHookDoesNotSendBeanIfSocketIsNotConfigured()
    {
        $this->socketClient->method('isConfigured')->willReturn(false);
        $this->socketClient->expects($this->never())->method('send');
        $this->hook->hook($this->notification, 'after_save', array(
            'isUpdate' => false,
        ));
    }

    /**
     * We should format and send proper data
     *
     * @covers CarrierSugarHook::hook
     */
    public function testHookSendsBeanToSocket()
    {
        $this->socketClient->method('isConfigured')->willReturn(true);
        $this->socketClient
            ->expects($this->once())
            ->method('recipient')
            ->with($this->equalTo(SocketClient::RECIPIENT_USER_ID), $this->equalTo($this->notification->assigned_user_id))
            ->willReturnSelf();

        $type = '';
        $data = array();
        $this->socketClient
            ->expects($this->once())
            ->method('send')
            ->willReturnCallback(function($a, $b) use (&$type, &$data) {
                $type = $a;
                $data = $b;
            });

        $this->hook->hook($this->notification, 'after_save', array(
            'isUpdate' => false,
        ));
        $this->assertEquals('notification', $type);

        $this->assertArrayHasKey('assigned_user_id', $data);
        $this->assertEquals($this->notification->assigned_user_id, $data['assigned_user_id']);
        $this->assertArrayHasKey('name', $data);
        $this->assertEquals($this->notification->name, $data['name']);
        $this->assertArrayHasKey('description', $data);
        $this->assertEquals($this->notification->description, $data['description']);
    }
}

/**
 * Mock to override retrieve of user
 *
 * @package Sugarcrm\SugarcrmTests\modules\CarrierSugar
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
