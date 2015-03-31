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
 * Use a dedicated single index for given module. The index name defaults
 * to the module name but can be overriden through `$sugar_config`.
 *
 * Example configuration:
 *
 * $sugar_config['full_text_engine']['Elastic']['index_strategy']['Accounts'] = array(
 *      'strategy' => 'Single',
 *      'index' => 'index_name_goes_here',
 * );
 *
 */
class SingleStrategy extends AbstractStrategy
{
    /**
     * {@inheritdoc}
     */
    public function getManagedIndices($module)
    {
        return array($this->getSingleIndex($module));
    }

    /**
     * {@inheritdoc}
     */
    public function getReadIndices($module, array $context = array())
    {
        return array($this->getSingleIndex($module));
    }
    /**
     * {@inheritdoc}
     */
    public function getWriteIndex($module, array $context = array())
    {
        return $this->getSingleIndex($module);
    }

    /**
     * Wrapper
     * @param string $module
     * @return array
     */
    private function getSingleIndex($module)
    {
        return $this->getConfig($module, 'index', $module);
    }
}
