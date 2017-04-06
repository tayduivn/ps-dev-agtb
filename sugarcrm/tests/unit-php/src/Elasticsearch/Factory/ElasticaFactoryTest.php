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

use Sugarcrm\Sugarcrm\Elasticsearch\Factory\ElasticaFactory;

/**
 *
 * @coversDefaultClass \Sugarcrm\Sugarcrm\Elasticsearch\Factory\ElasticaFactory
 *
 */
class ElasticaFactoryTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @covers ::getClassMapping
     * @covers ::createNewInstance
     * @covers ::getFullClassName
     *
     * @dataProvider providerGetClassMapping
     *
     */
    public function testGetClassMapping($name, $parameters, $expectecd)
    {
        if (!empty($parameters)) {
            $object = ElasticaFactory::createNewInstance($name, $parameters);
        } else {
            $object = ElasticaFactory::createNewInstance($name);
        }

        $this->assertInstanceOf($expectecd, $object, 'wrong instance: ' . $expectecd);
    }

    public function providerGetClassMapping()
    {
        return array(
            array(
                'Term',
                array(),
                '\Elastica\Query\Term',
            ),
            array(
                'Terms',
                array('id'),
                '\Elastica\Query\Terms',
            ),
            array(
                'Bool',
                array(),
                '\Elastica\Query\BoolQuery',
            ),
            array(
                'Range',
                array(),
                '\Elastica\Query\Range',
            ),
            array(
                'AggRange',
                array('id'),
                '\Elastica\Aggregation\Range',
            ),
            array(
                'AggTerms',
                array('id'),
                'Elastica\Aggregation\Terms',
            ),
            array(
                'AggFilter',
                array('id'),
                '\Elastica\Aggregation\Filter',
            ),
        );
    }

    /**
     * @covers ::createNewInstance
     * @covers ::getFullClassName
     * @dataProvider providerElasticaFactoryTestException
     *
     * @expectedException \Exception
     */
    public function testElasticaFactoryException($name)
    {
        ElasticaFactory::createNewInstance($name);
    }

    public function providerElasticaFactoryTestException()
    {
        return array(
            array('Wrong Name'),
            array('BoolOr'),
            array('BoolQuery'),
        );
    }
}
