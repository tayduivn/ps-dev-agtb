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

use Sugarcrm\Sugarcrm\Notification\EmitterInterface;

/**
 * Class Emitter.
 * Emitter that emits application-level Events.
 * @package Sugarcrm\Sugarcrm\Notification\Emitter\Application
 */
class Emitter implements EmitterInterface
{
    /**
     * Get an Event by a given string.
     * @param string $string Event identifier.
     * @return Event application-level Event.
     */
    public function getEventPrototypeByString($string)
    {
        return new Event($string);
    }

    /**
     * {@inheritdoc}
     */
    public function getEventStrings()
    {
        return array();
    }

    /**
     * {@inheritdoc}
     */
    public function __toString()
    {
        return 'ApplicationEmitter';
    }
}
