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

namespace Sugarcrm\Sugarcrm\Notification\BeanEmitter;

use Sugarcrm\Sugarcrm\Notification\EventInterface;
use Sugarcrm\Sugarcrm\Notification\MessageBuilder\MessageBuilderInterface;

/**
 * Builder for  BeanEmitter events
 *
 * Class MessageBuilder
 * @package Notification
 */
class MessageBuilder implements MessageBuilderInterface
{

    /**
     * Build message for BeanEmitter events
     *
     * {@inheritdoc}
     */
    public function build(EventInterface $event, \User $user, array $message)
    {
        // TODO: Implement build() method. current code explains the essence of the method
        foreach ($message as $key => $val) {
            $message[$key] = rtrim($val) . " Dear $user->full_name, {$event} appeared";
        }
        return $message;
    }

    /**
     * {@inheritdoc}
     */
    public function getLevel()
    {
        return self::LEVEL_MODULE;
    }

    /**
     * {@inheritdoc}
     */
    public function supports(EventInterface $event)
    {
        return $event instanceof Event;
    }
}
