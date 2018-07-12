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
 * @coversDefaultClass \Sugarcrm\Sugarcrm\Elasticsearch\Provider\GlobalSearch\Handler\Implement\WorklogHandler
 *
 */
class WorklogHandlerTest extends TestCase
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
        $implements = class_implements($nsPrefix . '\Implement\WorklogHandler');
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
                array('worklog'),
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
                array('worklog_search' => 'worklog'),
            ),
            array(
                null,
                array(),
                'addSkipTypesFromQueue',
                array('worklog'),
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
                    'gs_analyzer_worklog' => array(
                        'tokenizer' => 'whitespace',
                        'filter' => array(
                            'lowercase',
                        ),
                        'type' => 'custom',
                    ),
                    'gs_analyzer_worklog_ngram' => array(
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
            // test 'worklog' type for 'worklog' field
            array(
                'testModule',
                'worklog',
                array(
                    'name' => 'worklog',
                    'type' => 'worklog',
                ),
                array(
                    'testModule__worklog_search' => array(
                        'type' => 'object',
                        'dynamic' => false,
                        'enabled' => true,
                        'properties' => array(
                            'worklog_entry' => array(
                                'type' => 'keyword',
                                'index' => false,
                                'fields' => array(
                                    'gs_worklog' => array(
                                        'type' => 'text',
                                        'index' => true,
                                        'analyzer' => 'gs_analyzer_worklog',
                                        'store' => true,
                                    ),
                                    'gs_worklog_wildcard' => array(
                                        'type' => 'text',
                                        'index' => true,
                                        'analyzer' => 'gs_analyzer_worklog_ngram',
                                        'search_analyzer' => 'gs_analyzer_worklog',
                                        'store' => true,
                                    ),
                                ),
                            ),
                        ),
                    ),
                    'testModule__worklog' => array(
                        'type' => 'object',
                        'dynamic' => false,
                        'enabled' => false,
                    ),
                ),
            ),
            // test 'worklog' type for non 'worklog' field
            array(
                'Accounts',
                'other_worklog',
                array(
                    'name' => 'other_worklog',
                    'type' => 'worklog',
                ),
                array(),
            ),
            // test non 'worklog' type for 'worklog' field
            array(
                'Contacts',
                'worklog',
                array(
                    'name' => 'worklog',
                    'type' => 'non_worklog',
                ),
                array(),
            ),
            // test non 'worklog' type for non 'worklog' field
            array(
                'Leads',
                'other_worklog',
                array(
                    'name' => 'other_worklog',
                    'type' => 'non_worklog',
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
            // worklog field
            array(
                'Contacts',
                'worklog',
                array(
                    'name' => 'worklog',
                    'type' => 'worklog',
                ),
                array(
                    'Contacts__worklog_search.worklog_entry.gs_worklog',
                    'Contacts__worklog_search.worklog_entry.gs_worklog_wildcard',
                ),
            ),
            // non worklog type/field
            array(
                'Contacts',
                'first_name',
                array(
                    'name' => 'first_name',
                    'type' => 'varchar',
                ),
                array(),
            ),
            // worklog field, non worklog type
            array(
                'Contacts',
                'worklog',
                array(
                    'name' => 'worklog',
                    'type' => 'varchar',
                ),
                array(),
            ),
            // non worklog field, worklog type
            array(
                'Contacts',
                'other_worklog',
                array(
                    'name' => 'other_worklog',
                    'type' => 'worklog',
                ),
                array(),
            ),
        );
    }
    /**
     * Get WorklogHandler Mock
     * @param array $methods
     * @return \Sugarcrm\Sugarcrm\Elasticsearch\Provider\GlobalSearch\Handler\WorklogHandler
     */
    protected function getWorklogHandlerMock(array $methods = null)
    {
        return $this->getMockBuilder('Sugarcrm\Sugarcrm\Elasticsearch\Provider\GlobalSearch\Handler\Implement\WorklogHandler')
            ->disableOriginalConstructor()
            ->setMethods($methods)
            ->getMock();
    }
}
