<?php
//FILE SUGARCRM flav=pro ONLY
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

require_once('include/externalAPI/Google/ExtAPIGoogle.php');


/**
 * @ticket 43652
 */
class Bug43652Test extends Sugar_PHPUnit_Framework_TestCase
{
    private $fileData1;
    private $extAPI;

    public function setUp()
    {
        //Just need base class but its abstract so we use the google implementation for this test.
        $this->extAPI = new ExtAPIGoogle();
        $this->fileData1 = sugar_cached('unittest');
        file_put_contents($this->fileData1, "Unit test for mime type");
    }

    public function tearDown()
	{
        unlink($this->fileData1);
	}

    function _fileMimeProvider()
    {
        return array(
            array( array('name' => 'te.st.png','type' => 'img/png'),'img/png'),
            array( array('name' => 'test.jpg','type' => 'img/jpeg'),'img/jpeg'),
            array( array('name' => 'test.out','type' => 'application/octet-stream'),'application/octet-stream'),
            array( array('name' => 'test_again','type' => 'img/png'),'img/png'),
        );
    }

    /**
     * Test the getMime function for the use case where the mime type is already provided.
     *
     * @dataProvider _fileMimeProvider
     */
    public function testUploadFileWithMimeType($file_info, $expectedMime)
    {
        $uf = new UploadFile('');
        $mime = $uf->getMime($file_info);

        $this->assertEquals($expectedMime, $mime);
    }

    /**
     * Test file with no extension but with provided mime-type
     *
     * @return void
     */
    public function testUploadFileWithEmptyFileExtension()
    {
        $file_info = array('name' => 'test', 'type' => 'application/octet-stream', 'tmp_name' => $this->fileData1);
        $expectedMime = $this->extAPI->isMimeDetectionAvailable() ? 'text/plain' : 'application/octet-stream';
        $uf = new UploadFile('');
        $mime = $uf->getMime($file_info);
        $this->assertEquals($expectedMime, $mime);
    }


    /**
     * Test file with no extension and no provided mime-type
     *
     * @return void
     */
    public function testUploadFileWithEmptyFileExtenEmptyMime()
    {
        $file_info = array('name' => 'test','tmp_name' => $this->fileData1);
        $expectedMime = $this->extAPI->isMimeDetectionAvailable() ? 'text/plain' : 'application/octet-stream';
        $uf = new UploadFile('');
        $mime = $uf->getMime($file_info);
        $this->assertEquals($expectedMime, $mime);
    }
}
