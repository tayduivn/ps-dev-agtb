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

namespace Sugarcrm\Sugarcrm\SearchEngine\Engine;

use Sugarcrm\Sugarcrm\Elasticsearch\Container;
use Sugarcrm\Sugarcrm\SearchEngine\Capability\GlobalSearch\GlobalSearchInterface;

/**
 *
 * Elasticsearch engine
 *
 */
class Elastic implements EngineInterface, GlobalSearchInterface
{
    /**
     * @var \Sugarcrm\Sugarcrm\Elasticsearch\Container
     */
    protected $container;

    /**
     * @param \Sugarcrm\Sugarcrm\Elasticsearch\Container $container
     */
    public function __construct(Container $container = null)
    {
        $this->container = $container ?: Container::create();
    }

    //// BASE INTERFACE ////

    /**
     * {@inheritDoc}
     */
    public function setEngineConfig(array $config)
    {
        $this->container->setConfig('engine', $config);
    }

    /**
     * {@inheritDoc}
     */
    public function getEngineConfig()
    {
        return $this->container->getConfig('engine');
    }

    /**
     * {@inheritDoc}
     */
    public function setGlobalConfig(array $config)
    {
        $this->container->setConfig('global', $config);
    }

    /**
     * {@inheritDoc}
     */
    public function getGlobalConfig()
    {
        return $this->container->getConfig('global');
    }

    /**
     * {@inheritDoc}
     */
    public function isAvailable($force = false)
    {
        return $this->container->client->isAvailable($force);
    }

    /**
     * {@inheritDoc}
     */
    public function verifyConnectivity($updateAvailability = true)
    {
        return $this->container->client->verifyConnectivity($updateAvailability);
    }

    /**
     * {@inheritDoc}
     */
    public function scheduleIndexing(array $modules = array(), $clearData = false)
    {
        return $this->container->indexManager->scheduleIndexing($modules, $clearData);
    }

    /**
     * {@inheritDoc}
     */
    public function indexBean(\SugarBean $bean, array $options = array())
    {
        $this->container->indexer->indexBean($bean);
    }

    /**
     * {@inheritDoc}
     */
    public function runFullReindex($clearData = false)
    {
        $this->scheduleIndexing(array(), $clearData);
        $this->container->queueManager->consumeQueue();
    }

    /**
     * {@inheritDoc}
     */
    public function setForceAsyncIndex($toggle)
    {
        $this->container->indexer->setForceAsyncIndex($toggle);
    }

    /**
     * {@inheritDoc}
     */
    public function setDisableIndexing($toggle)
    {
        $this->container->indexer->setDisable($toggle);
    }

    //// GLOBALSEARCH CAPABILITY /////

    /**
     * {@inheritDoc}
     */
    public function search()
    {
        return $this->gsProvider()->search();
    }

    /**
     * {@inheritDoc}
     */
    public function term($term)
    {
        return $this->gsProvider()->term($term);
    }

    /**
     * {@inheritDoc}
     */
    public function from(array $modules = array())
    {
        return $this->gsProvider()->from($modules);
    }

    /**
     * {@inheritDoc}
     */
    public function limit($limit)
    {
        return $this->gsProvider()->limit($limit);
    }

    /**
     * {@inheritDoc}
     */
    public function offset($offset)
    {
        return $this->gsProvider()->offset($offset);
    }

    /**
     * {@inheritDoc}
     */
    public function highlighter($toggle)
    {
        return $this->gsProvider()->highlighter($toggle);
    }

    /**
     * {@inheritDoc}
     */
    public function fieldBoost($toggle)
    {
        return $this->gsProvider()->fieldBoost($toggle);
    }

    //// ELASTIC ENGINE SPECIFIC ////

    /**
     * Get Elastic service container
     * @return \Sugarcrm\Sugarcrm\Elasticsearch\Container
     */
    public function getContainer()
    {
        return $this->container;
    }

    /**
     * @return \Sugarcrm\Sugarcrm\Elasticsearch\Provider\GlobalSearch\GlobalSearch
     */
    protected function gsProvider()
    {
        return $this->container->getProvider('GlobalSearch');
    }
}
