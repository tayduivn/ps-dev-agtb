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
    public $tempFileFrom = 'tests/clients/base/api/FileApiTempFileFrom.txt';
    public $tempFileTo;

    public function setUp() {
        SugarTestHelper::setUp("current_user");
        SugarTestHelper::setUp("ACLStatic");
        // load up the unifiedSearchApi for good times ahead
        $this->fileApi = $this->getMock('FileApiMockUp', array('getDownloadFileApi'));
        $this->fileApi
            ->expects($this->any())
            ->method('getDownloadFileApi')
            ->with($this->isInstanceOf('ServiceBase'))
            ->will($this->returnCallback(function ($service) {
                return new DownloadFileApi($service);
            }));


        $document = BeanFactory::newBean('Documents');
        $document->name = "RelateApi setUp Documents";
        $document->save();
        $this->documents[] = $document;

        // no Document view, delete
        $acldata['module']['view']['aclaccess'] = ACL_ALLOW_NONE;
        $acldata['module']['delete']['aclaccess'] = ACL_ALLOW_NONE;
        ACLAction::setACLData($GLOBALS['current_user']->id, 'Documents', $acldata);
    }

    public function tearDown() {
        // Clean up temp file stuff
        if ($this->tempFileTo && file_exists($this->tempFileTo)) {
            @unlink($this->tempFileTo);
        }

        $GLOBALS['current_user']->is_admin = 1;

        foreach($this->documents AS $document) {
            $document->mark_deleted($document->id);
        }

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

    public function testCreateTempFileFromInput()
    {
        // Tests checking encoding requests
        $encoded = $this->fileApi->isFileEncoded(new FileApiServiceMockUp(), array('content_transfer_encoding' => 'base64'));
        $this->assertTrue($encoded, "Encoded request check failed");

        // Handle our test of file encoding
        $this->tempFileTo = $this->fileApi->getTempFileName();
        $this->fileApi->createTempFileFromInput($this->tempFileTo, $this->tempFileFrom, $encoded);

        // Test that the temporary file was created
        $this->assertFileExists($this->tempFileTo, "Temp file was not created");

        // Test that the contents of the new file are the base64_decoded contents of the test file
        $createdContents = file_get_contents($this->tempFileTo);
        $encodedContents = base64_decode(file_get_contents($this->tempFileFrom));
        $this->assertEquals($createdContents, $encodedContents, "Creating temp file from encoded file failed");
    }

    public function testCreateTempFileFromInputNoEncoding()
    {
        // Tests checking encoding requests
        $encoded = $this->fileApi->isFileEncoded(new FileApiServiceMockUp(), array());
        $this->assertFalse($encoded, "Second encoded request check failed");

        // Handle our test of file encoding
        $this->tempFileTo = $this->fileApi->getTempFileName();
        $this->fileApi->createTempFileFromInput($this->tempFileTo, $this->tempFileFrom, $encoded);

        // Test that the temporary file was created
        $this->assertFileExists($this->tempFileTo, "Temp file was not created");

        // Test that the contents of the new file are the same as the contents of the test file
        $createdContents = file_get_contents($this->tempFileTo);
        $encodedContents = file_get_contents($this->tempFileFrom);
        $this->assertEquals($createdContents, $encodedContents, "Creating temp file from encoded file failed");
    }

    /**
     * Test protected method getDownloadFileApi
     */
    public function testGetDownloadFileApi()
    {
        $method = new ReflectionMethod('FileApi', 'getDownloadFileApi');
        $method->setAccessible(true);

        $api = new FileApi();
        $result = $method->invoke($api, new FileApiServiceMockUp());

        $this->assertNotEmpty($result);
        $this->assertInstanceOf('DownloadFileApi', $result);
    }
}

class FileApiServiceMockUp extends RestService
{
    public function execute() {}
    protected function handleException(Exception $exception) {}
}

class FileApiMockUp extends FileApi 
{
    public function createTempFileFromInput($tempfile, $input, $encoded = false)
    {
        parent::createTempFileFromInput($tempfile, $input, $encoded);
    }
    
    public function isFileEncoded($api, $args)
    {
        return parent::isFileEncoded($api, $args);
    }
}
