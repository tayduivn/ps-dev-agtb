<?php

/*
 * Your installation or use of this SugarCRM file is subject to the applicable
 * terms available at
 * http://support.sugarcrm.com/06_Customer_Center/10_Master_Subscription_Agreements/.
 * If you do not agree to all of the applicable terms or do not have the
 * authority to bind the entity as an authorized representative, then do not
 * install or use this SugarCRM file.
 *
 * Copyright (C) SugarCRM Inc. All rights reserved.
 */



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
                      'analyzer'=>'core_email_lowercase'),
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
