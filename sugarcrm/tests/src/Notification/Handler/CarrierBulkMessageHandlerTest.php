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

class CarrierBulkMessageHandlerTest extends \Sugar_PHPUnit_Framework_TestCase
{
    const NS_HANDLER = 'Sugarcrm\\Sugarcrm\\Notification\\Handler\\CarrierBulkMessageHandler';
    const NS_MESSAGE_BUILDER_REGISTRY = 'Sugarcrm\Sugarcrm\Notification\MessageBuilder\MessageBuilderRegistry';
    const NS_MESSAGE_BUILDER = 'Sugarcrm\Sugarcrm\Notification\MessageBuilder\MessageBuilderInterface';
    const NS_CARRIER = 'Sugarcrm\\Sugarcrm\\Notification\\Carrier\\CarrierInterface';
    const NS_CARRIER_REGISTRY = 'Sugarcrm\\Sugarcrm\\Notification\\CarrierRegistry';
    const NS_JOB_QUEUE_MANAGER = 'Sugarcrm\Sugarcrm\JobQueue\Manager\Manager';

    public function testRun()
    {
        $event = new ApplicationEvent('someApplicationEvent');
        $user1 = \SugarTestUserUtilities::createAnonymousUser();
        $user2 = \SugarTestUserUtilities::createAnonymousUser();
        $messageSignature = array('messageSignature' . microtime());
        $message1 = array('message1' . microtime());
        $message2 = array('message2' . microtime());

        $carrierName = 'carrierName1';
        $carrier = $this->getMock(self::NS_CARRIER, array('getMessageSignature', 'getTransport', 'getAddressType'));
        $carrier->expects($this->atLeastOnce())->method('getMessageSignature')
            ->willReturn($messageSignature);

        $usersOptions = array(
            $user1->id => array(
                'filter' => 'filterName1',
                'options' => array('user1@email1.com', 'user1@email2.com')
            ),
            $user2->id => array(
                'filter' => 'filterName2',
                'options' => array('user2@email1.com')
            )
        );

        $messageBuilder = $this->getMock(self::NS_MESSAGE_BUILDER, array('build', 'supports', 'getLevel'));
        $messageBuilder->expects($this->exactly(count($usersOptions)))->method('build')
            ->with(
                $this->equalTo($event),
                $this->logicalOr($this->equalTo('filterName1'), $this->equalTo('filterName2')),
                $this->logicalOr($this->equalTo($user1), $this->equalTo($user2)),
                $this->equalTo($messageSignature)
            )
            ->will($this->onConsecutiveCalls($message1, $message2));

        $messageBuilderRegistry = $this->getMock(self::NS_MESSAGE_BUILDER_REGISTRY, array('getBuilder'));
        $messageBuilderRegistry->expects($this->atLeastOnce())->method('getBuilder')
            ->with($this->equalTo($event))
            ->willReturn($messageBuilder);

        $totalOptionsCount = count($usersOptions[$user1->id]['options']) + count($usersOptions[$user2->id]['options']);
        $jobQueueManager = $this->getMock(self::NS_JOB_QUEUE_MANAGER, array('NotificationSend'));
        $jobQueueManager->expects($this->exactly($totalOptionsCount))->method('NotificationSend')
            ->with(
                $this->equalTo($carrierName),
                $this->logicalOr(
                    $this->equalTo($usersOptions[$user1->id]['options'][0]),
                    $this->equalTo($usersOptions[$user1->id]['options'][1]),
                    $this->equalTo($usersOptions[$user2->id]['options'][0])
                ),
                $this->logicalOr($this->equalTo($message1), $this->equalTo($message2))
            );

        $carrierRegistry = $this->getMock(self::NS_CARRIER_REGISTRY, array('getCarrier'));
        $carrierRegistry->expects($this->atLeastOnce())->method('getCarrier')
            ->will($this->returnValueMap(array(
                array($carrierName, $carrier),
            )));

        $handler = $this->getMock(
            self::NS_HANDLER,
            array('getMessageBuilderRegistry', 'getJobQueueManager', 'getCarrierRegistry'),
            array(
                array('src/Notification/Emitter/Application/Event.php', serialize($event)),
                array('', serialize($carrierName)),
                array('', serialize($usersOptions))
            )
        );

        $handler->expects($this->atLeastOnce())->method('getMessageBuilderRegistry')
            ->willReturn($messageBuilderRegistry);
        $handler->expects($this->atLeastOnce())->method('getJobQueueManager')
            ->willReturn($jobQueueManager);
        $handler->expects($this->once())->method('getCarrierRegistry')
            ->willReturn($carrierRegistry);

        $handler->run();
    }

    protected function tearDown()
    {
        \SugarTestUserUtilities::removeAllCreatedAnonymousUsers();
        parent::tearDown();
    }
}
