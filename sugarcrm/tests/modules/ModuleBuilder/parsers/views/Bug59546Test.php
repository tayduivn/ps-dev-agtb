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

require_once 'modules/Opportunities/Dashlets/MyOpportunitiesDashlet/MyOpportunitiesDashlet.php';
require_once 'modules/ModuleBuilder/parsers/views/DashletMetaDataParser.php';

class Bug59546Test extends Sugar_PHPUnit_Framework_TestCase
{
    protected $_testFile = 'custom/modules/Opportunities/metadata/dashletviewdefs.php';
    protected $_customDefs = array();
    
    public function setUp()
    {
        // Back up our current custom file and remove it if it is there
        if (file_exists($this->_testFile)) {
            copy($this->_testFile, $this->_testFile . '.backup');
            SugarAutoLoader::unlink($this->_testFile, true);
        }
        
        SugarTestHelper::setUp('app_list_strings');
        SugarTestHelper::setUp('moduleList');
        SugarTestHelper::setUp('beanList');
        SugarTestHelper::setUp('beanFiles');
        SugarTestHelper::setUp('current_user', array(true, true));
        
        // Build the POST array
        $_POST = array(
            // Enabled fields
            "group_0" => array("name", "probability", "account_name"),
            
            // Available fields
            "group_1" => array("opportunity_type", "lead_source"),
        );
        
        // Save the custom file
        $parser = new DashletMetaDataParser(MB_DASHLET, 'Opportunities');        
        $parser->handleSave();
        
        $id = create_guid();
        $dashlet = new TestMyOpportunitiesDashlet($id);
        $this->_customDefs = $dashlet->getColumns();
    }
    
    public function tearDown()
    {
        SugarTestHelper::tearDown();
        
        // Remove the test file from the autoloader
        SugarAutoLoader::unlink($this->_testFile, true);
        
        // Restore the backup if it is there
        if (file_exists($this->_testFile . '.backup')) {
            rename($this->_testFile . '.backup', $this->_testFile);
            SugarAutoLoader::addToMap($this->_testFile);
        }
    }

    /**
     * Tests whether the right fields saved in the right way
     * 
     * @dataProvider _newLayoutMetaProvider
     * @param string  $field
     * @param boolean $default
     */
    public function testDashletSavePicksUpNewLayout($field, $default)
    {
        $defDefault = isset($this->_customDefs[$field]['default']) ? $this->_customDefs[$field]['default'] : 'ZZZ';
        $this->assertEquals($default, $defDefault, "Default value for $field did not meet the tested expectation");
    }

    /**
     * Data provider for the test method
     * 
     * @return array
     */
    public function _newLayoutMetaProvider()
    {
        return array(
            // Enabled fields
            array("field" => "name", "default" => true,),
            array("field" => "probability", "default" => true,),
            array("field" => "account_name", "default" => true,),
            // Available fields
            array("field" => "opportunity_type", "default" => false,),
            array("field" => "lead_source", "default" => false,),
        );
    }
}

/**
 * Simple accessor to the columns array via a protected parent method
 */
class TestMyOpportunitiesDashlet extends MyOpportunitiesDashlet
{
    public function getColumns()
    {
        parent::loadCustomMetadata();
        return $this->columns;
    }
}