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

use Sugarcrm\Sugarcrm\Cache;

/**
 * In-memory cache middleware
 */
final class InMemory implements Cache
{
    /**
     * Cached data
     *
     * @var mixed[]
     */
    private $data = [];

    /**
     * {@inheritDoc}
     */
    public function fetch(string $key, ?bool &$success = null)
    {
        if (array_key_exists($key, $this->data)) {
            $success = true;

            return $this->data[$key];
        }

        $success = false;

        return null;
    }

    /**
     * {@inheritDoc}
     */
    public function store(string $key, $value, ?int $ttl = null) : void
    {
        $this->data[$key] = $value;
    }

    /**
     * {@inheritDoc}
     */
    public function delete(string $key) : void
    {
        unset($this->data[$key]);
    }

    /**
     * {@inheritDoc}
     */
    public function clear() : void
    {
        $this->data = [];
    }
}
