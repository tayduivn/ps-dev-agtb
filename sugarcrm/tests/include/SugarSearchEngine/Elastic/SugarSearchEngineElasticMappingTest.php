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



require_once 'include/SugarSearchEngine/Elastic/SugarSearchEngineElasticMapping.php';

class SugarSearchEngineElasticMappingTest extends Sugar_PHPUnit_Framework_TestCase
{
    static $stub; // Using static as we are not sharing state for the unit tests

    public static function setUpBeforeClass()
    {
        self::$stub = new SugarSearchEngineElasticMappingTestStub();
    }

    public function testConstructMappingProperties()
    {
        $fieldDefs = array (
            'field1' => array (
                'name'=>'first_name',
                'full_text_search' => array (
                    'enabled' => true, 'boost' => 3,
                    'type' => 'string',
                ),
            ),
        );
        $expected = array(
            'first_name' => array (
                'enabled' => true, 'boost' => 3,
                'type' => 'string',
            ),
        );
        $result = self::$stub->constructMappingProperties($fieldDefs);

        $this->assertArrayHasKey('first_name', $result);
        $this->assertEquals($expected['first_name'], $result['first_name'], 'result is different from expected array');
    }

    public function mappingNameProvider()
    {
        return array(
            array('boost', 'boost'),
            array('analyzer', 'analyzer'),
            array('type', 'type'),
        );
    }

    /**
     * @dataProvider mappingNameProvider
     */
    public function testGetMappingName($originalName, $expectedName)
    {

        $newName = self::$stub->getMappingName($originalName);

        $this->assertEquals($expectedName, $newName, 'not expected name');
    }

    public function mappingTypeProvider()
    {
        return array(
            array(array('type'=>'datetimecombo'), 'date'),
            array(array('type'=>'date'), 'string'),
            array(array('type'=>'int'), 'string'),
            array(array('type'=>'currency'), 'string'),
            array(array('type'=>'bool'), 'string'),
            array(array('dbType'=>'decimal'), 'string'),
        );
    }

    /**
     * @dataProvider mappingTypeProvider
     */
    public function testGetMappingType($fieldDef, $expectedType)
    {
        $newType = self::$stub->getTypeFromSugarType($fieldDef);
        $this->assertEquals($expectedType, $newType, 'not expected type');
    }
}


class SugarSearchEngineElasticMappingTestStub extends SugarSearchEngineElasticMapping
{
    // to override the parent constructor so we don't need to pass in search engine object
    public function __construct()
    {
    }

    // to test protected function
    public function constructMappingProperties($fieldDefs)
    {
        return parent::constructMappingProperties($fieldDefs);
    }

    public function getTypeFromSugarType($fieldDef)
    {
        return parent::getTypeFromSugarType($fieldDef);
    }

    public function getMappingName($sugarName)
    {
        return parent::getMappingName($sugarName);
    }

}
