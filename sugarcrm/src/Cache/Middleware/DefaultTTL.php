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

namespace Sugarcrm\Sugarcrm\Cache\Middleware;

use Sugarcrm\Sugarcrm\Cache;

/**
 * Provides default TTL for cache entries
 */
final class DefaultTTL implements Cache
{
    /**
     * @var Cache
     */
    private $backend;

    /**
     * TTL in seconds
     *
     * @var int
     */
    private $ttl;

    public function __construct(Cache $backend, int $ttl)
    {
        $this->backend = $backend;
        $this->ttl = $ttl;
    }

    /**
     * {@inheritDoc}
     *
     * @codeCoverageIgnore
     */
    public function fetch(string $key, ?bool &$success = null)
    {
        return $this->backend->fetch($key, $success);
    }

    /**
     * {@inheritDoc}
     */
    public function store(string $key, $value, ?int $ttl = null) : void
    {
        $this->backend->store($key, $value, $ttl ?? $this->ttl);
    }

    /**
     * {@inheritDoc}
     *
     * @codeCoverageIgnore
     */
    public function delete(string $key) : void
    {
        $this->backend->delete($key);
    }

    /**
     * {@inheritDoc}
     *
     * @codeCoverageIgnore
     */
    public function clear() : void
    {
        $this->backend->clear();
    }
}
