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

namespace Sugarcrm\Sugarcrm\Elasticsearch\Indexer;

use Sugarcrm\Sugarcrm\Elasticsearch\Container;
use Sugarcrm\Sugarcrm\Elasticsearch\Provider\ProviderCollection;
use Sugarcrm\Sugarcrm\Elasticsearch\Adapter\Document;

/**
 *
 * The Indexer is responsible to handle all data synchronisation into the
 * Elasticsearch backend. It will take care of populating the data properly
 * and using bulk indexers to send data.
 *
 */
class Indexer
{
    /**
     * @var \Sugarcrm\Sugarcrm\Elasticsearch\Container
     */
    protected $container;

    /**
     * @var \Sugarcrm\Sugarcrm\Elasticsearch\Indexer\BulkHandler
     */
    protected $bulkHandler;

    /**
     * @var boolean Asynchronous index mode
     */
    protected $async = false;

    /**
     * @var boolean Disable indexer
     */
    protected $disabled = false;

    /**
     * Ctor
     * @param array $config
     */
    public function __construct(array $config, Container $container)
    {
        if (!empty($config['force_async_index'])) {
            $this->async = $config['force_async_index'];
        }

        $this->container = $container;
    }

    /**
     * Index SugarBean into Elastichsearch. By default we send all beans
     * through the bulk (batch) handler to minimize the amount of updates
     * to the Elasticsearch backend. On the end of the page load the in
     * memory queue will be flushed.
     *
     * @param \SugarBean $bean
     * @param boolean $batch
     */
    public function indexBean(\SugarBean $bean, $batch = true)
    {
        // Skip indexing if we are disabled
        if ($this->disabled) {
            return false;
        }

        // Send to database queue when Elastic is unavailable
        if (!$this->container->client->isAvailable() || $this->async) {
            $this->container->queueManager->queueBean($bean);
        }

        // Skip bean if module not enabled.
        if (!$this->container->metaDataHelper->isModuleEnabled($bean->module_name)) {
            return;
        }

        $this->indexDocument($this->getDocumentFromBean($bean), $batch);
    }

    /**
     * Index Elastica Document into Elasticsearch. By default we send all
     * documents through the bulk (batch) handler to minimize the amount of
     * updates to the Elasticsearch backend. On the end of the page load the
     * in memory queue will be flushed.
     *
     * @param \Sugarcrm\Sugarcrm\Elasticsearch\Adapter\Document $document
     * @param string $batch
     */
    public function indexDocument(Document $document, $batch = true)
    {
        // Skip indexing if we are disabled
        if ($this->disabled) {
            return false;
        }

        // Safeguard avoid sending documents without data
        if (!$document->hasData()) {
            return;
        }

        // Send to database queue when Elastic is unavailable
        if (!$this->container->client->isAvailable() || $this->async) {
            $this->container->queueManager->queueDocument($document);
        }

        if ($batch) {
            // Use in memory queue
            $this->getBulkHandler()->batchDocument($document);
        } else {
            // Send it out immediately
            $bulk = $this->newBulkHandler();
            $bulk->batchDocument($document);
            $bulk->finishBatch();
        }
    }

    /**
     * Enable/disable asynchronous indexing
     * @param boolean $toggle
     */
    public function setForceAsyncIndex($toggle)
    {
        $this->async = $toggle;
    }

    /**
     * Enable/disable indexing
     * @param boolean $toggle
     */
    public function setDisable($toggle)
    {
        $this->disabled = $toggle;
    }

    /**
     * Lazy load local bulk handler
     * @return \Sugarcrm\Sugarcrm\Elasticsearch\Indexer\BulkHandler
     */
    protected function getBulkHandler()
    {
        if (empty($this->bulkHandler)) {
            $this->bulkHandler = $this->newBulkHandler();
        }
        return $this->bulkHandler;
    }

    /**
     * Create new bulk handler object
     * @return \Sugarcrm\Sugarcrm\Elasticsearch\Indexer\BulkHandler
     */
    protected function newBulkHandler()
    {
        return new BulkHandler($this->container);
    }

    /**
     * Get index object for given bean.
     * @param \SugarBean $bean
     * @return \Sugarcrm\Sugarcrm\Elasticsearch\Adapter\Index
     */
    protected function getWriteIndex(\SugarBean $bean)
    {
        $context = array('bean' => $bean);
        return $this->container->indexPool->getWriteIndex($bean->module_name, $context);
    }

    /**
     * Get field list to be indexed for given module.
     * @param string $module
     * @return array
     */
    public function getBeanIndexFields($module)
    {
        $fields = array();
        $providers = new ProviderCollection($this->container, $this->container->getRegisteredProviders());
        foreach ($providers as $provider) {
            /* @var $provider ProviderInterace */
            $fields = array_merge($fields, $provider->getBeanIndexFields($module));
        }

        // TODO: needs to be extended with additional fields from provider
        // TODO: add visibility
        return $fields;
    }

    /**
     * Get document for given bean. The returned document will have the
     * the operation action (create/update/delete) and target index set.
     * @param \SugarBean $bean
     * @return \Sugarcrm\Sugarcrm\Elasticsearch\Adapter\Document
     */
    protected function getDocumentFromBean(\SugarBean $bean)
    {
        $module = $bean->module_name;
        $index = $this->getWriteIndex($bean);
        $document = new Document($bean->id, array(), $bean->module_name, $index);

        // We dont need to send the whole data when deleting a record
        if ($bean->deleted) {
            $document->setOpType(\Elastica\Bulk\Action::OP_TYPE_DELETE);
            return $document;
        }

        $fields = $this->getBeanIndexFields($module);
        $data = array();
        foreach ($fields as $field => $type) {
            if (isset($bean->$field)) {
                $data[$field] = $bean->$field;
            }
        }

        $document->setId($bean->id);
        $document->setData($data);
        return $document;
    }
}
