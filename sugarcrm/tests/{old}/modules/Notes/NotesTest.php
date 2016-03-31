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

    public function testEmailAttachmentNote_attachmentFileFound_fileSizeSaved()
    {
        $noteId = create_guid();
        $fileName = 'upload://' . $noteId;
        $note = \SugarTestNoteUtilities::createNote($noteId);
        $note->email_id = create_guid();
        file_put_contents($fileName, 'test');
        $note->save();
        $this->assertEquals(filesize($fileName), $note->file_size, 'File Size Not computed on Attachment Save');
    }

    public function testEmailAttachmentNote_attachmentFileNotFound_fileSizeIsZero()
    {
        $noteId = create_guid();
        $note = \SugarTestNoteUtilities::createNote($noteId);
        $note->email_id = create_guid();
        $note->save();
        $this->assertEquals(0, $note->file_size, 'File Size Should be Zero when No Matching File');
    }

    public function testNoteWithAttachedFile_MatchingFileButNoEmailReference_fileSizeIsZero()
    {
        $noteId = create_guid();
        $fileName = 'upload://' . $noteId;
        $note = \SugarTestNoteUtilities::createNote($noteId);
        file_put_contents($fileName, 'test');
        $note->save();
        $this->assertEquals(0, $note->file_size, 'File Size computed for Note with No Email Reference');
    }
}
