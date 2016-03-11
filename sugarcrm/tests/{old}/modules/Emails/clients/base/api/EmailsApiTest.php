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

require_once 'modules/Emails/clients/base/api/EmailsApi.php';

/**
 * @group api
 * @group email
 */
class EmailsApiTest extends Sugar_PHPUnit_Framework_TestCase
{
    const UPLOADED_FILES = 1;
    public static $testEmailName;
    public $emailsApi;
    public $serviceMock;
    public $fileUploads;

    public static function setUpBeforeClass()
    {
        parent::setUpBeforeClass();
        SugarTestHelper::setUp('beanList');
        SugarTestHelper::setUp('beanFiles');
        SugarTestHelper::setUp('current_user');
        static::$testEmailName = "EmailsApiTest: " . create_guid();
    }

    public function setUp()
    {
        parent::setUp();
        $this->fileUploads = array();
        for ($i = 0; $i < static::UPLOADED_FILES; $i++) {
            $fileName = create_guid();
            file_put_contents('upload://' . $fileName, 'test');
            $this->fileUploads[] = $fileName;
        }
        $this->emailsApi = new EmailsApi();
        $this->serviceMock = SugarTestRestUtilities::getRestServiceMock();
    }

    public function tearDown()
    {
        for ($i = 0; $i < count($this->fileUploads); $i++) {
            unlink('upload://' . $this->fileUploads[$i]);
        }
        $this->removeAllCreatedEmailsAndAttachments();
        parent::tearDown();
    }

    public static function tearDownAfterClass()
    {
        SugarTestEmailUtilities::removeAllCreatedEmails();
        SugarTestHelper::tearDown();
        parent::tearDownAfterClass();
    }

    public function testCreateEmail_SaveAsDraft_EmailCreated()
    {
        $createArgs = array(
            'module' => 'Emails',
            'name' => static::$testEmailName,
            'state' => Email::EMAIL_STATE_DRAFT,
            'assigned_user_id' => $GLOBALS['current_user']->id
        );
        $result = $this->emailsApi->createRecord($this->serviceMock, $createArgs);

        //--- Verify Created Email Record Returned
        $this->assertNotEmpty($result);
        $this->assertArrayHasKey('id', $result);
        $this->assertEquals($createArgs['name'], $result['name']);
        $this->assertEquals($createArgs['state'], $result['state']);
        $this->assertEquals($createArgs['assigned_user_id'], $result['assigned_user_id']);

        //--- Verify Created Email Record Created in Database
        $email = BeanFactory::newBean('Emails');
        $email->retrieve($result['id']);
        $this->assertAttributeNotEmpty('id', $email);
        $this->assertEquals($createArgs['name'], $email->name);
        $this->assertEquals($createArgs['state'], $email->state);
        $this->assertEquals($createArgs['assigned_user_id'], $email->assigned_user_id);
    }

    public function testUpdateDraftEmail_SaveAsDraft_EmailUpdated()
    {
        $createArgs = array(
            'module' => 'Emails',
            'name' => static::$testEmailName,
            'state' => Email::EMAIL_STATE_DRAFT,
            'assigned_user_id' => $GLOBALS['current_user']->id
        );
        $createResult = $this->emailsApi->createRecord($this->serviceMock, $createArgs);
        $this->assertNotEmpty($createResult);
        $this->assertArrayHasKey('id', $createResult);

        $updateArgs = array(
            'module' => 'Emails',
            'record' => $createResult['id'],
            'name' => static::$testEmailName,
            'state' => Email::EMAIL_STATE_DRAFT,
        );
        $updateResult = $this->emailsApi->updateRecord($this->serviceMock, $updateArgs);

        //--- Verify Updated Email Record Returned
        $this->assertNotEmpty($updateResult);
        $this->assertArrayHasKey('id', $updateResult);
        $this->assertEquals($createResult['id'], $updateResult['id']);
        $this->assertEquals($updateArgs['name'], $updateResult['name']);
        $this->assertEquals($updateArgs['state'], $updateResult['state']);
        $this->assertEquals($createArgs['assigned_user_id'], $updateResult['assigned_user_id']);

        //--- Verify Email Record Created in Database
        $email = BeanFactory::newBean('Emails');
        $email->retrieve($updateResult['id']);
        $this->assertAttributeNotEmpty('id', $email);
        $this->assertEquals($updateResult['name'], $email->name);
        $this->assertEquals($updateResult['state'], $email->state);
        $this->assertEquals($updateResult['assigned_user_id'], $email->assigned_user_id);
    }

    public function testEmailCreate_StateTransition_ExceptionThrown()
    {
        $createArgs = array(
            'name' => static::$testEmailName,
            'module' => 'Emails',
            'state' => 'SomeBogusToState',
        );
        $this->setExpectedException('SugarApiExceptionInvalidParameter');
        $result = $this->emailsApi->createRecord($this->serviceMock, $createArgs);
        $this->assertNotEmpty($result);
        $this->assertArrayHasKey('id', $result);
    }

    /**
     * @dataProvider emailCreate_StateTransitionProvider
     */
    public function testEmailCreate_StateTransition($toState)
    {
        $createArgs = array(
            'name' => static::$testEmailName,
            'module' => 'Emails',
            'state' => $toState,
        );
        $result = $this->emailsApi->createRecord($this->serviceMock, $createArgs);
        $this->assertNotEmpty($result);
        $this->assertArrayHasKey('id', $result);
    }

    public function emailCreate_StateTransitionProvider()
    {
        return array(
            array(Email::EMAIL_STATE_DRAFT),
            array(Email::EMAIL_STATE_ARCHIVED),
            array(Email::EMAIL_STATE_READY),
            array(Email::EMAIL_STATE_SCHEDULED),
        );
    }

    /**
     * @dataProvider emailUpdate_StateTransitionProvider_ExceptionThrown
     */
    public function testEmailUpdate_StateTransition_ExceptionThrown($fromState, $toState)
    {
        $createArgs = array(
            'name' => static::$testEmailName,
            'module' => 'Emails',
            'state' => $fromState,
        );
        $result = $this->emailsApi->createRecord($this->serviceMock, $createArgs);
        $this->assertTrue(!empty($result['id']), 'Create Email Failed');
        $args['record'] = $result['id'];

        $updateArgs = array(
            'module' => 'Emails',
            'record' => $result['id'],
            'state' => $toState,
        );

        $this->setExpectedException('SugarApiExceptionInvalidParameter');

        $result = $this->emailsApi->updateRecord($this->serviceMock, $updateArgs);
        $this->assertTrue(!empty($result['id']), 'Update Email Failed');

    }

    public function emailUpdate_StateTransitionProvider_ExceptionThrown()
    {
        return array(
            array(Email::EMAIL_STATE_ARCHIVED, Email::EMAIL_STATE_ARCHIVED),
            array(Email::EMAIL_STATE_ARCHIVED, Email::EMAIL_STATE_READY),
            array(Email::EMAIL_STATE_DRAFT, Email::EMAIL_STATE_ARCHIVED),
            array(Email::EMAIL_STATE_SCHEDULED, Email::EMAIL_STATE_DRAFT),
        );
    }

    /**
     * @dataProvider emailUpdate_StateTransitionProvider_ExceptionThrown
     */
    public function testEmailUpdate_StateTransition($fromState, $toState)
    {
        $createArgs = array(
            'name' => static::$testEmailName,
            'module' => 'Emails',
            'state' => $fromState,
        );
        $result = $this->emailsApi->createRecord($this->serviceMock, $createArgs);
        $this->assertTrue(!empty($result['id']), 'Create Email Failed');
        $args['record'] = $result['id'];

        $updateArgs = array(
            'module' => 'Emails',
            'record' => $result['id'],
            'state' => $toState,
        );

        $this->setExpectedException('SugarApiExceptionInvalidParameter');

        $result = $this->emailsApi->updateRecord($this->serviceMock, $updateArgs);
        $this->assertTrue(!empty($result['id']), 'Update Email Failed');

    }

    public function emailUpdate_StateTransitionProvider()
    {
        return array(
            array(Email::EMAIL_STATE_DRAFT, Email::EMAIL_STATE_DRAFT, false),
            array(Email::EMAIL_STATE_DRAFT, Email::EMAIL_STATE_SCHEDULED, false),
            array(Email::EMAIL_STATE_DRAFT, Email::EMAIL_STATE_READY, false),
            array(Email::EMAIL_STATE_SCHEDULED, Email::EMAIL_STATE_SCHEDULED, false),
            array(Email::EMAIL_STATE_SCHEDULED, Email::EMAIL_STATE_ARCHIVED, false),
        );
    }


    public function testEmailCreateDraft_WithUploadedAttachment_AttachmentMovedOrCopied()
    {
        $createArgs = array(
            'module' => 'Emails',
            'name' => static::$testEmailName,
            'state' => Email::EMAIL_STATE_DRAFT,
            'attachments' => array(
                'create' => array(
                    array(
                        '_file' => $this->fileUploads[0],
                        'name' => 'aaaaa',
                        'filename' => 'aaaaa.png',
                        'file_mime_type' => 'image/png',
                    ),
                ),
            ),
        );

        $emailsApiMock = $this->getMock('EmailsApi', array('moveOrCopyAttachment'));
        $emailsApiMock->expects($this->once())
            ->method('moveOrCopyAttachment')
            ->with('upload://' . $this->fileUploads[0], $this->stringContains('upload://'), false)
            ->will($this->returnValue(true));

        $result = $emailsApiMock->createRecord($this->serviceMock, $createArgs);

        $this->assertNotEmpty($result);
        $this->assertArrayHasKey('id', $result);
    }

    public function testEmailCreateDraft_WithExistingAttachment_AttachmentMovedOrCopied()
    {
        $createArgs = array(
            'module' => 'Emails',
            'name' => static::$testEmailName,
            'state' => Email::EMAIL_STATE_DRAFT,
            'attachments' => array(
                'create' => array(
                    array(
                        '_file' => $this->fileUploads[0],
                        'name' => 'aaaaa',
                        'filename' => 'aaaaa.png',
                        'file_mime_type' => 'image/png',
                    ),
                ),
            ),
        );

        $emailsApiMock = $this->getMock('EmailsApi', array('moveOrCopyAttachment'));
        $emailsApiMock->expects($this->once())
            ->method('moveOrCopyAttachment')
            ->with('upload://' . $this->fileUploads[0], $this->stringContains('upload://'), false)
            ->will($this->returnValue(true));

        $result = $emailsApiMock->createRecord($this->serviceMock, $createArgs);

        $this->assertNotEmpty($result);
        $this->assertArrayHasKey('id', $result);
    }

    public function testEmailUpdateDraft_WithExistingAttachment_AttachmentMovedOrCopied()
    {
        $createArgs = array(
            'module' => 'Emails',
            'name' => static::$testEmailName,
            'state' => Email::EMAIL_STATE_DRAFT,
        );
        $result = $this->emailsApi->createRecord($this->serviceMock, $createArgs);
        $this->assertTrue(!empty($result['id']), 'Create Email Failed');

        $updateArgs = array(
            'module' => 'Emails',
            'record' => $result['id'],
            'state' => Email::EMAIL_STATE_DRAFT,
            'attachments' => array(
                'create' => array(
                    array(
                        '_file' => $this->fileUploads[0],
                        'name' => 'aaaaa',
                        'filename' => 'aaaaa.png',
                        'file_mime_type' => 'image/png',
                    ),
                ),
            ),
        );

        $emailsApiMock = $this->getMock('EmailsApi', array('moveOrCopyAttachment'));
        $emailsApiMock->expects($this->once())
            ->method('moveOrCopyAttachment')
            ->with('upload://' . $this->fileUploads[0], $this->stringContains('upload://'), false)
            ->will($this->returnValue(true));

        $result = $emailsApiMock->updateRecord($this->serviceMock, $updateArgs);

        $this->assertNotEmpty($result);
        $this->assertArrayHasKey('id', $result);
    }

    public function testEmailCreateDraft_WithAttachment_NoteCreated()
    {
        $createArgs = array(
            'module' => 'Emails',
            'name' => static::$testEmailName,
            'state' => Email::EMAIL_STATE_DRAFT,
            'attachments' => array(
                'create' => array(
                    array(
                        '_file' => $this->fileUploads[0],
                        '_uploaded' => true,
                        'name' => 'aaaaa',
                        'filename' => 'aaaaa.png',
                        'file_mime_type' => 'image/png',
                    ),
                ),
            ),
        );

        $note = BeanFactory::newBean('Notes');
        $note->id = $this->fileUploads[0];

        $notesModuleApiMock = $this->getMock('ModuleApi', array('createBean'));
        $notesModuleApiMock->expects($this->once())
            ->method('createBean')
            ->will($this->returnValue($note));

        $relatedApiMock = $this->getMock('RelateRecordApi', array('getModuleApi', 'getRelatedRecord'));
        $relatedApiMock->expects($this->once())
            ->method('getModuleApi')
            ->with($this->serviceMock, 'Notes')
            ->will($this->returnValue($notesModuleApiMock));
        $relatedApiMock->expects($this->once())
            ->method('getRelatedRecord')
            ->will($this->returnValue(array()));

        $emailsApiMock = $this->getMock('EmailsApi', array('getRelateRecordApi', 'moveOrCopyAttachment'));
        $emailsApiMock->expects($this->any())
            ->method('getRelateRecordApi')
            ->will($this->returnValue($relatedApiMock));

        $emailsApiMock->expects($this->once())
            ->method('moveOrCopyAttachment')
            ->with('upload://' . $this->fileUploads[0], $this->stringContains('upload://'), true)
            ->will($this->returnValue(true));

        $result = $emailsApiMock->createRecord($this->serviceMock, $createArgs);

        $this->assertNotEmpty($result);
        $this->assertArrayHasKey('id', $result);
    }

    public function testEmailUpdateDraft_WithAttachment_NoteCreated()
    {
        $createArgs = array(
            'module' => 'Emails',
            'name' => static::$testEmailName,
            'state' => Email::EMAIL_STATE_DRAFT,
        );
        $result = $this->emailsApi->createRecord($this->serviceMock, $createArgs);
        $this->assertTrue(!empty($result['id']), 'Create Email Failed');

        $updateArgs = array(
            'module' => 'Emails',
            'record' => $result['id'],
            'state' => Email::EMAIL_STATE_DRAFT,
            'attachments' => array(
                'create' => array(
                    array(
                        '_file' => $this->fileUploads[0],
                        '_uploaded' => true,
                        'name' => 'aaaaa',
                        'filename' => 'aaaaa.png',
                        'file_mime_type' => 'image/png',
                    ),
                ),
            ),
        );

        $note = BeanFactory::newBean('Notes');
        $note->id = $this->fileUploads[0];

        $notesModuleApiMock = $this->getMock('ModuleApi', array('createBean'));
        $notesModuleApiMock->expects($this->once())
            ->method('createBean')
            ->will($this->returnValue($note));

        $relatedApiMock = $this->getMock('RelateRecordApi', array('getModuleApi', 'getRelatedRecord'));
        $relatedApiMock->expects($this->once())
            ->method('getModuleApi')
            ->with($this->serviceMock, 'Notes')
            ->will($this->returnValue($notesModuleApiMock));
        $relatedApiMock->expects($this->once())
            ->method('getRelatedRecord')
            ->will($this->returnValue(array()));

        $emailsApiMock = $this->getMock('EmailsApi', array('getRelateRecordApi', 'moveOrCopyAttachment'));
        $emailsApiMock->expects($this->any())
            ->method('getRelateRecordApi')
            ->will($this->returnValue($relatedApiMock));

        $emailsApiMock->expects($this->once())
            ->method('moveOrCopyAttachment')
            ->with('upload://' . $this->fileUploads[0], $this->stringContains('upload://'), true)
            ->will($this->returnValue(true));

        $result = $emailsApiMock->updateRecord($this->serviceMock, $updateArgs);

        $this->assertNotEmpty($result);
        $this->assertArrayHasKey('id', $result);
    }

    private function getCreatedEmailIds()
    {
        $ids = array();
        $sql = "SELECT id FROM emails WHERE name = '" . static::$testEmailName . "'";
        $result = $GLOBALS['db']->query($sql);
        if ($result) {
            while ($row = $GLOBALS['db']->fetchByAssoc($result)) {
                $ids[] = $row['id'];
            }
        }
        return $ids;
    }

    private function removeAllCreatedEmailsAndAttachments()
    {
        $emailIds = $this->getCreatedEmailIds();
        foreach ($emailIds as $emailId) {
            $this->deleteAllAttachments($emailId);
            $GLOBALS['db']->query("DELETE from emails WHERE id = '{$emailId}'");
        }
    }

    private function getEmailAttachmentIds($emailId)
    {
        $idArray = array();
        $sql = "SELECT id from notes WHERE email_id='" . $emailId . "' AND deleted=0";
        $result = $GLOBALS['db']->query($sql);
        if ($result) {
            while ($row = $GLOBALS['db']->fetchByAssoc($result)) {
                $idArray[$row['id']] = true;
            }
        }
        return $idArray;
    }

    private function deleteAllAttachments($emailId)
    {
        $idArray = $this->getEmailAttachmentIds($emailId);
        foreach (array_keys($idArray) as $fileId) {
            $sql = "DELETE from notes WHERE id='" . $fileId . "'";
            $GLOBALS['db']->query($sql);
            unlink('upload://' . $fileId);
        }
    }
}
