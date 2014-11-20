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

require_once 'modules/KBOLDDocuments/KBOLDDocument.php';
require_once 'modules/KBOLDDocumentRevisions/KBOLDDocumentRevision.php';

class KBOLDDocumentRevisionsTest extends Sugar_PHPUnit_Framework_TestCase
{

    /**
     * @var KBOLDDocument
     */
    private $doc;

    /**
     * @var DocumentRevision
     */
    private $docrev;

    /**
     * @var KBOLDDocumentRevision
     */
    private $rev;

    public function setUp()
    {
        SugarTestHelper::setUp("current_user");
        SugarTestHelper::setUp('beanList');

        $this->doc = new KBOLDDocument();
        $this->doc->description = 'This is a unit test for the KB document revisions';
        $this->doc->kbolddocument_name = 'KBOLDDocumentRevisionsTest';
        $this->doc->status = 'Published';
        $this->doc->assigned_user_id = $GLOBALS['current_user']->id;
        $this->doc->kbdoc_approver_id = $GLOBALS['current_user']->id;
        $this->doc->save();

        $this->doc->load_relationship('revisions');

        $this->docrev = BeanFactory::newBean('DocumentRevisions');
        $this->docrev->change_log = 'Created document revision for KBOLDDocumentRevisions Unit Test';
        $this->docrev->doc_type = 'Sugar';
        $this->docrev->revision = '1';
        $this->docrev->filename = 'unittest.txt';
        $this->docrev->file_ext = 'txt';
        $this->docrev->file_mime_type = 'text/plain';
        $this->docrev->save();

        $this->rev = new KBOLDDocumentRevision();
        $this->rev->change_log = 'Created revision for KBOLDDocumentRevisions Unit Test';
        $this->rev->kbolddocument_id = $this->doc->id;
        $this->rev->document_revision_id = $this->docrev->id;
        $this->rev->revision = '1';
        $this->rev->save();

        $this->doc->revisions->add(array($this->rev));
        $this->doc->kbolddocument_revision_id = $this->rev->id;
        $this->doc->save();
    }

    public function tearDown()
    {
        unset($GLOBALS['current_user']);

        $db = DBManagerFactory::getInstance();
        $docId = $db->quoted($this->doc->id);

        $db->query('DELETE FROM kbolddocuments WHERE id =' . $db->quoted($this->doc->id));

        if ($this->docrev instanceof SugarBean) {
            $GLOBALS['db']->query('DELETE FROM document_revisions WHERE id = ' . $db->quoted($this->docrev->id));
        }

        if ($this->rev instanceof SugarBean) {
            $db->query('DELETE FROM kbolddocument_revisions WHERE id = ' . $db->quoted($this->rev->id));
        }
        SugarTestHelper::tearDown();
    }

    public function testKBOLDDocumentRevisions()
    {
        $this->assertInstanceOf('Link2', $this->doc->revisions);
        $revisions = $this->doc->revisions->getBeans(); 
        $this->assertEquals(1, count($revisions));
        $revision = array_shift($revisions);
        $this->assertInstanceOf("SugarBean", $this->docrev);
        $this->assertInstanceOf('SugarBean', $revision);
        $this->assertEquals($revision->kbolddocument_id, $this->doc->id);
    }

    public function testFillDocumentNameRevision()
    {
        $this->rev->fill_document_name_revision($this->doc->id);
        $this->assertEquals(1, $this->rev->latest_revision);
    }

    public function testGetDocumentRevisionName()
    {
        $rev = $this->rev->get_document_revision_name($this->docrev->id);
        $this->assertEquals(1, $rev);
    }

    public function testGetDocumentRevisions()
    {
        $revisions = KBOLDDocumentRevision::get_document_revisions($this->doc->id);
        $this->assertEquals(1, count($revisions));
    }

    public function testGetDocuments()
    {
        $documents = KBOLDDocumentRevision::get_documents($this->rev->id);
        $this->assertEquals($this->doc->id, array_shift($documents));
    }

    public function testGetDocrevs()
    {
        $docrevs = KBOLDDocumentRevision::get_docrevs($this->docrev->id);
        $this->assertEquals($this->docrev->id, array_shift($docrevs));
    }

}
