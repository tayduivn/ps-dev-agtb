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
use Sugarcrm\Sugarcrm\Cache;

/**
 * WinCache implementation of the cache backend
 *
 * @link http://pecl.php.net/package/WinCache
 */
final class WinCache implements Cache
{
    /**
     * @codeCoverageIgnore
     */
    public function __construct()
    {
        if (!extension_loaded('wincache')) {
            throw new RuntimeException('WinCache extension is not loaded');
        }

        if (!ini_get('wincache.ucenabled')) {
            throw new RuntimeException('WinCache extension is disabled');
        }

        if (php_sapi_name() === 'cli' && !ini_get('wincache.enablecli')) {
            throw new RuntimeException('WinCache extension is disabled for CLI');
        }
    }

    /**
     * {@inheritDoc}
     */
    public function fetch(string $key, ?bool &$success = null)
    {
        $value = wincache_ucache_get($key, $success);

        if ($success) {
            return $value;
        }

        return null;
    }

    /**
     * {@inheritDoc}
     */
    public function store(string $key, $value, ?int $ttl = null) : void
    {
        wincache_ucache_set($key, $value, $ttl ?? 0);
    }

    /**
     * {@inheritDoc}
     */
    public function delete(string $key) : void
    {
        wincache_ucache_delete($key);
    }

    /**
     * {@inheritDoc}
     */
    public function clear() : void
    {
        wincache_ucache_clear();
    }
}
