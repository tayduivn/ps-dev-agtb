<?php
/********************************************************************************
 *The contents of this file are subject to the SugarCRM Professional End User License Agreement
 *("License") which can be viewed at http://www.sugarcrm.com/EULA.
 *By installing or using this file, You have unconditionally agreed to the terms and conditions of the License, and You may
 *not use this file except in compliance with the License. Under the terms of the license, You
 *shall not, among other things: 1) sublicense, resell, rent, lease, redistribute, assign or
 *otherwise transfer Your rights to the Software, and 2) use the Software for timesharing or
 *service bureau purposes such as hosting the Software for commercial gain and/or for the benefit
 *of a third party.  Use of the Software may be subject to applicable fees and any use of the
 *Software without first paying applicable fees is strictly prohibited.  You do not have the
 *right to remove SugarCRM copyrights from the source code or user interface.
 * All copies of the Covered Code must include on each user interface screen:
 * (i) the "Powered by SugarCRM" logo and
 * (ii) the SugarCRM copyright notice
 * in the same form as they appear in the distribution.  See full license for requirements.
 *Your Warranty, Limitations of liability and Indemnity are expressly stated in the License.  Please refer
 *to the License for the specific language governing these rights and limitations under the License.
 *Portions created by SugarCRM are Copyright (C) 2004 SugarCRM, Inc.; All Rights Reserved.
 ********************************************************************************/

require_once "modules/Mailer/SmtpMailer.php";

class SmtpMailerTest extends Sugar_PHPUnit_Framework_TestCase
{
    /**
     * @group mailer
     */
    public function testGetMailTransmissionProtocol_ReturnsSmtp() {
        $mailer   = new SmtpMailer(new SmtpMailerConfiguration());
        $expected = SmtpMailer::MailTransmissionProtocol;
        $actual   = $mailer->getMailTransmissionProtocol();
        self::assertEquals(
            $expected,
            $actual,
            "The SmtpMailer should have {$expected} for its mail transmission protocol"
        );
    }

    /**
     * @group mailer
     */
    public function testClearRecipients_ClearToAndBccButNotCc() {
        $mockMailer = self::getMock(
            "SmtpMailer",
            array(
                 "clearRecipientsTo",
                 "clearRecipientsCc",
                 "clearRecipientsBcc"
            ),
            array(new SmtpMailerConfiguration())
        );

        $mockMailer->expects(self::once())
            ->method("clearRecipientsTo");

        $mockMailer->expects(self::never())
            ->method("clearRecipientsCc");

        $mockMailer->expects(self::once())
            ->method("clearRecipientsBcc");

        $mockMailer->clearRecipients(true, false, true);
    }

    /**
     * @group mailer
     */
    public function testSend_PHPMailerSmtpConnectThrowsException_ConnectToHostCatchesAndThrowsMailerException() {
        $mockPhpMailer = self::getMock(
            "PHPMailer",
            array("SmtpConnect"),
            array(true) // use PHPMailer with exceptions
        );

        $mockPhpMailer->expects(self::once())
            ->method("SmtpConnect")
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
            array(new SmtpMailerConfiguration())
        );

        $mockMailer->expects(self::once())
            ->method("generateMailer")
            ->will(self::returnValue($mockPhpMailer));

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

    /**
     * @group mailer
     */
    public function testSend_PHPMailerSetFromThrowsException_TransferHeadersThrowsMailerException() {
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

        $mockPhpMailer = self::getMock(
            "PHPMailer",
            array("SetFrom"),
            array(true) // use PHPMailer with exceptions
        );

        $mockPhpMailer->expects(self::once())
            ->method("SetFrom")
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
            array(new SmtpMailerConfiguration())
        );

        $mockMailer->setHeaders($mockEmailHeaders);

        $mockMailer->expects(self::once())
            ->method("generateMailer")
            ->will(self::returnValue($mockPhpMailer));

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

    /**
     * @group mailer
     */
    public function testSend_PHPMailerAddReplyToReturnsFalse_TransferHeadersThrowsMailerException() {
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

        $mockPhpMailer = self::getMock(
            "PHPMailer",
            array("AddReplyTo"),
            array(true) // use PHPMailer with exceptions
        );

        $mockPhpMailer->expects(self::once())
            ->method("AddReplyTo")
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
            array(new SmtpMailerConfiguration())
        );

        $mockMailer->setHeaders($mockEmailHeaders);

        $mockMailer->expects(self::once())
            ->method("generateMailer")
            ->will(self::returnValue($mockPhpMailer));

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

    /**
     * @group mailer
     */
    public function testSend_NoMessageParts_TransferBodyThrowsMailerException() {
        $mockMailer = self::getMock(
            "SmtpMailer",
            array(
                 "transferConfigurations",
                 "connectToHost",
                 "transferHeaders",
                 "transferRecipients",
                 "transferAttachments",
            ),
            array(new SmtpMailerConfiguration())
        );

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

        // transferBody should fail between transferRecipients and transferAttachments

        $mockMailer->expects(self::never())
            ->method("transferAttachments");

        self::setExpectedException("MailerException");
        $mockMailer->send();
    }

    /**
     * @group mailer
     */
    public function testSend_PHPMailerAddAttachmentThrowsException_TransferAttachmentsThrowsMailerException() {
        $mockPhpMailer = self::getMock(
            "PHPMailer",
            array("AddAttachment"),
            array(true) // use PHPMailer with exceptions
        );

        $mockPhpMailer->expects(self::once())
            ->method("AddAttachment")
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
            array(new SmtpMailerConfiguration())
        );

        $attachment = new Attachment("/foo/bar.txt");
        $mockMailer->addAttachment($attachment);

        $mockMailer->expects(self::once())
            ->method("generateMailer")
            ->will(self::returnValue($mockPhpMailer));

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

    /**
     * @group mailer
     */
    public function testSend_PHPMailerAddEmbeddedImageReturnsFalse_TransferAttachmentsThrowsMailerException() {
        $mockPhpMailer = self::getMock(
            "PHPMailer",
            array("AddEmbeddedImage"),
            array(true) // use PHPMailer with exceptions
        );

        $mockPhpMailer->expects(self::once())
            ->method("AddEmbeddedImage")
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
            array(new SmtpMailerConfiguration())
        );

        $embeddedImage = new EmbeddedImage("/foo/bar.txt", "foobar");
        $mockMailer->addEmbeddedImage($embeddedImage);

        $mockMailer->expects(self::once())
            ->method("generateMailer")
            ->will(self::returnValue($mockPhpMailer));

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

    /**
     * @group mailer
     */
    public function testSend_PhpMailerSendThrowsException_SendCatchesItAndThrowsMailerException() {
        $mockPhpMailer = self::getMock("PHPMailer", array("Send"));

        $mockPhpMailer->expects(self::once())
            ->method("Send")
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
            array(new SmtpMailerConfiguration())
        );

        $mockMailer->expects(self::once())
            ->method("generateMailer")
            ->will(self::returnValue($mockPhpMailer));

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

    /**
     * @group mailer
     */
    public function testSend_AllMethodCallsAreSuccessful_NoExceptionsThrown() {
        $mockPhpMailer = self::getMock("PHPMailer", array("Send"));

        $mockPhpMailer->expects(self::once())
            ->method("Send")
            ->will(self::returnValue(true));

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
            array(new SmtpMailerConfiguration())
        );

        $mockMailer->expects(self::once())
            ->method("generateMailer")
            ->will(self::returnValue($mockPhpMailer));

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

        $mockMailer->send();
    }
}
