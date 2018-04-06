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
 * APCu implementation of the cache backend
 *
 * @link http://pecl.php.net/package/APCu
 */
final class APCu implements Cache
{
    /**
     * @codeCoverageIgnore
     */
    public function __construct()
    {
        if (!extension_loaded('apcu')) {
            throw new RuntimeException('APCu extension is not loaded');
        }

        if (!ini_get('apc.enabled')) {
            throw new RuntimeException('APCu extension is disabled');
        }

        if (php_sapi_name() === 'cli' && !ini_get('apc.enable_cli')) {
            throw new RuntimeException('APCu extension is disabled for CLI');
        }
    }

    /**
     * {@inheritDoc}
     */
    public function fetch(string $key, ?bool &$success = null)
    {
        $value = apcu_fetch($key, $success);

        if (!$success) {
            return null;
        }

        return $value;
    }

    /**
     * {@inheritDoc}
     */
    public function store(string $key, $value, ?int $ttl = null) : void
    {
        apcu_store($key, $value, $ttl ?? 0);
    }

    /**
     * {@inheritDoc}
     */
    public function delete(string $key) : void
    {
        apcu_delete($key);
    }

    /**
     * {@inheritDoc}
     */
    public function clear() : void
    {
        apcu_clear_cache();
    }
}
