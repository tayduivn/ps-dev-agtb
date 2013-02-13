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

require_once 'modules/ModuleBuilder/parsers/parser.dropdown.php';

/**
 * Bug 58463 - Drop Down Lists do not show in studio after save
 */
class Bug58463Test extends Sugar_PHPUnit_Framework_TestCase
{
    protected $_testCustomFile = 'custom/include/language/en_us.lang.php';
    protected $_currentRequest;
    
    public function setUp()
    {
        SugarTestHelper::setUp('current_user');
        SugarTestHelper::setUp('app_list_strings');
        
        // Back up the current file if there is one
        if (file_exists($this->_testCustomFile)) {
            rename($this->_testCustomFile, $this->_testCustomFile . '.testbackup');
        } else {
            SugarAutoLoader::addToMap($this->_testCustomFile);
        }
        
        // Create an empty test custom file
        mkdir_recursive(dirname($this->_testCustomFile));
        sugar_file_put_contents($this->_testCustomFile, '<?php' . "\n");
        
        // Back up the current request vars
        $this->_currentRequest = $_REQUEST;
    }
    
    public function tearDown()
    {
        SugarTestHelper::tearDown();
        
        // Clean up our file
        unlink($this->_testCustomFile);
        
        if (file_exists($this->_testCustomFile . '.testbackup')) {
            rename($this->_testCustomFile . '.testbackup', $this->_testCustomFile);
        } else {
            SugarAutoLoader::delFromMap($this->_testCustomFile);
        }
        
        // Reset the request
        $_REQUEST = $this->_currentRequest;
        
        // Clear the cache
        sugar_cache_clear('app_list_strings.en_us');
    }

    /**
     * @group Bug58463
     */
    public function testCustomDropDownListSavesProperly()
    {
        $values = array(
            array('bobby', 'Bobby'),
            array('billy', 'Billy'),
            array('benny', 'Benny'),
        );
        
        $_REQUEST = array(
            'list_value' => json_encode($values),
            'dropdown_lang' => 'en_us',
            'dropdown_name' => 'test_dropdown',
            'view_package' => 'studio',
        );
        $parser = new ParserDropDown();
        $parser->saveDropDown($_REQUEST);
        
        $als = $this->_getCustomDropDownEntry();
        $this->assertArrayHasKey('test_dropdown', $als, "The dropdown did not save");
        foreach ($values as $item) {
            $this->assertArrayHasKey($item[0], $als['test_dropdown'], "The dropdown list item {$item[0]} did not save");
        }
    }
    
    protected function _getCustomDropDownEntry()
    {
        if (file_exists($this->_testCustomFile)) {
            require $this->_testCustomFile;
            if (isset($app_list_strings)) {
                return $app_list_strings;
            }
        }
        
        // This would indicate a failure
        return array();
    }
}