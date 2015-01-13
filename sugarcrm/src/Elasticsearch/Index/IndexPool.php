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

namespace Sugarcrm\Sugarcrm\Elasticsearch\Index;

use Sugarcrm\Sugarcrm\Elasticsearch\Mapping\MappingCollection;
use Sugarcrm\Sugarcrm\Elasticsearch\Mapping\Mapping;
use Sugarcrm\Sugarcrm\Elasticsearch\Adapter\Index;
use Sugarcrm\Sugarcrm\Elasticsearch\Exception\IndexPoolStrategyException;
use Sugarcrm\Sugarcrm\Elasticsearch\Container;
use Sugarcrm\Sugarcrm\Elasticsearch\Adapter\Client;

/**
 *
 * Wrapper class to manage different indices. Every module can have one or
 * more indices assigned. The logic resides in the strategy classes. This
 * class manages the link between modules and indices. All index objects
 * needed have to be requested through this class.
 *
 */
class IndexPool
{
    const DEFAULT_STRATEGY = 'Shared';

    /**
     * TODO make the usage of the prefix configurable
     * @var string Prefix for every index
     */
    protected $prefix;

    /**
     * @var array Configuration parameters
     */
    protected $config;

    /**
     * @var \Sugarcrm\Sugarcrm\Elasticsearch\Container
     */
    protected $container;

    /**
     * @var \Sugarcrm\Sugarcrm\Elasticsearch\Index\Strategy\StrategyInterface[]
     */
    protected $strategies = array();

    /**
     * @param string $prefix Index prefix
     * @param array $config
     */
    public function __construct($prefix, array $config, Container $container)
    {
        $this->prefix = $prefix;
        $this->config = $config;
        $this->container = $container;
    }

    /**
     * Build index collection for given mapping
     * @param MappingCollection $mappings
     * @return \Sugarcrm\Sugarcrm\Elasticsearch\Index\IndexCollection
     */
    public function buildIndexCollection(MappingCollection $mappings)
    {
        $collection = new IndexCollection($this->container);

        foreach ($mappings as $mapping) {
            /* @var Mapping $mapping */
            $module = $mapping->getModule();
            $indices = $this->getStrategy($module)->getManagedIndices($module);
            $collection->addType($indices, $module);
        }

        return $collection;
    }

    /**
     * Normalize index name and add prefix. The normalized named is only
     * referenced in the underlaying \Elastica\Index objects. Index name
     * access should always be resolved against the non-prefixed format.
     *
     * @param string $name Index name
     * @return string
     */
    public function normalizeIndexName($name)
    {
        if (!empty($this->prefix)) {
            $name = $this->prefix . '_' . $name;
        }

        // only lowercase index names are allowed
        return strtolower($name);
    }

    /**
     * Get strategy object for given module
     * @param string $module Module name
     * @throws \Sugarcrm\Sugarcrm\Elasticsearch\Exception\IndexPoolStrategyException
     * @return \Sugarcrm\Sugarcrm\Elasticsearch\Index\Strategy\StrategyInterface
     */
    protected function getStrategy($module)
    {
        if (empty($this->config[$module]) || empty($this->config[$module]['strategy'])) {
            $this->config[$module] = array('strategy' => self::DEFAULT_STRATEGY);
        }

        $strategy = $this->config[$module]['strategy'];

        if (!isset($this->strategies[$strategy])) {
            $className = \SugarAutoLoader::customClass(
                sprintf('\\Sugarcrm\\Sugarcrm\\Elasticsearch\\Index\\Strategy\\%sStrategy', $strategy)
            );

            if (!class_exists($className)) {
                throw new IndexPoolStrategyException("Invalid strategy $strategy for module $module");
            }

            // create strategy object and pass index_strategy config
            $this->strategies[$strategy] = new $className($this->config);
        }

        return $this->strategies[$strategy];
    }

    /**
     * Get list of available read indices for given modules
     * @param array $modules
     * @param array $context
     * @return \Sugarcrm\Sugarcrm\Elasticsearch\Index\IndexCollection
     */
    public function getReadIndices(array $modules, array $context = array())
    {
        $collection = new IndexCollection($this->container);
        foreach ($modules as $module) {
            $indices = $this->getStrategy($module)->getReadIndices($module, $context);
            $collection->addIndices($indices);
        }
        return $collection;
    }

    /**
     * Get write index for given module. There can only be one write index at
     * any given time for a module.
     * @param string $module
     * @param array $context
     * @return \Sugarcrm\Sugarcrm\Elasticsearch\Adapter\Index
     */
    public function getWriteIndex($module, array $context = array())
    {
        $index = $this->getStrategy($module)->getWriteIndex($module, $context);
        $normalized = $this->normalizeIndexName($index);
        return $this->newIndexObject($normalized);
    }

    /**
     * Get index object
     * @param string $indexName Index name
     * @param Client $client Optional client
     * @return \Sugarcrm\Sugarcrm\Elasticsearch\Adapter\Index
     */
    protected function newIndexObject($name, Client $client = null)
    {
        $client = $client ?: $this->container->client;
        return new Index($client, $name);
    }
}
