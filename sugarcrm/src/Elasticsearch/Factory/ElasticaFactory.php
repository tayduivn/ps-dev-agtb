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

namespace Sugarcrm\Sugarcrm\Elasticsearch\Factory;

/**
 *
 * Factory class to instantiate corresponding elastica objects
 *
 */
class ElasticaFactory
{
    /**
     * const for es version
     */
    const ES5X = 'es5x';

    /**
     * elastica class pathes for 5.x
     * @var array
     */
    protected static function getClassMapping()
    {
        return array(
            self::ES5X => array(
                'Term' => '\Elastica\Query\Term',
                'Terms' => '\Elastica\Query\Terms',
                'Bool' => '\Elastica\Query\BoolQuery',
                'Range' => '\Elastica\Query\Range',
                'AggRange' => '\Elastica\Aggregation\Range',
                'AggTerms' => 'Elastica\Aggregation\Terms',
                'AggFilter' => '\Elastica\Aggregation\Filter',
            ),
        );
    }

    /**
     * to create an instance of a request className
     *
     * @param string $className, the short class name, for instance: 'Term', 'Terms'
     * @return object, full class name
     * @throws \Exception
     */
    public static function createNewInstance($className) {
        $fullClassName = self::getFullClassName($className);
        $reflector = new \ReflectionClass($fullClassName);
        if ($reflector->hasMethod('__construct')) {
            $args = func_get_args();
            // pop up the first arugment
            array_shift($args);
            return $reflector->newInstanceArgs($args);
        } else {
            // default ctor
            return new $fullClassName();
        }
    }

    /**
     * to get the corresponding full path of the class name
     * @param $className
     * @return string
     * @throws \Exception
     */
    protected static function getFullClassName($className)
    {
        $classMapping = self::getClassMapping();
        if (isset($classMapping[self::ES5X][$className])) {
            $fullClassName = $classMapping[self::ES5X][$className];
        } else {
            throw new \Exception("no valid class mapping for $className");
        }

        return $fullClassName;
    }
}
