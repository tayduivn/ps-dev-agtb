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

require_once "modules/Mailer/SmtpMailer.php";

/**
 * @group email
 * @group mailer
 */
class SmtpMailerTest extends Sugar_PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        SugarTestHelper::setUp("current_user");
    }

    public function tearDown()
    {
        SugarTestHelper::tearDown();
    }
    
    public function testGetMailTransmissionProtocol_ReturnsSmtp()
    {
        $mailer   = new SmtpMailer(new OutboundSmtpEmailConfiguration($GLOBALS["current_user"]));
        $expected = SmtpMailer::MailTransmissionProtocol;
        $actual   = $mailer->getMailTransmissionProtocol();
        self::assertEquals(
            $expected,
            $actual,
            "The SmtpMailer should have {$expected} for its mail transmission protocol"
        );
    }

    public function testClearRecipients_ClearToAndBccButNotCc()
    {
        $mockMailer = self::getMock(
            "SmtpMailer",
            array(
                 "clearRecipientsTo",
                 "clearRecipientsCc",
                 "clearRecipientsBcc"
            ),
            array(new OutboundSmtpEmailConfiguration($GLOBALS["current_user"]))
        );

        $mockMailer->expects(self::once())
            ->method("clearRecipientsTo");

        $mockMailer->expects(self::never())
            ->method("clearRecipientsCc");

        $mockMailer->expects(self::once())
            ->method("clearRecipientsBcc");

        $mockMailer->clearRecipients(true, false, true);
    }

    public function testSend_PHPMailerSmtpConnectThrowsException_ConnectToHostCatchesAndThrowsMailerException()
    {
        $mockPhpMailerProxy = self::getMock("PHPMailerProxy", array("smtpConnect"));

        $mockPhpMailerProxy->expects(self::once())
            ->method("smtpConnect")
            ->will(self::throwException(new phpmailerException()));

        $mockMailer = self::getMock(
            "SmtpMailer",
            array(
                 "generateMailer",
                 "transferConfigurations",
                 "transferHeaders",
                 "transferRecipients",
                 "transferBody",
                 "transferAttachments",
            ),
            array(new OutboundSmtpEmailConfiguration($GLOBALS["current_user"]))
        );

        $mockMailer->expects(self::once())
            ->method("generateMailer")
            ->will(self::returnValue($mockPhpMailerProxy));

        $mockMailer->expects(self::once())
            ->method("transferConfigurations")
            ->will(self::returnValue(true));

        // connectToHost should fail between transferConfigurations and transferHeaders

        $mockMailer->expects(self::never())
            ->method("transferHeaders");

        $mockMailer->expects(self::never())
            ->method("transferRecipients");

        $mockMailer->expects(self::never())
            ->method("transferBody");

        $mockMailer->expects(self::never())
            ->method("transferAttachments");

        self::setExpectedException("MailerException");
        $mockMailer->send();
    }

    public function testSend_MessageIdHeaderIsSet()
    {
        $config = new OutboundSmtpEmailConfiguration($GLOBALS['current_user']);
        $config->setHostname('mycompany.com');
        $config->setLocale($GLOBALS['locale']);

        $phpMailerProxy = $this->getMockBuilder('PHPMailerProxy')
            ->setMethods(array('postSend'))
            ->getMock();
        $phpMailerProxy->expects($this->once())->method('postSend')->willReturn(true);
        $phpMailerProxy->addAddress('foo@bar.com');
        $phpMailerProxy->Body = 'baz';

        $mailer = $this->getMockBuilder('SmtpMailer')
            ->setConstructorArgs(array($config))
            ->setMethods(array(
                'generateMailer',
                'connectToHost',
                'transferRecipients',
                'transferBody',
                'transferAttachments',
            ))
            ->getMock();
        $mailer->expects($this->once())->method('generateMailer')->willReturn($phpMailerProxy);
        $mailer->expects($this->once())->method('connectToHost')->willReturn(true);
        $mailer->expects($this->once())->method('transferRecipients')->willReturn(true);
        $mailer->expects($this->once())->method('transferBody')->willReturn(true);
        $mailer->expects($this->once())->method('transferAttachments')->willReturn(true);
        $mailer->setHeader(EmailHeaders::From, new EmailIdentity('sales@mycompany.com'));
        $mailer->setSubject('biz');

        $id = create_guid();
        $mailer->setMessageId($id);
        $expected = $mailer->getHeader(EmailHeaders::MessageId);

        $mailer->send();

        $actual = $mailer->getHeader(EmailHeaders::MessageId);
        $this->assertSame($expected, $actual);
    }

    public function testSend_MessageIdHeaderIsNotSet()
    {
        $config = new OutboundSmtpEmailConfiguration($GLOBALS['current_user']);
        $config->setHostname('mycompany.com');
        $config->setLocale($GLOBALS['locale']);

        $phpMailerProxy = $this->getMockBuilder('PHPMailerProxy')
            ->setMethods(array('postSend'))
            ->getMock();
        $phpMailerProxy->expects($this->once())->method('postSend')->willReturn(true);
        $phpMailerProxy->addAddress('foo@bar.com');
        $phpMailerProxy->Body = 'baz';

        $mailer = $this->getMockBuilder('SmtpMailer')
            ->setConstructorArgs(array($config))
            ->setMethods(
                array(
                    'generateMailer',
                    'connectToHost',
                    'transferRecipients',
                    'transferBody',
                    'transferAttachments',
                )
            )
            ->getMock();
        $mailer->expects($this->once())->method('generateMailer')->willReturn($phpMailerProxy);
        $mailer->expects($this->once())->method('connectToHost')->willReturn(true);
        $mailer->expects($this->once())->method('transferRecipients')->willReturn(true);
        $mailer->expects($this->once())->method('transferBody')->willReturn(true);
        $mailer->expects($this->once())->method('transferAttachments')->willReturn(true);
        $mailer->setHeader(EmailHeaders::From, new EmailIdentity('sales@mycompany.com'));
        $mailer->setSubject('biz');

        $this->assertEmpty($mailer->getHeader(EmailHeaders::MessageId), 'Should be empty before sending');

        $mailer->send();

        $this->assertNotEmpty($mailer->getHeader(EmailHeaders::MessageId), 'Should not be empty after sending');
    }

    public function testSend_PHPMailerSetFromThrowsException_TransferHeadersThrowsMailerException()
    {
        $packagedEmailHeaders = array(
            EmailHeaders::From => array(
                "foo@bar.com",
                null,
            ),
        );
        $mockEmailHeaders     = self::getMock("EmailHeaders", array("packageHeaders"));

        $mockEmailHeaders->expects(self::once())
            ->method("packageHeaders")
            ->will(self::returnValue($packagedEmailHeaders));

        $mockPhpMailerProxy = self::getMock("PHPMailerProxy", array("setFrom"));

        $mockPhpMailerProxy->expects(self::once())
            ->method("setFrom")
            ->will(self::throwException(new phpmailerException()));

        $mockMailer = self::getMock(
            "SmtpMailer",
            array(
                 "generateMailer",
                 "transferConfigurations",
                 "connectToHost",
                 "transferRecipients",
                 "transferBody",
                 "transferAttachments",
            ),
            array(new OutboundSmtpEmailConfiguration($GLOBALS["current_user"]))
        );

        $mockMailer->setHeaders($mockEmailHeaders);

        $mockMailer->expects(self::once())
            ->method("generateMailer")
            ->will(self::returnValue($mockPhpMailerProxy));

        $mockMailer->expects(self::once())
            ->method("transferConfigurations")
            ->will(self::returnValue(true));

        $mockMailer->expects(self::once())
            ->method("connectToHost")
            ->will(self::returnValue(true));

        // transferHeaders should fail between connectToHost and transferRecipients

        $mockMailer->expects(self::never())
            ->method("transferRecipients");

        $mockMailer->expects(self::never())
            ->method("transferBody");

        $mockMailer->expects(self::never())
            ->method("transferAttachments");

        self::setExpectedException("MailerException");
        $mockMailer->send();
    }

    public function testSend_PHPMailerAddReplyToReturnsFalse_TransferHeadersThrowsMailerException()
    {
        $packagedEmailHeaders = array(
            EmailHeaders::ReplyTo => array(
                "foo@bar.com",
                null,
            ),
        );
        $mockEmailHeaders     = self::getMock("EmailHeaders", array("packageHeaders"));

        $mockEmailHeaders->expects(self::once())
            ->method("packageHeaders")
            ->will(self::returnValue($packagedEmailHeaders));

        $mockPhpMailerProxy = self::getMock("PHPMailerProxy", array("addReplyTo"));

        $mockPhpMailerProxy->expects(self::once())
            ->method("addReplyTo")
            ->will(self::returnValue(false));

        $mockMailer = self::getMock(
            "SmtpMailer",
            array(
                 "generateMailer",
                 "transferConfigurations",
                 "connectToHost",
                 "transferRecipients",
                 "transferBody",
                 "transferAttachments",
            ),
            array(new OutboundSmtpEmailConfiguration($GLOBALS["current_user"]))
        );

        $mockMailer->setHeaders($mockEmailHeaders);

        $mockMailer->expects(self::once())
            ->method("generateMailer")
            ->will(self::returnValue($mockPhpMailerProxy));

        $mockMailer->expects(self::once())
            ->method("transferConfigurations")
            ->will(self::returnValue(true));

        $mockMailer->expects(self::once())
            ->method("connectToHost")
            ->will(self::returnValue(true));

        // transferHeaders should fail between connectToHost and transferRecipients

        $mockMailer->expects(self::never())
            ->method("transferRecipients");

        $mockMailer->expects(self::never())
            ->method("transferBody");

        $mockMailer->expects(self::never())
            ->method("transferAttachments");

        self::setExpectedException("MailerException");
        $mockMailer->send();
    }

    public function testSend_PHPMailerAddAttachmentThrowsException_TransferAttachmentsThrowsMailerException()
    {
        $mockLocale = self::getMock("Localization", array("translateCharset"));
        $mockLocale->expects(self::any())
            ->method("translateCharset")
            ->will(self::returnValue("foobar")); // the filename that Localization::translateCharset will return

        $mailerConfiguration = new OutboundSmtpEmailConfiguration($GLOBALS["current_user"]);
        $mailerConfiguration->setLocale($mockLocale);

        $mockPhpMailerProxy = self::getMock("PHPMailerProxy", array("addAttachment"));

        $mockPhpMailerProxy->expects(self::once())
            ->method("addAttachment")
            ->will(self::throwException(new phpmailerException()));

        $mockMailer = self::getMock(
            "SmtpMailer",
            array(
                 "generateMailer",
                 "transferConfigurations",
                 "connectToHost",
                 "transferHeaders",
                 "transferRecipients",
                 "transferBody",
            ),
            array($mailerConfiguration)
        );

        $attachment = new Attachment("/foo/bar.txt");
        $mockMailer->addAttachment($attachment);

        $mockMailer->expects(self::once())
            ->method("generateMailer")
            ->will(self::returnValue($mockPhpMailerProxy));

        $mockMailer->expects(self::once())
            ->method("transferConfigurations")
            ->will(self::returnValue(true));

        $mockMailer->expects(self::once())
            ->method("connectToHost")
            ->will(self::returnValue(true));

        $mockMailer->expects(self::once())
            ->method("transferRecipients")
            ->will(self::returnValue(true));

        $mockMailer->expects(self::once())
            ->method("transferBody")
            ->will(self::returnValue(true));

        // transferAttachments should fail after transferBody and before PHPMailer's Send is called

        self::setExpectedException("MailerException");
        $mockMailer->send();
    }

    public function testSend_PHPMailerAddEmbeddedImageReturnsFalse_TransferAttachmentsThrowsMailerException()
    {
        $mockLocale = self::getMock("Localization", array("translateCharset"));
        $mockLocale->expects(self::any())
            ->method("translateCharset")
            ->will(self::returnValue("foobar")); // the filename that Localization::translateCharset will return

        $mailerConfiguration = new OutboundSmtpEmailConfiguration($GLOBALS["current_user"]);
        $mailerConfiguration->setLocale($mockLocale);

        $mockPhpMailerProxy = self::getMock("PHPMailerProxy", array("addEmbeddedImage"));

        $mockPhpMailerProxy->expects(self::once())
            ->method("addEmbeddedImage")
            ->will(self::returnValue(false));

        $mockMailer = self::getMock(
            "SmtpMailer",
            array(
                 "generateMailer",
                 "transferConfigurations",
                 "connectToHost",
                 "transferHeaders",
                 "transferRecipients",
                 "transferBody",
            ),
            array($mailerConfiguration)
        );

        $embeddedImage = new EmbeddedImage("foobar", "/foo/bar.txt");
        $mockMailer->addAttachment($embeddedImage);

        $mockMailer->expects(self::once())
            ->method("generateMailer")
            ->will(self::returnValue($mockPhpMailerProxy));

        $mockMailer->expects(self::once())
            ->method("transferConfigurations")
            ->will(self::returnValue(true));

        $mockMailer->expects(self::once())
            ->method("connectToHost")
            ->will(self::returnValue(true));

        $mockMailer->expects(self::once())
            ->method("transferRecipients")
            ->will(self::returnValue(true));

        $mockMailer->expects(self::once())
            ->method("transferBody")
            ->will(self::returnValue(true));

        // transferAttachments should fail after transferBody and before PHPMailer's Send is called

        self::setExpectedException("MailerException");
        $mockMailer->send();
    }

    public function testSend_PHPMailerSendThrowsException_SendCatchesItAndThrowsMailerException()
    {
        $mockPhpMailerProxy = self::getMock("PHPMailerProxy", array("send"));

        $mockPhpMailerProxy->expects(self::once())
            ->method("send")
            ->will(self::throwException(new phpmailerException()));

        $mockMailer = self::getMock(
            "SmtpMailer",
            array(
                 "generateMailer",
                 "transferConfigurations",
                 "connectToHost",
                 "transferHeaders",
                 "transferRecipients",
                 "transferBody",
                 "transferAttachments",
            ),
            array(new OutboundSmtpEmailConfiguration($GLOBALS["current_user"]))
        );

        $mockMailer->expects(self::once())
            ->method("generateMailer")
            ->will(self::returnValue($mockPhpMailerProxy));

        $mockMailer->expects(self::once())
            ->method("transferConfigurations")
            ->will(self::returnValue(true));

        $mockMailer->expects(self::once())
            ->method("connectToHost")
            ->will(self::returnValue(true));

        $mockMailer->expects(self::once())
            ->method("transferHeaders")
            ->will(self::returnValue(true));

        $mockMailer->expects(self::once())
            ->method("transferRecipients")
            ->will(self::returnValue(true));

        $mockMailer->expects(self::once())
            ->method("transferBody")
            ->will(self::returnValue(true));

        $mockMailer->expects(self::once())
            ->method("transferAttachments")
            ->will(self::returnValue(true));

        self::setExpectedException("MailerException");
        $mockMailer->send();
    }

    public function testSend_AllMethodCallsAreSuccessful_ReturnsSentMessage()
    {
        $mockPhpMailerProxy = self::getMock("PHPMailerProxy", array("send", 'getSentMIMEMessage'));

        $mockPhpMailerProxy->expects(self::once())
            ->method("send")
            ->will(self::returnValue(true));

        $expected = 'the sent email';
        $mockPhpMailerProxy->expects($this->once())->method('getSentMIMEMessage')->willReturn($expected);

        $mockMailer = self::getMock(
            "SmtpMailer",
            array(
                 "generateMailer",
                 "transferConfigurations",
                 "connectToHost",
                 "transferHeaders",
                 "transferRecipients",
                 "transferBody",
                 "transferAttachments",
            ),
            array(new OutboundSmtpEmailConfiguration($GLOBALS["current_user"]))
        );

        $mockMailer->expects(self::once())
            ->method("generateMailer")
            ->will(self::returnValue($mockPhpMailerProxy));

        $mockMailer->expects(self::once())
            ->method("transferConfigurations")
            ->will(self::returnValue(true));

        $mockMailer->expects(self::once())
            ->method("connectToHost")
            ->will(self::returnValue(true));

        $mockMailer->expects(self::once())
            ->method("transferHeaders")
            ->will(self::returnValue(true));

        $mockMailer->expects(self::once())
            ->method("transferRecipients")
            ->will(self::returnValue(true));

        $mockMailer->expects(self::once())
            ->method("transferBody")
            ->will(self::returnValue(true));

        $mockMailer->expects(self::once())
            ->method("transferAttachments")
            ->will(self::returnValue(true));

        $actual = $mockMailer->send();
        $this->assertEquals(
            $expected,
            $actual,
            'The sent MIME message should have been returned as confirmation for the send'
        );
    }
}
