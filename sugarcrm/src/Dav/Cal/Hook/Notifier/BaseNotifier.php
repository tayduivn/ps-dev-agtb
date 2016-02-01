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

namespace Sugarcrm\Sugarcrm\Dav\Cal\Hook\Notifier;

/**
 * Class BaseNotifier
 * @package Sugarcrm\Sugarcrm\Dav\Cal\Hook\Notifier
 */
abstract class BaseNotifier
{
    /**
     * List of listeners.
     *
     * @var ListenerInterface[]
     */
    protected $listeners = array();

    /**
     * Attach given listener.
     *
     * @param ListenerInterface $listener
     */
    public function attach(ListenerInterface $listener)
    {
        $this->listeners[spl_object_hash($listener)] = $listener;
    }

    /**
     * Detach given listener.
     *
     * @param ListenerInterface $listener
     */
    public function detach(ListenerInterface $listener)
    {
        $key = array_search($listener, $this->listeners, true);
        if ($key !== false) {
            unset($this->listeners[$key]);
        }
    }

    /**
     * Notify all listeners using given data.
     *
     * @param string $moduleName Name of the module.
     * @param string $beanId record id.
     * @param array $preparedData Data.
     * @return bool Result of notify action. True means we should continue to notify listeners, false means halt.
     */
    public function notify($moduleName, $beanId, $preparedData)
    {
        foreach ($this->listeners as $listener) {
            if (!$listener->update($moduleName, $beanId, $preparedData)) {
                return false;
            }
        }
        return true;
    }
}
