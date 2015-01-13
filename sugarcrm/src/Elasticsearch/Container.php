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

namespace Sugarcrm\Sugarcrm\Elasticsearch;

use Sugarcrm\Sugarcrm\SearchEngine\MetaDataHelper;
use Sugarcrm\Sugarcrm\Elasticsearch\Adapter\Client;
use Sugarcrm\Sugarcrm\Elasticsearch\Index\IndexPool;
use Sugarcrm\Sugarcrm\Elasticsearch\Index\IndexManager;
use Sugarcrm\Sugarcrm\Elasticsearch\Mapping\MappingManager;
use Sugarcrm\Sugarcrm\Elasticsearch\Indexer\Indexer;
use Sugarcrm\Sugarcrm\Elasticsearch\Exception\ProviderException;
use Sugarcrm\Sugarcrm\Elasticsearch\Queue\QueueManager;

/**
 *
 * Elasticsearch service container
 *
 * List of properties exposed through `$this->__get()`
 *
 * @property-read Logger logger
 * @property-read MetaDataHelper metaDataHelper
 * @property-read QueueManager queueManager
 * @property-read Client client
 * @property-read IndexPool indexPool
 * @property-read IndexManager indexManager
 * @property-read MappingManager mappingManager
 * @property-read Indexer indexer
 *
 */
class Container
{
    /**
     * @var \Sugarcrm\Sugarcrm\Elasticsearch\Logger
     */
    private $logger;

    /**
     * @var \Sugarcrm\Sugarcrm\SearchEngine\MetaDataHelper
     */
    private $metaDataHelper;

    /**
     * @var \Sugarcrm\Sugarcrm\Elasticsearch\Queue\QueueManager
     */
    private $queueManager;

    /**
     * @var \Sugarcrm\Sugarcrm\Elasticsearch\Adapter\Client
     */
    private $client;

    /**
     * @var \Sugarcrm\Sugarcrm\Elasticsearch\Index\IndexPool
     */
    private $indexPool;

    /**
     * @var \Sugarcrm\Sugarcrm\Elasticsearch\Index\IndexManager
     */
    private $indexManager;

    /**
     * @var \Sugarcrm\Sugarcrm\Elasticsearch\Mapping\MappingManager
     */
    private $mappingManager;

    /**
     * @var \Sugarcrm\Sugarcrm\Elasticsearch\Indexer\Indexer
     */
    private $indexer;

    /**
     * Registered providers (name/class mapping)
     * @var array
     */
    private $providers = array();

    /**
     * Configuration parameters
     * @var array
     */
    private $config = array(
        'engine' => array(),
        'global' => array(),
    );

    /**
     * To instantiate this container self::create() should be used instead
     * of using this ctor directly unless you know what your are doing. This
     * ctor is not made private for testing purposes and edge cases where
     * its desirable to be able to instantiate this container directly.
     *
     * @param array $config Optional configuration settings
     */
    public function __construct(array $config = array())
    {
        $this->config = $config;
        $this->registerProviders();
    }

    /**
     * Create container object
     * @return \Sugarcrm\Sugarcrm\Elasticsearch\Container
     */
    public static function create()
    {
        $class = \SugarAutoLoader::customClass('Sugarcrm\\Sugarcrm\\Elasticsearch\\Container');
        return new $class();
    }

    /**
     * Stock providers, can be overriden in custom class implementation.
     */
    public function registerProviders()
    {
        $this->registerProvider('GlobalSearch');
    }

    /**
     * Set configuration parameters
     * @param array $config
     */
    public function setConfig($key, array $config)
    {
        $this->config[$key] = $config;
    }

    /**
     * Get configuration parameters
     * @param string $key Configuration key
     * @param array $default Default array if not found
     * @return array
     */
    public function getConfig($key, array $default = array())
    {
        if (isset($this->config[$key])) {
            return $this->config[$key];
        }
        return $default;
    }

    /**
     * Overload for container resources
     * @param string $resource
     */
    public function __get($resource)
    {
        // Return the resource if already initialized
        if (!empty($this->$resource)) {
            return $this->$resource;
        }

        // Lazy load resources when accessed
        $init = 'init' . ucfirst($resource);
        if (property_exists($this, $resource) && method_exists($this, $init)) {
            $this->$init();
            return $this->$resource;
        }
    }

    /**
     * Initialize \Sugarcrm\Sugarcrm\Elasticsearch\Logger
     */
    private function initLogger()
    {
        $this->logger = new Logger(\LoggerManager::getLogger());
    }

    /**
     * Initialize \Sugarcrm\Sugarcrm\SearchEngine\MetaDataHelper
     */
    private function initMetaDataHelper()
    {
        $this->metaDataHelper = new MetaDataHelper();
    }

    /**
     * Initialize \Sugarcrm\Sugarcrm\SearchEngine\Queue\QueueManager
     */
    private function initQueueManager()
    {
        $this->queueManager = new QueueManager($this->getConfig('global'), $this);
    }

    /**
     * Initialize \Sugarcrm\Sugarcrm\Elasticsearch\Adapter\Client
     */
    private function initClient()
    {
        $this->initLogger();
        $this->client = new Client($this->getConfig('engine'), $this->logger);
    }

    /**
     * Initialize \Sugarcrm\Sugarcrm\Elasticsearch\Index\IndexPool
     */
    private function initIndexPool()
    {
        $prefix = \SugarConfig::getInstance()->get('unique_key', 'sugarcrm');
        $config = \SugarArray::staticGet($this->getConfig('engine'), 'index_strategy', array());
        $this->indexPool = new IndexPool($prefix, $config, $this);
    }

    /**
     * Initialize \Sugarcrm\Sugarcrm\Elasticsearch\Index\IndexManager
     */
    private function initIndexManager()
    {
        $config = \SugarArray::staticGet($this->getConfig('engine'), 'index_settings', array());
        $this->indexManager = new IndexManager($config, $this);
    }

    /**
     * Initialize \Sugarcrm\Sugarcrm\Elasticsearch\Mapping\MappingManager
     */
    private function initMappingManager()
    {
        $this->mappingManager = new MappingManager();
    }

    /**
     * Initialize \Sugarcrm\Sugarcrm\Elasticsearch\Indexer\Indexer
     */
    private function initIndexer()
    {
        $this->indexer = new Indexer($this->getConfig('global'), $this);
    }

    /**
     * Register a new provider on the stack
     * @param string $name Provider name
     */
    public function registerProvider($name)
    {
        $this->providers[$name] = true;
    }

    /**
     * Unregister a provider
     * @param string $name Provider name
     */
    public function unregisterProvider($name)
    {
        if (isset($this->providers[$name])) {
            unset($this->providers[$name]);
        }
    }

    /**
     * Return list of registered providers
     * @return array
     */
    public function getRegisteredProviders()
    {
        return array_keys($this->providers);
    }

    /**
     * Check if given provider is available
     * @param string $name Provider name
     * @return boolean
     */
    public function isProviderAvailable($name)
    {
        return isset($this->providers[$name]);
    }

    /**
     * Create new provider object
     * @param string $name Provider name
     * @throws \Sugarcrm\Sugarcrm\Elasticsearch\Exception\ProviderException
     * @return \Sugarcrm\Sugarcrm\Elasticsearch\Provider\AbstractProvider
     */
    public function getProvider($name)
    {
        if (!isset($this->providers[$name])) {
            throw new ProviderException("Unknown Elastic provider '{$name}'");
        }

        $providerClassName = \SugarAutoLoader::customClass(
            sprintf('\\Sugarcrm\\Sugarcrm\\Elasticsearch\\Provider\\%s\\%s', $name, $name)
        );

        if (class_exists($providerClassName)) {
            return new $providerClassName($this);
        }

        throw new ProviderException("Invalid provider class '{$providerClassName}' for '{$name}'");
    }
}
