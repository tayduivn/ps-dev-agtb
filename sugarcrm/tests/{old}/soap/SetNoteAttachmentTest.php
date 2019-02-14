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

class SetNoteAttachmentTest extends SOAPTestCase
{
    private $noteId;

    public function setUp()
    {
        parent::setUp();
        $this->_login();
    }

    public function tearDown()
    {
        $db = $GLOBALS['db'];
        $conn = $db->getConnection();
        $conn->delete('notes', ['id' => $this->noteId]);
        UploadFile::unlink_file($this->noteId);
        parent::tearDown();
    }

    public function testSetNoteAttachment()
    {
        //create Note
        $set_entry_parameters = [
            //session id
            'session' => $this->_sessionId,
            //The name of the module
            'module_name' => 'Notes',
            //Record attributes
            'name_value_list' => [
                ['name' => 'document_name', 'value' => 'Example Note'],
            ],
        ];

        $set_entry_result = $this->_soapClient->call('set_entry', $set_entry_parameters);
        $note_id = $set_entry_result['id'];
        $this->noteId = $note_id;
        //create document revision

        $contents = base64_encode(file_get_contents(__FILE__));

        $set_note_attachment_parameters = array(
            //session id
            'session' => $this->_sessionId,
            //The attachment details
            'note' => array(
                //The ID of the parent document.
                'id' => $note_id,
                //The binary contents of the file.
                'file' => $contents,
                //The name of the file
                'filename' => 'example_note.txt',
                //The revision number
            ),
        );

        $set_note_attachment_result = $this->_soapClient->call(
            'set_note_attachment',
            $set_note_attachment_parameters
        );

        $note = new Note();
        $note->retrieve($note_id);

        $this->assertEquals($set_note_attachment_result['id'], $note->id);
        $this->assertEquals('example_note.txt', $note->filename);
    }
}
