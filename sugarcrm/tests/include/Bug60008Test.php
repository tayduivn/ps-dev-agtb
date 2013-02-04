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

class Bug60008Test extends Sugar_PHPUnit_Framework_TestCase
{
    protected $_language = 'en_us'; // Test against English
    protected $_testCustFile = 'tests/include/Bug60008TestCustFile.php';
    protected $_custFile = 'custom/include/language/en_us.lang.php';
    protected $_backedUp = false;
    
    public function setUp()
    {
        // Back up existing custom app list strings if they exist
        if (file_exists($this->_custFile)) {
            rename($this->_custFile, $this->_custFile . '-backup');
            $this->_backedUp = true;
        } 
        
        // For cases in which this test runs before the custom include directory
        // is created
        $custDir = dirname($this->_custFile);
        if (!is_dir($custDir)) {
            mkdir_recursive($custDir);
        }
        
        copy($this->_testCustFile, $this->_custFile);
        
        // File map cache this bad boy
        SugarAutoLoader::addToMap($this->_custFile);
    }
    
    public function tearDown()
    {
        // Delete the custom file we just created
        unlink($this->_custFile);
        
        if ($this->_backedUp) {
            // Move the backup back into place. No need to mess with the file map cache
            rename($this->_custFile . '-backup', $this->_custFile);
        } else {
            // There was no back up, so remove this from the file map cache
            SugarAutoLoader::delFromMap($this->_custFile);
        }
    }

    /**
     * Tests that both $app_list_strings and $GLOBALS['app_list_strings'] are picked
     * up when getting app_list_strings
     * 
     * @group Bug60008
     */
    public function testAppListStringsParsedEvenWhenInGlobals()
    {
        $als = return_app_list_strings_language($this->_language);
        
        // Assert that the indexes are found
        $this->assertArrayHasKey('aaa_test_list', $als, "First GLOBALS index not found");
        $this->assertArrayHasKey('bbb_test_list', $als, "First app_list_strings index not found");
        $this->assertArrayHasKey('ccc_test_list', $als, "Second GLOBALS index not found");
        
        // Assert that the indexes actually have elements
        $this->assertArrayHasKey('boop', $als['bbb_test_list'], "An element of the first app_list_strings array was not found");
        $this->assertArrayHasKey('sam', $als['ccc_test_list'], "An element of the second GLOBALS array not found");
    }
}