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

require_once 'tests/rest/RestTestBase.php';
class RestMetadataModuleListMobileTest extends RestTestBase {
    public $unitTestFiles = array();

    // Need to set the platform to something else
    protected function _restLogin($username = '', $password = '', $platform = 'mobile')
    {
        return parent::_restLogin($username,$password,$platform);
    }

    public function setUp()
    {
        parent::setUp();
        $this->unitTestFiles[] = 'custom/include/MVC/Controller/wireless_module_registry.php';
    }
    public function tearDown()
    {
        foreach($this->unitTestFiles as $unitTestFile ) {
            if ( file_exists($unitTestFile) ) {
                // Ignore the warning on this, the file stat cache causes the file_exist to trigger even when it's not really there
                SugarAutoLoader::unlink($unitTestFile);
            }
        }
        SugarAutoLoader::saveMap();
        parent::tearDown();
    }
    /**
     * @group rest
     */
    public function testMetadataGetModuleListMobile() {
        $this->_clearMetadataCache();
        $restReply = $this->_restCall('me');

        foreach (SugarAutoLoader::existingCustom('include/MVC/Controller/wireless_module_registry.php') as $file) {
            require $file;
        }

        // $wireless_module_registry is defined in the file loaded above
        $enabledMobile = array_keys($wireless_module_registry);


        $this->assertTrue(isset($restReply['reply']['current_user']['module_list']),'There is no mobile module list');
        $restModules = $restReply['reply']['current_user']['module_list'];
        unset($restModules['_hash']);
        foreach ( $enabledMobile as $module ) {
            $this->assertTrue(in_array($module,$restModules),'Module '.$module.' missing from the mobile module list.');
        }
        $this->assertEquals(count($enabledMobile),count($restModules),'There are extra modules in the mobile module list');

        // Create a custom set of wireless modules to test if it is loading those properly
        SugarAutoLoader::ensureDir('custom/include/MVC/Controller');
        SugarAutoLoader::put('custom/include/MVC/Controller/wireless_module_registry.php','<'."?php\n".'$wireless_module_registry = array("Accounts"=>"Accounts","Contacts"=>"Contacts","Opportunities"=>"Opportunities");', true);

        $enabledMobile = array('Accounts','Contacts','Opportunities');

        $this->_clearMetadataCache();
        $restReply = $this->_restCall('me');
        $this->assertTrue(isset($restReply['reply']['current_user']['module_list']),'There is no mobile module list on the second pass');
        $restModules = $restReply['reply']['current_user']['module_list'];
        foreach ( $enabledMobile as $module ) {
            $this->assertTrue(in_array($module,$restModules),'Module '.$module.' missing from the mobile module list on the second pass');
        }
        $this->assertEquals(count($enabledMobile),count($restModules),'There are extra modules in the mobile module list on the second pass');


    }

}