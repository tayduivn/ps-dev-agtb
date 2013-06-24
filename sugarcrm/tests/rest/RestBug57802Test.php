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
require_once 'tests/rest/RestTestBase.php';
require_once 'include/MetaDataManager/MetaDataHacks.php';

/**
 * Bug 57802 - REST API Metadata: vardef len property must be number, not string
 */
class RestBug57802Test extends RestTestBase
{
    /**
     * @group rest
     * @group Bug57802
     */
    public function testMetadataModuleVardefLenFieldsAreNumericType()
    {
        $reply = $this->_restCall('metadata?module_filter=Accounts&type_filter=modules');
        $this->assertTrue(isset($reply['reply']['modules']['Accounts']['fields']), "Fields were not returned in the metadata response");
        
        // Handle assertions for all defs
        foreach ($reply['reply']['modules']['Accounts']['fields'] as $field => $def) {
            if (isset($def['len'])) {
                $this->assertInternalType('int', $def['len'], "$field len property should of type int");
            }
            
            if (isset($def['size'])) {
                $this->assertInternalType('int', $def['size'], "$field size property should of type int");
            }
        }
    }

    /**
     * @group 57802
     */
    public function testMetaDataManagerReturnsProperLenType()
    {
        $fielddef = array(
            'test_field_c' => array(
                'source' => "custom_fields",
                'name' => "test_field_c",
                'vname' => "LBL_AAA_TEST",
                'type' => "varchar",
                'len' => '30', // Force string to test as int
                'size' => '20', // Same here
                'id' => "Accountstest_field_c",
                'custom_module' => "Accounts",
            ),
            'test_field1_c' => array(
                'source' => "custom_fields",
                'name' => "test_field1_c",
                'vname' => "LBL_AAA1_TEST",
                'type' => "varchar",
                'len' => '100', // Force string to test as int
                'size' => '90', // Same here
                'id' => "Accountstest_field1_c",
                'custom_module' => "Accounts",
            ),
        );
        
        $mm = new RestBug57802MetaDataHacks();
        $cleaned = $mm->getNormalizedFields($fielddef);
        
        foreach ($cleaned as $field => $def) {
            if (isset($def['len'])) {
                $this->assertInternalType('int', $def['len'], "$field len property should of type int");
            }
            
            if (isset($def['size'])) {
                $this->assertInternalType('int', $def['size'], "$field size property should of type int");
            }
        }
    }
}

/**
 * Accessor class to the protected metadata manager method needed for testing
 */
class RestBug57802MetaDataHacks extends MetaDataHacks
{
    public function getNormalizedFields($fielddef) {
        return $this->normalizeFielddefs($fielddef);
    }
}