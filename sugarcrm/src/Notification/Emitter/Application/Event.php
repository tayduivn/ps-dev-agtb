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

namespace Sugarcrm\Sugarcrm\Notification\Emitter\Application;

use Sugarcrm\Sugarcrm\Notification\EventInterface;

/**
 * Class Event.
 * Should be used, when something happens on an application level.
 * @package Sugarcrm\Sugarcrm\Notification\Emitter\Application
 */
class Event implements EventInterface
{
    /**
     * Event name.
     * @var string
     */
    protected $name;

    /**
     * Create an Event with a specified name.
     * @param string $name name of the Event.
     */
    public function __construct($name)
    {
        $this->name = $name;
    }

    /**
     * {@inheritdoc}
     */
    public function __toString()
    {
        return $this->name;
    }
}
