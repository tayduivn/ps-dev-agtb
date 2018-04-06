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

namespace Sugarcrm\Sugarcrm;

/**
 * Cache interface
 */
interface Cache
{
    /**
     * Fetches cached value by key
     *
     * @param string $key
     * @param bool $success
     * @return mixed
     */
    public function fetch(string $key, ?bool &$success = null);

    /**
     * Stores value
     *
     * @param string $key
     * @param $value
     * @param null $ttl Time to live in seconds
     */
    public function store(string $key, $value, int $ttl = 300) : void;

    /**
     * Deletes value with the given key
     *
     * @param string $key
     */
    public function delete(string $key) : void;

    /**
     * Clears all values
     */
    public function clear() : void;
}
