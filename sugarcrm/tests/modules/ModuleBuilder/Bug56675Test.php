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

require_once 'modules/ModuleBuilder/controller.php';

class Bug56675Test extends Sugar_PHPUnit_Framework_TestCase {
    public $mbController;
    public $mbPackage;
    public $mbModule;
    public $dirname  = 'custom/modulebuilder/packages/test/modules/test/clients/';

    public function setUp() {
        SugarTestHelper::setUp('current_user');
        $GLOBALS['current_user']->is_admin = true;
        SugarTestHelper::setUp('app_list_strings');
        // Cannot use the SugarTestHelper because it requires a module name
        $GLOBALS['mod_strings'] = array();

        $_REQUEST['name'] = 'test';
        $_REQUEST['view'] = 'advanced_search';
        $_REQUEST['view_package'] = 'test';
        $_REQUEST['view_module'] = 'test';

        $this->mbController = new ModuleBuilderController();
        $_REQUEST['description'] = '';
        $_REQUEST['author'] = '';
        $_REQUEST['readme'] = '';
        $_REQUEST['label'] = 'test';
        $_REQUEST['key'] = 'test';
        $this->mbController->action_SavePackage();
        
        $_REQUEST['type'] = 'basic';
        $this->mbController->action_SaveModule();
        unset($_REQUEST['key']);
        unset($_REQUEST['label']);
        unset($_REQUEST['readme']);
        unset($_REQUEST['author']);
        unset($_REQUEST['description']);

    }

    public function tearDown() {

        $_REQUEST['package'] = 'test';
        $_REQUEST['module'] = 'test';
        $_REQUEST['view_module'] = 'test';
        $_REQUEST['view_package']= 'test';
        $this->mbController->action_DeleteModule();
        unset($_REQUEST['view_module']);
        unset($_REQUEST['module']);
        $this->mbController->action_DeletePackage();
        unset($_REQUEST['view_package']);
        unset($_REQUEST['package']);
        unset($this->mbController);

        unset($_REQUEST['view_module']);
        unset($_REQUEST['view_package']);
        unset($_REQUEST['view']);
        unset($_REQUEST['name']);

        SugarTestHelper::tearDown();
    }

	/**
     * @group Bug56675
     * 
     * Tests that a clients directory and metadata files for clients exist after
     * creating a custom module
	 */
    public function testClientsDirectoryCreatedWhenCustomModuleSaved() {
        // Make sure the clients directory is there
        $this->assertFileExists($this->dirname, "$this->dirname was not created when the custom module was saved.");
        
        //BEGIN SUGARCRM flav=pro ONLY
        // Make sure the child directories and files are there for mobile
        $types = array('list', 'edit', 'detail');
        foreach ($types as $type) {
            $dir = $this->dirname . 'mobile/views/' . $type;
            $this->assertFileExists($dir, "$dir directory was not created when the module was saved");
            
            $file = $dir . '/' . $type . '.php';
            $this->assertFileExists($file, "$file was not created when module was saved");
        }
        //END SUGARCRM flav=pro ONLY
        
        //BEGIN SUGARCRM flav=ent ONLY
        // Now make sure list copied over for portal (list is only portal view def for basic)
        $dir = $this->dirname . 'portal/views/list';
        $this->assertFileExists($dir, "$dir directory was not created when the module was saved");
        
        $file = $dir . '/list.php';
        $this->assertFileExists($file, "$file was not created when module was saved");
        //END SUGARCRM flav=ent ONLY
    }
}