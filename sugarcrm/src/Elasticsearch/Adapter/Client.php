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

namespace Sugarcrm\Sugarcrm\Elasticsearch\Adapter;

use Sugarcrm\Sugarcrm\Elasticsearch\Exception\ConnectionException;
use Sugarcrm\Sugarcrm\Elasticsearch\Logger;
use Elastica\Client as BaseClient;
use Elastica\Connection;
use Elastica\Request;
use Elastica\Response;
use Psr\Log\LogLevel;
use Psr\Log\LoggerInterface;

/**
 *
 * Adapter class for \Elastica\Client
 *
 */
class Client extends BaseClient
{
    /**
     * Administration config settings
     */
    const STATUS_CATEGORY = 'info';
    const STATUS_KEY = 'fts_down';

    /**
     * Connection status
     */
    const CONN_SUCCESS = 1;
    const CONN_ERROR = -1;
    const CONN_VERSION_NOT_SUPPORTED = -2;
    const CONN_NO_VERSION_AVAILABLE = -3;
    const CONN_FAILURE = -99;

    /**
     * Supported Elasticsearch versions
     * @var array
     */
    protected $supportedVersions = array(
        '1.3.4',
        '1.4.0',
    );

    /**
     * List of supported $sugar_config Elastic configuration options
     * @see \Elastica\Client::$_config
     */
    protected $connAllowedConfig = array(
        'host',
        'port',
        'path',
        'transport',
        'timeout',
        'curl',
        'headers',
        'url',
    );

    /**
     * @var \Sugarcrm\Sugarcrm\Elasticsearch\Logger
     */
    protected $_logger;

    /**
     * @var boolean Elasticsearch backend availability
     */
    protected $available;

    /**
     * Ctor
     * @param array $config Connection configuration from `$sugar_config`
     */
    public function __construct(array $config, LoggerInterface $logger)
    {
        $this->setLogger($logger);
        $config = $this->parseConfig($config);
        parent::__construct($config, array($this, 'onConnectionFailure'));
    }

    /**
     * Check if Elasticsearch is available. Note that the availability state
     * is based on a cached value saved in config table for 'info_fts_down'.
     * Once declared unavailable only the cron execution will be able to lift
     * it and promote the connection back to available.
     *
     * @return boolean
     */
    public function isAvailable($force = false)
    {
        if ($force) {
            $this->verifyConnectivity();
        }
        return $this->loadAvailability();
    }

    /**
     * This call will *always* try to create a connection to the Elasticsearch
     * backend to determine its availability. This should basically only be
     * called by the Indexer Job and should never be used on inline calls
     * except during install/upgrade and the search admin section.
     *
     * @return integer Connection status, see declared CONN_ constants
     */
    public function verifyConnectivity()
    {
        try {
            $result = $this->ping();
            if ($result->isOk()) {
                $data = $result->getData();
                if (empty($data['version']['number'])) {
                    $status = self::CONN_NO_VERSION_AVAILABLE;
                    $this->_logger->critical("No valid version string available");
                } else {
                    if ($this->isVersionCompatible($data['version']['number'])) {
                        $status = self::CONN_SUCCESS;
                    } else {
                        $status = self::CONN_VERSION_NOT_SUPPORTED;
                        $this->_logger->critical("Unsupported Elasticsearch version");
                    }
                }
            } else {
                $status = self::CONN_ERROR;
                $this->_logger->critical("No valid return  code");
            }
        } catch (\Exception $e) {
            $status = self::CONN_FAILURE;
            $this->_logger->critical("Elasticsearch connection failure");
        }

        $availability = ($status > 0) ? true : false;
        $this->updateAvailability($availability);

        return $status;
    }

    /**
     * Handle connection pool failures. Will be more useful when we support
     * multiple connection to Elastichsearc backend.
     * @param \Elastica\Connection $conn
     * @param \Exception $e
     * @param \Sugarcrm\Sugarcrm\Elasticsearch\Adapter\Client $client
     */
    public function onConnectionFailure(Connection $conn, \Exception $e, Client $client)
    {
        // TODO add better logging which server has connection issues ...
        $this->_logger->log(LogLevel::CRITICAL, 'Elastichsearch connection went away ...');
    }

    /**
     * Send generic ping to backend
     * @return \Elastica\Response
     */
    protected function ping()
    {
        return parent::request('', Request::GET);
    }

    /**
     * Verify if Elasticsearch version meets the supported list. In developer
     * mode only the minumum version applies.
     * @param array $version Elasticsearch version array
     * @return boolean
     */
    protected function isVersionCompatible($version)
    {
        // TODO add dev mode support
        return in_array($version, $this->supportedVersions);
    }

    /**
     * Update new persistent status
     * @param boolean $status True if available, false if not
     * @return boolean
     */
    protected function updateAvailability($status)
    {
        $this->loadAvailability();

        if ($status !== $this->available) {
            $admin = \BeanFactory::getBean('Administration');
            $admin->saveSetting(self::STATUS_CATEGORY, self::STATUS_KEY, ($status ? 0 : 1));
            $this->available = $status;
            if ($status) {
                $this->_logger->critical("Elasticsearch promoted as available");
            } else {
                $this->_logger->critical("Elasticsearch no longer available");
            }
        }
        return $status;
    }

    /**
     * Load the current availability
     * @return boolean
     */
    protected function loadAvailability()
    {
        if ($this->available === null) {
            $settings = \Administration::getSettings();
            $this->available = empty($settings->settings['info_fts_down']);
        }
        return $this->available;
    }

    /**
     * Build connection configuration from $sugar_config format
     * @param array $config `$sugar_config['full_text_search']`
     * @return array
     */
    protected function parseConfig(array $config)
    {
        // Currently only one connection is supported. This might be extended
        // in the future being able to use multiple connections and/or having
        // a split between search endpoints and index endpoints.
        $connection = array();
        foreach ($config as $k => $v) {
            if (in_array($k, $this->connAllowedConfig)) {
                $connection[$k] = $v;
            }
        }
        return array('connections' => array($connection));
    }

    /**
     * Override request taking logging into our own hands. This will be removed
     * when the logging capabilities in Elastica are cleaned up:
     * https://github.com/ruflin/Elastica/issues/712
     * https://github.com/ruflin/Elastica/issues/482
     *
     * {@inheritdoc}
     *
     * @throws \Exception
     * @throws \Sugarcrm\Sugarcrm\Elasticsearch\Exception\ConnectionException
     */
    public function request($path, $method = Request::GET, $data = array(), array $query = array())
    {
        // Enforce cached availability
        if (!$this->isAvailable()) {
            throw new \Exception('Elasticsearch not available');
        }

        try {
            $response = parent::request($path, $method, $data, $query);
            $this->_logger->onRequestSuccess($this->_lastRequest, $this->_lastResponse);
        } catch (\Exception $e) {
            $this->_logger->onRequestFailure($e);

            // On connection issues flag Elasticsearch as unavailable
            if ($e instanceof \Elastica\Exception\ConnectionException) {
                $this->updateAvailability(false);
                throw new ConnectionException($e->getMessage(), $e->getCode(), $e);
            }

            // Let is pass
            throw $e;
        }
        return $response;
    }

    /**
     * Override logging capabilities.
     * {@inheritdoc}
     */
    protected function _log($context)
    {
        return;
    }
}
