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
require_once 'modules/Administration/controller.php';

class Bug59196Test extends Sugar_PHPUnit_Framework_TestCase
{
    protected $_request;
    protected $_customFile = 'custom/include/MVC/Controller/wireless_module_registry.php';
    public function setUp()
    {
        SugarTestHelper::setUp('app_strings');
        SugarTestHelper::setUp('app_list_strings');
        SugarTestHelper::setUp('moduleList');
        SugarTestHelper::setUp('current_user', array(true, true)); // Admin
        
        // Backup the custom file if there is one
        if (file_exists($this->_customFile)) {
            $this->_backedUp = true;
            rename($this->_customFile, $this->_customFile . '.backup');
            
            // Remove it from the autoloader as well
            SugarAutoLoader::delFromMap($this->_customFile);
        }
        
        // Backup the request
        $this->_request = $_REQUEST;
    }
    
    public function tearDown()
    {
        $_REQUEST = $this->_request;
        
        @unlink($this->_customFile);
        SugarAutoLoader::delFromMap($this->_customFile);
        
        if ($this->_backedUp) {
            rename($this->_customFile . '.backup', $this->_customFile);
            SugarAutoLoader::addToMap($this->_customFile);
        }
        
        SugarTestHelper::tearDown();
    }

    /**
     * @group Bug59196
     */
    public function testChangingMobileModuleListMaintainsSelectedOrder()
    {
        // Add Documents module to the list
        $_REQUEST['enabled_modules'] = "Accounts,Documents,Contacts,Leads";
        $admin = new AdministrationController();
        
        // Capturing the output since that could affect the suite
        ob_start();
        $admin->action_updatewirelessenabledmodules();
        $out = ob_get_clean();
        
        // Begin assertions
        $this->assertFileExists($this->_customFile, "Custom wireless module registry file was not written");
        
        include $this->_customFile;
        
        $this->assertTrue(isset($wireless_module_registry), "Wireless module registry not found in the custom file");
        $this->assertInternalType('array', $wireless_module_registry, "Wireless module registry is not an array");
        $this->assertEquals(4, count($wireless_module_registry), "Expected wireless module registry to contain 4 modules");
        
        // Grab the keys and compare
        $modules = array_keys($wireless_module_registry);
        $this->assertEquals('Documents', $modules[1], "Second module in wireless module list should be 'Documents'");
    }
}