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

require_once 'include/download_file.php';
require_once 'include/upload_file.php';

class Bug55455Test extends Sugar_PHPUnit_Framework_TestCase
{
    protected $_actualFile = 'upload/sugartestfile.txt';
    protected $_mockFile   = 'thisfilenamedoesnotexist.doc';
    
    public function setUp()
    {
        sugar_file_put_contents($this->_actualFile, create_guid());
    }
    
    public function tearDown()
    {
        unlink($this->_actualFile);
    }
    
    public function testProperMimeTypeFetching()
    {
        // Default expectation
        $expect = 'text/plain';

        // If the two functions that are used to collect mime data aren't available
        if (!function_exists('mime_content_type') && !function_exists('ext2mime')) {
            // Fall back to what the download class will fall back to
            $expect = 'application/octet-stream';
        }

        // Test actual file
        $dl = new DownloadFile();
        $mime = $dl->getMimeType($this->_actualFile);
        $this->assertEquals($expect, $mime, "Returned mime type [$mime] was not '$expect'");
        
        // Test non existent file
        $mime = $dl->getMimeType($this->_mockFile);
        $this->assertFalse($mime, "$mime should be (boolean) FALSE");
    }
}
