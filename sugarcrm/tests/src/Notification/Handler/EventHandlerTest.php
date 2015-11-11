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

use Sugarcrm\Sugarcrm\Notification\ApplicationEmitter\Event as ApplicationEvent;
use Sugarcrm\Sugarcrm\Notification\Carrier\AddressType\Email as AddressTypeEmail;
use Sugarcrm\Sugarcrm\Notification\Handler\EventHandler;

/**
 * @coversDefaultClass Sugarcrm\Sugarcrm\Notification\Handler\EventHandler
 */
class EventHandlerTest extends \Sugar_PHPUnit_Framework_TestCase
{
    const NS_EVENT_HANDLER = 'Sugarcrm\\Sugarcrm\\Notification\\Handler\\EventHandler';
    const NS_SUBSCRIPTIONS_REGISTRY = 'Sugarcrm\\Sugarcrm\\Notification\\SubscriptionsRegistry';
    const NS_CARRIER_REGISTRY = 'Sugarcrm\\Sugarcrm\\Notification\\CarrierRegistry';
    const NS_MANAGER = 'Sugarcrm\\Sugarcrm\\JobQueue\\Manager\\Manager';


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
        \SugarTestEmailAddressUtilities::addAddressToPerson($user1, 'user1@email1.com');
        \SugarTestEmailAddressUtilities::addAddressToPerson($user1, 'user1@email2.com');
        $user1->save();

        $user2 = \SugarTestUserUtilities::createAnonymousUser();
        \SugarTestEmailAddressUtilities::addAddressToPerson($user2, 'user2@email1.com');
        \SugarTestEmailAddressUtilities::addAddressToPerson($user2, 'user2@email2.com');
        $user2->save();

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

        $addressTypeEmail = new AddressTypeEmail();

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
            array('getSubscriptionsRegistry', 'getCarrierRegistry', 'getJobQueueManager'),
            array(array('src/Notification/ApplicationEmitter/Event.php', serialize($event)))
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
                    $this->equalTo(
                        array(
                            $user1->id => array(
                                'filter' => 'filterName',
                                'options' => array('user1@email1.com', 'user1@email2.com')
                            ),
                            $user2->id => array(
                                'filter' => 'filterName',
                                'options' => array('user2@email1.com', 'user2@email2.com')
                            )
                        )
                    ),
                    $this->equalTo(
                        array(
                            $user1->id => array(
                                'filter' => 'filterName',
                                'options' => array('user1@email1.com')
                            ),
                            $user2->id => array(
                                'filter' => 'filterName',
                                'options' => array('user2@email1.com')
                            )
                        )
                    )
                )
            );

        $eventHandler->expects($this->once())->method('getJobQueueManager')
            ->willReturn($manager);

        $eventHandler->run();
    }
}
