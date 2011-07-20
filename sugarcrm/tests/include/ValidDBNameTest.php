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
 
require_once("include/utils.php");

class ValidDBNameTest extends Sugar_PHPUnit_Framework_TestCase
{
    public function testShortNameUneffected()
    {
        $this->assertEquals(
            'idx_test_123_id',
            getValidDBName('idx_test_123_id')
        );
    }

    public function testmaxLengthParam()
    {
        $this->assertEquals(
            'idx_test_123_456_789_foo_bar_id',
            getValidDBName('idx_test_123_456_789_foo_bar_id', false, 40)
        );
    }

    public function testEnsureUnique()
    {
        $this->assertEquals(
            getValidDBName('idx_test_123_456_789_foo_bar_id', true),
            getValidDBName('idx_test_123_456_789_foo_bar_id', true)
        );

        $this->assertNotEquals(
            getValidDBName('idx_test_123_456_789_foo_bar_id', true),
            getValidDBName('idx_test_123_446_789_foo_bar_id', true)
        );
    }

    public function testValidMySQLNameReturnsTrue()
    {
        $this->assertTrue(isValidDBName('sugarCRM', 'mysql'));
        $this->assertTrue(isValidDBName('sugar-crm', 'mysql'));
        $this->assertTrue(isValidDBName('sugar_crm', 'mysql'));
        $this->assertTrue(isValidDBName('sugar-crm', 'mysql'));
        $this->assertTrue(isValidDBName('sugar-CRM_ver6', 'mysql'));
    }

    public function testInvalidMySQLNameReturnsFalse()
    {
        $this->assertFalse(isValidDBName('sugar/crm', 'mysql'));
        $this->assertFalse(isValidDBName('sugar\crm', 'mysql'));
        $this->assertFalse(isValidDBName('sugar.crm', 'mysql'));
    }

    public function testValidOracleNameReturnsTrue()
    {
        $this->assertTrue(isValidDBName('sugarCRM', 'oci8'));
        $this->assertTrue(isValidDBName('sugar_crm', 'oci8'));
        $this->assertTrue(isValidDBName('sugarCRM_ver6', 'oci8'));
    }

    public function testInvalidOracleNameReturnsFalse()
    {
        $this->assertFalse(isValidDBName('sugar=CRM', 'oci8'));
        $this->assertFalse(isValidDBName('sugar crm', 'oci8'));
        $this->assertFalse(isValidDBName('sugarCRM_ver#63', 'oci8'));
    }

    public function testValidMSSQLNameReturnsTrue()
    {
        $this->assertTrue(isValidDBName('sugarCRM', 'mssql'));
        $this->assertTrue(isValidDBName('sugar_crm', 'mssql'));
        $this->assertTrue(isValidDBName('sugarCRM_ver6', 'mssql'));
    }

    public function testInvalidMSSQLNameReturnsFalse()
    {
        $this->assertFalse(isValidDBName('622sugarCRM', 'mssql'));
        $this->assertFalse(isValidDBName('sugar crm', 'mssql'));
        $this->assertFalse(isValidDBName('#sugarCRM_ver6', 'mssql'));
    }
}
