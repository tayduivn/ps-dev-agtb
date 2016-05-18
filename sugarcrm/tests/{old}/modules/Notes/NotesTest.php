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
 

class NotesTest extends Sugar_PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        $GLOBALS['current_user'] = SugarTestUserUtilities::createAnonymousUser();
	}

    public function tearDown()
    {
        SugarTestUserUtilities::removeAllCreatedAnonymousUsers();
        SugarTestNoteUtilities::removeAllCreatedNotes();
        unset($GLOBALS['current_user']);
    }
    
    /**
     * @ticket 19499
     */
    public function testCreateProperNameFieldContainsFirstAndLastName()
    {
        $contact = new Contact();
        $contact->first_name = "Josh";
        $contact->last_name = "Chi";
        $contact->salutation = "Mr";
        $contact->title = 'VP Operations';
        $contact->disable_row_level_security = true;
        $contact_id = $contact->save();
        
        $note = new Note();
        $note->contact_id = $contact_id;
        $note->disable_row_level_security = true;
        $note->fill_in_additional_detail_fields();
        
        $this->assertContains($contact->first_name,$note->contact_name);
        $this->assertContains($contact->last_name,$note->contact_name);
        
        $GLOBALS['db']->query('DELETE FROM contacts WHERE id =\''.$contact_id.'\'');
    }

    public function testSave_EmailAttachmentFileFound_FileIsSizeSaved()
    {
        $note = SugarTestNoteUtilities::createNote();

        $file = "upload://{$note->id}";
        file_put_contents($file, $note->id);
        $filesize = filesize($file);

        $note->email_type = 'Emails';
        $note->email_id = create_guid();
        $note->save(false);

        $this->assertSame($filesize, $note->file_size);
    }

    public function testSave_EmailAttachmentFileFoundAtUploadId_FileIsSizeSaved()
    {
        $note = SugarTestNoteUtilities::createNote();
        $note->upload_id = create_guid();

        $file = "upload://{$note->upload_id}";
        file_put_contents($file, $note->upload_id);
        $filesize = filesize($file);

        $note->email_type = 'Emails';
        $note->email_id = create_guid();
        $note->save(false);

        $this->assertSame($filesize, $note->file_size);
    }

    public function testSave_EmailAttachmentFileUploaded_FileIsSizeSaved()
    {
        $filename = create_guid();
        $file = "upload://{$filename}";
        file_put_contents($file, $filename);
        $filesize = filesize($file);

        $uploadFile = $this->getMockBuilder('UploadFile')
            ->disableOriginalConstructor()
            ->setMethods(array('get_temp_file_location'))
            ->getMock();
        $uploadFile->method('get_temp_file_location')->willReturn($file);

        $note = BeanFactory::newBean('Notes');
        $note->email_type = 'Emails';
        $note->email_id = create_guid();
        $note->file = $uploadFile;
        $note->save(false);
        SugarTestNoteUtilities::setCreatedNotes(array($note->id));

        $this->assertSame($filesize, $note->file_size);

        unlink($file);
    }

    public function testSave_EmailAttachmentFileNotFound_FileSizeIsZero()
    {
        $note = SugarTestNoteUtilities::createNote();
        $note->email_type = 'Emails';
        $note->email_id = create_guid();
        $note->save(false);

        $this->assertSame(0, $note->file_size);
    }

    public function testSave_NotAnEmailAttachment_FileSizeIsZero()
    {
        $note = SugarTestNoteUtilities::createNote();

        $file = "upload://{$note->id}";
        file_put_contents($file, $note->id);

        $note->save(false);

        $this->assertNull($note->file_size);
    }

    public function testMarkDeleted()
    {
        $note = SugarTestNoteUtilities::createNote('', array(
            'email_type' => 'Emails',
            'email_id' => create_guid(),
        ));

        $file = "upload://{$note->id}";
        file_put_contents($file, $note->id);
        $this->assertFileExists($file);

        $note->mark_deleted($note->id);
        $this->assertFileNotExists($file);
    }

    public function testMarkDeleted_FileFoundAtUploadId()
    {
        $uploadId = create_guid();
        $note = SugarTestNoteUtilities::createNote(
            '',
            array(
                'email_type' => 'Emails',
                'email_id' => create_guid(),
                'upload_id' => $uploadId,
            )
        );

        $file = "upload://{$uploadId}";
        file_put_contents($file, $uploadId);
        $this->assertFileExists($file);

        $note->mark_deleted($note->id);
        $this->assertFileNotExists($file);
    }
}
