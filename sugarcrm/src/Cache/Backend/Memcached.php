<?php declare(strict_types=1);
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

namespace Sugarcrm\Sugarcrm\Cache\Backend;

use Memcached as Client;
use RuntimeException;
use Sugarcrm\Sugarcrm\Cache;

/**
 * Memcached implementation of the cache backend
 *
 * @link http://pecl.php.net/package/memcached
 */
final class Memcached implements Cache
{
    private $client;

    /**
     * @codeCoverageIgnore
     */
    public function __construct(?string $host, ?int $port = null)
    {
        if (!extension_loaded('memcached')) {
            throw new RuntimeException('memcached extension is not loaded');
        }

        $this->client = new Client();
        $this->client->addServer($host ?? '127.0.0.1', $port ?? 11211);

        // force connection to detect availability before the backend is declared available
        if ($this->client->getVersion() === false) {
            throw new RuntimeException('Unable to connect to memcached server');
        }
    }

    /**
     * {@inheritDoc}
     */
    public function fetch(string $key, ?bool &$success = null)
    {
        $value = $this->client->get($key);

        if ($this->client->getResultCode() !== Client::RES_SUCCESS) {
            $success = false;

            return null;
        }

        $success = true;

        return $value;
    }

    /**
     * {@inheritDoc}
     */
    public function store(string $key, $value, ?int $ttl = null) : void
    {
        $this->client->set($key, $value, $ttl ?? 0);
    }

    /**
     * {@inheritDoc}
     */
    public function delete(string $key) : void
    {
        $this->client->delete($key);
    }

    /**
     * {@inheritDoc}
     */
    public function clear() : void
    {
        $this->client->flush();
    }
}
