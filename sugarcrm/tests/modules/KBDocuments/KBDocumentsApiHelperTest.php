<?php
//File SUGARCRM flav=pro ONLY
/*********************************************************************************
     * The contents of this file are subject to the SugarCRM Master Subscription
     * Agreement ("License") which can be viewed at
     * http://www.sugarcrm.com/crm/master-subscription-agreement
     * By installing or using this file, You have unconditionally agreed to the
     * terms and conditions of the License, and You may not use this file except in
     * compliance with the License.  Under the terms of the license, You shall not,
     * among other things: 1) sublicense, resell, rent, lease, redistribute, assign
     * or otherwise transfer Your rights to the Software, and 2) use the Software
     * for timesharing or service bureau purposes such as hosting the Software for
     * commercial gain and/or for the benefit of a third party.  Use of the Software
     * may be subject to applicable fees and any use of the Software without first
     * paying applicable fees is strictly prohibited.  You do not have the right to
     * remove SugarCRM copyrights from the source code or user interface.
     *
     * All copies of the Covered Code must include on each user interface screen:
     *  (i) the "Powered by SugarCRM" logo and
     *  (ii) the SugarCRM copyright notice
     * in the same form as they appear in the distribution.  See full license for
     * requirements.
     *
     * Your Warranty, Limitations of liability and Indemnity are expressly stated
     * in the License.  Please refer to the License for the specific language
     * governing these rights and limitations under the License.  Portions created
     * by SugarCRM are Copyright (C) 2004-2011 SugarCRM, Inc.; All Rights Reserved.
     ********************************************************************************/

require_once 'modules/KBDocuments/KBDocument.php';
require_once 'include/api/ApiHelper.php';
require_once 'include/api/RestService.php';
require_once('data/SugarBeanApiHelper.php');


class KBDocumentsApiHelperTest extends Sugar_PHPUnit_Framework_TestCase
{

    private $_kb =null;

    public function setUp()
    {
        $GLOBALS['app_strings'] = return_application_language('en_us');
        SugarTestHelper::setUp("current_user");
        $GLOBALS['mod_strings'] = return_module_language($GLOBALS['current_language'], 'KBDocuments');
        $this->_api = new RestService();
        $this->_api->user = $GLOBALS['current_user'];
        SugarTestHelper::setUp('beanList');

        //create the KBDocument record
        $this->_kb = new KBDocument();
        $this->_kb->description = 'This is a unit test for the kb document api helper';
        $this->_kb->kbdocument_name = 'KBUnitTest API Helper';
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
        $GLOBALS['db']->query("DELETE FROM kbdocuments WHERE id = '".$this->_kb->id."'");
        if ( isset($this->_kbrev->id) ) {
            $GLOBALS['db']->query("DELETE FROM kbdocument_revisions WHERE id = '".$this->_kbrev->id."'");
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

        // Make sure a KBDocument with no attachements returns no values
        $this->assertEquals(0,count($data['attachment_list']));

        // Add a KBDocument Revision, and a document revision to it.
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

        $this->_kbrev = new KBDocumentRevision();
        $this->_kbrev->change_log = 'Created revision for KBApiHelper Unit Test';
        $this->_kbrev->kbdocument_id = $this->_kb->id;
        $this->_kbrev->document_revision_id = $this->_docrev->id;
        $this->_kbrev->revision = '1';
        $this->_kbrev->save();
        $this->_kb->revisions->add(array($this->_kbrev));

        // Make sure a KBDocument with one attachements returns one result
        $data = ApiHelper::getHelper($this->_api,$this->_kb)->formatForApi($this->_kb);
        $this->assertEquals(1,count($data['attachment_list']));

    }
}
