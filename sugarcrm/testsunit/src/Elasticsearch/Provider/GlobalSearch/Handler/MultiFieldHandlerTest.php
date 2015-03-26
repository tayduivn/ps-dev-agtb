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

namespace Sugarcrm\SugarcrmTestsUnit\Elasticsearch\Provider\GlobalSearch\Handler;

use Sugarcrm\SugarcrmTestsUnit\TestReflection;
use Sugarcrm\Sugarcrm\Elasticsearch\Analysis\AnalysisBuilder;
use Sugarcrm\Sugarcrm\Elasticsearch\Mapping\Mapping;
use Sugarcrm\Sugarcrm\Elasticsearch\Provider\GlobalSearch\SearchFields;

/**
 *
 * @coversDefaultClass \Sugarcrm\Sugarcrm\Elasticsearch\Provider\GlobalSearch\Handler\MultiFieldHandler
 *
 */
class MultiFieldHandlerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @coversNothing
     */
    public function testRequiredInterfaces()
    {
        $nsPrefix = 'Sugarcrm\Sugarcrm\Elasticsearch\Provider\GlobalSearch\Handler';
        $interfaces = array(
            $nsPrefix . '\AnalysisHandlerInterface',
            $nsPrefix . '\MappingHandlerInterface',
            $nsPrefix . '\SearchFieldsHandlerInterface',
        );
        $implements = class_implements($nsPrefix . '\MultiFieldHandler');
        $this->assertEquals($interfaces, array_values(array_intersect($implements, $interfaces)));
    }

    /**
     * @covers ::initialize
     * @dataProvider providerTestInitialize
     */
    public function testInitialize($property, array $value, $method, array $expected)
    {
        $provider = $this->getMockBuilder('\Sugarcrm\Sugarcrm\Elasticsearch\Provider\GlobalSearch\GlobalSearch')
            ->disableOriginalConstructor()
            ->getMock();

        $provider->expects($this->once())
            ->method($method)
            ->with($this->equalTo($expected));

        $sut = $this->getMultiFieldHandlerMock();
        $sut->setProvider($provider);
        TestReflection::setProtectedValue($sut, $property, $value);
        $sut->initialize();
    }

    public function providerTestInitialize()
    {
        return array(
            array(
                'typesMultiField',
                array(
                    'varchar' => array(
                        'gs_string_default',
                        'gs_string_ngram',
                    ),
                    'name' => array(
                        'gs_string_default',
                        'gs_string_ngram',
                    ),
                ),
                'addSupportedTypes',
                array(
                    'varchar',
                    'name',
                ),
            ),
            array(
                'weightedBoost',
                array(
                    'field' => 0.35,
                ),
                'addWeightedBoosts',
                array(
                    'field' => 0.35,
                ),
            ),
            array(
                'highlighterFields',
                array(
                    '*.field_default' => array(
                        'number_of_frags' => 0,
                    ),
                ),
                'addHighlighterFields',
                array(
                    '*.field_default' => array(
                        'number_of_frags' => 0,
                    ),
                ),
            ),
        );
    }

    /**
     * Validation test for implemented analysis settings
     * @covers ::buildAnalysis
     */
    public function testBuildAnalysisValidation()
    {
        $analysisBuilder = new AnalysisBuilder();
        $sut = $this->getMultiFieldHandlerMock();
        $sut->buildAnalysis($analysisBuilder);

        $expected = array(
            'analysis' => array(
                'analyzer' => array(
                    'gs_analyzer_string' => array(
                        'tokenizer' => 'standard',
                        'filter' => array(
                            'lowercase',
                        ),
                        'type' => 'custom',
                    ),
                    'gs_analyzer_string_ngram' => array(
                        'tokenizer' => 'standard',
                        'filter' => array(
                            'lowercase',
                            'gs_filter_ngram_1_15',
                        ),
                        'type' => 'custom',
                    ),
                    'gs_analyzer_phone_ngram' => array(
                        'tokenizer' => 'whitespace',
                        'filter' => array(
                            'gs_filter_ngram_3_15',
                        ),
                        'char_filter' => array(
                            'gs_char_num_pattern',
                        ),
                        'type' => 'custom',
                    ),
                    'gs_analyzer_phone' => array(
                        'tokenizer' => 'whitespace',
                        'char_filter' => array(
                            'gs_char_num_pattern',
                        ),
                        'type' => 'custom',
                    ),
                    'gs_analyzer_text_ngram' => array(
                        'tokenizer' => 'standard',
                        'filter' => array(
                            'lowercase',
                            'gs_filter_ngram_2_15',
                        ),
                        'type' => 'custom',
                    ),
                    'gs_analyzer_url' => array(
                        'tokenizer' => 'uax_url_email',
                        'filter' => array(
                            'lowercase',
                        ),
                        'type' => 'custom',
                    ),
                    'gs_analyzer_url_ngram' => array(
                        'tokenizer' => 'uax_url_email',
                        'filter' => array(
                            'lowercase',
                            'gs_filter_ngram_2_15',
                        ),
                        'type' => 'custom',
                    ),
                ),
                'tokenizer' => array(),
                'filter' => array(
                    'gs_filter_ngram_1_15' => array(
                        'min_gram' => 1,
                        'max_gram' => 15,
                        'type' => 'nGram',
                    ),
                    'gs_filter_ngram_2_15' => array(
                        'min_gram' => 2,
                        'max_gram' => 15,
                        'type' => 'nGram',
                    ),
                    'gs_filter_ngram_3_15' => array(
                        'min_gram' => 3,
                        'max_gram' => 15,
                        'type' => 'nGram',
                    ),
                ),
                'char_filter' => array(
                    'gs_char_num_pattern' => array(
                        'pattern' => '[^\\d\\s]+',
                        'replacement' => '',
                        'type' => 'pattern_replace',
                    ),
                ),
            ),
        );

        $this->assertEquals($expected, $analysisBuilder->compile());
    }

    /**
     * Validation test for implemented mapping
     * @coversNothing
     * @dataProvider providerTestBuildMappingValidation
     */
    public function testBuildMappingValidation($field, array $defs, array $expected)
    {
        $mapping = new Mapping('foobar');
        $sut = $this->getMultiFieldHandlerMock();
        $sut->buildMapping($mapping, $field, $defs);
        $this->assertEquals($expected, $mapping->compile());
    }

    public function providerTestBuildMappingValidation()
    {
        return array(
            // test 'varchar' type
            array(
                'billing_street',
                array(
                    'type' => 'varchar',
                ),
                array(
                    'billing_street' => array(
                        'type' => 'string',
                        'index' => 'not_analyzed',
                        'include_in_all' => false,
                        'fields' => array(
                            'gs_string' =>  array(
                                'type' => 'string',
                                'index' => 'analyzed',
                                'index_analyzer' => 'gs_analyzer_string',
                                'search_analyzer' => 'gs_analyzer_string',
                                'store' => false,
                            ),
                            'gs_string_wildcard' => array(
                                'type' => 'string',
                                'index' => 'analyzed',
                                'index_analyzer' => 'gs_analyzer_string_ngram',
                                'search_analyzer' => 'gs_analyzer_string',
                                'store' => false,
                            ),
                        ),
                    ),
                ),
            ),
            // test 'name' type
            array(
                'name',
                array(
                    'type' => 'name',
                ),
                array(
                    'name' => array(
                        'type' => 'string',
                        'index' => 'not_analyzed',
                        'include_in_all' => false,
                        'fields' => array(
                            'gs_string' =>  array(
                                'type' => 'string',
                                'index' => 'analyzed',
                                'index_analyzer' => 'gs_analyzer_string',
                                'search_analyzer' => 'gs_analyzer_string',
                                'store' => false,
                            ),
                            'gs_string_wildcard' => array(
                                'type' => 'string',
                                'index' => 'analyzed',
                                'index_analyzer' => 'gs_analyzer_string_ngram',
                                'search_analyzer' => 'gs_analyzer_string',
                                'store' => false,
                            ),
                        ),
                    ),
                ),
            ),
            // test 'username' type
            array(
                'name',
                array(
                    'type' => 'username',
                ),
                array(
                    'name' => array(
                        'type' => 'string',
                        'index' => 'not_analyzed',
                        'include_in_all' => false,
                        'fields' => array(
                            'gs_string' =>  array(
                                'type' => 'string',
                                'index' => 'analyzed',
                                'index_analyzer' => 'gs_analyzer_string',
                                'search_analyzer' => 'gs_analyzer_string',
                                'store' => false,
                            ),
                            'gs_string_wildcard' => array(
                                'type' => 'string',
                                'index' => 'analyzed',
                                'index_analyzer' => 'gs_analyzer_string_ngram',
                                'search_analyzer' => 'gs_analyzer_string',
                                'store' => false,
                            ),
                        ),
                    ),
                ),
            ),
            // test 'text' type
            array(
                'description',
                array(
                    'type' => 'text',
                ),
                array(
                    'description' => array(
                        'type' => 'string',
                        'index' => 'not_analyzed',
                        'include_in_all' => false,
                        'fields' => array(
                            'gs_string' =>  array(
                                'type' => 'string',
                                'index' => 'analyzed',
                                'index_analyzer' => 'gs_analyzer_string',
                                'search_analyzer' => 'gs_analyzer_string',
                                'store' => false,
                            ),
                            'gs_text_wildcard' => array(
                                'type' => 'string',
                                'index' => 'analyzed',
                                'index_analyzer' => 'gs_analyzer_text_ngram',
                                'search_analyzer' => 'gs_analyzer_string',
                                'store' => false,
                            ),
                        ),
                    ),
                ),
            ),
            // test 'datetime' type
            array(
                'date_modified',
                array(
                    'type' => 'datetime',
                ),
                array(
                    'date_modified' => array(
                        'type' => 'string',
                        'index' => 'not_analyzed',
                        'include_in_all' => false,
                        'fields' => array(
                            'gs_datetime' =>  array(
                                'type' => 'date',
                                'format' => 'YYYY-MM-dd HH:mm:ss',
                                'index' => 'no',
                                'store' => false,
                            ),
                        ),
                    ),
                ),
            ),
            // test 'int' type
            array(
                'case_number',
                array(
                    'type' => 'int',
                ),
                array(
                    'case_number' => array(
                        'type' => 'string',
                        'index' => 'not_analyzed',
                        'include_in_all' => false,
                        'fields' => array(
                            'gs_string' =>  array(
                                'type' => 'string',
                                'index' => 'analyzed',
                                'index_analyzer' => 'gs_analyzer_string',
                                'search_analyzer' => 'gs_analyzer_string',
                                'store' => false,
                            ),
                            'gs_string_wildcard' => array(
                                'type' => 'string',
                                'index' => 'analyzed',
                                'index_analyzer' => 'gs_analyzer_string_ngram',
                                'search_analyzer' => 'gs_analyzer_string',
                                'store' => false,
                            ),
                            'gs_integer' => array(
                                'type' => 'integer',
                                'index' => 'no',
                                'store' => false,
                            ),
                        ),
                    ),
                ),
            ),
            // test 'phone' type
            array(
                'mobile',
                array(
                    'type' => 'phone',
                ),
                array(
                    'mobile' => array(
                        'type' => 'string',
                        'index' => 'not_analyzed',
                        'include_in_all' => false,
                        'fields' => array(
                            'gs_phone_wildcard' =>  array(
                                'type' => 'string',
                                'index' => 'analyzed',
                                'index_analyzer' => 'gs_analyzer_phone_ngram',
                                'search_analyzer' => 'gs_analyzer_phone',
                                'store' => false,
                            ),
                        ),
                    ),
                ),
            ),
            // test 'url' type
            array(
                'website',
                array(
                    'type' => 'url',
                ),
                array(
                    'website' => array(
                        'type' => 'string',
                        'index' => 'not_analyzed',
                        'include_in_all' => false,
                        'fields' => array(
                            'gs_url' =>  array(
                                'type' => 'string',
                                'index' => 'analyzed',
                                'index_analyzer' => 'gs_analyzer_url',
                                'search_analyzer' => 'gs_analyzer_url',
                                'store' => false,
                            ),
                            'gs_url_wildcard' => array(
                                'type' => 'string',
                                'index' => 'analyzed',
                                'index_analyzer' => 'gs_analyzer_url_ngram',
                                'search_analyzer' => 'gs_analyzer_url',
                                'store' => false,
                            ),
                        ),
                    ),
                ),
            ),
        );
    }

    /**
     * @covers ::buildMapping
     * @covers ::getMultiFieldProperty
     * @dataProvider providerTestBuildMapping
     */
    public function testBuildMapping(array $types, array $multi, $field, array $defs, array $expected)
    {
        $mapping = new Mapping('foobar');
        $sut = $this->getMultiFieldHandlerMock();

        // set multi field types and definitions
        TestReflection::setProtectedValue($sut, 'typesMultiField', $types);
        TestReflection::setProtectedValue($sut, 'multiFieldDefs', $multi);

        $sut->buildMapping($mapping, $field, $defs);
        $this->assertEquals($expected, $mapping->compile());
    }

    public function providerTestBuildMapping()
    {
        return array(
            // missing field type
            array(
                array(),
                array(),
                'first_name',
                array(
                    'name' => 'first_name',
                ),
                array(),
            ),
            // missing mapping definition
            array(
                array(),
                array(),
                'first_name',
                array(
                    'name' => 'first_name',
                    'type' => 'does_not_exist',
                ),
                array(),
            ),
            // single definition
            array(
                array(
                    'type1' => array('mapping1'),
                ),
                array(
                    'mapping1' => array('type' => 'string'),
                ),
                'field1',
                array(
                    'type' => 'type1',
                ),
                array(
                    'field1' => array(
                        'type' => 'string',
                        'index' => 'not_analyzed',
                        'include_in_all' => false,
                        'fields' => array(
                            'mapping1' => array(
                                'type' => 'string',
                            ),
                        ),
                    ),
                ),
            ),
            // multi definition
            array(
                array(
                    'type1' => array('mapping1', 'mapping2'),
                ),
                array(
                    'mapping1' => array('type' => 'string'),
                    'mapping2' => array('type' => 'integer'),
                ),
                'field1',
                array(
                    'type' => 'type1',
                ),
                array(
                    'field1' => array(
                        'type' => 'string',
                        'index' => 'not_analyzed',
                        'include_in_all' => false,
                        'fields' => array(
                            'mapping1' => array(
                                'type' => 'string',
                            ),
                            'mapping2' => array(
                                'type' => 'integer',
                            ),
                        ),
                    ),
                ),
            ),
            // not_analyzed type
            array(
                array(
                    'type1' => array('not_analyzed'),
                ),
                array(
                    'not_analyzed' => array(),
                ),
                'field1',
                array(
                    'type' => 'type1',
                ),
                array(
                    'field1' => array(
                        'type' => 'string',
                        'index' => 'not_analyzed',
                        'include_in_all' => false,
                        'fields' => array(),
                    ),
                ),
            ),
            // multi definition with not_analyzed
            array(
                array(
                    'type1' => array('mapping1', 'not_analyzed', 'mapping2'),
                ),
                array(
                    'not_analyzed' => array(),
                    'mapping1' => array('type' => 'string'),
                    'mapping2' => array('type' => 'integer'),
                ),
                'field1',
                array(
                    'type' => 'type1',
                ),
                array(
                    'field1' => array(
                        'type' => 'string',
                        'index' => 'not_analyzed',
                        'include_in_all' => false,
                        'fields' => array(
                            'mapping1' => array(
                                'type' => 'string',
                            ),
                            'mapping2' => array(
                                'type' => 'integer',
                            ),
                        ),
                    ),
                ),
            ),
        );
    }

    /**
     * @covers ::buildSearchFields
     * @covers ::getStringFieldsForType
     * @covers ::isStringBased
     * @covers ::getMultiFieldProperty
     * @dataProvider providerTestBuildSearchFields
     */
    public function testBuildSearchFields(array $types, array $multi, $module, $field, array $defs, array $expected)
    {
        $sf = new SearchFields();
        $sut = $this->getMultiFieldHandlerMock();

        // set multi field types and definitions
        TestReflection::setProtectedValue($sut, 'typesMultiField', $types);
        TestReflection::setProtectedValue($sut, 'multiFieldDefs', $multi);

        $sut->buildSearchFields($sf, $module, $field, $defs);
        $this->assertEquals($expected, $sf->getSearchFields());
    }

    public function providerTestBuildSearchFields()
    {
        return array(
            // missing field type
            array(
                array(),
                array(),
                'Contacts',
                'first_name',
                array(
                    'name' => 'first_name',
                ),
                array(),
            ),
            // missing mapping definition
            array(
                array(),
                array(),
                'Contacts',
                'first_name',
                array(
                    'name' => 'first_name',
                    'type' => 'does_not_exist',
                ),
                array(),
            ),
            // test multi field string fields only
            array(
                array(
                    'varchar' => array(
                        'test_default',
                        'test_ngram',
                    ),
                ),
                array(
                    'test_default' => array('type' => 'string'),
                    'test_ngram' => array('type' => 'string'),
                ),
                'Contacts',
                'first_name',
                array(
                    'name' => 'first_name',
                    'type' => 'varchar',
                ),
                array(
                    'Contacts.first_name.test_default',
                    'Contacts.first_name.test_ngram',
                ),
            ),
            // test not_analyzed field
            array(
                array(
                    'custom_type' => array(
                        'not_analyzed',
                    ),
                ),
                array(
                    'not_analyzed' => array(),
                ),
                'CustomModule',
                'custom_field',
                array(
                    'name' => 'custom_field',
                    'type' => 'custom_type',
                ),
                array(
                    'CustomModule.custom_field',
                ),
            ),
            // test not_analyzed field combined with other multifields
            array(
                array(
                    'custom_type' => array(
                        'not_analyzed',
                        'test_default',
                        'test_ngram',
                    ),
                ),
                array(
                    'not_analyzed' => array(),
                    'test_default' => array('type' => 'string'),
                    'test_ngram' => array('type' => 'string'),
                ),
                'CustomModule',
                'custom_field',
                array(
                    'name' => 'custom_field',
                    'type' => 'custom_type',
                ),
                array(
                    'CustomModule.custom_field',
                    'CustomModule.custom_field.test_default',
                    'CustomModule.custom_field.test_ngram',
                ),
            ),
            // test mix string and non-string fields
            array(
                array(
                    'custom_type' => array(
                        'test_default',
                        'test_integer',
                    ),
                ),
                array(
                    'test_default' => array('type' => 'string'),
                    'test_integer' => array('type' => 'integer'),
                ),
                'CustomModule',
                'custom_field',
                array(
                    'name' => 'custom_field',
                    'type' => 'custom_type',
                ),
                array(
                    'CustomModule.custom_field.test_default',
                ),
            ),
            // test mix string and non-string fields with not_analyzed
            array(
                array(
                    'custom_type' => array(
                        'test_default',
                        'test_integer',
                        'not_analyzed',
                    ),
                ),
                array(
                    'not_analyzed' => array(),
                    'test_default' => array('type' => 'string'),
                    'test_integer' => array('type' => 'integer'),
                ),
                'CustomModule',
                'custom_field',
                array(
                    'name' => 'custom_field',
                    'type' => 'custom_type',
                ),
                array(
                    'CustomModule.custom_field.test_default',
                    'CustomModule.custom_field',
                ),
            ),
        );
    }

    /**
     * Get MultiFieldHandler Mock
     * @param array $methods
     * @return \Sugarcrm\Sugarcrm\Elasticsearch\Provider\GlobalSearch\Handler\MultiFieldHandler
     */
    protected function getMultiFieldHandlerMock(array $methods = null)
    {
        return $this->getMockBuilder('Sugarcrm\Sugarcrm\Elasticsearch\Provider\GlobalSearch\Handler\MultiFieldHandler')
            ->disableOriginalConstructor()
            ->setMethods($methods)
            ->getMock();
    }
}
