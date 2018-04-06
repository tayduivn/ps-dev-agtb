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

use Sugarcrm\Sugarcrm\Cache;
use Sugarcrm\Sugarcrm\DependencyInjection\Container;

/**
 * Adapter which allows using new cache API as the old API backend
 *
 * @internal Used only for forward compatibility with the new cache API
 *
 * @codingStandardsIgnoreFile due to the inherited method names
 */
class SugarCacheForwardCompatible extends SugarCacheAbstract
{
    /**
     * @var Cache
     */
    private $backend;

    /**
     * @var string|null
     */
    private $disableParameter;

    public function __construct(string $backendService, int $priority, ?string $disableParameter)
    {
        parent::__construct();

        try {
            $this->backend = Container::getInstance()->get($backendService);
        } catch (RuntimeException $e) {
        }

        $this->_priority = $priority;
        $this->disableParameter = $disableParameter;
    }

    /**
     * {@inheritDoc}
     */
    public function useBackend()
    {
        if (!$this->backend) {
            return false;
        }

        if (!parent::useBackend()) {
            return false;
        }

        if ($this->disableParameter && !empty($GLOBALS['sugar_config'][$this->disableParameter])) {
            return false;
        }

        return true;
    }

    /**
     * {@inheritDoc}
     */
    protected function _setExternal($key, $value)
    {
        $this->backend->store($key, $value, $this->_expireTimeout);
    }

    /**
     * {@inheritDoc}
     */
    protected function _getExternal($key)
    {
        $value = $this->backend->fetch($key, $success);

        if (!$success) {
            return null;
        }

        return $value;
    }

    /**
     * {@inheritDoc}
     */
    protected function _clearExternal($key)
    {
        $this->backend->delete($key);
    }

    /**
     * {@inheritDoc}
     */
    protected function _resetExternal()
    {
        $this->backend->clear();
    }
}
