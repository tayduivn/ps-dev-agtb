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

require_once "modules/Mailer/SMTPProxy.php";

/**
 * @group email
 * @group mailer
 */
class SMTPProxyTest extends Sugar_PHPUnit_Framework_TestCase
{
    private $logger;

    public function setUp()
    {
        SugarTestHelper::setUp("current_user");
        $this->logger   = $GLOBALS["log"]; // save the original logger
    }

    public function tearDown()
    {
        $GLOBALS["log"] = $this->logger; // restore the original logger
        SugarTestHelper::tearDown();
    }

    public function testHello_ConnectedReturnsTrue_SendHelloReturnsTrue_HandleErrorDoesNotLogAnyErrors()
    {
        $GLOBALS["log"] = new SugarMockLogger();

        $mockSmtpProxy = $this->getMock("SMTPProxy", array("Connected", "SendHello"));
        $mockSmtpProxy->expects($this->any())
                      ->method("Connected")
                      ->will($this->returnValue(true));
        $mockSmtpProxy->expects($this->any())
                      ->method("SendHello")
                      ->will($this->returnValue(true));

        $actual = $mockSmtpProxy->Hello();
        $this->assertTrue($actual, "Hello should have run to completion without error.");

        $expected = 0;
        $actual   = $GLOBALS["log"]->getMessageCount();
        $this->assertEquals($expected, $actual, "The logger should not have any errors to log.");
    }

    public function testHello_ConnectedReturnsFalse_HelloProducesAnErrorWithoutAnErrorCode_HandleErrorLogsTheErrorWithLevelWarn()
    {
        // SMTPProxy::handleError should log a warning
        $GLOBALS["log"] = $this->getMock("SugarMockLogger", array("__call"));
        $GLOBALS["log"]->expects($this->once())
                       ->method("__call")
                       ->with($this->equalTo("warn"));

        $mockSmtpProxy = $this->getMock("SMTPProxy", array("Connected"));
        $mockSmtpProxy->expects($this->any())
                      ->method("Connected")
                      ->will($this->returnValue(false));

        $actual = $mockSmtpProxy->Hello();
        $this->assertFalse($actual, "Connected returned false so Hello should return false.");
    }

    public function testHello_ConnectedReturnsTrue_SendHelloProducesAnErrorWithAnErrorCode_HandleErrorLogsTheErrorWithLevelError()
    {
        // SMTPProxy::handleError should log an 'error'
        $GLOBALS["log"] = $this->getMock("SugarMockLogger", array("__call"));
        $GLOBALS["log"]->expects($this->once())
                       ->method("__call")
                       ->with($this->equalTo("error"));

        $mockSmtpProxy = $this->getMock(
            "SMTPProxy",
            array(
                 "Connected",
                 "client_send",
                 "get_lines",
            )
        );
        $mockSmtpProxy->expects($this->any())
                      ->method("Connected")
                      ->will($this->returnValue(true));
        $mockSmtpProxy->expects($this->any())
                      ->method("client_send")
                      ->will($this->returnValue(true));
        $mockSmtpProxy->expects($this->any())
                      ->method("get_lines")
                      ->will($this->returnValue("501 Syntax error in parameters or arguments"));

        $actual = $mockSmtpProxy->Hello();
        $this->assertFalse($actual, "SendHello returned false so Hello should return false.");
    }
}
