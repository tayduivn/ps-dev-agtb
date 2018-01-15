<?php
/*
 * Your installation or use of this SugarCRM file is subject to the applicable
 * terms available at
 * http://support.sugarcrm.com/Resources/Master_Subscription_Agreements/.
 * If you do not agree to all of the applicable terms or do not have the
 * authority to bind the entity as an authorized representative, then do not
 * install or use this SugarCRM file.
 *
 * Copyright (C) SugarCRM Inc. All rights reserved.
 */

namespace Sugarcrm\SugarcrmTestsUnit\Elasticsearch\Provider\GlobalSearch\Handler\Implement;

use PHPUnit\Framework\TestCase;
use Sugarcrm\Sugarcrm\Elasticsearch\Analysis\AnalysisBuilder;
use Sugarcrm\Sugarcrm\Elasticsearch\Mapping\Mapping;
use Sugarcrm\Sugarcrm\Elasticsearch\Provider\GlobalSearch\SearchFields;
use Sugarcrm\SugarcrmTestsUnit\TestReflection;

/**
 *
 * @coversDefaultClass \Sugarcrm\Sugarcrm\Elasticsearch\Provider\GlobalSearch\Handler\Implement\MultiFieldHandler
 *
 */
class MultiFieldHandlerTest extends TestCase
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
        $implements = class_implements($nsPrefix . '\Implement\MultiFieldHandler');
        $this->assertEquals($interfaces, array_values(array_intersect($implements, $interfaces)));
    }

    /**
     * @covers ::setProvider
     * @dataProvider providerTestSetProvider
     */
    public function testSetProvider($property, array $value, $method, array $expected)
    {
        $provider = $this->getMockBuilder('\Sugarcrm\Sugarcrm\Elasticsearch\Provider\GlobalSearch\GlobalSearch')
            ->disableOriginalConstructor()
            ->getMock();

        $provider->expects($this->once())
            ->method($method)
            ->with($this->equalTo($expected));

        $sut = $this->getMultiFieldHandlerMock();
        TestReflection::setProtectedValue($sut, $property, $value);
        $sut->setProvider($provider);
    }

    public function providerTestSetProvider()
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
                        'number_of_fragments' => 0,
                    ),
                ),
                'addHighlighterFields',
                array(
                    '*.field_default' => array(
                        'number_of_fragments' => 0,
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
                            'gs_filter_ngram_3_15',
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
                            'gs_filter_ngram_3_15',
                        ),
                        'type' => 'custom',
                    ),
                    'gs_analyzer_string_exact' => array(
                        'tokenizer' => 'whitespace',
                        'filter' => array(
                            'lowercase',
                        ),
                        'type' => 'custom',
                    ),
                    'gs_analyzer_string_html' => array(
                        'tokenizer' => 'standard',
                        'filter' => array(
                            'lowercase',
                        ),
                        'char_filter' => array(
                            'html_strip',
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
                        'pattern' => '[^\\d]+',
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
    public function testBuildMappingValidation($module, $field, array $defs, array $expected)
    {
        $mapping = new Mapping($module);
        $sut = $this->getMultiFieldHandlerMock();
        $sut->buildMapping($mapping, $field, $defs);
        $this->assertEquals($expected, $mapping->compile());
    }

    public function providerTestBuildMappingValidation()
    {
        return array(
            // test 'varchar' type
            array(
                'Accounts',
                'billing_street',
                array(
                    'type' => 'varchar',
                ),
                array(
                    'Accounts__billing_street' => array(
                        'type' => 'keyword',
                        'index' => false,
                        'fields' => array(
                            'gs_string' =>  array(
                                'type' => 'text',
                                'index' => true,
                                'analyzer' => 'gs_analyzer_string',
                                'store' => true,
                            ),
                            'gs_string_wildcard' => array(
                                'type' => 'text',
                                'index' => true,
                                'analyzer' => 'gs_analyzer_string_ngram',
                                'search_analyzer' => 'gs_analyzer_string',
                                'store' => true,
                            ),
                        ),
                    ),
                    'billing_street' => array(
                        'type' => 'keyword',
                        'index' => false,
                        'copy_to' => array(
                            'Accounts__billing_street',
                        ),
                    ),
                ),
            ),
            // test 'name' type
            array(
                'Opporunities',
                'name',
                array(
                    'type' => 'name',
                ),
                array(
                    'Opporunities__name' => array(
                        'type' => 'keyword',
                        'index' => false,
                        'fields' => array(
                            'gs_string' =>  array(
                                'type' => 'text',
                                'index' => true,
                                'analyzer' => 'gs_analyzer_string',
                                'store' => true,
                            ),
                            'gs_string_wildcard' => array(
                                'type' => 'text',
                                'index' => true,
                                'analyzer' => 'gs_analyzer_string_ngram',
                                'search_analyzer' => 'gs_analyzer_string',
                                'store' => true,
                            ),
                        ),
                    ),
                    'name' => array(
                        'type' => 'keyword',
                        'index' => false,
                        'copy_to' => array(
                            'Opporunities__name',
                        ),
                    ),
                ),
            ),
            // test 'text' type
            array(
                'Accounts',
                'description',
                array(
                    'type' => 'text',
                ),
                array(
                    'Accounts__description' => array(
                        'type' => 'keyword',
                        'index' => false,
                        'fields' => array(
                            'gs_string' =>  array(
                                'type' => 'text',
                                'index' => true,
                                'analyzer' => 'gs_analyzer_string',
                                'store' => true,
                            ),
                            //'gs_text_wildcard' => array(
                            //    'type' => 'string',
                            //    'index' => 'analyzed',
                            //    'index_analyzer' => 'gs_analyzer_text_ngram',
                            //    'search_analyzer' => 'gs_analyzer_string',
                            //    'store' => true,
                            //),
                        ),
                    ),
                    'description' => array(
                        'type' => 'keyword',
                        'index' => false,
                        'copy_to' => array(
                            'Accounts__description',
                        ),
                    ),
                ),
            ),
            // test 'datetime' type
            array(
                'Accounts',
                'date_modified',
                array(
                    'type' => 'datetime',
                ),
                array(
                    'Accounts__date_modified' => array(
                        'type' => 'keyword',
                        'index' => false,
                        'fields' => array(
                            'gs_datetime' =>  array(
                                'type' => 'date',
                                'format' => 'YYYY-MM-dd HH:mm:ss',
                                'store' => false,
                            ),
                        ),
                    ),
                    'date_modified' => array(
                        'type' => 'keyword',
                        'index' => false,
                        'copy_to' => array(
                            'Accounts__date_modified',
                            'Common__date_modified',
                        ),
                    ),
                    'Common__date_modified' => array(
                        'type' => 'keyword',
                        'index' => false,
                        'fields' => array(
                            'gs_datetime' => array(
                                'type' => 'date',
                                'format' => 'YYYY-MM-dd HH:mm:ss',
                                'store' => false,
                            ),
                        ),
                    ),
                ),
            ),
            // test 'datetimecombo' type
            array(
                'Meetings',
                'date_start',
                array(
                    'type' => 'datetimecombo',
                ),
                array(
                    'Meetings__date_start' => array(
                        'type' => 'keyword',
                        'index' => false,
                        'fields' => array(
                            'gs_datetime' =>  array(
                                'type' => 'date',
                                'format' => 'YYYY-MM-dd HH:mm:ss',
                                'store' => false,
                            ),
                        ),
                    ),
                    'date_start' => array(
                        'type' => 'keyword',
                        'index' => false,
                        'copy_to' => array(
                            'Meetings__date_start',
                        ),
                    ),
                ),
            ),
            // test 'date' type
            array(
                'Opportunities',
                'date_closed',
                array(
                    'type' => 'date',
                ),
                array(
                    'Opportunities__date_closed' => array(
                        'type' => 'keyword',
                        'index' => false,
                        'fields' => array(
                            'gs_date' =>  array(
                                'type' => 'date',
                                'format' => 'YYYY-MM-dd',
                                'store' => false,
                            ),
                        ),
                    ),
                    'date_closed' => array(
                        'type' => 'keyword',
                        'index' => false,
                        'copy_to' => array(
                            'Opportunities__date_closed',
                        ),
                    ),
                ),
            ),
            // test 'int' type
            array(
                'Cases',
                'case_number',
                array(
                    'type' => 'int',
                ),
                array(
                    'Cases__case_number' => array(
                        'type' => 'keyword',
                        'index' => false,
                        'fields' => array(
                            'gs_string' =>  array(
                                'type' => 'text',
                                'index' => true,
                                'analyzer' => 'gs_analyzer_string',
                                'store' => true,
                            ),
                            'gs_string_wildcard' => array(
                                'type' => 'text',
                                'index' => true,
                                'analyzer' => 'gs_analyzer_string_ngram',
                                'search_analyzer' => 'gs_analyzer_string',
                                'store' => true,
                            ),
                            'gs_integer' => array(
                                'type' => 'integer',
                                'index' => false,
                                'store' => false,
                            ),
                        ),
                    ),
                    'case_number' => array(
                        'type' => 'keyword',
                        'index' => false,
                        'copy_to' => array(
                            'Cases__case_number',
                        ),
                    ),
                ),
            ),
            // test 'phone' type
            array(
                'Contacts',
                'mobile',
                array(
                    'type' => 'phone',
                ),
                array(
                    'Contacts__mobile' => array(
                        'type' => 'keyword',
                        'index' => false,
                        'fields' => array(
                            'gs_phone_wildcard' => array(
                                'type' => 'text',
                                'index' => true,
                                'analyzer' => 'gs_analyzer_phone_ngram',
                                'search_analyzer' => 'gs_analyzer_phone',
                                'store' => true,
                            ),
                            'gs_not_analyzed' => array(
                                'type' => 'keyword',
                                'index' => true,
                                'store' => true,
                            ),
                        ),
                    ),
                    'mobile' => array(
                        'type' => 'keyword',
                        'index' => false,
                        'copy_to' => array(
                            'Contacts__mobile',
                        ),
                    ),
                ),
            ),
            // test 'url' type
            array(
                'Accounts',
                'website',
                array(
                    'type' => 'url',
                ),
                array(
                    'Accounts__website' => array(
                        'type' => 'keyword',
                        'index' => false,
                        'fields' => array(
                            'gs_url' =>  array(
                                'type' => 'text',
                                'index' => true,
                                'analyzer' => 'gs_analyzer_url',
                                'store' => false,
                            ),
                            'gs_url_wildcard' => array(
                                'type' => 'text',
                                'index' => true,
                                'analyzer' => 'gs_analyzer_url_ngram',
                                'search_analyzer' => 'gs_analyzer_url',
                                'store' => false,
                            ),
                        ),
                    ),
                    'website' => array(
                        'type' => 'keyword',
                        'index' => false,
                        'copy_to' => array(
                            'Accounts__website',
                        ),
                    ),
                ),
            ),
            // test 'id' type
            array(
                'Accounts',
                'id',
                array(
                    'type' => 'id',
                ),
                array(
                    'Accounts__id' => array(
                        'type' => 'keyword',
                        'index' => false,
                        'fields' => array(
                            'gs_not_analyzed' => array(
                                'type' => 'keyword',
                                'index' => true,
                                'store' => true,
                            ),
                        ),
                    ),
                    'id' => array(
                        'type' => 'keyword',
                        'index' => false,
                        'copy_to' => array(
                            'Accounts__id',
                        ),
                    ),
                ),
            ),
            // test 'exact' type
            array(
                'Accounts',
                'stuff',
                array(
                    'type' => 'exact',
                ),
                array(
                    'Accounts__stuff' => array(
                        'type' => 'keyword',
                        'index' => false,
                        'fields' => array(
                            'gs_string_exact' => array(
                                'type' => 'text',
                                'index' => true,
                                'analyzer' => 'gs_analyzer_string_exact',
                                'store' => true,
                            ),
                        ),
                    ),
                    'stuff' => array(
                        'type' => 'keyword',
                        'index' => false,
                        'copy_to' => array(
                            'Accounts__stuff',
                        ),
                    ),
                ),
            ),
            // test 'longtext' type
            array(
                'Accounts',
                'description',
                array(
                    'type' => 'longtext',
                ),
                array(
                    'Accounts__description' => array(
                        'type' => 'keyword',
                        'index' => false,
                        'fields' => array(
                            'gs_string' =>  array(
                                'type' => 'text',
                                'index' => true,
                                'analyzer' => 'gs_analyzer_string',
                                'store' => true,
                            ),
                            //'gs_text_wildcard' => array(
                            //    'type' => 'string',
                            //    'index' => 'analyzed',
                            //    'index_analyzer' => 'gs_analyzer_text_ngram',
                            //    'search_analyzer' => 'gs_analyzer_string',
                            //    'store' => true,
                            //),
                        ),
                    ),
                    'description' => array(
                        'type' => 'keyword',
                        'index' => false,
                        'copy_to' => array(
                            'Accounts__description',
                        ),
                    ),
                ),
            ),
            // test 'htmleditable_tinymce' type
            array(
                'KBContents',
                'body',
                array(
                    'type' => 'htmleditable_tinymce',
                ),
                array(
                    'KBContents__body' => array(
                        'type' => 'keyword',
                        'index' => false,
                        'fields' => array(
                            'gs_string' =>  array(
                                'type' => 'text',
                                'index' => true,
                                'analyzer' => 'gs_analyzer_string',
                                'store' => true,
                            ),
                            //'gs_text_wildcard' => array(
                            //    'type' => 'string',
                            //    'index' => 'analyzed',
                            //    'index_analyzer' => 'gs_analyzer_text_ngram',
                            //    'search_analyzer' => 'gs_analyzer_string',
                            //    'store' => true,
                            //),
                        ),
                    ),
                    'body' => array(
                        'type' => 'keyword',
                        'index' => false,
                        'copy_to' => array(
                            'KBContents__body',
                        ),
                    ),
                ),
            ),
            // test 'enum' type
            array(
                'Bugs',
                'status',
                array(
                    'type' => 'enum',
                ),
                array(
                    'Bugs__status' => array(
                        'type' => 'keyword',
                        'index' => false,
                        'fields' => array(
                            'gs_not_analyzed' => array(
                                'type' => 'keyword',
                                'index' => true,
                                'store' => true,
                            ),
                        ),
                    ),
                    'status' => array(
                        'type' => 'keyword',
                        'index' => false,
                        'copy_to' => array(
                            'Bugs__status',
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
    public function testBuildMapping($module, $types, array $multi, $field, $defs, $expected)
    {
        $mapping = new Mapping($module);
        $sut = $this->getMultiFieldHandlerMock();

        // set multi field types and definitions
        TestReflection::setProtectedValue($sut, 'typesMultiField', $types);
        TestReflection::setProtectedValue($sut, 'multiFieldDefs', $multi);

        $sut->buildMapping($mapping, $field, $defs);
        $this->assertSame($expected, $mapping->compile());
    }

    public function providerTestBuildMapping()
    {
        return array(
            // missing field type
            array(
                'FooBar',
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
                'FooBar',
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
                'custom_Module',
                array(
                    'type1' => array('mapping1'),
                ),
                array(
                    'mapping1' => array('type' => 'text'),
                ),
                'field1',
                array(
                    'type' => 'type1',
                ),
                array(
                    'custom_Module__field1' => array(
                        'type' => 'keyword',
                        'index' => false,
                        'fields' => array(
                            'mapping1' => array(
                                'type' => 'text',
                            ),
                        ),
                    ),
                    'field1' => array(
                        'type' => 'keyword',
                        'index' => false,
                        'copy_to' => array(
                            'custom_Module__field1',
                        ),
                    ),
                ),
            ),
            // multi definition
            array(
                'Accounts',
                array(
                    'type1' => array('mapping1', 'mapping2'),
                ),
                array(
                    'mapping1' => array('type' => 'text'),
                    'mapping2' => array('type' => 'integer'),
                ),
                'field1',
                array(
                    'type' => 'type1',
                ),
                array(
                    'Accounts__field1' => array(
                        'type' => 'keyword',
                        'index' => false,
                        'fields' => array(
                            'mapping1' => array(
                                'type' => 'text',
                            ),
                            'mapping2' => array(
                                'type' => 'integer',
                            ),
                        ),
                    ),
                    'field1' => array(
                        'type' => 'keyword',
                        'index' => false,
                        'copy_to' => array(
                            'Accounts__field1',
                        ),
                    ),
                ),
            ),
            // sortable
            array(
                'Module',
                array(
                    'type1' => array('mapping1'),
                ),
                array(
                    'mapping1' => array('type' => 'text'),
                ),
                'field1',
                array(
                    'type' => 'type1',
                    'full_text_search' => array(
                        'sortable' => true,
                    ),
                ),
                array(
                    'Module__field1' => array(
                        'type' => 'keyword',
                        'index' => false,
                        'fields' => array(
                            'mapping1' => array(
                                'type' => 'text',
                            ),
                        ),
                    ),
                    'field1' => array(
                        'type' => 'keyword',
                        'index' => false,
                        'copy_to' => array(
                            'Module__field1',
                            'Common__field1',
                        ),
                    ),
                    'Common__field1' => array(
                        'type' => 'keyword',
                        'index' => false,
                        'fields' => array(
                            'mapping1' => array(
                                'type' => 'text',
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
        $sut = $this->getMultiFieldHandlerMock(array('addHighlighterField'));

        // set multi field types and definitions
        TestReflection::setProtectedValue($sut, 'typesMultiField', $types);
        TestReflection::setProtectedValue($sut, 'multiFieldDefs', $multi);

        // mock SearchFields
        $sfs = new SearchFields();
        $sut->buildSearchFields($sfs, $module, $field, $defs);

        $fields = [];
        foreach ($sfs as $sf) {
            $fields[] = $sf->compile();
        }
        $this->assertEquals($expected, $fields);
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
                    'test_default' => array('type' => 'text'),
                    'test_ngram' => array('type' => 'text'),
                ),
                'Contacts',
                'first_name',
                array(
                    'name' => 'first_name',
                    'type' => 'varchar',
                ),
                array(
                    'Contacts__first_name.test_default',
                    'Contacts__first_name.test_ngram',
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
                    'test_default' => array('type' => 'text'),
                    'test_integer' => array('type' => 'integer'),
                ),
                'CustomModule',
                'custom_field',
                array(
                    'name' => 'custom_field',
                    'type' => 'custom_type',
                ),
                array(
                    'CustomModule__custom_field.test_default',
                ),
            ),
        );
    }

    /**
     * Get MultiFieldHandler Mock
     * @param array $methods
     * @return \Sugarcrm\Sugarcrm\Elasticsearch\Provider\GlobalSearch\Handler\Implement\MultiFieldHandler
     */
    protected function getMultiFieldHandlerMock(array $methods = null)
    {
        return $this->getMockBuilder('Sugarcrm\Sugarcrm\Elasticsearch\Provider\GlobalSearch\Handler\Implement\MultiFieldHandler')
            ->setMethods($methods)
            ->getMock();
    }
}
