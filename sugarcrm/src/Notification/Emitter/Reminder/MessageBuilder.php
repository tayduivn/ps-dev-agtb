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

        // ToDo: build a correct message.
        if (array_key_exists('title', $messageSignature)) {
            $message['title'] = translate('LBL_EVENT_REMINDER_ABOUT', $module) . $bean->name;
        }
        if (array_key_exists('text', $messageSignature)) {
            $message['text'] = translate('LBL_EVENT_REMINDER_ABOUT', $module) . $this->generateUrl($module, $bean);
        }
        if (array_key_exists('html', $messageSignature)) {
            $message['html'] = $this->generateHtml($module, $bean);
        }
        return $message;
    }

    /**
     * Generate url for bean
     * @param $module
     * @param $bean
     * @return string generated url
     */
    protected function generateUrl($module, $bean)
    {
        return $GLOBALS['sugar_config']['site_url'] . '#' . buildSidecarRoute($module, $bean->id);
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
     * ToDo: We should generate HTML from some EmailTemplate or something like that.
     * Generates HTML part of the message, based on the information contained in the given event.
     * @param string $module Name of the module where event occurred.
     * @param \SugarBean $bean Bean where event occurred.
     * @return string HTML text with information about the event.
     */
    protected function generateHtml($module, $bean)
    {
        $url = $this->generateUrl($module, $bean);
        $msg = "Data of $module: <a href=\"{$url}\">{$bean->name}</a></br>";
        $msg .= "<table>";

        foreach ($bean->field_defs as $field => $fieldDef) {
            if ($fieldDef['type'] == 'link' || (isset($fieldDef['source']) && $fieldDef['source'] == 'non-db')) {
                continue;
            }
            $fieldName = $fieldDef['name'];
            $data = htmlspecialchars($bean->$field);
            $msg .= "<tr><td>$fieldName</td><td>$data</td></tr>";
        }

        $msg .= '</table>';

        return $msg;
    }
}
