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

namespace Sugarcrm\Sugarcrm\Dav\Cal\Adapter\CallsAdapter;

use Sugarcrm\Sugarcrm\Dav\Cal\Adapter\FactoryInterface;

/**
 * Factory class for Calls Data and CustomProperties adapters.
 *
 * Class Factory
 * @package Sugarcrm\Sugarcrm\Dav\Cal\Adapter\CallsAdapter
 */
class Factory implements FactoryInterface
{
    /**
     * @inheritdoc
     */
    public function getAdapter()
    {
        $adapterClass = \SugarAutoLoader::customClass('Sugarcrm\Sugarcrm\Dav\Cal\Adapter\CallsAdapter\DataAdapter');
        if (!in_array('Sugarcrm\Sugarcrm\Dav\Cal\Adapter\DataAdapterInterface', class_implements($adapterClass))) {
            $this->getLogger()->warning('The adapter class ' . $adapterClass . ' does not ' .
                'implement Sugarcrm\Sugarcrm\Dav\Cal\Adapter\DataAdapterInterface');
        }
        return new $adapterClass();
    }

    /**
     * @inheritdoc
     */
    public function getPropertiesAdapter()
    {
        $adapterClass = \SugarAutoLoader::customClass(
            'Sugarcrm\Sugarcrm\Dav\Cal\Adapter\CallsAdapter\CustomPropertiesAdapter'
        );
        if (!in_array(
            'Sugarcrm\Sugarcrm\Dav\Cal\Adapter\CustomPropertiesAdapterInterface',
            class_implements($adapterClass)
        )
        ) {
            $this->getLogger()->warning('The adapter class ' . $adapterClass . ' does not ' .
                'implement Sugarcrm\Sugarcrm\Dav\Cal\Adapter\CustomPropertiesAdapterInterface');
        }
        return new $adapterClass();
    }

    /**
     * Factory method for LoggerManager.
     *
     * @return \LoggerManager
     */
    protected function getLogger()
    {
        return \LoggerManager::getLogger();
    }
}
