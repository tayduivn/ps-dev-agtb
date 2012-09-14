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

require_once 'modules/Mailer/SimpleMailer.php';

class SimpleMailerTest extends Sugar_PHPUnit_Framework_TestCase
{
    /**
     * @group mailer
     */
    public function testLoadDefaultConfigs_SmtpPortAndCharsetAreBothReset_WordwrapAndSmtpAuthenticateAreBothInitialized() {
        $mailer = new SimpleMailer();

        // change the default configs in order to show that loadDefaultConfigs will reset them
        // this effectively tests SimpleMailer::setConfig() as well
        $mailer->setConfig("charset", "asdf"); // some asinine value that wouldn't actually be used
        $mailer->setConfig("smtp.port", 9000); // should not match the default

        // test that the charset has been changed from its default
        $expected = "asdf";
        $actual   = $mailer->getConfig("charset");
        self::assertEquals($expected, $actual, "The charset should have been reset to {$expected}");

        // test that the smtp.port has been changed from its default
        $expected = 9000;
        $actual   = $mailer->getConfig("smtp.port");
        self::assertEquals($expected, $actual, "The smtp.port should have been reset to {$expected}");

        $mailer->loadDefaultConfigs();

        // test that the charset has been returned to its default
        $expected = "utf-8";
        $actual   = $mailer->getConfig("charset");
        self::assertEquals($expected, $actual, "The charset should have been reset to {$expected}");

        // test that the smtp.port has been returned its default
        $expected = 25;
        $actual   = $mailer->getConfig("smtp.port");
        self::assertEquals($expected, $actual, "The smtp.port should have been reset to {$expected}");

        // test that the wordwrap has been initialized correctly
        $expected = 996;
        $actual   = $mailer->getConfig("wordwrap");
        self::assertEquals($expected, $actual, "The wordwrap should have been initialized to {$expected}");

        // test that the smtp.authenticate has been initialized correctly
        $actual = $mailer->getConfig("smtp.authenticate");
        self::assertFalse($actual, "The smtp.authenticate should have been initialized to false");
    }

    /**
     * @group mailer
     */
    public function testMergeConfigs_NewConfigAddedToDefaultConfigs() {
        $mailer = new SimpleMailer();

        $additionalConfigs = array(
            "foo" => "bar",
        );
        $mailer->mergeConfigs($additionalConfigs);

        $expected = "utf-8";
        $actual   = $mailer->getConfig("charset");
        self::assertEquals($expected, $actual, "The charset should have been {$expected}");

        $expected = "bar";
        $actual   = $mailer->getConfig("foo");
        self::assertEquals($expected, $actual, "The foo should have been {$expected}");
    }

    /**
     * @group mailer
     */
    public function testMergeConfigs_OverwriteExistingConfig() {
        $mailer = new SimpleMailer();

        $expected          = "iso-8559-1";
        $additionalConfigs = array(
            "charset" => $expected,
        );
        $mailer->mergeConfigs($additionalConfigs);

        $actual = $mailer->getConfig("charset");
        self::assertEquals($expected, $actual, "The charset should have been {$expected}");
    }

    /**
     * @group mailer
     */
    public function testSetConfigs_ReplaceDefaultConfigsWithNewConfigs() {
        $mailer = new SimpleMailer();

        $newConfigs = array(
            "foo" => "bar",
        );
        $mailer->setConfigs($newConfigs);

        $expected = "bar";
        $actual   = $mailer->getConfig("foo");
        self::assertEquals($expected, $actual, "The foo should have been {$expected}");

        $exceptionWasCaught = false;

        try {
            $actual = $mailer->getConfig("charset"); // hopefully this default no longer exists
        } catch (MailerException $me) {
            $exceptionWasCaught = true;
        }

        if (!$exceptionWasCaught) {
            self::fail("A MailerException should have been raised because charset is an invalid config");
        }
    }

    /**
     * @group mailer
     */
    public function testClearRecipients_ClearToAndBccButNotCc() {
        $mockMailer = self::getMock(
            "SimpleMailer",
            array("clearRecipientsTo", "clearRecipientsCc", "clearRecipientsBcc")
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
    public function testSend_TransferConfigurationsThrowsAnException() {
        $mockMailer = self::getMock(
            "SimpleMailer",
            array(
                 "transferConfigurations",
                 "connectToHost",
                 "transferHeaders",
                 "transferRecipients",
                 "transferBody",
                 "transferAttachments",
            )
        );

        $mockMailer->expects(self::any())
            ->method("transferConfigurations")
            ->will(self::throwException(new MailerException()));

        $mockMailer->expects(self::never())
            ->method("connectToHost");

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
    public function testSend_ConnectToHostThrowsAnException() {
        $mockMailer = self::getMock(
            "SimpleMailer",
            array(
                 "transferConfigurations",
                 "connectToHost",
                 "transferHeaders",
                 "transferRecipients",
                 "transferBody",
                 "transferAttachments",
            )
        );

        $mockMailer->expects(self::any())
            ->method("transferConfigurations")
            ->will(self::returnValue(true));

        $mockMailer->expects(self::any())
            ->method("connectToHost")
            ->will(self::throwException(new MailerException()));

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
    public function testSend_TransferHeadersThrowsAnException() {
        $mockMailer = self::getMock(
            "SimpleMailer",
            array(
                 "transferConfigurations",
                 "connectToHost",
                 "transferHeaders",
                 "transferRecipients",
                 "transferBody",
                 "transferAttachments",
            )
        );

        $mockMailer->expects(self::any())
            ->method("transferConfigurations")
            ->will(self::returnValue(true));

        $mockMailer->expects(self::any())
            ->method("connectToHost")
            ->will(self::returnValue(true));

        $mockMailer->expects(self::any())
            ->method("transferHeaders")
            ->will(self::throwException(new MailerException()));

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
    public function testSend_TransferRecipientsThrowsAnException() {
        $mockMailer = self::getMock(
            "SimpleMailer",
            array(
                 "transferConfigurations",
                 "connectToHost",
                 "transferHeaders",
                 "transferRecipients",
                 "transferBody",
                 "transferAttachments",
            )
        );

        $mockMailer->expects(self::any())
            ->method("transferConfigurations")
            ->will(self::returnValue(true));

        $mockMailer->expects(self::any())
            ->method("connectToHost")
            ->will(self::returnValue(true));

        $mockMailer->expects(self::any())
            ->method("transferHeaders")
            ->will(self::returnValue(true));

        $mockMailer->expects(self::any())
            ->method("transferRecipients")
            ->will(self::throwException(new MailerException()));

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
    public function testSend_TransferBodyThrowsAnException() {
        $mockMailer = self::getMock(
            "SimpleMailer",
            array(
                 "transferConfigurations",
                 "connectToHost",
                 "transferHeaders",
                 "transferRecipients",
                 "transferBody",
                 "transferAttachments",
            )
        );

        $mockMailer->expects(self::any())
            ->method("transferConfigurations")
            ->will(self::returnValue(true));

        $mockMailer->expects(self::any())
            ->method("connectToHost")
            ->will(self::returnValue(true));

        $mockMailer->expects(self::any())
            ->method("transferHeaders")
            ->will(self::returnValue(true));

        $mockMailer->expects(self::any())
            ->method("transferRecipients")
            ->will(self::returnValue(true));

        $mockMailer->expects(self::any())
            ->method("transferBody")
            ->will(self::throwException(new MailerException()));

        $mockMailer->expects(self::never())
            ->method("transferAttachments");

        self::setExpectedException("MailerException");
        $mockMailer->send();
    }

    /**
     * @group mailer
     */
    public function testSend_TransferAttachmentsThrowsAnException() {
        $mockMailer = self::getMock(
            "SimpleMailer",
            array(
                 "transferConfigurations",
                 "connectToHost",
                 "transferHeaders",
                 "transferRecipients",
                 "transferBody",
                 "transferAttachments",
            )
        );

        $mockMailer->expects(self::any())
            ->method("transferConfigurations")
            ->will(self::returnValue(true));

        $mockMailer->expects(self::any())
            ->method("connectToHost")
            ->will(self::returnValue(true));

        $mockMailer->expects(self::any())
            ->method("transferHeaders")
            ->will(self::returnValue(true));

        $mockMailer->expects(self::any())
            ->method("transferRecipients")
            ->will(self::returnValue(true));

        $mockMailer->expects(self::any())
            ->method("transferBody")
            ->will(self::returnValue(true));

        $mockMailer->expects(self::any())
            ->method("transferAttachments")
            ->will(self::throwException(new MailerException()));

        self::setExpectedException("MailerException");
        $mockMailer->send();
    }

    /**
 * @group mailer
 */
    public function testSend_PhpMailerSendThrowsAnException() {
        $mockPhpMailer = self::getMock("PHPMailer", array("Send"));

        $mockPhpMailer->expects(self::any())
            ->method("Send")
            ->will(self::throwException(new phpmailerException()));

        $mockMailer = self::getMock(
            "SimpleMailer",
            array(
                 "generateMailer",
                 "transferConfigurations",
                 "connectToHost",
                 "transferHeaders",
                 "transferRecipients",
                 "transferBody",
                 "transferAttachments",
            )
        );

        $mockMailer->expects(self::any())
            ->method("generateMailer")
            ->will(self::returnValue($mockPhpMailer));

        $mockMailer->expects(self::any())
            ->method("transferConfigurations")
            ->will(self::returnValue(true));

        $mockMailer->expects(self::any())
            ->method("connectToHost")
            ->will(self::returnValue(true));

        $mockMailer->expects(self::any())
            ->method("transferHeaders")
            ->will(self::returnValue(true));

        $mockMailer->expects(self::any())
            ->method("transferRecipients")
            ->will(self::returnValue(true));

        $mockMailer->expects(self::any())
            ->method("transferBody")
            ->will(self::returnValue(true));

        $mockMailer->expects(self::any())
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

        $mockPhpMailer->expects(self::any())
            ->method("Send")
            ->will(self::returnValue(true));

        $mockMailer = self::getMock(
            "SimpleMailer",
            array(
                 "generateMailer",
                 "transferConfigurations",
                 "connectToHost",
                 "transferHeaders",
                 "transferRecipients",
                 "transferBody",
                 "transferAttachments",
            )
        );

        $mockMailer->expects(self::any())
            ->method("generateMailer")
            ->will(self::returnValue($mockPhpMailer));

        $mockMailer->expects(self::any())
            ->method("transferConfigurations")
            ->will(self::returnValue(true));

        $mockMailer->expects(self::any())
            ->method("connectToHost")
            ->will(self::returnValue(true));

        $mockMailer->expects(self::any())
            ->method("transferHeaders")
            ->will(self::returnValue(true));

        $mockMailer->expects(self::any())
            ->method("transferRecipients")
            ->will(self::returnValue(true));

        $mockMailer->expects(self::any())
            ->method("transferBody")
            ->will(self::returnValue(true));

        $mockMailer->expects(self::any())
            ->method("transferAttachments")
            ->will(self::returnValue(true));

        $mockMailer->send();
    }
}
