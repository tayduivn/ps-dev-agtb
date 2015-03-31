<?php
/*
 * Your installation or use of this SugarCRM file is subject to the applicable
 * terms available at
 * http://support.sugarcrm.com/06_Customer_Center/10_Master_Subscription_Agreements/.
 * If you do not agree to all of the applicable terms or do not have the
 * authority to bind the entity as an authorized representative, then do not
 * install or use this SugarCRM file.
 *
 * Copyright (C) SugarCRM Inc. All rights reserved.
 */

namespace Sugarcrm\Sugarcrm\Elasticsearch\Index\Strategy;

/**
 *
 * Abstract Strategy
 *
 */
abstract class AbstractStrategy implements StrategyInterface
{
    /**
     * @var array Strategy configuration parameters
     */
    protected $config = array();

    /**
     * Ctor
     * @param array $config
     */
    public function __construct(array $config = array())
    {
        $this->config = $config;
    }

    /**
     * Get module specific configuration
     * @param string $module
     * @param string $key Config key to retrieve
     * @param mixed $default Default value if config key is not found
     * @return mixed
     */
    protected function getConfig($module, $key, $default = null)
    {
        return (isset($this->config[$module][$key])) ? $this->config[$module][$key] : $default;
    }
}
