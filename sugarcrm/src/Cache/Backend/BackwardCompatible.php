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

use RuntimeException;
use SugarCache;
use SugarCacheAbstract;
use Sugarcrm\Sugarcrm\Cache;

/**
 * Backward compatible cache adapter
 */
final class BackwardCompatible implements Cache
{
    /**
     * Cached backend
     *
     * @var SugarCacheAbstract
     */
    private $backend;

    /**
     * @codeCoverageIgnore
     */
    public function __construct(SugarCacheAbstract $backend)
    {
        if (!$backend->useBackend()) {
            throw new RuntimeException('Backend unavailable');
        }

        $this->backend = $backend;
    }

    /**
     * {@inheritDoc}
     */
    public function fetch(string $key, ?bool &$success = null)
    {
        $value = $this->backend->get($key);
        $success = $value !== null;

        if ($value === SugarCache::EXTERNAL_CACHE_NULL_VALUE) {
            $value = null;
        }

        return $value;
    }

    /**
     * {@inheritDoc}
     */
    public function store(string $key, $value, ?int $ttl = null) : void
    {
        $this->backend->set($key, $value, $ttl);
    }

    /**
     * {@inheritDoc}
     */
    public function delete(string $key) : void
    {
        $this->backend->__unset($key);
    }

    /**
     * {@inheritDoc}
     */
    public function clear() : void
    {
        $this->backend->flush();
    }
}
