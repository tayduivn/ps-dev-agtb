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
     * @covers ::createBean
     * @covers ::getRelatedRecordArguments
     * @covers ::createRelatedRecords
     * @covers ::getAttachmentSource
     * @covers ::setupAttachmentNoteRecord
     * @covers ::moveOrCopyAttachment
     */
    public function testCreateRecord_WithAttachmentsUsingAnUploadedFile()
    {
        $uploadId = create_guid();
        file_put_contents("upload://{$uploadId}", 'test');

        $args = array(
            'state' => Email::EMAIL_STATE_DRAFT,
            'attachments' => array(
                'create' => array(
                    array(
                        '_file' => $uploadId,
                        'name' => 'aaaaa',
                        'filename' => 'aaaaa.png',
                        'file_mime_type' => 'image/png',
                        'file_source' => Email::EMAIL_ATTACHMENT_UPLOADED,
                    ),
                ),
            ),
        );
        $record = $this->createRecord($args);

        $this->assertFileNotExists("upload://{$uploadId}", 'The file should have been moved');

        $attachments = $this->getRelatedRecords($record['id']);
        $this->assertCount(1, $attachments['records']);
        $this->assertFiles($attachments['records']);
    }

    /**
     * @covers ::createRecord
     * @covers ::createBean
     * @covers ::getRelatedRecordArguments
     * @covers ::createRelatedRecords
     * @covers ::getAttachmentSource
     * @covers ::setupAttachmentNoteRecord
     * @covers ::moveOrCopyAttachment
     */
    public function testCreateRecord_WithAttachmentsUsingAnExistingFile()
    {
        $docId = create_guid();
        file_put_contents("upload://{$docId}", 'test');

        $args = array(
            'state' => Email::EMAIL_STATE_DRAFT,
            'attachments' => array(
                'create' => array(
                    array(
                        '_file' => $docId,
                        'name' => 'aaaaa',
                        'filename' => 'aaaaa.png',
                        'file_mime_type' => 'image/png',
                        'file_source' => Email::EMAIL_ATTACHMENT_DOCUMENT,
                    ),
                ),
            ),
        );
        $record = $this->createRecord($args);

        $this->assertFileExists("upload://{$docId}", 'The document file should remain');
        unlink("upload://{$docId}");

        $attachments = $this->getRelatedRecords($record['id']);
        $this->assertCount(1, $attachments['records']);
        $this->assertFiles($attachments['records']);
    }

    /**
     * @covers ::updateRecord
     * @covers ::updateBean
     * @covers ::getRelatedRecordArguments
     * @covers ::createRelatedRecords
     * @covers ::unlinkRelatedRecords
     * @covers ::getAttachmentSource
     * @covers ::setupAttachmentNoteRecord
     * @covers ::moveOrCopyAttachment
     */
    public function testUpdateRecord_CreateAndRemoveAttachments()
    {
        $uploadId = create_guid();
        file_put_contents("upload://{$uploadId}", 'test');

        $templateId = create_guid();
        file_put_contents("upload://{$templateId}", 'test');

        $attachment1 = SugarTestNoteUtilities::createNote();
        file_put_contents("upload://{$attachment1->id}", 'test');

        $attachment2 = SugarTestNoteUtilities::createNote();
        file_put_contents("upload://{$attachment2->id}", 'test');

        $email = SugarTestEmailUtilities::createEmail('', array('state' => Email::EMAIL_STATE_DRAFT));
        $email->load_relationship('attachments');
        $email->attachments->add($attachment1);
        $email->attachments->add($attachment2);
        $this->assertCount(2, $email->attachments->get(), 'Should start with two attachments');

        $args = array(
            'attachments' => array(
                'create' => array(
                    array(
                        '_file' => $uploadId,
                        'name' => 'aaaaa',
                        'filename' => 'aaaaa.png',
                        'file_mime_type' => 'image/png',
                        'file_source' => Email::EMAIL_ATTACHMENT_UPLOADED,
                    ),
                    array(
                        '_file' => $templateId,
                        'name' => 'bbbbb',
                        'filename' => 'bbbbb.png',
                        'file_mime_type' => 'image/png',
                        'file_source' => Email::EMAIL_ATTACHMENT_TEMPLATE,
                    ),
                ),
                'delete' => array(
                    $attachment2->id
                ),
            ),
        );
        $record = $this->updateRecord($email->id, $args);

        $attachments = $this->getRelatedRecords($record['id']);
        $this->assertCount(3, $attachments['records'], 'Should have three attachments after updating');
        $this->assertFiles($attachments['records']);

        $found = array_filter($attachments['records'], function ($attachment) use ($attachment2) {
            return $attachment['id'] === $attachment2->id;
        });
        $this->assertCount(0, $found, "{$attachment2->id} should have been removed");
    }

    /**
     * Retrieves an Emails record's "attachments" link using {@link RelateApi::filterRelated()} as a convenience for use
     * in assertions.
     *
     * @param string $id The ID of the Emails record that contains the attachments.
     * @return array
     */
    protected function getRelatedRecords($id)
    {
        $args = array(
            'module' => 'Emails',
            'record' => $id,
            'link_name' => 'attachments',
        );
        $api = new RelateApi();
        return $api->filterRelated($this->service, $args);
    }

    /**
     * Asserts that each attachment's corresponding file exists.
     *
     * @param array $attachments The records from the response retrieved using
     * {@link EmailsApiAttachments::getRelatedRecords()}.
     */
    protected function assertFiles(array $attachments)
    {
        foreach ($attachments as $attachment) {
            $this->assertFileExists("upload://{$attachment['id']}");
        }
    }
}
