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
 * Class MessageBuilder.
 * Application's bean entity MessageBuilder implementation.
 * Is used to build messages for notifications of the bean-level events.
 * @package Sugarcrm\Sugarcrm\Notification\BeanEmitter
 */
class MessageBuilder implements MessageBuilderInterface
{
    /**
     * This is basic bean-level MessageBuilder indicator.
     * @var int
     */
    protected $level = self::LEVEL_BASE;

    /**
     * {@inheritdoc}
     */
    public function build(EventInterface $event, \User $user, array $messageSignature)
    {
        $message = array();
        $module = $event->getModuleName();
        $bean = $event->getBean();

        // ToDo: build a correct message.
        if (array_key_exists('title', $messageSignature)) {
            $message['title'] = "$event triggered";
        }
        if (array_key_exists('text', $messageSignature)) {
            $message['text'] = "Triggered in $module:'{$bean->name}'";
        }

        return $message;
    }

    /**
     * {@inheritdoc}
     */
    public function getLevel()
    {
        return $this->level;
    }

    /**
     * {@inheritdoc}
     */
    public function supports(EventInterface $event)
    {
        return $event instanceof Event;
    }
}
