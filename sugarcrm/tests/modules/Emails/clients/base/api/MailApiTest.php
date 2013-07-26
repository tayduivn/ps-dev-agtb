<?php
/*
 * By installing or using this file, you are confirming on behalf of the entity
 * subscribed to the SugarCRM Inc. product ("Company") that Company is bound by
 * the SugarCRM Inc. Master Subscription Agreement (“MSA”), which is viewable at:
 * http://www.sugarcrm.com/master-subscription-agreement
 *
 * If Company is not bound by the MSA, then by installing or using this file
 * you are agreeing unconditionally that Company will be bound by the MSA and
 * certifying that you have authority to bind Company accordingly.
 *
 * Copyright  2004-2013 SugarCRM Inc.  All rights reserved.
 */

require_once("modules/Emails/clients/base/api/MailApi.php");

/**
 * @group api
 * @group email
 */
class MailApiTest extends Sugar_PHPUnit_Framework_TestCase
{
    private $api,
            $mailApi,
            $emailUI,
            $userCacheDir;

    public function setUp()
    {
        parent::setUp();
        SugarTestHelper::setUp("current_user");
        $this->api     = SugarTestRestUtilities::getRestServiceMock();
        $this->mailApi = $this->getMock("MailApi", array("initMailRecord", "getEmailRecipientsService", "getEmailBean"));

        $this->emailUI = new EmailUI();
        $this->emailUI->preflightUserCache();
        $this->userCacheDir = $this->emailUI->userCacheDir;
    }

    public function tearDown()
    {
        SugarTestUserUtilities::removeAllCreatedAnonymousUsers();
        SugarTestHelper::tearDown();
        parent::tearDown();
        if (file_exists($this->userCacheDir)) {
            rmdir_recursive($this->userCacheDir);
        }
    }

    public function testCreateMail_StatusIsSaveAsDraft_CallsMailRecordSaveAsDraft()
    {
        $args = array(
            "status" => "draft",
        );

        $mockResult = array(
            "id" => '1234567890',
        );

        $mailRecordMock = $this->getMock("MailRecord", array("saveAsDraft"));
        $mailRecordMock->expects($this->once())
            ->method("saveAsDraft")
            ->will($this->returnValue($mockResult));

        $this->mailApi->expects($this->any())
            ->method("initMailRecord")
            ->will($this->returnValue($mailRecordMock));

        $this->mailApi->createMail($this->api, $args);
    }

    public function testCreateMail_StatusIsReady_CallsMailRecordSend()
    {
        $args = array(
            "status"       => "ready",
            "email_config" => "foo",
        );

        $mockResult = array(
            "id" => '1234567890',
        );

        $mailRecordMock = $this->getMock("MailRecord", array("send"));
        $mailRecordMock->expects($this->once())
            ->method("send")
            ->will($this->returnValue($mockResult));

        $this->mailApi->expects($this->any())
            ->method("initMailRecord")
            ->will($this->returnValue($mailRecordMock));

        $this->mailApi->createMail($this->api, $args);
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

        $this->setExpectedException("SugarApiExceptionInvalidParameter");
        $this->mailApi->createMail($this->api, $args);
    }

    public function testRecipientLookup_AttemptToResolveTenRecipients_CallsLookupTenTimes()
    {
        $expected = 10;
        $args     = array();

        for ($i = 0; $i < $expected; $i++) {
            $args[] = array("email" => "recipient{$i}");
        }

        $emailRecipientsServiceMock = $this->getMock("EmailRecipientsService", array("lookup"));
        $emailRecipientsServiceMock->expects($this->exactly($expected))
            ->method("lookup")
            ->will($this->returnArgument(0));

        $this->mailApi->expects($this->any())
            ->method("getEmailRecipientsService")
            ->will($this->returnValue($emailRecipientsServiceMock));

        $actual = $this->mailApi->recipientLookup($this->api, $args);
        $this->assertEquals($args, $actual, "Should have returned an array matching \$args.");
    }

    public function testValidateEmailAddresses_OneIsValidAndOneIsInvalid()
    {
        $args = array(
            "foo@bar.com",
            "foo",
        );

        $emailRecipientsServiceMock = $this->getMock("EmailRecipientsService", array("isValidEmailAddress"));
        $emailRecipientsServiceMock->expects($this->exactly(count($args)))
            ->method("isValidEmailAddress")
            ->will($this->onConsecutiveCalls(true, false));

        $this->mailApi->expects($this->any())
            ->method("getEmailRecipientsService")
            ->will($this->returnValue($emailRecipientsServiceMock));

        $actual = $this->mailApi->validateEmailAddresses($this->api, $args);
        $this->assertTrue($actual[$args[0]], "Should have set the value for key '{$args[0]}' to true.");
        $this->assertFalse($actual[$args[1]], "Should have set the value for key '{$args[1]}' to false.");
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

    /**
     * @group mailattachment
     */
    public function testClearUserCache_UserCacheDirDoesNotExist_CreatedSuccessfully()
    {
        if (file_exists($this->userCacheDir)) {
            rmdir_recursive($this->userCacheDir);
        }
        $this->mailApi->clearUserCache($this->api, array());
        $this->_assertCacheDirCreated();
        $this->_assertCacheDirEmpty();
    }

    /**
     * @group mailattachment
     */
    public function testClearUserCache_UserCacheDirContainsFiles_ClearedSuccessfully()
    {
        sugar_file_put_contents($this->userCacheDir . "/test.txt", create_guid());
        $this->mailApi->clearUserCache($this->api, array());
        $this->_assertCacheDirCreated();
        $this->_assertCacheDirEmpty();
    }

    /**
     * @group mailattachment
     */
    public function testSaveAttachment_CallsAppropriateEmailFunction()
    {
        $mockResult = array('name' => 'foo');

        $emailMock = $this->getMock("Email", array("email2init", "email2saveAttachment"));
        $emailMock->expects($this->once())
            ->method("email2init");
        $emailMock->expects($this->once())
            ->method("email2saveAttachment")
            ->will($this->returnValue($mockResult));

        $this->mailApi->expects($this->once())
            ->method("getEmailBean")
            ->will($this->returnValue($emailMock));

        $result = $this->mailApi->saveAttachment($this->api, array());

        $this->assertEquals($mockResult, $result, "Should return the response from email2saveAttachment");
    }

    /**
     * @group mailattachment
     */
    public function testRemoveAttachment_FileExists_RemovedSuccessfully()
    {
        //clear the cache first
        $em = new EmailUI();
        $em->preflightUserCache();

        //create the test attachment to be removed
        $fileGuid = create_guid();
        sugar_file_put_contents($this->userCacheDir . '/' . $fileGuid, create_guid());

        $this->mailApi->expects($this->once())
            ->method("getEmailBean")
            ->will($this->returnValue(new Email()));

        $this->mailApi->removeAttachment($this->api, array('file_guid' => $fileGuid));

        //verify it was removed
        $this->_assertCacheDirEmpty();
    }

    /**
     * Check to make sure path is created
     */
    protected function _assertCacheDirCreated()
    {
        $this->assertTrue(file_exists($this->userCacheDir), "Cache directory should exist");
    }

    /**
     * Check to make sure path is empty
     */
    protected function _assertCacheDirEmpty()
    {
        $files = findAllFiles($this->userCacheDir, array());
        $this->assertEquals(0, count($files), "Cache directory should be empty");
    }
}
