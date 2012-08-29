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

class RestTestPublicMetadataSugarLayouts extends RestTestBase {
    protected $_testPaths = array(
        'wiggle' => 'clients/base/layouts/wiggle/wiggle.php',
        'woggle' => 'custom/clients/base/layouts/woggle/woggle.php',
        'bizzle' => 'clients/portal/layouts/bizzle/bizzle.php',
        'bozzle' => 'custom/clients/portal/layouts/bozzle/bozzle.php',
        'pizzle' => 'clients/mobile/layouts/dizzle/dazzle.php', // Tests improperly named metadata files
        'pozzle' => 'custom/clients/mobile/layouts/pozzle/pozzle.php',
    );
    
    protected $_testFilesCreated = array();
    
    protected $_oldFileContents = array();

    public function setUp()
    {
        //Create an anonymous user for login purposes/
        $this->_user = SugarTestUserUtilities::createAnonymousUser();
        $GLOBALS['current_user'] = $this->_user;

        foreach ($this->_testPaths as $file) {
            preg_match('#clients/(.*)/layouts/#', $file, $m);
            $platform = $m[0];
            $filename = basename($file, '.php');
            $contents = "<?php\n\$viewdefs['$platform']['layout']['$filename'] = array('test' => 'foo');\n";
            if (file_exists($file)) {
                $this->_oldFileContents[$file] = file_get_contents($file);
            } else {
                $this->_testFilesCreated[] = $file;
                
                $dirname  = dirname($file);
                if (!is_dir($dirname)) {
                    mkdir($dirname, 0777, true);
                }
            }
            
            file_put_contents($file, $contents);
        }
    }

    public function tearDown()
    {
        foreach ($this->_oldFileContents as $file => $contents) {
            file_put_contents($file, $contents);
        }
        
        foreach ($this->_testFilesCreated as $file) {
            unlink($file);
        }

        SugarTestUserUtilities::removeAllCreatedAnonymousUsers();
    }

    public function testBaseLayoutRequestAll() {
        $reply = $this->_restCall('metadata/public');
        $this->assertNotEmpty($reply['reply']['layouts'], 'Layouts return data is missing');
        $this->assertTrue(isset($reply['reply']['layouts']['_hash']), 'Layout hash is missing.');
        $this->assertArrayHasKey('wiggle', $reply['reply']['layouts'], 'Test result not found');
    }

    public function testBaseLayoutRequestLayoutsOnly() {
        $reply = $this->_restCall('metadata/public?type_filter=layouts');
        $this->assertNotEmpty($reply['reply']['layouts'], 'Layouts return data is missing');
        $this->assertTrue(isset($reply['reply']['layouts']['_hash']), 'Layout hash is missing.');
        $this->assertArrayHasKey('woggle', $reply['reply']['layouts'], 'Test result not found');
    }

    public function testPortalLayoutRequestAll() {
        $reply = $this->_restCall('metadata/public?platform=portal');
        $this->assertNotEmpty($reply['reply']['layouts'], 'Layouts return data is missing');
        $this->assertTrue(isset($reply['reply']['layouts']['_hash']), 'Layout hash is missing.');
        $this->assertArrayHasKey('bizzle', $reply['reply']['layouts'], 'Test result not found');
    }

    public function testPortalLayoutRequestLayoutsOnly() {
        $reply = $this->_restCall('metadata/public?type_filter=layouts&platform=portal');
        $this->assertNotEmpty($reply['reply']['layouts'], 'Layouts return data is missing');
        $this->assertTrue(isset($reply['reply']['layouts']['_hash']), 'Layout hash is missing.');
        $this->assertArrayHasKey('bozzle', $reply['reply']['layouts'], 'Test result not found');
    }

    public function testMobileLayoutRequestAll() {
        $reply = $this->_restCall('metadata/public?platform=mobile');
        $this->assertNotEmpty($reply['reply']['layouts'], 'Layouts return data is missing');
        $this->assertTrue(isset($reply['reply']['layouts']['_hash']), 'Layout hash is missing.');
        $this->assertArrayHasKey('pozzle', $reply['reply']['layouts'], 'Test result not found');
    }

    public function testMobileLayoutRequestLayoutsOnly() {
        $reply = $this->_restCall('metadata/public?type_filter=layouts&platform=mobile');
        $this->assertNotEmpty($reply['reply']['layouts'], 'Layouts return data is missing');
        $this->assertTrue(isset($reply['reply']['layouts']['_hash']), 'Layout hash is missing.');
        $this->assertEmpty($reply['reply']['layouts']['dizzle'], 'Incorrectly picked up metadata that should not have been read');
        $this->assertArrayHasKey('wiggle', $reply['reply']['layouts'], 'BASE metadata not picked up');
        $this->assertNotEmpty($reply['reply']['layouts']['wiggle']['meta']['test'], 'Test result data not returned');
    }
}
