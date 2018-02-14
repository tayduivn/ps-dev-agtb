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

use Sugarcrm\Sugarcrm\Util\Uuid;

/**
 * @coversDefaultClass Note
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

        $note = SugarTestNoteUtilities::createNote(null, array(
            'contact_id' => $contact_id,
        ));

        $note->disable_row_level_security = true;
        $note->retrieve();

        $this->assertContains($contact->first_name,$note->contact_name);
        $this->assertContains($contact->last_name,$note->contact_name);
        
        $GLOBALS['db']->query('DELETE FROM contacts WHERE id =\''.$contact_id.'\'');
    }

    public function testSave_NoFile_FileMetadataIsDefaulted()
    {
        $note = SugarTestNoteUtilities::createNote();
        $this->assertEmpty($note->file_mime_type, 'Should not store a mime type when there is no file');
        $this->assertEmpty($note->file_ext, 'Should not store an extension when there is no file');
        $this->assertSame(0, $note->file_size);
    }

    public function testSave_FileFound_FileMetadataIsSaved()
    {
        $note = SugarTestNoteUtilities::createNote();

        $file = "upload://{$note->id}";
        file_put_contents($file, $note->id);
        $filesize = filesize($file);

        $note->filename = 'quote.pdf';
        $note->save(false);

        // Note: We can't test that the right mime type is stored because the file is fake. But it shouldn't be empty.
        $this->assertNotEmpty($note->file_mime_type, 'Should have stored the mime type');
        $this->assertSame('pdf', $note->file_ext, 'Incorrect extension');
        $this->assertSame($filesize, $note->file_size, 'Incorrect file size');
    }

    public function testSave_FileFoundAtUploadId_FileMetadataIsSaved()
    {
        $note = SugarTestNoteUtilities::createNote();
        $note->upload_id = Uuid::uuid1();

        $file = "upload://{$note->upload_id}";
        file_put_contents($file, $note->upload_id);
        $filesize = filesize($file);

        $note->filename = 'quote.pdf';
        $note->save(false);

        // Note: We can't test that the right mime type is stored because the file is fake. But it shouldn't be empty.
        $this->assertNotEmpty($note->file_mime_type, 'Should have stored the mime type');
        $this->assertSame('pdf', $note->file_ext, 'Incorrect extension');
        $this->assertSame($filesize, $note->file_size, 'Incorrect file size');
    }

    public function testSave_FileFoundInTemporaryLocation_FileMetadataIsSaved()
    {
        $filename = Uuid::uuid1();
        $file = "upload://tmp/{$filename}";
        file_put_contents($file, $filename);
        $filesize = filesize($file);

        $uploadFile = $this->getMockBuilder('UploadFile')
            ->disableOriginalConstructor()
            ->setMethods(array('get_temp_file_location'))
            ->getMock();
        $uploadFile->method('get_temp_file_location')->willReturn($file);

        $note = BeanFactory::newBean('Notes');
        $note->file = $uploadFile;
        $note->filename = 'quote.pdf';
        $note->save(false);
        SugarTestNoteUtilities::setCreatedNotes(array($note->id));

        // Note: We can't test that the right mime type is stored because the file is fake. But it shouldn't be empty.
        $this->assertNotEmpty($note->file_mime_type, 'Should have stored the mime type');
        $this->assertSame('pdf', $note->file_ext, 'Incorrect extension');
        $this->assertSame($filesize, $note->file_size, 'Incorrect file size');

        unlink($file);
    }

    public function markDeletedProvider()
    {
        return array(
            array(
                array(
                    'upload_id' => Sugarcrm\Sugarcrm\Util\Uuid::uuid1(),
                ),
                true,
            ),
            array(
                array(),
                false,
            ),
        );
    }

    /**
     * @covers ::mark_deleted
     * @dataProvider markDeletedProvider
     */
    public function testMarkDeleted($data, $expected)
    {
        $note = SugarTestNoteUtilities::createNote('', $data);

        $file = $note->upload_id ? "upload://{$note->upload_id}" : "upload://{$note->id}";
        file_put_contents($file, $note->id);
        $this->assertFileExists($file);

        $note->mark_deleted($note->id);
        $this->assertSame($expected, file_exists($file));
    }

    public function deleteAttachmentProvider()
    {
        return array(
            array(
                array(
                    'filename' => 'foo.jpg',
                    'file_mime_type' => 'image/jpg',
                    'file_ext' => 'jpg',
                    'file_size' => 111,
                    'email_type' => 'Emails',
                    'email_id' => Sugarcrm\Sugarcrm\Util\Uuid::uuid1(),
                    'upload_id' => Sugarcrm\Sugarcrm\Util\Uuid::uuid1(),
                ),
                true,
            ),
            array(
                array(
                    'filename' => 'foo.jpg',
                    'file_mime_type' => 'image/jpg',
                    'file_ext' => 'jpg',
                    'file_size' => 111,
                    'email_type' => 'Emails',
                    'email_id' => Sugarcrm\Sugarcrm\Util\Uuid::uuid1(),
                ),
                false,
            ),
        );
    }

    /**
     * @covers ::deleteAttachment
     * @dataProvider deleteAttachmentProvider
     */
    public function testDeleteAttachment($data, $expected)
    {
        $note = SugarTestNoteUtilities::createNote('', $data);

        $file = $note->upload_id ? "upload://{$note->upload_id}" : "upload://{$note->id}";
        file_put_contents($file, $note->id);
        $this->assertFileExists($file);

        $note->deleteAttachment();
        $this->assertSame($expected, file_exists($file));

        $note = BeanFactory::retrieveBean('Notes', $note->id, array('use_cache' => false));
        $this->assertEmpty($note->filename, 'The filename should be empty');
        $this->assertEmpty($note->file_mime_type, 'The file_mime_type should be empty');
        $this->assertEmpty($note->file_ext, 'The file_ext should be empty');
        $this->assertEmpty($note->file_size, 'The file_size should be empty');
        $this->assertEmpty($note->file_source, 'The file_source should be empty');
        $this->assertEmpty($note->email_type, 'The email_type should be empty');
        $this->assertEmpty($note->email_id, 'The email_id should be empty');
        $this->assertEmpty($note->upload_id, 'The upload_id should be empty');
        $this->assertEmpty($note->file, 'There should not be an UploadFile object');

        unlink($file);
    }
}
