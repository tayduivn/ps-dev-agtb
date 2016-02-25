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

use Sugarcrm\Sugarcrm\Notification\Carrier\CarrierInterface;
use Sugarcrm\Sugarcrm\Notification\Carrier\TransportInterface as CarrierTransportInterface;
use Sugarcrm\Sugarcrm\Notification\CarrierRegistry as NotificationCarrierRegistry;
use Sugarcrm\Sugarcrm\Notification\Handler\SendHandler;

/**
 * Class SendHandlerTest
 *
 * @covers Sugarcrm\Sugarcrm\Notification\Handler\SendHandler
 */
class SendHandlerTest extends \Sugar_PHPUnit_Framework_TestCase
{
    /** @var CarrierTransportInterface|\PHPUnit_Framework_MockObject_MockObject */
    protected $transportMock = null;

    /** @var NotificationCarrierRegistry|\PHPUnit_Framework_MockObject_MockObject */
    protected $carrierRegistry = null;

    /** @var CarrierInterface|\PHPUnit_Framework_MockObject_MockObject */
    protected $carrierMock = null;

    /**
     * {@inheritdoc}
     */
    protected function setUp()
    {
        parent::setUp();
        $this->transportMock = $this->getMock('Sugarcrm\Sugarcrm\Notification\Carrier\TransportInterface');
        $this->carrierRegistry = $this->getMock('Sugarcrm\Sugarcrm\Notification\CarrierRegistry');
        $this->carrierMock = $this->getMock('Sugarcrm\Sugarcrm\Notification\Carrier\CarrierInterface');
        $this->carrierMock->method('getTransport')->willReturn($this->transportMock);
        $this->carrierRegistry->method('getCarrier')->willReturn($this->carrierMock);
    }

    /**
     * Data provider for testRun.
     *
     * @see Sugarcrm\SugarcrmTests\Notification\Handler\SendHandlerTest::testRun
     * @return array
     */
    public static function runProvider()
    {
        $transport1 = rand(1000, 1999);
        $transport2 = rand(2000, 2999);
        $transport3 = rand(3000, 3999);

        $message1 = array(
            'label' => 'Message Info' . rand(1000, 1999),
            'subject' => 'Subject' . rand(1000, 1999),
        );
        $message2 = array(
            'label' => 'Message Info' . rand(2000, 2999),
            'subject' => 'Subject' . rand(2000, 2999),
        );
        $message3 = array(
            'label' => 'Message Info' . rand(3000, 3999),
            'subject' => 'Subject' . rand(3000, 3999),
        );

        return array(
            'returnTrueSendingSuccessful' => array(
                'transportValue' => array('', serialize($transport1)),
                'message' => array('', serialize($message1)),
                'expectedTransport' => $transport1,
                'expectedMessage' => $message1,
                'sendResult' => true,
                'expectedResult' => \SchedulersJob::JOB_SUCCESS,
            ),
            'returnFalseSendingFailed' => array(
                'transportValue' => array('', serialize($transport2)),
                'message' => array('', serialize($message2)),
                'expectedTransport' => $transport2,
                'expectedMessage' => $message2,
                'sendResult' => false,
                'expectedResult' => \SchedulersJob::JOB_FAILURE,
            ),
            'returnFalseSendingReturnsNotStrictTrue' => array(
                'transportValue' => array('', serialize($transport3)),
                'message' => array('', serialize($message3)),
                'expectedTransport' => $transport3,
                'expectedMessage' => $message3,
                'sendResult' => 1,
                'expectedResult' => \SchedulersJob::JOB_FAILURE,
            ),
        );
    }

    /**
     * Should returns false if sending was failed otherwise returns true.
     *
     * @dataProvider runProvider
     * @covers Sugarcrm\Sugarcrm\Notification\Handler\SendHandler::run
     * @param array $transportValue
     * @param array $message
     * @param int $expectedTransport
     * @param array $expectedMessage
     * @param mixed $sendResult
     * @param string $expectedResult
     */
    public function testRun(
        $transportValue,
        $message,
        $expectedTransport,
        $expectedMessage,
        $sendResult,
        $expectedResult
    ) {
        /** @var $handler SendHandler|\PHPUnit_Framework_MockObject_MockObject */
        $handler = $this->getMock(
            'Sugarcrm\Sugarcrm\Notification\Handler\SendHandler',
            array('getCarrierRegistry'),
            array(
                rand(1000, 1999),
                array(__FILE__, serialize('CarrierCRYS1288')),
                $transportValue,
                $message,
            )
        );
        $handler->method('getCarrierRegistry')->willReturn($this->carrierRegistry);

        $this->transportMock->expects($this->once())
            ->method('send')
            ->with($this->equalTo($expectedTransport), $this->equalTo($expectedMessage))
            ->willReturn($sendResult);

        $this->assertEquals($expectedResult, $handler->run());
    }
}

/**
 * Class CarrierCRYS1288 uses for carrier serialization/unserialization.
 *
 * @package Sugarcrm\SugarcrmTests\Notification\Handler
 */
class CarrierCRYS1288 implements CarrierInterface
{
    /**
     * {@inheritdoc}
     */
    public function getTransport()
    {

    }

    /**
     * {@inheritdoc}
     */
    public function getMessageSignature()
    {

    }

    /**
     * {@inheritdoc}
     */
    public function getAddressType()
    {

    }
}
