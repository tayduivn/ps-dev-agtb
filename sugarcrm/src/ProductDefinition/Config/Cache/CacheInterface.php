<?php
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

namespace Sugarcrm\Sugarcrm\ProductDefinition\Config\Cache;

/**
 * Product definition cache interface
 */
interface CacheInterface
{
    /**
     * get current definition from cache
     * @return string|null
     */
    public function getCurrentDefinition():? string;

    /**
     * get previous definition from cache
     * @return string|null
     */
    public function getPreviousDefinition():? string;

    /**
     * set definition to cache
     * @param string $data
     * @return void
     */
    public function set(string $data);
}
