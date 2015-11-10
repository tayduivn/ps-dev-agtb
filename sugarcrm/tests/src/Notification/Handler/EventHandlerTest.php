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
namespace Sugarcrm\SugarcrmTests\Notification\Handler;

use Sugarcrm\Sugarcrm\Notification\Emitter\Application\Event as ApplicationEvent;

/**
 * @coversDefaultClass Sugarcrm\Sugarcrm\Notification\Handler\EventHandler
 */
class EventHandlerTest extends \Sugar_PHPUnit_Framework_TestCase
{
    const NS_EVENT_HANDLER = 'Sugarcrm\\Sugarcrm\\Notification\\Handler\\EventHandler';
    const NS_SUBSCRIPTIONS_REGISTRY = 'Sugarcrm\\Sugarcrm\\Notification\\SubscriptionsRegistry';
    const NS_CARRIER_REGISTRY = 'Sugarcrm\\Sugarcrm\\Notification\\CarrierRegistry';
    const NS_MANAGER = 'Sugarcrm\\Sugarcrm\\JobQueue\\Manager\\Manager';
    const NS_ADDRESS_TYPE_EMAIL = 'Sugarcrm\\Sugarcrm\\Notification\\Carrier\\AddressType\\Email';


    protected function setUp()
    {
        parent::setUp();
        \SugarTestHelper::setUp('current_user');
    }

    protected function tearDown()
    {
        \SugarTestEmailAddressUtilities::removeAllCreatedAddresses();
        \SugarTestUserUtilities::removeAllCreatedAnonymousUsers();
        \SugarTestHelper::tearDown();
        parent::tearDown();
    }

    public function testRun()
    {
        $user1 = \SugarTestUserUtilities::createAnonymousUser();
        $user2 = \SugarTestUserUtilities::createAnonymousUser();
        $usersEmails = array(
            $user1->id => array('0' => 'user1@email1.com', '1' => 'user1@email2.com'),
            $user2->id => array('0' => 'user2@email1.com', '1' => 'user2@email2.com'),
        );

        $userList = array(
            $user1->id => array(
                'filter' => 'filterName',
                'config' => array(
                    array('carrierName1', '0'),
                    array('carrierName1', '0'), // checking dublicates
                    array('carrierName1', '1'),
                    array('carrierName2', '0'),
                ),
            ),
            $user2->id => array(
                'filter' => 'filterName',
                'config' => array(
                    array('carrierName1', '0'),
                    array('carrierName1', '1'),
                    array('carrierName2', '0'),
                ),
            ),
        );

        $addressTypeEmail = $this->getMock(self::NS_ADDRESS_TYPE_EMAIL, array('getTransportValue'));
        $addressTypeEmail->expects($this->any())->method('getTransportValue')
            ->will($this->returnValueMap(array(
                array($user1, '0', $usersEmails[$user1->id]['0']),
                array($user1, '1', $usersEmails[$user1->id]['1']),
                array($user2, '0', $usersEmails[$user2->id]['0']),
                array($user2, '1', $usersEmails[$user2->id]['1']),
            )));

        $event = new ApplicationEvent('someApplicationEvent');

        $carrierName1 = $this->getMock('carrierName1', array('getAddressType'));
        $carrierName1->expects($this->atLeastOnce())->method('getAddressType')
            ->willReturn($addressTypeEmail);
        $carrierName2 = $this->getMock('carrierName2', array('getAddressType'));
        $carrierName2->expects($this->atLeastOnce())->method('getAddressType')
            ->willReturn($addressTypeEmail);

        $carrierRegistry = $this->getMock(self::NS_CARRIER_REGISTRY, array('getCarrier'));
        $carrierRegistry->expects($this->any())->method('getCarrier')
            ->will($this->returnValueMap(array(
                array('carrierName1', $carrierName1),
                array('carrierName2', $carrierName2),
            )));

        $subscriptionsRegistry = $this->getMock(self::NS_SUBSCRIPTIONS_REGISTRY, array('getUsers'));
        $subscriptionsRegistry->expects($this->once())->method('getUsers')
            ->with($this->equalTo($event))
            ->willReturn($userList);

        $eventHandler = $this->getMock(
            self::NS_EVENT_HANDLER,
            array('getSubscriptionsRegistry', 'getCarrierRegistry', 'getJobQueueManager', 'getUser'),
            array(array('src/Notification/Emitter/Application/Event.php', serialize($event)))
        );
        $eventHandler->expects($this->once())->method('getSubscriptionsRegistry')
            ->willReturn($subscriptionsRegistry);
        $eventHandler->expects($this->once())->method('getCarrierRegistry')
            ->willReturn($carrierRegistry);

        $manager = $this->getMock(self::NS_MANAGER, array('NotificationCarrierBulkMessage'), array(), '', false);
        $manager->expects($this->exactly(2))->method('NotificationCarrierBulkMessage')
            ->with(
                $this->equalTo($event),
                $this->logicalOr($this->equalTo('carrierName1'), $this->equalTo('carrierName2')),
                $this->logicalOr(
                    $this->callback(function ($data) use ($user1, $user2) {
                        return
                            array_key_exists($user1->id, $data) &&
                            array_key_exists($user2->id, $data) &&
                            'filterName' == $data[$user1->id]['filter'] &&
                            'filterName' == $data[$user2->id]['filter'] &&
                            in_array('user1@email1.com', $data[$user1->id]['options']) &&
                            in_array('user2@email1.com', $data[$user2->id]['options']);
                    }),
                    $this->callback(function ($data) use ($user1, $user2) {
                        return
                            array_key_exists($user1->id, $data) &&
                            array_key_exists($user2->id, $data) &&
                            'filterName' == $data[$user1->id]['filter'] &&
                            'filterName' == $data[$user2->id]['filter'] &&
                            in_array('user1@email1.com', $data[$user1->id]['options']) &&
                            in_array('user1@email2.com', $data[$user1->id]['options']) &&
                            in_array('user2@email1.com', $data[$user2->id]['options']) &&
                            in_array('user2@email2.com', $data[$user2->id]['options']);
                    })
                )
            );

        $eventHandler->expects($this->any())->method('getUser')
            ->will($this->returnValueMap(
                array(
                    array($user1->id, $user1),
                    array($user2->id, $user2)
                )
            ));

        $eventHandler->expects($this->once())->method('getJobQueueManager')
            ->willReturn($manager);

        $eventHandler->run();
    }
}
