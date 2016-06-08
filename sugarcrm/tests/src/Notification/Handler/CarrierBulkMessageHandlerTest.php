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

namespace Sugarcrm\SugarcrmTests\Notification\Handler;

require_once 'tests/SugarTestReflection.php';

use Sugarcrm\Sugarcrm\Notification\Emitter\Application\Event as ApplicationEvent;
use Sugarcrm\Sugarcrm\Notification\Handler\CarrierBulkMessageHandler as CarrierBulkMessageHandler;
use Sugarcrm\Sugarcrm\Notification\Carrier\CarrierInterface as CarrierInterface;
use Sugarcrm\Sugarcrm\Notification\MessageBuilder\MessageBuilderInterface as MessageBuilderInterface;
use Sugarcrm\Sugarcrm\Notification\MessageBuilder\MessageBuilderRegistry as MessageBuilderRegistry;
use Sugarcrm\Sugarcrm\JobQueue\Manager\Manager as JobQueueManager;
use Sugarcrm\Sugarcrm\Notification\CarrierRegistry as NotificationCarrierRegistry;

/**
 * Class CarrierBulkMessageHandlerTest
 *
 * @covers Sugarcrm\Sugarcrm\Notification\Handler\CarrierBulkMessageHandler
 */
class CarrierBulkMessageHandlerTest extends \Sugar_PHPUnit_Framework_TestCase
{
    /** @var ApplicationEvent */
    protected $event = null;

    /** @var array[string] */
    protected $messageSignature = array();

    /** @var CarrierInterface|\PHPUnit_Framework_MockObject_MockObject */
    protected $carrier = null;

    /** @var MessageBuilderInterface|\PHPUnit_Framework_MockObject_MockObject */
    protected $messageBuilder = null;

    /** @var MessageBuilderRegistry|\PHPUnit_Framework_MockObject_MockObject */
    protected $messageBuilderRegistry = null;

    /** @var JobQueueManager|\PHPUnit_Framework_MockObject_MockObject */
    protected $jobQueueManager = null;

    /** @var NotificationCarrierRegistry|\PHPUnit_Framework_MockObject_MockObject */
    protected $carrierRegistry = null;

    /**
     * {@inheritdoc}
     */
    protected function setUp()
    {
        parent::setUp();
        \BeanFactory::setBeanClass('Users', 'Sugarcrm\SugarcrmTests\Notification\Handler\UserCRYS1286');

        $this->event = new ApplicationEvent("applicationEvent" . rand(1000, 1999));
        $this->messageSignature = array("messageSignature" . rand(1000, 1999));
        $this->carrier = $this->getMock('Sugarcrm\Sugarcrm\Notification\Carrier\CarrierInterface');
        $this->messageBuilder = $this->getMock('Sugarcrm\Sugarcrm\Notification\MessageBuilder\MessageBuilderInterface');
        $this->messageBuilderRegistry = $this->getMock(
            'Sugarcrm\Sugarcrm\Notification\MessageBuilder\MessageBuilderRegistry'
        );
        $this->jobQueueManager = $this->getMock(
            'Sugarcrm\Sugarcrm\JobQueue\Manager\Manager',
            array('NotificationSend')
        );
        $this->carrierRegistry = $this->getMock('Sugarcrm\Sugarcrm\Notification\CarrierRegistry');

        $this->carrier->method('getMessageSignature')->willReturn($this->messageSignature);
        $this->messageBuilderRegistry->method('getBuilder')->willReturn($this->messageBuilder);
    }

    /**
     * {@inheritdoc}
     */
    protected function tearDown()
    {
        \BeanFactory::setBeanClass('Users');
        parent::tearDown();
    }

    /**
     * Data provider for testRun.
     *
     * @see CarrierBulkMessageHandlerTest::testRun
     * @return array
     */
    public static function runProvider()
    {
        $carrierName = 'carrier' . rand(1000, 1999);
        $user1 = create_guid();
        $user1Message = array('message' . rand(1000, 1999));
        $user1Email1 = 'user' . rand(1000, 1999) . '@email.com';
        $user1Email2 = 'user' . rand(1000, 1999) . '@email.com';

        $user2 = create_guid();
        $user2Message = array('message' . rand(2000, 2999));
        $user2Email2 = 'user' . rand(3000, 3999) . '@email.com';

        return array(
            'generatesMessagesForEachUserAndSend' => array(
                'carrierName' => $carrierName,
                'usersList' => array(
                    $user1 => array(
                        'message' => $user1Message,
                        'filter' => 'filterName' . rand(1000, 1999),
                        'options' => array(
                            0 => $user1Email1,
                            1 => $user1Email2,
                        ),
                    ),
                    $user2 => array(
                        'message' => $user2Message,
                        'filter' => 'filterName' . rand(2000, 2999),
                        'options' => array(
                            0 => $user2Email2,
                        ),
                    ),
                ),
                'expectedNotifications' => array(
                    0 => array(
                        'expectedUserId' => $user1,
                        'expectedCarrierName' => $carrierName,
                        'expectedEmail' =>  $user1Email1,
                        'expectedMessage' => $user1Message,
                    ),
                    1 => array(
                        'expectedUserId' => $user1,
                        'expectedCarrierName' => $carrierName,
                        'expectedEmail' => $user1Email2,
                        'expectedMessage' => $user1Message,
                    ),
                    2 => array(
                        'expectedUserId' => $user2,
                        'expectedCarrierName' => $carrierName,
                        'expectedEmail' => $user2Email2,
                        'expectedMessage' => $user2Message,
                    ),
                ),
            ),
        );
    }

    /**
     * Check if function generates proper message for each user and creates send handler with proper arguments.
     *
     * @dataProvider runProvider
     * @covers Sugarcrm\Sugarcrm\Notification\Handler\CarrierBulkMessageHandler::run
     * @param string $carrierName
     * @param array $usersList
     * @param array $expectedNotifications
     */
    public function testRun($carrierName, $usersList, $expectedNotifications)
    {
        $usersOptions = array();
        foreach ($usersList as $userId => $userData) {
            /** @var $user \User|\PHPUnit_Framework_MockObject_MockObject */
            $user = $this->getMock('Sugarcrm\SugarcrmTests\Notification\Handler\UserCRYS1286');
            $user->id = $userId;
            $usersOptions[$user->id] = array(
                'filter' => $userData['filter'],
                'options' => $userData['options'],
            );
        }
        //set correct message return for each user with valid event and filter
        $this->messageBuilder->method('build')
            ->willReturnCallback(function ($event, $filter, $user, $signature) use ($usersList) {
                $userId = $user->id;
                if (isset($usersList[$userId]) &&
                    $event == $this->event &&
                    $filter == $usersList[$userId]['filter'] &&
                    $signature == $this->messageSignature
                ) {
                    return $usersList[$userId]['message'];
                }
            });

        /** @var CarrierBulkMessageHandler|\PHPUnit_Framework_MockObject_MockObject $handler */
        $handler = $this->getMock(
            'Sugarcrm\Sugarcrm\Notification\Handler\CarrierBulkMessageHandler',
            array('getMessageBuilderRegistry', 'getJobQueueManager', 'getCarrierRegistry'),
            array(
                null,
                array('', 'Sugarcrm\Sugarcrm\Notification\Emitter\Application\Event', $this->event->serialize()),
                array('', null, serialize($carrierName)),
                array('', null, serialize($usersOptions)),
            )
        );

        $handler->method('getMessageBuilderRegistry')->willReturn($this->messageBuilderRegistry);
        $handler->method('getJobQueueManager')->willReturn($this->jobQueueManager);
        $handler->method('getCarrierRegistry')->willReturn($this->carrierRegistry);

        foreach ($expectedNotifications as $index => $notificationsParams) {
            $this->jobQueueManager->expects($this->at($index))
                ->method('NotificationSend')
                ->with(
                    $this->equalTo($notificationsParams['expectedUserId']),
                    $this->equalTo($notificationsParams['expectedCarrierName']),
                    $this->equalTo($notificationsParams['expectedEmail']),
                    $this->equalTo($notificationsParams['expectedMessage'])
                );
        }

        $this->carrierRegistry->method('getCarrier')
            ->willReturnMap(array(
                array($carrierName, $this->carrier),
            ));

        $this->assertEquals(\SchedulersJob::JOB_SUCCESS, $handler->run());
    }
}

/**
 * Class UserCRYS1286
 * @package Sugarcrm\SugarcrmTests\Notification\Emitter\Reminder
 */
class UserCRYS1286 extends \User
{
    /**
     * {@inheritdoc}
     *
     * @param string $id
     * @param bool|true $encode
     * @param bool|true $deleted
     * @return UserCRYS1286
     */
    public function retrieve($id = '-1', $encode = true, $deleted = true)
    {
        $this->id = $id;
        return $this;
    }
}
