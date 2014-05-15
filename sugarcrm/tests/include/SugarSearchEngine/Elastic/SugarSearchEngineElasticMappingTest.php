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

    public function mappingTypeProvider()
    {
        return array(
            array(
                array('type'=>'datetimecombo'),
                array('type'=>'date'),
                "testing basic datetimecombo mapping",
            ),
            array(
                array('type'=>'datetime'),
                array('type'=>'date'),
                "testing basic datetime mapping",
            ),
            array(
                array('type'=>'id'),
                array('type'=>'string',
                      'index'=>'not_analyzed'),
                "testing basic id mapping",
            ),
            array(
                array('type'=>'enum'),
                array('type'=>'string',
                      'index'=>'not_analyzed'),
                "testing enum mapping",
            ),
            array(
                array('type'=>'email'),
                array('type'=>'string',
                      'index'=>'not_analyzed'),
                "testing email mapping",
            ),
            array(
                array('type'=>'url'),
                array('type'=>'string',
                      'index'=>'not_analyzed'),
                "testing url mapping",
            ),
            array(
                array('type'=>'name'),
                array('type'=>'string',
                      'analyzer'=>'standard'),
                "testing name mapping",
            ),
            array(
                array('type'=>'phone'),
                array('type'=>'string',
                      'analyzer'=>'standard'),
                "testing phone mapping",
            ),
            array(
                array('type'=>'varchar'),
                array('type'=>'string',
                      'analyzer'=>'standard'),
                "testing varchar mapping",
            ),
            array(
                array('type'=>'fullname'),
                array('type'=>'string',
                      'analyzer'=>'standard'),
                "testing fullname mapping",
            ),
            array(
                array(
                    'type' => 'double',
                    'name' => 'test_double',
                ),
                array(
                    'type' => 'multi_field',
                    'fields' => array(
                        'number' => array('type' => 'double'),
                        'test_double' => array('type' => 'string'),
                    ),
                ),
                'testing double mapping',
            ),
            array(
                array(
                    'type' => 'currency',
                    'name' => 'test_currency',
                ),
                array(
                    'type' => 'multi_field',
                    'fields' => array(
                        'number' => array('type' => 'double'),
                        'test_currency' => array('type' => 'string'),
                    ),
                ),
                'testing currency mapping',
            ),
            array(
                array(
                    'type' => 'float',
                    'name' => 'test_float',
                ),
                array(
                    'type' => 'multi_field',
                    'fields' => array(
                        'number' => array('type' => 'double'),
                        'test_float' => array('type' => 'string'),
                    ),
                ),
                'testing float mapping',
            ),
            array(
                array(
                    'type' => 'decimal',
                    'name' => 'test_decimal',
                ),
                array(
                    'type' => 'multi_field',
                    'fields' => array(
                        'number' => array('type' => 'double'),
                        'test_decimal' => array('type' => 'string'),
                    ),
                ),
                'testing decimal mapping',
            ),
            array(
                array(
                    'type' => 'int',
                    'name' => 'test_int',
                ),
                array(
                    'type' => 'multi_field',
                    'fields' => array(
                        'number' => array('type' => 'integer'),
                        'test_int' => array('type' => 'string'),
                    ),
                ),
                'testing int mapping',
            ),
            array(
                array('type'=>'boolean'),
                array('type'=>'boolean'),
                "testing basic boolean mapping",
            ),
            array(
                array('type'=>'bool'),
                array('type'=>'boolean'),
                "testing bool mapping",
            ),
            array(
                array(
                    'type' => 'relate',
                    'name' => 'unit_test',
                ),
                array(
                    'type' => 'multi_field',
                    'fields' => array(
                        'raw' => array('type' => 'string'),
                        'unit_test' => array('type' => 'string'),
                    ),
                ),
                'testing relate mapping',
            ),
        );
    }

    /**
     * @dataProvider mappingTypeProvider
     */
    public function testGetFtsTypeFromDef($fieldDef, $expectedType, $message)
    {
        $ftsType = self::$stub->getFtsTypeFromDef($fieldDef);
        foreach ($expectedType as $key => $val) {
            $this->assertArrayHasKey($key, $ftsType, "Mapped type is missing $key for $message");
            if ($key == 'fields') {
                $this->assertFields($val, $ftsType[$key], $message);
            } else {
                $this->assertEquals($val, $ftsType[$key], "$key did not match for $message");
            }
        }
    }

    /**
     * Provisional method for asserting multi-field mappings
     * This need to be revisited once we have more than 1 multi-field type in our mappings
     * @param $val
     * @param $mappedType
     * @param $message
     */
    private function assertFields($expectedType, $ftsType, $message)
    {
        $this->assertEquals(count($expectedType), count($ftsType), "Number of multi-fields did not match for $message");
        foreach ($expectedType as $field => $map) {
            $this->assertArrayHasKey($field, $ftsType, "Multi-field is missing definition for field '$field'");

            foreach ($map as $key => $val) {
                $this->assertEquals($val, $ftsType[$field][$key], $message);
            }
        }
    }
}


class SugarSearchEngineElasticMappingTestStub extends SugarSearchEngineElasticMapping
{
    // to override the parent constructor so we don't need to pass in search engine object
    public function __construct()
    {
    }

    public function getFtsTypeFromDef($fieldDef)
    {
        return parent::getFtsTypeFromDef($fieldDef);
    }

}
