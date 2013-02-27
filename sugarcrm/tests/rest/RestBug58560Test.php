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
require_once 'tests/rest/RestTestBase.php';

/**
 * Bug 58560 - Custom address don't have group property in vardefs
 */
class Bug58560Test extends RestTestBase
{
    /**
     * Module Builder Controller
     * 
     * @var ModuleBuilderController
     */
    protected static $_mb;

    /**
     * Holder for the current request array
     * 
     * @var array
     */
    protected static $_request = array();

    /**
     * Mock request for creating a field
     * 
     * @var array
     */
    protected static $_createFieldRequestVars = array(
        "action" => "saveField",
        "comments" => "",
        "default" => "",
        "dependency" => "",
        "dependency_display" => "",
        "duplicate_merge" => "0",
        "enforced" => "false",
        "formula" => "",
        "formula_display" => "",
        "help" => "",
        "importable" => "true",
        "is_update" => "true",
        "labelValue" => "Test Address",
        "label" => "LBL_TEST_ADDRESS",
        "new_dropdown" => "",
        "reportableCheckbox" => "1",
        "reportable" => "1",
        "to_pdf" => "true",
        "type" => "address",
        "name" => "test_address",
        "module" => "ModuleBuilder",
        "view_module" => "Accounts",
    );

    /**
     * Mock request for deleting a field
     * 
     * @var array
     */
    protected static $_deleteFieldRequestVars = array(
        "action" => "DeleteField",
        "labelValue" => "Test Address",
        "label" => "LBL_TEST_ADDRESS",
        "to_pdf" => "true",
        "type" => "varchar",
        "name" => "test_address_c",
        "module" => "ModuleBuilder",
        "view_module" => "Accounts",
    );
    
    public static function setUpBeforeClass()
    {
        // Basic setup of the environment
        SugarTestHelper::setUp('current_user', array(true, true));
        SugarTestHelper::setUp('beanList');
        SugarTestHelper::setUp('beanFiles');
        SugarTestHelper::setUp('app_list_strings');
        SugarTestHelper::setUp('mod_strings', array('ModuleBuilder'));
        
        // Back up and reset the REQUEST
        self::$_request = $_REQUEST;
        $_REQUEST = self::$_createFieldRequestVars;
        
        // Build up the controller to save the new field
        self::$_mb = new ModuleBuilderController();
        self::$_mb->metadataApiCacheCleared = false;
        self::$_mb->action_SaveField();
    }
    
    public static function tearDownAfterClass()
    {
        // Set the request to delete the test field
        $_REQUEST = self::$_deleteFieldRequestVars;
        
        // Loop through the created fields and wipe them out
        $suffixes = array('street', 'city', 'state', 'postalcode', 'country');
        foreach ($suffixes as $suffix) {
            $_REQUEST['name'] = self::_getFieldName($suffix);
            self::$_mb->metadataApiCacheCleared = false;
            self::$_mb->action_DeleteField();
        }
        
        // Clean up the environment
        SugarTestHelper::tearDown();
        
        // Reset the request
        $_REQUEST = self::$_request;
    }

    /**
     * Since our setup is needed before class, but we still need the rest utils,
     * we will simply override the rest base setup method, setting up only the
     * current user.
     */
    public function setUp()
    {
        // Copied from RestTestBase and modified for our use here
        // Create an anonymous user for login purposes
        $this->_user = $GLOBALS['current_user'];
    }
    
    /**
     * Since our teardown should be done after class we will simply override the
     * rest base teardown method.
     */
    public function tearDown()
    {
        
    }
    
    /**
     * @group 58560
     * @dataProvider _testFieldFileProvider
     */
    public function testCustomFieldMetaDataFilesSaved($suffix)
    {
        $field = self::$_deleteFieldRequestVars['name'];
        $name = self::_getFieldName($suffix);
        $file = 'custom/Extension/modules/Accounts/Ext/Vardefs/sugarfield_' . $name . '.php';
        $this->assertFileExists($file, "Custom field vardefs file not found");
        
        require $file;
        
        $this->assertNotEmpty($dictionary['Account']['fields'][$name]['group'], "The group setting was not saved");
        $this->assertEquals($dictionary['Account']['fields'][$name]['group'], $field, "Field group was not saved correctly");
    }
    
    /**
     * @group rest
     * @group 58560
     */
    public function testGroupSetForAddressInMetaData()
    {
        $field = self::$_deleteFieldRequestVars['name'];
        $reply = $this->_restCall("metadata?module_filter=Accounts&type_filter=modules");
        $this->assertNotEmpty($reply['reply']['modules']['Accounts']['fields'], "Fields metadata array is empty");
        
        // Break it down a bit
        $fields = $reply['reply']['modules']['Accounts']['fields'];
        
        // This is kinda dirty, but it saves us from making 5 rest calls
        foreach ($this->_testFieldFileProvider() as $params) {
            $name = self::_getFieldName($params['suffix']);
            $this->assertArrayHasKey($name,$fields, "The field $name is missing");
            $this->assertNotEmpty($fields[$name]['group'], "Group index of the fields metadata for $name is not set");
            $this->assertEquals($fields[$name]['group'], $field, "Field group {$fields[$name]['group']} did not match the known field name $field");
        }
    }
    
    public function _testFieldFileProvider()
    {
        return array(
            array('suffix' => 'street'),
            array('suffix' => 'city'),
            array('suffix' => 'state'),
            array('suffix' => 'postalcode'),
            array('suffix' => 'country'),
        );
    }
    
    protected static function _getFieldName($suffix)
    {
        $field = self::$_createFieldRequestVars['name'];
        $name = $field . '_' . $suffix . '_c';
        return $name;
    }
}
