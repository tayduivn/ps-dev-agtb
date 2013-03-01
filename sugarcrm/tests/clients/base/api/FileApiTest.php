<?php
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
 * by SugarCRM are Copyright (C) 2004-2012 SugarCRM, Inc.; All Rights Reserved.
 ********************************************************************************/



require_once ('include/api/RestService.php');
require_once ("clients/base/api/FileApi.php");


/**
 * @group ApiTests
 */
class FileApiTest extends Sugar_PHPUnit_Framework_TestCase 
{
    public $acl = array();
    public $documents;
    public $fileApi;

    public function setUp() {
        SugarTestHelper::setUp("current_user");
        // load up the unifiedSearchApi for good times ahead
        $this->fileApi = new FileApi();
        $document = BeanFactory::newBean('Documents');
        $document->name = "RelateApi setUp Documents";
        $document->save();
        $this->documents[] = $document;

        // save ACLs
        $this->acl = $_SESSION['ACL'];
        unset($_SESSION['ACL']);
        
        // no Account view, delete
        $_SESSION['ACL'][$GLOBALS['current_user']->id]['Documents']['module']['view']['aclaccess'] = ACL_ALLOW_NONE;
        $_SESSION['ACL'][$GLOBALS['current_user']->id]['Documents']['module']['delete']['aclaccess'] = ACL_ALLOW_NONE;
    }

    public function tearDown() {
        $GLOBALS['current_user']->is_admin = 1;

        foreach($this->documents AS $document) {
            $document->mark_deleted($document->id);
        }

        unset($_SESSION['ACL']);
        $_SESSION['ACL'] = $this->acl;
        SugarTestHelper::tearDown();
        parent::tearDown();        
    }

    public function testSaveFilePost()
    {
        $this->setExpectedException(
          'SugarApiExceptionNotAuthorized'
        );          
        $this->fileApi->saveFilePost(new FileApiServiceMockUp(), array('module' => 'Documents', 'record' => $this->documents[0]->id, 'field' => 'filename'));
    }

    public function testGetFileList()
    {
        $this->setExpectedException(
          'SugarApiExceptionNotAuthorized'
        );          
        $this->fileApi->getFileList(new FileApiServiceMockUp(), array('module' => 'Documents', 'record' => $this->documents[0]->id, 'field' => 'filename'));
    }
    
}

class FileApiServiceMockUp extends RestService
{
    public function execute() {}
    protected function handleException(Exception $exception) {}
}
