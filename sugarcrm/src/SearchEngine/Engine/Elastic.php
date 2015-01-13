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

/**
 *
 * Elasticsearch engine
 *
 */
class Elastic implements EngineInterface
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

    /**
     * {@inheritDoc}
     * @see \Sugarcrm\Sugarcrm\SearchEngine\Engine\EngineInterface::setEngineConfig()
     */
    public function setEngineConfig(array $config)
    {
        $this->container->setConfig('engine', $config);
    }

    /**
     * {@inheritDoc}
     * @see \Sugarcrm\Sugarcrm\SearchEngine\Engine\EngineInterface::setGlobalConfig()
     */
    public function setGlobalConfig(array $config)
    {
        $this->container->setConfig('global', $config);
    }

    public function isAvailable($force = false)
    {
        return $this->container->client->isAvailable($force);
    }

    /**
     * {@inheritDoc}
     * @see SearchEngineInterface::scheduleIndexing()
     */
    public function scheduleIndexing(array $modules = array(), $clearData = false)
    {
        return $this->container->indexManager->scheduleIndexing($modules, $clearData);
    }

    /**
     * Get Elastic service container
     * @return \Sugarcrm\Sugarcrm\Elasticsearch\Container
     */
    public function getContainer()
    {
        return $this->container;
    }
}
