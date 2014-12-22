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
require_once 'include/api/ApiHelper.php';
require_once 'include/api/RestService.php';
require_once('data/SugarBeanApiHelper.php');


class KBOLDDocumentsApiHelperTest extends Sugar_PHPUnit_Framework_TestCase
{

    private $_kb =null;

    public function setUp()
    {
        $GLOBALS['app_strings'] = return_application_language('en_us');
        SugarTestHelper::setUp("current_user");
        $GLOBALS['mod_strings'] = return_module_language($GLOBALS['current_language'], 'KBOLDDocuments');
        $this->_api = new RestService();
        $this->_api->user = $GLOBALS['current_user'];
        SugarTestHelper::setUp('beanList');

        //create the KBOLDDocument record
        $this->_kb = new KBOLDDocument();
        $this->_kb->description = 'This is a unit test for the kb document api helper';
        $this->_kb->kbolddocument_name = 'KBUnitTest API Helper';
        $this->_kb->status = 'In Review';
        $this->_kb->assigned_user_id = $GLOBALS['current_user']->id;
        //bug56843 - test approver name is filled automatically
        $this->_kb->kbdoc_approver_id = $GLOBALS['current_user']->id;
        $this->_kb->save();
    }

    public function tearDown()
    {
        unset($GLOBALS['mod_strings']);
        unset($GLOBALS['app_strings']);
        unset($GLOBALS['current_user']);
        $GLOBALS['db']->query("DELETE FROM kbolddocuments WHERE id = '".$this->_kb->id."'");
        if ( isset($this->_kbrev->id) ) {
            $GLOBALS['db']->query("DELETE FROM kbolddocument_revisions WHERE id = '".$this->_kbrev->id."'");
        }
        if ( isset($this->_docrev->id) ) {
            $GLOBALS['db']->query("DELETE FROM document_revisions WHERE id = '".$this->_docrev->id."'");
        }
        SugarTestHelper::tearDown();
    }

    public function testBug56834ApiHelper() {
        //bug 56834 - the api doesn't return kbdoc_approver_name
        $baseApiHelper = new SugarBeanApiHelper($this->_api);
        $data = $baseApiHelper->formatForApi($this->_kb);
        $this->assertFalse(isset($data['kbdoc_approver_name']));

        //test approver name has been filled
        $data = ApiHelper::getHelper($this->_api,$this->_kb)->formatForApi($this->_kb);
        $this->assertEquals($data['kbdoc_approver_name'],$GLOBALS['current_user']->name);
    }

    public function testAttachmentListApiHelper() {
        $this->markTestIncomplete("Sugar_Injector_Core is trying to get a property that doesn't exist.  Sending to FRM for work.");        
        $data = ApiHelper::getHelper($this->_api,$this->_kb)->formatForApi($this->_kb);

        // Make sure a KBOLDDocument with no attachements returns no values
        $this->assertEquals(0,count($data['attachment_list']));

        // Add a KBOLDDocument Revision, and a document revision to it.
        $this->_kb->load_relationship('revisions');
        $this->_docrev = BeanFactory::newBean('DocumentRevisions');
        $this->assertInstanceOf("SugarBean", $this->_docrev);
        $this->_docrev->change_log = 'Created document revision for KBApiHelper Unit Test';
        $this->_docrev->doc_type = 'Sugar';
        $this->_docrev->revision = '1';
        $this->_docrev->filename = 'unittest.txt';
        $this->_docrev->file_ext = 'txt';
        $this->_docrev->file_mime_type = 'text/plain';
        $this->_docrev->save();

        $this->_kbrev = new KBOLDDocumentRevision();
        $this->_kbrev->change_log = 'Created revision for KBApiHelper Unit Test';
        $this->_kbrev->kbolddocument_id = $this->_kb->id;
        $this->_kbrev->document_revision_id = $this->_docrev->id;
        $this->_kbrev->revision = '1';
        $this->_kbrev->save();
        $this->_kb->revisions->add(array($this->_kbrev));

        // Make sure a KBOLDDocument with one attachements returns one result
        $data = ApiHelper::getHelper($this->_api,$this->_kb)->formatForApi($this->_kb);
        $this->assertEquals(1,count($data['attachment_list']));

    }
}
