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
require_once 'modules/CarrierEmail/Transport.php';
require_once 'modules/Mailer/SmtpMailer.php';

/**
 * Test cases for CarrierEmailTransport.
 */
class CarrierEmailTransportTest extends \Sugar_PHPUnit_Framework_TestCase
{
    /**
     * @var array Represents an empty message.
     */
    protected $emptyMessage;

    /**
     * @var array Represents some test message.
     */
    protected $someMessage;

    /**
     * {@inheritdoc}
     */
    public function setUp()
    {
        parent::setUp();
        $this->emptyMessage = array('title' => '', 'text' => '', 'html' => '');
        $this->someMessage = array('title' => 'foo', 'text' => 'bar', 'html' => 'baz');
    }

    /**
     * {@inheritdoc}
     */
    public function tearDown()
    {
        \SugarTestUserUtilities::removeAllCreatedAnonymousUsers();
        parent::tearDown();
    }

    /**
     * Test that send() is unsuccessful when System Mailer isn't configured.
     */
    public function testDoNotSendWhenTransportUnavailable()
    {
        $transport = $this->getMock('CarrierEmailTransport', array('test'));
        $transport->expects($this->once())->method('test')->willReturn(false);
        $isSent = $transport->send('userId', $this->someMessage);
        $this->assertFalse($isSent);
    }

    /**
     * Test that send() is unsuccessful when given message is empty.
     */
    public function testDoNotSendEmptyMessage()
    {
        $transport = $this->getMock('CarrierEmailTransport', array('test'));
        $transport->expects($this->once())->method('test')->willReturn(true);
        $isSent = $transport->send('userId', $this->emptyMessage);
        $this->assertFalse($isSent);
    }

    /**
     * Test that send() forms a correct Email and sends it.
     */
    public function testSendMail()
    {
        $user = \SugarTestUserUtilities::createAnonymousUser();
        $user->email1 = "test@example.com";

        $mailIdentity = new \EmailIdentity($user->email1, $user->full_name);

        $mailer = $this->getMockBuilder('SmtpMailer')
            ->disableOriginalConstructor()
            ->setMethods(array('addRecipientsTo', 'setSubject', 'setTextBody', 'setHtmlBody', 'send'))
            ->getMock();

        $mailer->expects($this->once())->method('addRecipientsTo')->with($this->equalTo($mailIdentity));
        $mailer->expects($this->once())->method('setSubject')->with($this->equalTo($this->someMessage['title']));
        $mailer->expects($this->once())->method('setTextBody')->with($this->equalTo($this->someMessage['text']));
        $mailer->expects($this->once())->method('setHtmlBody')->with($this->equalTo($this->someMessage['html']));
        $mailer->expects($this->once())->method('send');

        $transport = $this->getMock('CarrierEmailTransport', array('test', 'getMailer'));
        $transport->expects($this->once())->method('test')->willReturn(true);
        $transport->expects($this->once())->method('getMailer')->willReturn($mailer);

        $isSent = $transport->send($user->id, $this->someMessage);
        $this->assertTrue($isSent);
    }
}
