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

require_once 'tests/{old}/modules/Emails/clients/base/api/EmailsApiIntegrationTestCase.php';

/**
 * @coversDefaultClass EmailsApi
 */
class EmailsApiAttachmentsTest extends EmailsApiIntegrationTestCase
{
    public static function tearDownAfterClass()
    {
        SugarTestNoteUtilities::removeAllCreatedNotes();
        parent::tearDownAfterClass();
    }

    /**
     * @covers ::createRecord
     * @covers ::getRelatedRecordArguments
     * @covers ::createRelatedRecords
     * @covers EmailsRelateRecordApi::createRelatedRecord
     * @covers ModuleApi::moveTemporaryFiles
     */
    public function testCreateRecord()
    {
        $uploadId = create_guid();
        file_put_contents("upload://tmp/{$uploadId}", 'test');

        $docRevisionId = create_guid();
        file_put_contents("upload://{$docRevisionId}", 'test');

        $args = array(
            'state' => Email::EMAIL_STATE_DRAFT,
            'assigned_user_id' => $GLOBALS['current_user']->id,
            'attachments' => array(
                'create' => array(
                    array(
                        'filename_guid' => $uploadId,
                        'name' => 'aaaaa',
                        'filename' => 'aaaaa.png',
                        'file_mime_type' => 'image/png',
                    ),
                    array(
                        'upload_id' => $docRevisionId,
                        'name' => 'aaaaa',
                        'filename' => 'aaaaa.png',
                        'file_mime_type' => 'image/png',
                        'file_source' => 'DocumentRevisions',
                    ),
                ),
            ),
        );
        $record = $this->createRecord($args);

        $this->assertFileNotExists("upload://tmp/{$uploadId}", 'The file should have been moved');

        $attachments = $this->getRelatedRecords($record['id'], 'attachments');
        $this->assertCount(2, $attachments['records']);
        $this->assertFiles($attachments['records']);

        unlink("upload://{$docRevisionId}");
    }

    /**
     * @covers ::updateRecord
     * @covers ::getRelatedRecordArguments
     * @covers ::createRelatedRecords
     * @covers ::unlinkRelatedRecords
     * @covers EmailsRelateRecordApi::createRelatedRecord
     * @covers ModuleApi::moveTemporaryFiles
     */
    public function testUpdateRecord()
    {
        $uploadId = create_guid();
        file_put_contents("upload://tmp/{$uploadId}", 'test');

        $templateId = create_guid();
        file_put_contents("upload://{$templateId}", 'test');

        $attachment1 = SugarTestNoteUtilities::createNote();
        file_put_contents("upload://{$attachment1->id}", 'test');

        $attachment2 = SugarTestNoteUtilities::createNote();
        file_put_contents("upload://{$attachment2->id}", 'test');

        $email = SugarTestEmailUtilities::createEmail(
            '',
            array(
                'state' => Email::EMAIL_STATE_DRAFT,
                'assigned_user_id' => $GLOBALS['current_user']->id,
            )
        );
        $email->load_relationship('attachments');
        $email->attachments->add($attachment1);
        $email->attachments->add($attachment2);
        $this->assertCount(2, $email->attachments->get(), 'Should start with two attachments');

        $args = array(
            'attachments' => array(
                'create' => array(
                    array(
                        'filename_guid' => $uploadId,
                        'name' => 'aaaaa',
                        'filename' => 'aaaaa.png',
                        'file_mime_type' => 'image/png',
                    ),
                    array(
                        'upload_id' => $templateId,
                        'name' => 'bbbbb',
                        'filename' => 'bbbbb.png',
                        'file_mime_type' => 'image/png',
                        'file_source' => 'EmailTemplates',
                    ),
                ),
                'delete' => array(
                    $attachment2->id,
                ),
            ),
        );
        $record = $this->updateRecord($email->id, $args);

        $attachments = $this->getRelatedRecords($record['id'], 'attachments');
        $this->assertCount(3, $attachments['records'], 'Should have three attachments after updating');
        $this->assertFiles($attachments['records']);

        $found = array_filter($attachments['records'], function ($attachment) use ($attachment2) {
            return $attachment['id'] === $attachment2->id;
        });
        $this->assertCount(0, $found, "{$attachment2->id} should not have been returned");
        $this->assertFileNotExists(
            "upload://{$attachment2->id}",
            "The file {$attachment2->id} should have been deleted"
        );

        $result = $GLOBALS['db']->query("SELECT * FROM notes WHERE email_id='{$record['id']}' AND deleted=1");

        while ($row = $GLOBALS['db']->fetchByAssoc($result)) {
            $this->assertEquals($attachment2->id, $row['id'], "{$row['id']} should not have been deleted");
            $this->assertFileNotExists("upload://{$row['id']}", "The file {$row['id']} should have been deleted");
        }

        unlink("upload://{$templateId}");
    }

    /**
     * @covers ::deleteRecord
     * @covers EmailsHookHandler::removeEmailAttachment
     */
    public function testDeleteRecord()
    {
        $uploadId = create_guid();
        file_put_contents("upload://tmp/{$uploadId}", 'test');

        $docId = create_guid();
        file_put_contents("upload://{$docId}", 'test');

        $args = array(
            'state' => Email::EMAIL_STATE_DRAFT,
            'assigned_user_id' => $GLOBALS['current_user']->id,
            'attachments' => array(
                'create' => array(
                    array(
                        'filename_guid' => $uploadId,
                        'name' => 'aaaaa',
                        'filename' => 'aaaaa.png',
                        'file_mime_type' => 'image/png',
                    ),
                    array(
                        'upload_id' => $docId,
                        'name' => 'bbbbb',
                        'filename' => 'bbbbb.png',
                        'file_mime_type' => 'image/png',
                        'file_source' => 'DocumentRevisions',
                    ),
                ),
            ),
        );
        $record = $this->createRecord($args);

        $attachments = $this->getRelatedRecords($record['id'], 'attachments');
        $this->assertCount(2, $attachments['records'], 'Should have two attachments');
        $this->assertFiles($attachments['records']);

        $this->deleteRecord($record['id']);

        $result = $GLOBALS['db']->query("SELECT * FROM notes WHERE email_id='{$record['id']}'");

        while ($row = $GLOBALS['db']->fetchByAssoc($result)) {
            $this->assertEquals(1, $row['deleted'], "{$row['id']} should have been deleted");
            $file = empty($row['upload_id']) ? $row['id'] : $row['upload_id'];
            $this->assertFileNotExists("upload://{$file}", "The file {$file} should not exist");
        }
    }
}
