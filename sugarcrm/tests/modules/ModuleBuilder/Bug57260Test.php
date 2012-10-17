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

require_once 'modules/ModuleBuilder/controller.php';
require_once 'modules/ModuleBuilder/parsers/ParserFactory.php';

/**
 * Bug 57260 - Panel label of mobile layout in module builder is wrong
 */
class Bug57260Test extends Sugar_PHPUnit_Framework_TestCase {
    public $mbController;
    public $mbPackage;
    public $mbModule;
    

    public function setUp() {
        SugarTestHelper::setUp('current_user');
        $GLOBALS['current_user']->is_admin = true;
        SugarTestHelper::setUp('app_list_strings');
        SugarTestHelper::setUp('app_strings');
        SugarTestHelper::setUp('mod_strings', array('ModuleBuilder'));

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
     * @group Bug57260
     * 
     * Tests that the default panel label of LBL_PANEL_DEFAULT correctly translates
     * to 'Default' when rendered for undeployed modules in studio
	 */
    public function testUndeployedModuleHasDefaultLabelInStudioLayoutEditor() {
        // Mock the request
        $_REQUEST['module'] = 'ModuleBuilder';
        $_REQUEST['MB'] = true;
        $_REQUEST['action'] = 'editLayout';
        $_REQUEST['view'] = 'wirelessdetailview';
        $_REQUEST['view_module'] = 'test';
        $_REQUEST['view_package']= 'test';
        
        // Get the view we need
        require_once 'modules/ModuleBuilder/views/view.layoutview.php';
        $view = new ViewLayoutView();
        
        // Get the output
        ob_start();
        $view->display();
        $output = ob_get_clean();
        $output = json_decode($output);
        _ppl($output);
        
        // Test that our output is what we wanted
        $this->assertNotEmpty($output->center->content, "Expected output from parsing layout editor not returned");

        // Test the actual output
        $this->assertRegExp("|<span class='panel_name'?.*>\s*Default\s*</span>|", $output->center->content, "'Default' was not found in the rendered view");
    }
}