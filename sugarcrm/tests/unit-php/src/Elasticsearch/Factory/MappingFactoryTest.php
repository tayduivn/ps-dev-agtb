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

namespace Sugarcrm\SugarcrmTestsUnit\Elasticsearch\Factory;

use Sugarcrm\Sugarcrm\Elasticsearch\Factory\MappingFactory;

/**
 *
 * @coversDefaultClass \Sugarcrm\Sugarcrm\Elasticsearch\Factory\MappingFactory
 *
 */
class MappingFactoryTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @covers ::getBaseStringType
     *
     */
    public function testGetBaseStringType()
    {
        $this->assertSame(MappingFactory::TEXT_TYPE, MappingFactory::getBaseStringType());
    }

    /**
     * @covers ::getAllowedTypes
     *
     */
    public function testGetAllowedTypes()
    {
        $allowedTypes = array(
            'text',
            'keyword',
            'float',
            'double',
            'byte',
            'short',
            'integer',
            'long',
            'token_count',
            'date',
            'boolean',
        );

        $this->assertSame($allowedTypes, MappingFactory::getAllowedTypes());
    }

    /**
     * @covers ::isStringType
     *
     * @dataProvider providerTestIsStringType
     */
    public function testIsStringType($type, $expected)
    {
        $this->assertSame($expected, MappingFactory::isStringType($type));
    }

    public function providerTestIsStringType()
    {
        return array(
            array('text', true),
            array('keyword', true),
            array('integer', false),
        );
    }

    /**
     * @covers ::createFieldBase
     * @covers ::createBaseProperty
     *
     * @dataProvider providerTestCreateFieldBase
     */
    public function testCreateFieldBase($type, $index, $includeAll, $expected)
    {
        $this->assertSame($expected, MappingFactory::createFieldBase($type, $index, $includeAll));
    }

    public function providerTestCreateFieldBase()
    {
        return array(
            array(
                'text',
                true,
                false,
                array(
                    'type' => 'text',
                    'index' => true,
                    'include_in_all' => false,
                )
            ),
            array(
                'keyword',
                true,
                true,
                array(
                    'type' => 'keyword',
                    'index' => true,
                    'include_in_all' => true,
                )
            ),
            // no index type
            array(
                'date',
                null,
                false,
                array(
                    'type' => 'date',
                    'include_in_all' => false,
                )
            ),
        );
    }

    /**
     * @covers ::createMappingDef
     *
     * @dataProvider providerTestCreateMappingDef
     */
    public function testCreateMappingDef(
        $type,
        $index,
        $analyzerName,
        $searchAnalyzerName,
        $store,
        $format,
        $expected
    ) {
        $mappingDef = MappingFactory::createMappingDef(
            $type,
            $index,
            $analyzerName,
            $searchAnalyzerName,
            $store,
            $format
        );

        $this->assertSame($expected, $mappingDef);
    }

    public function providerTestCreateMappingDef()
    {
        return array(
            array(
                'text',
                true,
                'gs_string_analyzer',
                'gs_string_search_analyzer',
                'store',
                'this format',
                array(
                    'type' => 'text',
                    'index' => true,
                    'analyzer' => 'gs_string_analyzer',
                    'search_analyzer' => 'gs_string_search_analyzer',
                    'store' => 'store',
                    'format' => 'this format',
                ),
            ),
            // no search_analyzer will be stored if it has the same name with analyzer
            array(
                'keyword',
                true,
                'gs_string_analyzer',
                'gs_string_analyzer',
                'store',
                'this format',
                array(
                    'type' => 'keyword',
                    'index' => true,
                    'analyzer' => 'gs_string_analyzer',
                    'store' => 'store',
                    'format' => 'this format',
                ),
            ),
            // no index type specified
            array(
                'integer',
                null,
                'gs_string_analyzer',
                'gs_string_analyzer',
                'store',
                'this format',
                array(
                    'type' => 'integer',
                    'analyzer' => 'gs_string_analyzer',
                    'store' => 'store',
                    'format' => 'this format',
                ),
            ),
            // no analyzer name provided
            array(
                'keyword',
                true,
                null,
                null,
                'store',
                'this format',
                array(
                    'type' => 'keyword',
                    'index' => true,
                    'store' => 'store',
                    'format' => 'this format',
                ),
            ),
        );
    }

    /**
     * @covers ::createBaseProperty
     *
     * @dataProvider providerTestCreateBasePropertyException
     *
     * @expectedException \Exception
     */
    public function testCreateBasePropertyException($type, $index)
    {
        MappingFactory::createBaseProperty($type, $index);
    }

    public function providerTestCreateBasePropertyException()
    {
        return array(
            array('text', 'no_analyzed'),
            array('integer', 'yes'),
            array('keyword', 'no'),
        );
    }
}
