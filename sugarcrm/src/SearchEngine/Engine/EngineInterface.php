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

namespace Sugarcrm\Sugarcrm\SearchEngine\Engine;

/**
 *
 * Engine interface
 *
 */
interface EngineInterface
{
    /**
     * Set engine configuration parameters which are defined
     * in `$sugar_config['full_text_search']['engine']`
     * @param array $config
     */
    public function setEngineConfig(array $config);

    /**
     * Set global search engine configuration parameters which
     * are defined in `$sugar_config['search_engine']`
     * @param array $config
     */
    public function setGlobalConfig(array $config);

    /**
     * Verify if search engine connection is available
     * @param boolean $force Force connection check
     * @return boolean
     */
    public function isAvailable($force = false);

    /**
     * Schedule indexing
     * @param array $modules
     * @param string $clearData
     * @return boolean
     */
    public function scheduleIndexing(array $modules = array(), $clearData = false);
}
