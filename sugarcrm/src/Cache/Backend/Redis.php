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

use Redis as Client;
use RedisException;
use RuntimeException;
use Sugarcrm\Sugarcrm\Cache;

/**
 * Redis implementation of the cache backend
 *
 * @link http://pecl.php.net/package/redis
 */
final class Redis implements Cache
{
    private $client;

    /**
     * @codeCoverageIgnore
     */
    public function __construct(?string $host, ?int $port = null)
    {
        if (!extension_loaded('redis')) {
            throw new RuntimeException('Redis extension is not loaded');
        }

        $client = new Client();

        try {
            $client->connect($host ?? '127.0.0.1', $port ?? 6379);
        } catch (RedisException $e) {
            throw new RuntimeException('Unable to connect to redis server', 0, $e);
        }

        $this->client = $client;
    }

    /**
     * {@inheritDoc}
     */
    public function fetch(string $key, ?bool &$success = null)
    {
        $value = $this->client->get($key);

        if ($value === false) {
            $success = false;

            return null;
        }

        $success = true;

        return unserialize($value);
    }

    /**
     * {@inheritDoc}
     */
    public function store(string $key, $value, ?int $ttl = null) : void
    {
        $this->client->set($key, serialize($value));

        if ($ttl !== null) {
            $this->client->expire($key, $ttl);
        }
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
        $this->client->flushAll();
    }
}
