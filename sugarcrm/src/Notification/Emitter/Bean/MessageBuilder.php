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

use Sugarcrm\Sugarcrm\Notification\EventInterface;
use Sugarcrm\Sugarcrm\Notification\MessageBuilder\MessageBuilderInterface;

/**
 * Class MessageBuilder.
 * Application's bean entity MessageBuilder implementation.
 * Is used to build messages for notifications of the bean-level events.
 * @package Sugarcrm\Sugarcrm\Notification\Emitter\Bean
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
    public function build(EventInterface $event, $filter, \User $user, array $messageSignature)
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
        if (array_key_exists('html', $messageSignature)) {
            $message['html'] = $this->generateHtml($module, $bean);
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

    /**
     * ToDo: We should generate HTML from some EmailTemplate or something like that.
     * Generates HTML part of the message, based on the information contained in the given event.
     * @param string $module Name of the module where event occurred.
     * @param \SugarBean $bean Bean where event occurred.
     * @return string HTML text with information about the event.
     */
    protected function generateHtml($module, $bean)
    {
        $msg = "Data of $module: {$bean->name}</br>";
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
