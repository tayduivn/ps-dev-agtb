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
 
require_once 'include/SugarObjects/templates/basic/Basic.php';

class BasicTemplateTest extends Sugar_PHPUnit_Framework_TestCase
{
    private $_bean;
    
    public function setUp()
    {
        $this->_bean = new Basic;
    }
    
    public function tearDown()
    {
        unset($this->_bean);
    }
    
    public function testNameIsReturnedAsSummaryText()
    {
        $this->_bean->name = 'teststring';
        $this->assertEquals($this->_bean->get_summary_text(),$this->_bean->name);
    }
    
    /**
     * @ticket 27361
     */
    public function testSettingImportableFieldDefAttributeTrueAsAString()
    {
        $this->_bean->field_defs['date_entered']['importable'] = 'true';
        $this->assertTrue(array_key_exists('date_entered',$this->_bean->get_importable_fields()),
            'Field date_entered should be importable');
    }
    
    /**
     * @ticket 27361
     */
    public function testSettingImportableFieldDefAttributeTrueAsABoolean()
    {
        $this->_bean->field_defs['date_entered']['importable'] = true;
        $this->assertTrue(array_key_exists('date_entered',$this->_bean->get_importable_fields()),
            'Field date_entered should be importable');
    }
    
    /**
     * @ticket 27361
     */
    public function testSettingImportableFieldDefAttributeFalseAsAString()
    {
        $this->_bean->field_defs['date_entered']['importable'] = 'false';
        $this->assertFalse(array_key_exists('date_entered',$this->_bean->get_importable_fields()),
            'Field date_entered should not be importable');
    }
    
    /**
     * @ticket 27361
     */
    public function testSettingImportableFieldDefAttributeFalseAsABoolean()
    {
        $this->_bean->field_defs['date_entered']['importable'] = false;
        $this->assertFalse(array_key_exists('date_entered',$this->_bean->get_importable_fields()),
            'Field date_entered should not be importable');
    }
    
    public function testGetBeanFieldsAsAnArray()
    {
        $this->_bean->date_entered = '2009-01-01 12:00:00';
        $array = $this->_bean->toArray();
        $this->assertEquals($array['date_entered'],$this->_bean->date_entered);
    }
}
