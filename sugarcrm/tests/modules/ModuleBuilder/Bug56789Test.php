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
require_once 'modules/ModuleBuilder/parsers/views/SearchViewMetaDataParser.php';
require_once 'modules/ModuleBuilder/parsers/MetaDataFiles.php';

class Bug56789Test extends Sugar_PHPUnit_Framework_TestCase
{
    protected $_testModule = 'Accounts';
    protected $_testPlatform = 'mobile';
    protected $_filesToDelete = array(
        'custom/modules/Accounts/clients/mobile/views/search/search.php'
    );
    protected $_parser;
    
    public function setUp()
    {
        // Regular setup stuff
        SugarTestHelper::setUp('beanFiles');
        SugarTestHelper::setUp('beanList');
        SugarTestHelper::setUp('current_user');
        SugarTestHelper::setUp('app_list_strings');
        
        // Get the parser for wireless basic search
        $this->_parser = new SearchViewMetaDataParser('wireless_basic_search', $this->_testModule, '', $this->_testPlatform);
    }
    
    public function tearDown()
    {
        unset($this->_parser);
        foreach ($this->_filesToDelete as $file) {
            if (file_exists($file)) {
                unlink($file);
            }
            
            // Handle history files related to this test
            $file = str_replace('custom/', 'custom/history/', $file);
            $files = glob($file . '*');
            foreach ($files as $f) {
                unlink($f);
            }
        }
        
        SugarTestHelper::tearDown();
    }

    /**
     * @group bug56789
     */
    public function testAddingFieldToSearchLayoutSaves()
    {
        // Test the current known state of this layout
        $viewdefs = $this->_parser->getLayout();
        $this->assertArrayHasKey('name', $viewdefs, "There is no name key found in the OOTB viewdefs");
        $this->assertArrayNotHasKey('date_entered', $viewdefs, "Views has a data_entered field when it shouldn't");
        
        // Modify the layout by adding some fields to it
        $_POST['group_0'] = array(
            'date_entered',
            'name',
            'date_modified',
        );
        
        // Test the addition
        $this->_parser->handleSave();
        
        // Run a new test on the changes
        $viewdefs = $this->_parser->getLayout();
        $fields = array_keys($viewdefs);
        
        // Test and see
        foreach ($_POST['group_0'] as $k => $field) {
            $this->assertArrayHasKey($field, $viewdefs, "New field $field was not found in the viewdefs after save");
            $this->assertEquals($field, $fields[$k], "Field $field is not in the correct order: [{$fields[$k]}]");
        }
    }
}