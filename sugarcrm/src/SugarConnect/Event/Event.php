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

namespace Sugarcrm\Sugarcrm\SugarConnect\Event;

use Sugarcrm\Sugarcrm\SugarConnect\Publisher;
use Sugarcrm\Sugarcrm\SugarConnect\Client\Client;
use Sugarcrm\Sugarcrm\SugarConnect\Client\HTTP as HTTPClient;

final class Event
{
    /**
     * The client used to publish events to the Sugar Connect webhook.
     *
     * @var Client
     */
    private static $client;

    /**
     * Creates an Event publisher for the specific event.
     *
     * The Nop publisher is used if an event publisher strategy does not exist
     * for the specified event.
     *
     * @param string $event The type of event.
     *
     * @return Publisher
     */
    public static function getInstance(string $event) : Publisher
    {
        // Event names are transformed from snake_case to PascalCase to match
        // PHP class names.
        $classname = str_replace('_', '', ucwords($event, '_'));

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
     * Sends the event to the Sugar Connect webhook.
     *
     * @param array $event The final event or message to publish.
     *
     * @return void
     */
    public static function publish(array $event) : void
    {
        $client = static::$client ?? new HTTPClient();
        $client->send([$event]);
    }

    /**
     * HTTPClient is used by default to publish events to the Sugar Connect
     * webhook. Use this function to change the client.
     *
     * @param Client $client The client used to publish events.
     *
     * @return void
     */
    public static function setClient(Client $client) : void
    {
        static::$client = $client;
    }

    /**
     * Returns the names of all fields on the bean, excluding link fields.
     *
     * @param \SugarBean $bean The bean that was changed.
     *
     * @return array
     */
    public static function getFields(\SugarBean $bean) : array
    {
        return array_keys(
            array_filter(
                $bean->getFieldDefinitions(),
                function (array $def) : bool {
                    return !isset($def['type']) || $def['type'] !== 'link';
                }
            )
        );
    }
}
