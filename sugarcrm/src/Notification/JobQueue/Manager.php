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

namespace Sugarcrm\Sugarcrm\Notification\JobQueue;

use Sugarcrm\Sugarcrm\JobQueue\Manager\Manager as JobQueueManager;

class Manager extends JobQueueManager
{
    /**
     * Base handler class name.
     */
    const BASE_HANDLER = 'Sugarcrm\\Sugarcrm\\Notification\\JobQueue\\BaseHandler';

    /**
     * Serialize arguments and store pass to object's class name if it's present.
     *
     * @inheritDoc
     */
    public function __call($name, $arguments)
    {
        $handlerParams = $this->handlerRegistry->get($name);

        if ($handlerParams && in_array(static::BASE_HANDLER, class_parents($handlerParams['class']))) {
            $arguments = $this->wrapArguments($arguments);
        }

        return parent::__call($name, $arguments);
    }

    /**
     * Wrap arguments for easy class unserializing.
     *
     * @param array $arguments
     * @return array wrapped arguments
     */
    protected function wrapArguments(array $arguments)
    {
        foreach ($arguments as $key => $argument) {
            $path = '';
            // The first argument is always null or userId - leave it as is.
            if ($key === 0) {
                continue;
            }
            if (is_object($argument)) {
                $reflection = new \ReflectionObject($argument);
                $path = $reflection->getFileName();
            }
            $arguments[$key] = array($path, serialize($argument));
        }

        return $arguments;
    }
}
