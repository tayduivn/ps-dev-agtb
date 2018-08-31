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
use Sugarcrm\Sugarcrm\Elasticsearch\Adapter\Document;
use Sugarcrm\Sugarcrm\Elasticsearch\Analysis\AnalysisBuilder;
use Sugarcrm\Sugarcrm\Elasticsearch\Mapping\Mapping;
use Sugarcrm\Sugarcrm\Elasticsearch\Provider\GlobalSearch\SearchFields;
use Sugarcrm\SugarcrmTestsUnit\TestReflection;

/**
 *
 * @coversDefaultClass \Sugarcrm\Sugarcrm\Elasticsearch\Provider\GlobalSearch\Handler\Implement\CommentslogHandler
 *
 */
class CommentslogHandlerTest extends TestCase
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
            $nsPrefix . '\ProcessDocumentHandlerInterface',
        );
        $implements = class_implements($nsPrefix . '\Implement\CommentslogHandler');
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

        $sut = $this->getWorklogHandlerMock();

        if ($property !== null) {
            TestReflection::setProtectedValue($sut, $property, $value);
        }

        $sut->setProvider($provider);
    }

    public function providerTestSetProvider()
    {
        return array(
            array(
                null,
                array(),
                'addSupportedTypes',
                array('commentslog'),
            ),
            array(
                'highlighterFields',
                array('stuff'),
                'addHighlighterFields',
                array('stuff'),
            ),
            array(
                'weightedBoost',
                array('morestuff'),
                'addWeightedBoosts',
                array('morestuff'),
            ),
            array(
                null,
                array(),
                'addFieldRemap',
                array('commentslog_search' => 'commentslog'),
            ),
            array(
                null,
                array(),
                'addSkipTypesFromQueue',
                array('commentslog'),
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
        $sut = $this->getWorklogHandlerMock();
        $sut->buildAnalysis($analysisBuilder);

        $expected = array(
            'analysis' => array(
                'analyzer' => array(
                    'gs_analyzer_commentslog' => array(
                        'tokenizer' => 'whitespace',
                        'filter' => array(
                            'lowercase',
                        ),
                        'type' => 'custom',
                    ),
                    'gs_analyzer_commentslog_ngram' => array(
                        'tokenizer' => 'whitespace',
                        'filter' => array(
                            'lowercase',
                            'gs_filter_ngram_1_15',
                        ),
                        'type' => 'custom',
                    ),
                ),
                'tokenizer' => array(),
                'filter' => array(),
                'char_filter' => array(),
            ),
        );

        $this->assertEquals($expected, $analysisBuilder->compile());
    }

    /**
     * @covers ::buildMapping
     * @dataProvider providerTestBuildMapping
     */
    public function testBuildMapping($module, $field, array $defs, array $expected)
    {
        $mapping = new Mapping($module);
        $sut = $this->getWorklogHandlerMock();
        $sut->buildMapping($mapping, $field, $defs);
        $this->assertEquals($expected, $mapping->compile());
    }

    public function providerTestBuildMapping()
    {
        return array(
            // test 'commentslog' type for 'commentslog' field
            array(
                'testModule',
                'commentslog',
                array(
                    'name' => 'commentslog',
                    'type' => 'commentslog',
                ),
                array(
                    'testModule__commentslog_search' => array(
                        'type' => 'object',
                        'dynamic' => false,
                        'enabled' => true,
                        'properties' => array(
                            'commentslog_entry' => array(
                                'type' => 'keyword',
                                'index' => false,
                                'fields' => array(
                                    'gs_commentslog' => array(
                                        'type' => 'text',
                                        'index' => true,
                                        'analyzer' => 'gs_analyzer_commentslog',
                                        'store' => true,
                                    ),
                                    'gs_commentslog_wildcard' => array(
                                        'type' => 'text',
                                        'index' => true,
                                        'analyzer' => 'gs_analyzer_commentslog_ngram',
                                        'search_analyzer' => 'gs_analyzer_commentslog',
                                        'store' => true,
                                    ),
                                ),
                            ),
                        ),
                    ),
                    'testModule__commentslog' => array(
                        'type' => 'object',
                        'dynamic' => false,
                        'enabled' => false,
                    ),
                ),
            ),
            // test 'commentslog' type for non 'commentslog' field
            array(
                'Accounts',
                'other_commentslog',
                array(
                    'name' => 'other_commentslog',
                    'type' => 'commentslog',
                ),
                array(),
            ),
            // test non 'commentslog' type for 'commentslog' field
            array(
                'Contacts',
                'commentslog',
                array(
                    'name' => 'commentslog',
                    'type' => 'non_commentslog',
                ),
                array(),
            ),
            // test non 'commentslog' type for non 'commentslog' field
            array(
                'Leads',
                'other_commentslog',
                array(
                    'name' => 'other_commentslog',
                    'type' => 'non_commentslog',
                ),
                array(),
            ),
        );
    }

    /**
     * @covers ::buildSearchFields
     * @dataProvider providerTestBuildSearchFields
     */
    public function testBuildSearchFields($module, $field, array $defs, array $expected)
    {
        $sfs = new SearchFields();
        $sut = $this->getWorklogHandlerMock();
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
            // commentslog field
            array(
                'Contacts',
                'commentslog',
                array(
                    'name' => 'commentslog',
                    'type' => 'commentslog',
                ),
                array(
                    'Contacts__commentslog_search.commentslog_entry.gs_commentslog',
                    'Contacts__commentslog_search.commentslog_entry.gs_commentslog_wildcard',
                ),
            ),
            // non commentslog type/field
            array(
                'Contacts',
                'first_name',
                array(
                    'name' => 'first_name',
                    'type' => 'varchar',
                ),
                array(),
            ),
            // commentslog field, non commentslog type
            array(
                'Contacts',
                'commentslog',
                array(
                    'name' => 'commentslog',
                    'type' => 'varchar',
                ),
                array(),
            ),
            // non commentslog field, commentslog type
            array(
                'Contacts',
                'other_commentslog',
                array(
                    'name' => 'other_commentslog',
                    'type' => 'commentslog',
                ),
                array(),
            ),
        );
    }
    /**
     * Get CommentslogHandler Mock
     * @param array $methods
     * @return \Sugarcrm\Sugarcrm\Elasticsearch\Provider\GlobalSearch\Handler\CommentslogHandler
     */
    protected function getWorklogHandlerMock(array $methods = null)
    {
        return $this->getMockBuilder('Sugarcrm\Sugarcrm\Elasticsearch\Provider\GlobalSearch\Handler\Implement\CommentslogHandler')
            ->disableOriginalConstructor()
            ->setMethods($methods)
            ->getMock();
    }
}
