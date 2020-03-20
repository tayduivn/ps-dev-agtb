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

namespace Sugarcrm\Sugarcrm\SugarConnect\Bean;

use Sugarcrm\Sugarcrm\SugarConnect\Publisher;
use Sugarcrm\Sugarcrm\SugarConnect\Event\Event;

class SugarBean implements Publisher
{
    /**
     * Use {@link SugarBean::getInstance()} to create a SugarBean publisher.
     */
    protected function __construct()
    {
    }

    /**
     * Creates a SugarBean publisher for the specific bean.
     *
     * The Nop publisher is used if a bean publisher strategy does not exist for
     * the specified bean.
     *
     * @param \SugarBean $bean The bean that was changed.
     *
     * @return Publisher
     */
    public static function getInstance(\SugarBean $bean) : Publisher
    {
        $classname = \BeanFactory::getObjectName($bean->getModuleName());

        if (!$classname) {
            $classname = 'Nop';
        }

        $fqcn = __NAMESPACE__ . '\\' . $classname;

        if (class_exists($fqcn)) {
            return new $fqcn();
        }

        return new Nop();
    }

    /**
     * Sends the bean event to the Sugar Connect webhook.
     *
     * @param \SugarBean $bean  The bean that was changed.
     * @param string     $event The type of event.
     * @param array      $args  Additional arguments.
     *
     * @return void
     */
    public function publish(\SugarBean $bean, string $event, array $args) : void
    {
        Event::getInstance($event)->publish($bean, $event, $args);
    }
}
