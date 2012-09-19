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

require_once "modules/Mailer/SimpleMailer.php";

class SimpleMailerTest extends Sugar_PHPUnit_Framework_TestCase
{
    private $mockMailerConfig;

    public function setUp() {
        $this->mockMailerConfig = self::getMock(
            "SmtpMailerConfiguration"
        );
    }

    /**
     * @group mailer
     */
    public function testClearRecipients_ClearToAndBccButNotCc() {
        $mockMailer = self::getMock(
            "SimpleMailer",
            array(
                 "clearRecipientsTo",
                 "clearRecipientsCc",
                 "clearRecipientsBcc"
            ),
            array($this->mockMailerConfig)
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
            ),
            array($this->mockMailerConfig)
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
            ),
            array($this->mockMailerConfig)
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
            ),
            array($this->mockMailerConfig)
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
            ),
            array($this->mockMailerConfig)
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
            ),
            array($this->mockMailerConfig)
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
            ),
            array($this->mockMailerConfig)
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
            ),
            array($this->mockMailerConfig)
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
            ),
            array($this->mockMailerConfig)
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
