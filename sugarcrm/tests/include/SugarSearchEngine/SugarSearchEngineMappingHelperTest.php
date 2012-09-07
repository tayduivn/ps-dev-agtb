<?php
//FILE SUGARCRM flav=pro ONLY
/*********************************************************************************
 * The contents of this file are subject to the SugarCRM Master Subscription
 * Agreement ("License") which can be viewed at
 * http://www.sugarcrm.com/crm/master-subscription-agreement
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




require_once 'include/SugarSearchEngine/SugarSearchEngineMappingHelper.php';

class SugarSearchEngineMappingHelperTest extends Sugar_PHPUnit_Framework_TestCase
{

    public function mappingNameProvider()
    {
        return array(
            array('Elastic', 'boost', 'boost'),
            array('Elastic', 'analyzer', 'analyzer'),
            array('Elastic', 'type', 'type'),
        );
    }

    /**
     * @dataProvider mappingNameProvider
     */
    public function testGetMappingName($searchEngineName, $originalName, $expectedName)
    {
        $newName = SugarSearchEngineMappingHelper::getMappingName($searchEngineName, $originalName);

        $this->assertEquals($expectedName, $newName, 'not expected name');
    }

    public function mappingTypeProvider()
    {
        return array(
            array('Elastic', array('type'=>'datetimecombo'), 'date'),
            array('Elastic', array('type'=>'date'), 'string'),
            array('Elastic', array('type'=>'int'), 'string'),
            array('Elastic', array('type'=>'currency'), 'string'),
            array('Elastic', array('type'=>'bool'), 'string'),
            array('Elastic', array('dbType'=>'decimal'), 'string'),
        );
    }

    /**
     * @dataProvider mappingTypeProvider
     */
    public function testGetMappingType($searchEngineName, $fieldDef, $expectedType)
    {
        $newType = SugarSearchEngineMappingHelper::getTypeFromSugarType($searchEngineName, $fieldDef);
        $this->assertTrue(in_array($expectedType, $newType), 'not expected type');
    }

    public function mappingSearchableTypeProvider()
    {
        return array(
            array('name', true),
            array('varchar', true),
            array('phone', true),
            array('enum', false),
            array('iframe', false),
            array('bool', false),
            array('invalid', false),
        );
    }

    /**
     * @dataProvider mappingSearchableTypeProvider
     */
    public function testSearchableType($type, $searchable)
    {
        $ret = SugarSearchEngineMappingHelper::isTypeFtsEnabled($type);
        $this->assertEquals($searchable, $ret, 'field type incorrect searchable definition');
    }
}
