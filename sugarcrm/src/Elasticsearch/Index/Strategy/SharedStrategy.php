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
 * Use a shared index for given module. Default index names can be overriden
 * through `$sugar_config`.
 *
 * Example configuration:
 *
 * $sugar_config['full_text_engine']['Elastic']['index_strategy']['Accounts'] = array(
 *      'strategy' => 'Shared',
 *      'index' => 'shared_index_name_goes_here',
 * );
 *
 */
class SharedStrategy extends AbstractStrategy
{
    const DEFAULT_SHARED = 'shared';

    /**
     * {@inheritdoc}
     */
    public function getManagedIndices($module)
    {
        return array($this->getSharedIndex($module));
    }

    /**
     * {@inheritdoc}
     */
    public function getReadIndices($module, array $context = array())
    {
        return array($this->getSharedIndex($module));
    }
    /**
     * {@inheritdoc}
     */
    public function getWriteIndex($module, array $context = array())
    {
        return $this->getSharedIndex($module);
    }

    /**
     * Wrapper
     * @param string $module
     * @return array
     */
    private function getSharedIndex($module)
    {
        return $this->getConfig($module, 'index', self::DEFAULT_SHARED);
    }
}
