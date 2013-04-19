<?php
/*********************************************************************************
 * The contents of this file are subject to the SugarCRM Professional End User
 * License Agreement ("License") which can be viewed at
 * http://www.sugarcrm.com/EULA.  By installing or using this file, You have
 * unconditionally agreed to the terms and conditions of the License, and You may
 * not use this file except in compliance with the License. Under the terms of the
 * license, You shall not, among other things: 1) sublicense, resell, rent, lease,
 * redistribute, assign or otherwise transfer Your rights to the Software, and 2)
 * use the Software for timesharing or service bureau purposes such as hosting the
 * Software for commercial gain and/or for the benefit of a third party.  Use of
 * the Software may be subject to applicable fees and any use of the Software
 * without first paying applicable fees is strictly prohibited.  You do not have
 * the right to remove SugarCRM copyrights from the source code or user interface.
 * All copies of the Covered Code must include on each user interface screen:
 * (i) the "Powered by SugarCRM" logo and (ii) the SugarCRM copyright notice
 * in the same form as they appear in the distribution.  See full license for
 * requirements.  Your Warranty, Limitations of liability and Indemnity are
 * expressly stated in the License.  Please refer to the License for the specific
 * language governing these rights and limitations under the License.
 * Portions created by SugarCRM are Copyright (C) 2004 SugarCRM, Inc.;
 * All Rights Reserved.
 ********************************************************************************/

require_once('tests/rest/RestTestBase.php');

/***
 * Used to test Mail Attachment API in Emails Module endpoints from MailAttachmentApi.php
 */
class MailAttachmentApiTest extends RestTestBase
{
    public function setUp()
    {
        parent::setUp();

        $this->emailUI = new EmailUI();
        $this->emailUI->preflightUserCache();
        $this->userCacheDir = $this->emailUI->userCacheDir;
    }

    public function tearDown()
    {
        parent::tearDown();
        if (file_exists($this->userCacheDir)) {
            rmdir_recursive($this->userCacheDir);
        }
    }

    /**
     * @group mailattachmentapi
     */
    public function testClearUserCache_UserCacheDirDoesNotExist_CreatedSuccessfully()
    {
        $this->markTestIncomplete('Migrate this to SOAP UI');
        if (file_exists($this->userCacheDir)) {
            rmdir_recursive($this->userCacheDir);
        }
        $this->_restCall("MailAttachment/cache", '', "DELETE");
        $this->_assertCacheDirCreated();
        $this->_assertCacheDirEmpty();
    }

    /**
     * @group mailattachmentapi
     */
    public function testClearUserCache_UserCacheDirContainsFiles_ClearedSuccessfully()
    {
        $this->markTestIncomplete('Migrate this to SOAP UI');
        sugar_file_put_contents($this->userCacheDir . "/test.txt", create_guid());
        $this->_restCall("MailAttachment/cache", '', "DELETE");
        $this->_assertCacheDirCreated();
        $this->_assertCacheDirEmpty();
    }

    /**
     * @group mailattachmentapi
     */
    public function testSaveAttachment_FileUploaded_CreatedSuccessfully()
    {
        $this->markTestIncomplete('Migrate this to SOAP UI');
        //create the test file
        $uploadFile = 'upload/' . create_guid();
        $fileContents = create_guid();
        file_put_contents($uploadFile, $fileContents);

        $this->_restCall('MailAttachment', array('email_attachment' => '@' . $uploadFile));

        //verify it was uploaded
        $files = findAllFiles($this->userCacheDir, array());
        $actualContents = file_get_contents($files[0]);
        $this->assertEquals($fileContents, $actualContents, "File uploaded should match file in cache directory");

        //clean up
        unlink($uploadFile);
    }

    /**
     * @group mailattachmentapi
     */
    public function testRemoveAttachment_FileExists_RemovedSuccessfully()
    {
        $this->markTestIncomplete('Migrate this to SOAP UI');
        //clear the cache first
        $em = new EmailUI();
        $em->preflightUserCache();

        //create the test attachment to be removed
        $fileGuid = create_guid();
        sugar_file_put_contents($this->userCacheDir . '/' . $fileGuid, create_guid());

        $this->_restCall('MailAttachment/' . $fileGuid, '', 'DELETE');

        //verify it was removed
        $this->_assertCacheDirEmpty();
    }

    /**
     * Check to make sure path is created
     */
    protected function _assertCacheDirCreated()
    {
        $this->assertTrue(file_exists($this->userCacheDir), "Cache directory should exist");
    }

    /**
     * Check to make sure path is empty
     */
    protected function _assertCacheDirEmpty()
    {
        $files = findAllFiles($this->userCacheDir, array());
        $this->assertEquals(0, count($files), "Cache directory should be empty");
    }
}
