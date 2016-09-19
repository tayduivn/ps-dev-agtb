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

use Sugarcrm\Sugarcrm\Elasticsearch\Provider\GlobalSearch\Handler\HandlerFilterIterator;
use Sugarcrm\Sugarcrm\Elasticsearch\Provider\GlobalSearch\Handler\HandlerCollection;
use Sugarcrm\Sugarcrm\Elasticsearch\Provider\GlobalSearch\GlobalSearch;
use Sugarcrm\SugarcrmTestsUnit\Elasticsearch\Provider\GlobalSearch\Handler\Fixtures\AnalysisHandler;
use Sugarcrm\SugarcrmTestsUnit\Elasticsearch\Provider\GlobalSearch\Handler\Fixtures\MappingHandler;
use Sugarcrm\SugarcrmTestsUnit\Elasticsearch\Provider\GlobalSearch\Handler\Fixtures\AnalysisMappingHandler;

/**
 *
 * @coversDefaultClass \Sugarcrm\Sugarcrm\Elasticsearch\Provider\GlobalSearch\Handler\HandlerFilterIterator
 *
 */
class HandlerFilterIteratorTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @covers ::__construct
     * @covers ::setInterface
     * @covers ::accept
     * @dataProvider providerTestIterator
     */
    public function testIterator(HandlerCollection $collection, $filter, $expected)
    {
        $iterator = new HandlerFilterIterator($collection->getIterator(), $filter);

        if (empty($expected)) {
            $this->assertEmpty(iterator_to_array($iterator));
        } else {
            foreach ($iterator as $key => $item) {
                $this->assertArrayHasKey($key, $expected);
                $this->assertInstanceOf($expected[$key], $item);
            }
        }
    }

    public function providerTestIterator()
    {
        return array(
            // no filter
            array(
                $this->getCollectionMock(array(
                    new AnalysisHandler(),
                    new MappingHandler(),
                )),
                null,
                array(
                    'AnalysisHandler' => $this->getHandlerInterface('Analysis'),
                    'MappingHandler' => $this->getHandlerInterface('Mapping'),
                ),
            ),
            // filter, no hits
            array(
                $this->getCollectionMock(array(
                    new AnalysisHandler(),
                )),
                'Mapping',
                array(),
            ),
            // filter, one hit
            array(
                $this->getCollectionMock(array(
                    new AnalysisHandler(),
                    new MappingHandler(),
                )),
                'Mapping',
                array(
                    'MappingHandler' => $this->getHandlerInterface('Mapping'),
                ),
            ),
            // filter, multiple hits
            array(
                $this->getCollectionMock(array(
                    new AnalysisHandler(),
                    new MappingHandler(),
                    new AnalysisMappingHandler(),
                )),
                'Analysis',
                array(
                    'AnalysisHandler' => $this->getHandlerInterface('Analysis'),
                    'AnalysisMappingHandler' => $this->getHandlerInterface('Analysis'),
                ),
            ),
        );
    }

    /**
     * Get fully qualified interfacename
     * @param string $interface
     * @return string
     */
    protected function getHandlerInterface($interface)
    {
        return sprintf(
            'Sugarcrm\Sugarcrm\Elasticsearch\Provider\GlobalSearch\Handler\%sHandlerInterface',
            $interface
        );
    }

    /**
     * Get GlobalSearch provider mock
     * @return GlobalSearch
     */
    protected function getProviderMock()
    {
        return $this->getMockBuilder('Sugarcrm\Sugarcrm\Elasticsearch\Provider\GlobalSearch\GlobalSearch')
            ->disableOriginalConstructor()
            ->setMethods(array())
            ->getMock();
    }

    /**
     * Create HandlerCollection mock
     * @param array $handlers
     * @return HandlerCollection
     */
    protected function getCollectionMock(array $handlers)
    {
        $collection = new HandlerCollection($this->getProviderMock());
        foreach ($handlers as $handler) {
            $collection->addHandler($handler);
        }
        return $collection;
    }
}
