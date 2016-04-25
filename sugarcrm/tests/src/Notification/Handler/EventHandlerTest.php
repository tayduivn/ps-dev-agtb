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

use Sugarcrm\Sugarcrm\Notification\Emitter\Application\Event as ApplicationEvent;

/**
 * Class EventHandlerTest
 *
 * @covers Sugarcrm\Sugarcrm\Notification\Handler\EventHandler
 */
class EventHandlerTest extends \Sugar_PHPUnit_Framework_TestCase
{
    /** @var ApplicationEvent */
    protected $event = null;

    /** @var \Sugarcrm\Sugarcrm\Notification\Carrier\AddressType\Email|\PHPUnit_Framework_MockObject_MockObject */
    protected $addressTypeEmail = null;

    /** @var \Sugarcrm\Sugarcrm\Notification\CarrierRegistry|\PHPUnit_Framework_MockObject_MockObject */
    protected $carrierRegistry = null;

    /** @var \Sugarcrm\Sugarcrm\Notification\SubscriptionsRegistry|\PHPUnit_Framework_MockObject_MockObject */
    protected $subscriptionsRegistry = null;

    /** @var \Sugarcrm\Sugarcrm\JobQueue\Manager\Manager|\PHPUnit_Framework_MockObject_MockObject */
    protected $jobQueueManager = null;

    /** @var \Sugarcrm\Sugarcrm\Notification\Handler\EventHandler|\PHPUnit_Framework_MockObject_MockObject */
    protected $handler = null;

    /**
     * {@inheritdoc}
     */
    protected function setUp()
    {
        $this->event = new ApplicationEvent('update' . rand(1000, 1999));
        $this->addressTypeEmail = $this->getMock('Sugarcrm\Sugarcrm\Notification\Carrier\AddressType\Email');
        $this->carrierRegistry = $this->getMock('Sugarcrm\Sugarcrm\Notification\CarrierRegistry');
        $this->subscriptionsRegistry = $this->getMock('Sugarcrm\Sugarcrm\Notification\SubscriptionsRegistry');
        $this->jobQueueManager = $this->getMock(
            'Sugarcrm\Sugarcrm\JobQueue\Manager\Manager',
            array('NotificationCarrierBulkMessage')
        );
        $this->handler = $this->getMock(
            'Sugarcrm\Sugarcrm\Notification\Handler\EventHandler',
            array(
                'getSubscriptionsRegistry',
                'getCarrierRegistry',
                'getJobQueueManager',
                'getUser',
            ),
            array(null, array(null, serialize($this->event))),
            '',
            true
        );

        $this->handler->method('getSubscriptionsRegistry')->willReturn($this->subscriptionsRegistry);
        $this->handler->method('getCarrierRegistry')->willReturn($this->carrierRegistry);
        $this->handler->method('getJobQueueManager')->willReturn($this->jobQueueManager);
        \SugarTestReflection::callProtectedMethod($this->handler, 'initialize', array($this->event));
    }

    /**
     * Data provider for testRun.
     *
     * @see Sugarcrm\SugarcrmTests\Notification\Handler\EventHandlerTest::testRun
     * @return array
     */
    public static function runProvider()
    {
        $carrierName1 = 'carrierName' . rand(1000, 1999);
        $carrierName2 = 'carrierName' . rand(2000, 2999);
        $filterName1 = 'filterName' . rand(1000, 1999);
        $filterName2 = 'filterName' . rand(2000, 2999);

        return array(
            'twoUsersDuplicatedCarrier' => array(
                'usersEmails' => array(
                    1000 => array(0 => 'user1000@email1.com', 1 => 'user1000@email2.com'),
                    2000 => array(0 => 'user2000@email1.com', 1 => 'user2000@email2.com'),
                ),
                'userList' => array(
                    1000 => array(
                        'filter' => $filterName1,
                        'config' => array(
                            array($carrierName1, 0),
                            array($carrierName1, 0), // checking duplicates
                            array($carrierName1, 1),
                            array($carrierName2, 0),
                        ),
                    ),
                    2000 => array(
                        'filter' => $filterName2,
                        'config' => array(
                            array($carrierName1, 0),
                            array($carrierName1, 1),
                            array($carrierName2, 0),
                        ),
                    ),
                ),
                'carriersList' => array(
                    $carrierName1,
                    $carrierName2,
                ),
                'expectedBulkMessagesParams' => array(
                    $carrierName1 => array(
                        null,
                        $carrierName1,
                        array(
                            1000 => array(
                                'filter' => $filterName1,
                                'options' => array(
                                    0 => 'user1000@email1.com',
                                    1 => 'user1000@email2.com',
                                ),
                            ),
                            2000 => array(
                                'filter' => $filterName2,
                                'options' => array(
                                    0 => 'user2000@email1.com',
                                    1 => 'user2000@email2.com',
                                ),
                            ),
                        ),
                    ),
                    $carrierName2 => array(
                        null,
                        $carrierName2,
                        array(
                            1000 => array(
                                'filter' => $filterName1,
                                'options' => array(
                                    0 => 'user1000@email1.com',
                                ),
                            ),
                            2000 => array(
                                'filter' => $filterName2,
                                'options' => array(
                                    0 => 'user2000@email1.com',
                                ),
                            ),
                        ),
                    ),
                ),
            ),
            'usersUniqueCarriers' => array(
                'usersEmails' => array(
                    3000 => array(0 => 'user3000@email1.com'),
                ),
                'userList' => array(
                    3000 => array(
                        'filter' => $filterName2,
                        'config' => array(
                            array($carrierName1, 0),
                        ),
                    ),
                ),
                'carriersList' => array(
                    $carrierName1,
                ),
                'expectedBulkMessagesParams' => array(
                    $carrierName1 => array(
                        null,
                        $carrierName1,
                        array(
                            3000 => array(
                                'filter' => $filterName2,
                                'options' => array(
                                    0 => 'user3000@email1.com',
                                ),
                            ),
                        ),
                    ),
                ),
            ),
        );
    }

    /**
     * Method should prepare carrier's list from suitable users with their carrier preference.
     * After list generation get job manager and call manager's NotificationCarrierBulkMessage method for each row.
     *
     * @dataProvider runProvider
     * @covers Sugarcrm\Sugarcrm\Notification\Handler\EventHandler::run
     * @param array $usersEmails
     * @param array $usersList
     * @param array $carriersList
     * @param array $expectedBulkMessagesParams
     */
    public function testRun($usersEmails, $usersList, $carriersList, $expectedBulkMessagesParams)
    {
        $usersValueMap = array();
        $transportsValueMap = array();
        $carrierValueMap = array();

        foreach ($usersList as $idUser => $userData) {
            /** @var \User|\PHPUnit_Framework_MockObject_MockObject $user */
            $user = $this->getMock('User');
            $user->id = $idUser;
            $usersValueMap[$idUser] = array($idUser, $user);
            foreach ($usersEmails[$idUser] as $idEmail => $email) {
                $transportsValueMap[] = array($user, $idEmail, $email);
            }
        }

        $this->addressTypeEmail->method('getTransportValue')->willReturnMap($transportsValueMap);

        foreach ($carriersList as $carrierName) {
            /** @var \PHPUnit_Framework_MockObject_MockObject $carrierMock */
            $carrierMock = $this->getMock($carrierName, array('getAddressType'));
            $carrierMock->expects($this->any())
                ->method('getAddressType')
                ->willReturn($this->addressTypeEmail);
            $carrierValueMap[] = array($carrierName, $carrierMock);
        }

        $this->carrierRegistry->method('getCarrier')->willReturnMap($carrierValueMap);

        $this->subscriptionsRegistry
            ->method('getUsers')
            ->with($this->equalTo($this->event))
            ->willReturn($usersList);

        $this->handler->method('getUser')->willReturnMap($usersValueMap);

        $index = 0;
        foreach ($expectedBulkMessagesParams as $carrierName => $notificationsParams) {
            list($arg1, $arg2, $arg3) = $notificationsParams;
            $this->jobQueueManager->expects($this->at($index))
                ->method('NotificationCarrierBulkMessage')
                ->with(
                    $this->equalTo($arg1),
                    $this->equalTo($this->event),
                    $this->equalTo($arg2),
                    $this->equalTo($arg3)
                );
            $index++;
        }
        $this->assertEquals(\SchedulersJob::JOB_SUCCESS, $this->handler->run());
    }
}
