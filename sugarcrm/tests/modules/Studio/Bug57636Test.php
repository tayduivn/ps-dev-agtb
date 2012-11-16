<?php
//FILE SUGARCRM flav=pro ONLY
/*********************************************************************************
 * The contents of this file are subject to the SugarCRM Master Subscription
 * Agreement ("License") which can be viewed at
 * http://www.sugarcrm.com/crm/en/msa/master_subscription_agreement_11_April_2011.pdf
 * By installing or using this file, You have unconditionally agreed to the
 * terms and conditions of the License, and You may not use this file except in
 * compliance with the License.  Under the terms of the license, You shall not,
 * among other things: 1) sublicense, resell, rent, lease, redistribute, assign
 * or otherwise transfer Your rights to the Software, and 2) use the Software
 * for timesharing or service bureau purposes such as hosting the Software for
 * commercial gain and/or for the benefit of a third party.  Use of the Software
 * may be subject to applicable fees and any use of the Software without first
 * paying applicable fees is strictly prohibited.  You do not have the right to
 * remove SugarCRM copyrights from the source code or user interface.
 *
 * All copies of the Covered Code must include on each user interface screen:
 *  (i) the "Powered by SugarCRM" logo and
 *  (ii) the SugarCRM copyright notice
 * in the same form as they appear in the distribution.  See full license for
 * requirements.
 *
 * Your Warranty, Limitations of liability and Indemnity are expressly stated
 * in the License.  Please refer to the License for the specific language
 * governing these rights and limitations under the License.  Portions created
 * by SugarCRM are Copyright (C) 2004-2011 SugarCRM, Inc.; All Rights Reserved.
 ********************************************************************************/

/**
 * Bug 57636
 *
 * For meetings module, in mobile edit and detail, duration hours and duration_minutes
 * should not be on any layout. 
 */
require_once('modules/ModuleBuilder/parsers/views/SidecarGridLayoutMetaDataParser.php');

class Bug57636Test extends Sugar_PHPUnit_Framework_TestCase {
    protected $testModule = 'Meetings';
    protected $testFields  = array('duration_hours', 'duration_minutes');
    
    public function setUp()
    {
        SugarTestHelper::setup('beanList');
        SugarTestHelper::setup('beanFiles');
        SugarTestHelper::setup('app_list_strings');
        SugarTestHelper::setup('mod_strings', array($this->testModule));
    }
    
    public function tearDown()
    {
        SugarTestHelper::tearDown();
    }

    /**
     * @group Bug57636
     * 
     * Tests that duration_minutes and duration_hours are not in both default fields
     * and available fields for mobile edit and detail layout editors
     */
    public function testDurationFieldsAreNotInMobileMeetingsGridLayout()
    {
        // Get the mobile edit parser
        $parser = ParserFactory::getParser(MB_WIRELESSEDITVIEW, $this->testModule, null, null, MB_WIRELESS);
        
        // Fields that are on the layout
        $fields = $parser->getLayout();
        foreach ($this->testFields as $field) {
            $test = $this->_fieldNameFoundInFields($field, $fields['LBL_PANEL_DEFAULT']);
            $this->assertFalse($test, "$field should not be in default edit view fields");
        }
        
        // Fields that can be added to a layout
        $fields = $parser->getAvailableFields();
        foreach ($this->testFields as $field) {
            $test = $this->_fieldNameFoundInFields($field, $fields);
            $this->assertFalse($test, "$field should not be in available edit view fields");
        }
        
        // Now get the mobile detail parser
        $parser = ParserFactory::getParser(MB_WIRELESSDETAILVIEW, $this->testModule, null, null, MB_WIRELESS);
        
        // Fields that are on the layout
        $fields = $parser->getLayout();
        foreach ($this->testFields as $field) {
            $test = $this->_fieldNameFoundInFields($field, $fields['LBL_PANEL_DEFAULT']);
            $this->assertFalse($test, "$field should not be in default detail view fields");
        }
        
        // Fields that can be added to a layout
        $fields = $parser->getAvailableFields();
        foreach ($this->testFields as $field) {
            $test = $this->_fieldNameFoundInFields($field, $fields);
            $this->assertFalse($test, "$field should not be in available in detail view fields");
        }
    }
    
    /**
     * Utility method to parse field defs for MOST grid type layouts
     * 
     * @param string $name The field name to check for
     * @param array $fields The defs to search
     * @return bool
     */
    protected function _fieldNameFoundInFields($name, $fields) {
        foreach ($fields as $field) {
            if (isset($field['name']) && $field['name'] == $name) {
                return true;
            }
        }
        
        return false;
    }
    
    /**
     * Utility method to search layout defs for mobile grid layouts for a field
     * 
     * @param string $name The field name to search for
     * @param array $layout The defs to search
     * @return bool
     */
    protected function _fieldNameFoundInLayoutFields($name, $layout) {
        foreach ($layout as $fields) {
            if ($this->_fieldNameFoundInFields($name, $fields)) {
                return true;
            }
        }
        
        return false;
    }
}