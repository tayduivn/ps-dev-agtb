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

namespace Sugarcrm\SugarcrmTests\modules\CarrierEmail;

require_once 'modules/CarrierEmail/Transport.php';

use CarrierEmailTransport;
use Localization;
use OutboundEmailConfiguration;
use OutboundEmailConfigurationPeer;
use MailerFactory;
use SmtpMailer;
use MailerException;
use EmailIdentity;

/**
 * Test cases for CarrierEmailTransport.
 */
class CarrierEmailTransportTest extends \Sugar_PHPUnit_Framework_TestCase
{
    /** @var CarrierEmailTransport|\PHPUnit_Framework_MockObject_MockObject object for testing */
    protected $transport = null;

    /** @var OutboundEmailConfigurationPeerCRYS1265 mocked object */
    protected $outboundEmailConfigurationPeer = null;

    /** @var OutboundEmailConfiguration mocked object */
    protected $outboundEmailConfiguration = null;

    /** @var MailerFactoryCRYS1265 mocked object */
    protected $mailerFactory = null;

    /** @var SmtpMailer|\PHPUnit_Framework_MockObject_MockObject mocked object */
    protected $smtpMailer = null;

    /**
     * @inheritDoc
     */
    protected function setUp()
    {
        parent::setUp();
        $this->outboundEmailConfigurationPeer = new OutboundEmailConfigurationPeerCRYS1265();
        $this->outboundEmailConfiguration = $this->getMock('OutboundEmailConfiguration', array(), array(), '', false);
        $this->mailerFactory = new MailerFactoryCRYS1265();
        $this->smtpMailer = $this->getMock('SmtpMailer', array(), array($this->outboundEmailConfiguration));

        $this->transport = $this->getMock('CarrierEmailTransport', array(
            'getOutboundEmailConfigurationPeer',
            'getMailerFactory',
        ));

        OutboundEmailConfigurationPeerCRYS1265::$isMailConfigurationValidReturn = true;
        OutboundEmailConfigurationPeerCRYS1265::$getSystemDefaultMailConfigurationReturn = $this->outboundEmailConfiguration;
        MailerFactoryCRYS1265::$getSystemDefaultMailerReturn = $this->smtpMailer;
        $this->transport->method('getOutboundEmailConfigurationPeer')->willReturn($this->outboundEmailConfigurationPeer);
        $this->transport->method('getMailerFactory')->willReturn($this->mailerFactory);
    }

    /**
     * @inheritDoc
     */
    protected function tearDown()
    {
        OutboundEmailConfigurationPeerCRYS1265::$isMailConfigurationValidArgs = array();
        OutboundEmailConfigurationPeerCRYS1265::$isMailConfigurationValidReturn = null;
        OutboundEmailConfigurationPeerCRYS1265::$getSystemDefaultMailConfigurationArgs = array();
        OutboundEmailConfigurationPeerCRYS1265::$getSystemDefaultMailConfigurationReturn = null;
        parent::tearDown();
    }

    /**
     * If email is not configured we should get false
     *
     * @covers CarrierEmailTransport::send
     */
    public function testSendReturnsFalseIfEmailIsNotConfigured()
    {
        OutboundEmailConfigurationPeerCRYS1265::$isMailConfigurationValidReturn = false;

        $result = $this->transport->send('test@test.com', array(
            'title' => 'title',
            'text' => 'text',
            'html' => 'html',
        ));

        $this->assertFalse($result);
        $this->assertEquals(array($this->outboundEmailConfiguration), OutboundEmailConfigurationPeerCRYS1265::$isMailConfigurationValidArgs);
    }

    /**
     * Data provider for testSendGeneratesEmailAndReturnsTrue
     *
     * @see CarrierEmailTransportTest::testSendGeneratesEmailAndReturnsTrue
     * @return array
     */
    public static function sendGeneratesEmailAndReturnsTrueProvider()
    {
        $rand = rand(1000, 9999);

        return array(
            'sendsTitle' => array(
                'recipient' => 'test' . $rand . '@test.com',
                'message' => array(
                    'title' => 'title ' . $rand,
                ),
                'expectations' => array(
                    'setSubject' => 'title ' . $rand,
                    'setTextBody' => false,
                    'setHtmlBody' => false,
                ),
            ),
            'sendsText' => array(
                'recipient' => 'test' . $rand . '@test.com',
                'message' => array(
                    'text' => 'text ' . $rand,
                ),
                'expectations' => array(
                    'setSubject' => false,
                    'setTextBody' => 'text ' . $rand,
                    'setHtmlBody' => false,
                ),
            ),
            'sendsHtml' => array(
                'recipient' => 'test' . $rand . '@test.com',
                'message' => array(
                    'html' => 'html ' . $rand,
                ),
                'expectations' => array(
                    'setSubject' => false,
                    'setTextBody' => false,
                    'setHtmlBody' => 'html ' . $rand,
                ),
            ),
            'sendsAll' => array(
                'recipient' => 'test' . $rand . '@test.com',
                'message' => array(
                    'title' => 'title ' . $rand,
                    'text' => 'text ' . $rand,
                    'html' => 'html ' . $rand,
                ),
                'expectations' => array(
                    'setSubject' => 'title ' . $rand,
                    'setTextBody' => 'text ' . $rand,
                    'setHtmlBody' => 'html ' . $rand,
                ),
            ),
        );
    }

    /**
     * Testing mail generation
     *
     * @covers CarrierEmailTransport::send
     * @dataProvider sendGeneratesEmailAndReturnsTrueProvider
     * @param string $recipient
     * @param array $message
     * @param array $expectations
     */
    public function testSendGeneratesEmailAndReturnsTrue($recipient, $message, $expectations)
    {
        foreach ($expectations as $method => $value) {
            if ($value) {
                $this->smtpMailer->expects($this->once())->method($method)->with($this->equalTo($value));
            } else {
                $this->smtpMailer->expects($this->never())->method($method);
            }
        }
        $actualRecipient = '';
        $this->smtpMailer->expects($this->once())->method('addRecipientsTo')->willReturnCallback(function(EmailIdentity $recipient) use (&$actualRecipient) {
            $actualRecipient = $recipient->getEmail();
        });
        $this->smtpMailer->expects($this->once())->method('send');
        $result = $this->transport->send($recipient, $message);
        $this->assertTrue($result);
        $this->assertEquals($recipient, $actualRecipient);
    }

    /**
     * If mailer send throws we should get false
     *
     * @covers CarrierEmailTransport::send
     */
    public function testSendReturnsFalseOnMailerException()
    {
        $this->smtpMailer->expects($this->once())->method('send')->will($this->throwException(new MailerException()));

        $result = $this->transport->send('test@test.com', array(
            'title' => 'title',
            'text' => 'text',
            'html' => 'html',
        ));

        $this->assertFalse($result);
    }
}

/**
 * Mocking static methods
 *
 * @package Sugarcrm\SugarcrmTests\modules\CarrierEmail
 */
class OutboundEmailConfigurationPeerCRYS1265 extends OutboundEmailConfigurationPeer
{
    /** @var mixed value which will be returned by getSystemDefaultMailConfiguration */
    public static $getSystemDefaultMailConfigurationReturn = null;

    /** @var mixed value which will be returned by isMailConfigurationValid */
    public static $isMailConfigurationValidReturn = null;

    /** @var array args which were passed to getSystemDefaultMailConfiguration */
    public static $getSystemDefaultMailConfigurationArgs = array();

    /** @var array args which were passed to isMailConfigurationValid */
    public static $isMailConfigurationValidArgs = array();

    /**
     * @inheritDoc
     */
    public static function getSystemDefaultMailConfiguration(Localization $locale = null, $charset = null)
    {
        static::$getSystemDefaultMailConfigurationArgs = func_get_args();
        return static::$getSystemDefaultMailConfigurationReturn;
    }

    /**
     * @inheritDoc
     */
    public static function isMailConfigurationValid(OutboundEmailConfiguration $configuration)
    {
        static::$isMailConfigurationValidArgs = func_get_args();
        return static::$isMailConfigurationValidReturn;
    }
}

/**
 * Mocking static methods
 *
 * @package Sugarcrm\SugarcrmTests\modules\CarrierEmail
 */
class MailerFactoryCRYS1265 extends MailerFactory
{
    /** @var SmtpMailer value which will be returned by getSystemDefaultMailer */
    public static $getSystemDefaultMailerReturn = null;

    /**
     * @inheritDoc
     */
    public static function getSystemDefaultMailer()
    {
        return static::$getSystemDefaultMailerReturn;
    }
}
