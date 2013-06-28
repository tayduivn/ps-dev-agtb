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
 * Bug 57890 - Required values should be boolean
 */
class RestBug57890Test extends RestTestBase
{
    /**
     * @group rest
     * @group Bug57890
     */
    public function testMetadataModuleVardefRequiredFieldsAreBooleanType()
    {
        $reply = $this->_restCall('metadata?module_filter=Leads&type_filter=modules');
        $this->assertTrue(isset($reply['reply']['modules']['Leads']['fields']), "Fields were not returned in the metadata response");
        
        // Handle assertions for all defs
        foreach ($reply['reply']['modules']['Leads']['fields'] as $field => $def) {
            if (isset($def['required'])) {
                $this->assertInternalType('bool', $def['required'], "$field required property should of type boolean");
            }
        }
    }

    /**
     * @group 57890
     */
    public function testMetaDataManagerReturnsProperRequiredType()
    {
        $fielddef = array(
            'test_field_c' => array(
                'source' => "custom_fields",
                'name' => "test_field_c",
                'vname' => "LBL_AAA_TEST",
                'type' => "varchar",
                'len' => '30',
                'required' => 'true',
                'size' => '20',
                'id' => "Leadstest_field_c",
                'custom_module' => "Leads",
            ),
            'test_field1_c' => array(
                'source' => "custom_fields",
                'name' => "test_field1_c",
                'vname' => "LBL_AAA1_TEST",
                'type' => "varchar",
                'len' => '100',
                'required' => 'off',
                'size' => '90',
                'id' => "Leadstest_field1_c",
                'custom_module' => "Leads",
            ),
            'test_field2_c' => array(
                'source' => "custom_fields",
                'name' => "test_field2_c",
                'vname' => "LBL_AAA1_TEST",
                'type' => "varchar",
                'len' => '100',
                'required' => true,
                'size' => '90',
                'id' => "Leadstest_field2_c",
                'custom_module' => "Leads",
            ),
        );
        
        $mm = new RestBug57890MetaDataHacks($this->_user);
        $cleaned = $mm->getNormalizedFields($fielddef);
        
        foreach ($cleaned as $field => $def) {
            if (isset($def['required'])) {
                $this->assertInternalType('bool', $def['required'], "$field required property should of type boolean");
            }
        }
    }
}

/**
 * Accessor class to the protected metadata manager method needed for testing
 */
class RestBug57890MetaDataHacks extends MetaDataHacks
{
    public function getNormalizedFields($fielddef) {
        return $this->normalizeFielddefs($fielddef);
    }
}