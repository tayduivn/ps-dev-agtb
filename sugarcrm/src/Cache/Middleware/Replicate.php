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
 * Replicates data between two backends
 */
final class Replicate implements Cache
{
    /**
     * Source backend
     *
     * @var Cache
     */
    private $source;

    /**
     * Replica backend
     *
     * @var Cache
     */
    private $replica;

    /**
     * @param Cache $source
     * @param Cache $replica
     */
    public function __construct(Cache $source, Cache $replica)
    {
        $this->source = $source;
        $this->replica = $replica;
    }

    /**
     * {@inheritDoc}
     */
    public function fetch(string $key, ?bool &$success = null)
    {
        $value = $this->replica->fetch($key, $success);

        if ($success) {
            return $value;
        }

        $value = $this->source->fetch($key, $success);

        if ($success) {
            $this->replica->store($key, $value/*, $ttl is currently irrelevant*/);
        }

        return $value;
    }

    /**
     * {@inheritDoc}
     */
    public function store(string $key, $value, ?int $ttl = null) : void
    {
        $this->source->store($key, $value, $ttl);
        $this->replica->store($key, $value, $ttl);
    }

    /**
     * {@inheritDoc}
     */
    public function delete(string $key) : void
    {
        $this->source->delete($key);
        $this->replica->delete($key);
    }

    /**
     * {@inheritDoc}
     */
    public function clear() : void
    {
        $this->source->clear();
        $this->replica->clear();
    }
}
