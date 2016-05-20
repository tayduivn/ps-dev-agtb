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


namespace Sugarcrm\Sugarcrm\Dav\Cal\Rebuild;

use Sugarcrm\Sugarcrm\Dav\Cal\Adapter\Factory as CalDavAdapterFactory;
use Sugarcrm\Sugarcrm\Util\Runner\RunnableInterface;

/**
 * Re-export Calls and Meeting to external application.
 *
 * Class Executer
 * @package Sugarcrm\Sugarcrm\Dav\Cal\Rebuild
 */
class Executer implements RunnableInterface
{

    /**
     * Return traversable list of call and meeting bean for re-exporting.
     *
     * @return \Traversable
     */
    public function getBeans()
    {
        $appendIterator = new \AppendIterator();
        foreach ($this->getCalDavAdapterFactory()->getSupportedModules() as $module) {
            $appendIterator->append($this->getBeanIterator($module));
        }
        return $appendIterator;
    }

    /**
     * Create new BeanIterator.
     *
     * @param string $module
     * @return BeanIterator
     */
    protected function getBeanIterator($module)
    {
        return new BeanIterator($module);
    }

    /**
     * Returns factory class to get list of supported modules.
     *
     * @return CalDavAdapterFactory
     */
    protected function getCalDavAdapterFactory()
    {
        return new CalDavAdapterFactory();
    }

    /**
     * Re-export Calls and Meeting to external application.
     *
     * @param \Call|\Meeting|\SugarBean $bean
     */
    public function execute(\SugarBean $bean)
    {
        $bean->getCalDavHook()->export($bean, false, true);
    }
}
