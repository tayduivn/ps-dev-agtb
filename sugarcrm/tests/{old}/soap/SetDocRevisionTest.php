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

class SetDocRevisionTest extends SOAPTestCase
{
    private $docId;
    private $documentRevisionId;

    public function setUp()
    {
        parent::setUp();
        $this->_login();
    }

    public function tearDown()
    {
        $db = $GLOBALS['db'];
        $conn = $db->getConnection();
        $conn->delete('documents', ['id' => $this->docId]);
        $conn->delete('document_revisions', ['document_id' => $this->docId]);
        UploadFile::unlink_file($this->documentRevisionId);
        parent::tearDown();
    }

    public function testSetDocRevision()
    {
        //create document
        $set_entry_parameters = [
            //session id
            'session' => $this->_sessionId,
            //The name of the module
            'module_name' => 'Documents',
            //Record attributes
            'name_value_list' => [
                ['name' => 'document_name', 'value' => 'Example Document'],
                ['name' => 'revision', 'value' => '1'],
            ],
        ];

        $set_entry_result = $this->_soapClient->call('set_entry', $set_entry_parameters);
        $document_id = $set_entry_result['id'];
        $this->docId = $document_id;
        //create document revision

        $contents = base64_encode(file_get_contents(__FILE__));

        $set_document_revision_parameters = array(
            //session id
            'session' => $this->_sessionId,
            //The attachment details
            'note' => array(
                //The ID of the parent document.
                'id' => $document_id,
                //The binary contents of the file.
                'file' => $contents,
                //The name of the file
                'filename' => 'example_document.txt',
                //The revision number
                'revision' => '1',
            ),
        );

        $set_document_revision_result = $this->_soapClient->call(
            'set_document_revision',
            $set_document_revision_parameters
        );
        $this->documentRevisionId = $set_document_revision_result['id'];

        $document = new Document();
        $document->retrieve($document_id);

        $this->assertEquals($set_document_revision_result['id'], $document->document_revision_id);
        $this->assertEquals('example_document.txt', $document->filename);
        $this->assertEquals('Example Document', $document->document_name);
    }
}
