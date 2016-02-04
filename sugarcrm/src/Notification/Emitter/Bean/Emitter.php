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

namespace Sugarcrm\Sugarcrm\Notification\Emitter\Bean;

use Sugarcrm\Sugarcrm\Notification\EmitterInterface;
use Sugarcrm\Sugarcrm\Notification\Dispatcher;

/**
 * Bean emitter provides possibility to detect event which has happened on bean.
 *
 * Class Emitter
 * @package Notification
 */
class Emitter implements EmitterInterface
{
    /**
     * Parent emitter
     *
     * @var BeanEmitterInterface
     */
    protected $parentEmitter;

    /**
     * Returns name of emitter.
     *
     * @return string emitter name
     */
    public function __toString()
    {
        return 'BeanEmitter';
    }

    /**
     * @see BeanEmitterInterface::exec()
     */
    public function exec(\SugarBean $bean, $event, $arguments)
    {
        $event = $this->getEventPrototypeByString('update')->setBean($bean);
        $dispatcher = $this->getDispatcher();
        $dispatcher->dispatch($event);
    }

    /**
     * {@inheritdoc}
     *
     */
    public function getEventPrototypeByString($eventString)
    {
        return new Event($eventString);
    }

    /**
     * {@inheritdoc}
     */
    public function getEventStrings()
    {
        return array();
    }

    protected function getDispatcher()
    {
        return new Dispatcher();
    }
}
