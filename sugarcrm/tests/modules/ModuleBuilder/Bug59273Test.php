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

/**
 * Bug 59273 - Field name in viewdefs has different char case as in vardefs
 */
class Bug59273Test extends Sugar_PHPUnit_Framework_TestCase
{
    protected $_viewFile = 'custom/modulebuilder/packages/test/modules/test/clients/mobile/views/list/list.php';
    protected $_request = array();
    protected $_mbc;
    
    public function setUp() {
        SugarTestHelper::setUp('app_list_strings');
        SugarTestHelper::setUp('app_strings');
        SugarTestHelper::setUp('current_user', array(true, true));
        SugarTestHelper::setUp('mod_strings', array('ModuleBuilder'));
        
        $this->_request = $_REQUEST;

        $_REQUEST['name'] = 'test';
        $_REQUEST['view_package'] = 'test';
        $_REQUEST['view_module'] = 'test';

        $this->_mbc = new ModuleBuilderController();
        $_REQUEST['description'] = '';
        $_REQUEST['author'] = '';
        $_REQUEST['readme'] = '';
        $_REQUEST['label'] = 'test';
        $_REQUEST['key'] = 'test';
        $this->_mbc->action_SavePackage();
        
        $_REQUEST['type'] = 'issue';
        $this->_mbc->action_SaveModule();
        unset($_REQUEST);
    }

    public function tearDown() {

        $_REQUEST['package'] = 'test';
        $_REQUEST['module'] = 'test';
        $_REQUEST['view_module'] = 'test';
        $_REQUEST['view_package']= 'test';
        $this->_mbc->action_DeleteModule();
        unset($_REQUEST['view_module']);
        unset($_REQUEST['module']);
        $this->_mbc->action_DeletePackage();
        
        $_REQUEST = $this->_request;

        SugarTestHelper::tearDown();
    }

    /**
     * Tests field name casing for mobile list views
     * 
     * @group Bug59273
     */
    public function testCustomModuleListViewDefsUseCorrectCase()
    {
        $this->assertFileExists($this->_viewFile, "Custom module list view file {$this->_viewFile} was not found");
        
        include $this->_viewFile;
        
        $this->assertTrue(isset($viewdefs['test_test']['mobile']['view']['list']['panels']), "Cannot find the panels in the mobile list view defs");
        $panels = $viewdefs['test_test']['mobile']['view']['list']['panels'];
        $this->assertTrue(isset($panels[0]['fields'][0]), "First member of the fields array not found in the mobile list view defs");
        $test = $this->_hasField('test_test_number', $panels[0]['fields']);
        $this->assertTrue($test, "Lowercase test_test_number not found in the fields array");
    }

    /**
     * Simple field searcher
     * 
     * @param string $field The field to look for
     * @param array $fields The fields array to search in
     * @return bool
     */
    protected function _hasField($field, $fields)
    {
        foreach ($fields as $f) {
            if (isset($f['name']) && $f['name'] == $field) {
                return true;
            }
        }
        
        return false;
    }
}