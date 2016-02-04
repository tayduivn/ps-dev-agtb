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

namespace Sugarcrm\Sugarcrm\Notification\Emitter\Reminder;

use Sugarcrm\Sugarcrm\Notification\EventInterface;
use Sugarcrm\Sugarcrm\Notification\MessageBuilder\MessageBuilderInterface;

/**
 * Class MessageBuilder
 * Basic MessageBuilder implementation.
 * @package Sugarcrm\Sugarcrm\Notification\Emitter\Calls
 */
class MessageBuilder implements MessageBuilderInterface
{
    /**
     * {@inheritdoc}
     */
    public function build(EventInterface $event, $filter, \User $user, array $messageSignature)
    {
        $message = array();
        $module = $event->getModuleName();
        $bean = $event->getBean();

        $time = $this->generateTime($bean, $user);
        $url = $this->generateUrl($module, $bean);

        if (array_key_exists('title', $messageSignature)) {
            $message['title'] = sprintf(translate('LBL_EVENT_REMINDER_TITLE', $module), $bean->name);
        }
        if (array_key_exists('text', $messageSignature)) {
            $message['text'] = sprintf(translate('LBL_EVENT_REMINDER_TEXT', $module), $bean->name, $time, $url);
        }
        if (array_key_exists('html', $messageSignature)) {
            $message['html'] = sprintf(translate('LBL_EVENT_REMINDER_HTML', $module), $bean->name, $time, $url);
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

    /**
     * Generate url for bean
     *
     * @param string $module Module name
     * @param \SugarBean $bean Bean for which we generate url link.
     * @return string generated url
     */
    protected function generateUrl($module, $bean)
    {
        return $GLOBALS['sugar_config']['site_url'] . '#' . buildSidecarRoute($module, $bean->id);
    }

    /**
     * Get time event takes place at.
     *
     * @param \SugarBean $bean Bean, causer of event.
     * @param \User $user recipient user.
     * @return string dat and time event takes place at.
     */
    protected function generateTime($bean, $user)
    {
        $timeDate = \TimeDate::getInstance();
        $start = $timeDate->fromUser($bean->date_start, $GLOBALS['current_user']);
        return $timeDate->asUser($start, $user);
    }
}
