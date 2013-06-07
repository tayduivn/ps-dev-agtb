<?php
/*********************************************************************************
 * The contents of this file are subject to the SugarCRM Master Subscription
 * Agreement ("License") which can be viewed at
 * http://www.sugarcrm.com/crm/master-subscription-agreement
 * By installing or using this file, You have unconditionally agreed to the
 * terms and conditions of the License, and You may not use this file except in
 * compliance with the License.  Under the terms of the license, You shall not,
 * among other things: 1) sublicense, resell, rent, lease, redistribute, assign
 * or otherwise transfer Your rights to the Software, and 2) use the Software
 * for timesharing or service bureau purposes such as hosting the Software for
 * commercial gain and/or for the benefit of a third party.  Use of the Software
 * may be subject to applicable fees and any use of the Software without first
 * paying applicable fees is strictly prohibited.  You do not have the right to
 * remove SugarCRM copyrights from the source code or user interface.
 *
 * All copies of the Covered Code must include on each user interface screen:
 *  (i) the "Powered by SugarCRM" logo and
 *  (ii) the SugarCRM copyright notice
 * in the same form as they appear in the distribution.  See full license for
 * requirements.
 *
 * Your Warranty, Limitations of liability and Indemnity are expressly stated
 * in the License.  Please refer to the License for the specific language
 * governing these rights and limitations under the License.  Portions created
 * by SugarCRM are Copyright (C) 2004-2012 SugarCRM, Inc.; All Rights Reserved.
 ********************************************************************************/

require_once("modules/Emails/clients/base/api/MailApi.php");

/**
 * @group api
 * @group email
 */
class MailApiTest extends Sugar_PHPUnit_Framework_TestCase
{
    private $api,
            $mailApi;

    public function setUp()
    {
        parent::setUp();
        SugarTestHelper::setUp("current_user");
        $this->api     = SugarTestRestUtilities::getRestServiceMock();
        $this->mailApi = $this->getMock("MailApi", array("initMailRecord", "getEmailRecipientsService"));
    }

    public function tearDown()
    {
        SugarTestUserUtilities::removeAllCreatedAnonymousUsers();
        SugarTestHelper::tearDown();
        parent::tearDown();
    }

    public function testCreateMail_StatusIsSaveAsDraft_CallsMailRecordSaveAsDraft()
    {
        $args = array(
            "status" => "draft",
        );

        $expected = array(
            "SUCCESS" => true,
        );

        $mailRecordMock = $this->getMock("MailRecord", array("saveAsDraft"));
        $mailRecordMock->expects($this->once())
            ->method("saveAsDraft")
            ->will($this->returnValue($expected));

        $this->mailApi->expects($this->any())
            ->method("initMailRecord")
            ->will($this->returnValue($mailRecordMock));

        $actual = $this->mailApi->createMail($this->api, $args);
        $this->assertEquals($expected, $actual, "Should have returned the value from the mock MailRecord.");
    }

    public function testCreateMail_StatusIsReady_CallsMailRecordSend()
    {
        $args = array(
            "status"       => "ready",
            "email_config" => "foo",
        );

        $expected = array(
            "SUCCESS" => true,
        );

        $mailRecordMock = $this->getMock("MailRecord", array("send"));
        $mailRecordMock->expects($this->once())
            ->method("send")
            ->will($this->returnValue($expected));

        $this->mailApi->expects($this->any())
            ->method("initMailRecord")
            ->will($this->returnValue($mailRecordMock));

        $actual = $this->mailApi->createMail($this->api, $args);
        $this->assertEquals($expected, $actual, "Should have returned the value from the mock MailRecord.");
    }

    public function testCreateMail_StatusIsReadyAndEmailConfigIsEmpty_ThrowsException()
    {
        $args = array(
            "status" => "ready",
        );

        $this->setExpectedException("SugarApiExceptionRequestMethodFailure");
        $this->mailApi->createMail($this->api, $args);
    }

    public function testCreateMail_StatusIsInvalid_ThrowsException()
    {
        $args = array(
            "status" => "foo",
        );

        $this->setExpectedException("SugarApiExceptionRequestMethodFailure");
        $this->mailApi->createMail($this->api, $args);
    }

    public function testCreateMail_ResultSuccessIsFalse_ThrowsException()
    {
        $args = array(
            "status" => "draft",
        );

        $result = array(
            "SUCCESS" => false,
        );

        $mailRecordMock = $this->getMock("MailRecord", array("saveAsDraft"));
        $mailRecordMock->expects($this->once())
            ->method("saveAsDraft")
            ->will($this->returnValue($result));

        $this->mailApi->expects($this->any())
            ->method("initMailRecord")
            ->will($this->returnValue($mailRecordMock));

        $this->setExpectedException("SugarApiExceptionRequestMethodFailure");
        $this->mailApi->createMail($this->api, $args);
    }

    public function testCreateMail_SuccessKeyNotFoundInResult_ThrowsException()
    {
        $args = array(
            "status" => "draft",
        );

        $result = array();

        $mailRecordMock = $this->getMock("MailRecord", array("saveAsDraft"));
        $mailRecordMock->expects($this->once())
            ->method("saveAsDraft")
            ->will($this->returnValue($result));

        $this->mailApi->expects($this->any())
            ->method("initMailRecord")
            ->will($this->returnValue($mailRecordMock));

        $this->setExpectedException("SugarApiExceptionRequestMethodFailure");
        $this->mailApi->createMail($this->api, $args);
    }

    public function testCreateMail_EmailKeyFoundInResult_EmailIsSerializedToArray()
    {
        $args = array(
            "status" => "draft",
        );

        $email     = new Email();
        $email->id = "foo123";

        $result = array(
            "SUCCESS" => true,
            "EMAIL"   => $email,
        );

        $mailRecordMock = $this->getMock("MailRecord", array("saveAsDraft"));
        $mailRecordMock->expects($this->once())
            ->method("saveAsDraft")
            ->will($this->returnValue($result));

        $this->mailApi->expects($this->any())
            ->method("initMailRecord")
            ->will($this->returnValue($mailRecordMock));

        $response = $this->mailApi->createMail($this->api, $args);
        $expected = $email->toArray();
        $actual   = $response["EMAIL"];
        $this->assertEquals($expected, $actual, "Should have returned the Email object serialized as an array.");
    }

    public function testFindRecipients_NextOffsetIsLessThanTotalRecords_ReturnsRealNextOffset()
    {
        $args = array(
            "offset"  => 0,
            "max_num" => 5,
        );

        $emailRecipientsServiceMock = $this->getMock("EmailRecipientsService", array("findCount", "find"));
        $emailRecipientsServiceMock->expects($this->any())
            ->method("findCount")
            ->will($this->returnValue(10));
        $emailRecipientsServiceMock->expects($this->any())
            ->method("find")
            ->will($this->returnValue(array()));

        $this->mailApi->expects($this->any())
            ->method("getEmailRecipientsService")
            ->will($this->returnValue($emailRecipientsServiceMock));

        $response = $this->mailApi->findRecipients($this->api, $args);
        $expected = 5;
        $actual   = $response["next_offset"];
        $this->assertEquals($expected, $actual, "The next offset should be {$expected}.");
    }

    public function testFindRecipients_NextOffsetIsGreaterThanTotalRecords_ReturnsNextOffsetAsNegativeOne()
    {
        $args = array(
            "offset"  => 5,
            "max_num" => 5,
        );

        $emailRecipientsServiceMock = $this->getMock("EmailRecipientsService", array("findCount", "find"));
        $emailRecipientsServiceMock->expects($this->any())
            ->method("findCount")
            ->will($this->returnValue(4));
        $emailRecipientsServiceMock->expects($this->any())
            ->method("find")
            ->will($this->returnValue(array()));

        $this->mailApi->expects($this->any())
            ->method("getEmailRecipientsService")
            ->will($this->returnValue($emailRecipientsServiceMock));

        $response = $this->mailApi->findRecipients($this->api, $args);
        $expected = -1;
        $actual   = $response["next_offset"];
        $this->assertEquals($expected, $actual, "The next offset should be -1.");
    }

    public function testFindRecipients_OffsetIsEnd_ReturnsNextOffsetAsNegativeOne()
    {
        $args = array(
            "offset" => "end",
        );

        $emailRecipientsServiceMock = $this->getMock("EmailRecipientsService", array("findCount", "find"));
        $emailRecipientsServiceMock->expects($this->never())->method("findCount");
        $emailRecipientsServiceMock->expects($this->never())->method("find");

        $this->mailApi->expects($this->any())
            ->method("getEmailRecipientsService")
            ->will($this->returnValue($emailRecipientsServiceMock));

        $response = $this->mailApi->findRecipients($this->api, $args);
        $expected = -1;
        $actual   = $response["next_offset"];
        $this->assertEquals($expected, $actual, "The next offset should be -1.");
    }

    public function testFindRecipients_NoArguments_CallsFindCountAndFindWithDefaults()
    {
        $args = array();

        $emailRecipientsServiceMock = $this->getMock("EmailRecipientsService", array("findCount", "find"));
        $emailRecipientsServiceMock->expects($this->once())
            ->method("findCount")
            ->with($this->isEmpty(),
                $this->equalTo("LBL_DROPDOWN_LIST_ALL"))
            ->will($this->returnValue(0));
        $emailRecipientsServiceMock->expects($this->once())
            ->method("find")
            ->with($this->isEmpty(),
                $this->equalTo("LBL_DROPDOWN_LIST_ALL"),
                $this->isEmpty(),
                $this->equalTo(20),
                $this->equalTo(0))
            ->will($this->returnValue(array()));

        $this->mailApi->expects($this->any())
            ->method("getEmailRecipientsService")
            ->will($this->returnValue($emailRecipientsServiceMock));

        $response = $this->mailApi->findRecipients($this->api, $args);
    }

    public function testFindRecipients_HasAllArguments_CallsFindCountAndFindWithArguments()
    {
        $args = array(
            "q"           => "foo",
            "module_list" => "contacts",
            "order_by"    => "name,email:desc",
            "max_num"     => 5,
            "offset"      => 3,
        );

        $emailRecipientsServiceMock = $this->getMock("EmailRecipientsService", array("findCount", "find"));
        $emailRecipientsServiceMock->expects($this->once())
            ->method("findCount")
            ->with($this->equalTo($args["q"]),
                $this->equalTo($args["module_list"]))
            ->will($this->returnValue(0));
        $emailRecipientsServiceMock->expects($this->once())
            ->method("find")
            ->with($this->equalTo($args["q"]),
                $this->equalTo($args["module_list"]),
                $this->equalTo(array("name" => "ASC", "email" => "DESC")),
                $this->equalTo(5),
                $this->equalTo(3))
            ->will($this->returnValue(array()));

        $this->mailApi->expects($this->any())
            ->method("getEmailRecipientsService")
            ->will($this->returnValue($emailRecipientsServiceMock));

        $response = $this->mailApi->findRecipients($this->api, $args);
    }
}
