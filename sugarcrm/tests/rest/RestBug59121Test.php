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
require_once 'tests/rest/RestTestBase.php';

class RestBug59121Test extends RestTestBase
{
    protected $_backedUp = false;
    protected $_customFile = 'custom/include/MVC/Controller/wireless_module_registry.php';
    protected $_request = array();

    public function setUp()
    {
        parent::setUp();
        
        // User needs to be an admin user
        $this->_user->is_admin = 1;
        $this->_user->save();
        
        // Check for an existing custom mobile file. If found, remove it. Hard.
        if (file_exists($this->_customFile)) {
            $this->_backedUp = true;
            rename($this->_customFile, $this->_customFile . '.backup');
            
            // Remove it from the autoloader as well
            SugarAutoLoader::delFromMap($this->_customFile);
        }
        
        $this->_request = $_REQUEST;
        
        $this->_clearMetadataCache();
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
        
        parent::tearDown();
    }

    /**
     * @group rest
     * @group Bug59121
     */
    public function testEnablingMobileModulesClearsMetadataCache()
    {
        // Force a mobile platform login since that's what we are testing
        $this->_restLogin($this->_user->user_name, $this->_user->user_name, 'mobile');
        
        // First test... no Documents module in the metadata request
        $reply = $this->_restCall('metadata?type_filter=modules');
        $this->assertArrayHasKey('modules', $reply['reply'], 'The modules list was not found in the response');
        $this->assertArrayNotHasKey('Documents', $reply['reply']['modules'], "Documents was found in the mobile modules array and it should not have been");
        
        // Now add the Documents module to the list
        $_REQUEST['enabled_modules'] = "Accounts,Documents,Contacts,Leads,Opportunities,Cases,Calls,Tasks,Meetings,Employees,Reports,Users";
        $admin = new AdministrationController();
        
        // Capturing the output since that could affect the suite
        ob_start();
        $admin->action_updatewirelessenabledmodules();
        $out = ob_get_clean();
        
        // Now test to make sure it is there
        $reply = $this->_restCall('metadata?type_filter=modules');
        $this->assertArrayHasKey('modules', $reply['reply'], 'The modules list was not found in the response for the second request');
        $this->assertArrayHasKey('Documents', $reply['reply']['modules'], "Documents was NOT found in the mobile modules array and it should have been");
    }
}