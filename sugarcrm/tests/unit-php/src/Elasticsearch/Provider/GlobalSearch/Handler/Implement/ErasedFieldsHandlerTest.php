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

use Sugarcrm\Sugarcrm\Elasticsearch\Provider\GlobalSearch\Handler\Implement\ErasedFieldsHandler;
use Sugarcrm\SugarcrmTestsUnit\TestReflection;
use Sugarcrm\Sugarcrm\Elasticsearch\Mapping\Mapping;
use Sugarcrm\Sugarcrm\Elasticsearch\Adapter\Document;

/**
 *
 * @coversDefaultClass \Sugarcrm\Sugarcrm\Elasticsearch\Provider\GlobalSearch\Handler\Implement\ErasedFieldsHandler
 *
 */
class ErasedFieldsHandlerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @coversNothing
     */
    public function testRequiredInterfaces()
    {
        $nsPrefix = 'Sugarcrm\Sugarcrm\Elasticsearch\Provider\GlobalSearch\Handler';
        $interfaces = [
            $nsPrefix . '\MappingHandlerInterface',
            $nsPrefix . '\ProcessDocumentHandlerInterface',
        ];
        $implements = class_implements($nsPrefix . '\Implement\ErasedFieldsHandler');
        $this->assertEquals(
            $interfaces,
            array_values(array_intersect($implements, $interfaces)),
            'missing required interface!'
        );
    }

    /**
     * @covers ::buildMapping
     * @dataProvider providerTestBuildMapping
     */
    public function testBuildMapping($module, array $fields, array $defs, array $expected)
    {
        $mapping = new Mapping($module);
        $sut = $this->getErasedFieldsHandlerMock();

        // make sure only 1 es field has been created
        foreach ($fields as $field) {
            $sut->buildMapping($mapping, $field, $defs);
        }
        $this->assertEquals($expected, $mapping->compile());
    }

    public function providerTestBuildMapping()
    {
        return [
            [
                'anyModule',
                ['test_field_1', 'test_field_2', 'test_field_3'],
                ['type' => 'not_used_type'],
                [
                    'Common__erased_fields' => [
                        'type' => 'keyword',
                        'index' => false,
                        'fields' => [
                                'erased_fields' => ['type' => 'keyword'],
                            ],
                        ],
                    'erased_fields' => [
                            'type' => 'keyword',
                            'index' => false,
                            'copy_to' => ['Common__erased_fields'],
                        ],

                ],
            ],
        ];
    }

    /**
     * @covers ::processDocumentPreIndex
     * @dataProvider providerTestProcessDocumentPreIndex
     *
     */
    public function testProcessDocumentPreIndex($module, $erasedFields, $expected)
    {
        $bean = $this->getSugarBeanMock();
        $sut = $this->getErasedFieldsHandlerMock(['retrieveErasedFields']);

        $sut->expects($this->once())
            ->method('retrieveErasedFields')
            ->will($this->returnValue($erasedFields));

        $document = new Document();
        $document->setType($module);

        $sut->processDocumentPreIndex($document, $bean);
        $this->assertEquals($expected, $document->getData());
    }

    public function providerTestProcessDocumentPreIndex()
    {
        return [
            // string value
            [
                'Contacts',
                '["email_address","email_address_caps"]',
                ['erased_fields' => '["email_address","email_address_caps"]'],
            ],
            // array value
            [
                'Contacts',
                ["email_address", "email_address_caps"],
                ['erased_fields' => ["email_address", "email_address_caps"]],
            ],
            // empty value
            [
                'Accounts',
                null,
                ['erased_fields' => null],
            ],
        ];
    }

    /**
     * Get ErasedFieldsHandler Mock
     * @param array $methods
     * @return \Sugarcrm\Sugarcrm\Elasticsearch\Provider\GlobalSearch\Handler\ErasedFieldsHandler
     */
    protected function getErasedFieldsHandlerMock(array $methods = null)
    {
        return $this->getMockBuilder('Sugarcrm\Sugarcrm\Elasticsearch\Provider\GlobalSearch\Handler\Implement\ErasedFieldsHandler')
            ->disableOriginalConstructor()
            ->setMethods($methods)
            ->getMock();
    }

    /**
     * Get SugarBean mock
     * @return \SugarBean
     */
    protected function getSugarBeanMock()
    {
        return $this->getMockBuilder('SugarBean')
            ->disableOriginalConstructor()
            ->setMethods(null)
            ->getMock();
    }
}
