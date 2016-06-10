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

namespace Sugarcrm\Sugarcrm\Notification;

/**
 * Interface EventInterface.
 * General interface for all system or custom Notification Events.
 * Event is emitted when something happens on a system- or module-level.
 * @package Sugarcrm\Sugarcrm\Notification
 */
interface EventInterface
{
    /**
     * Event provides information about itself via this method.
     * @return string event information.
     */
    public function __toString();

    /**
     * String representation of object.
     *
     * @return string the string representation of the object or null
     */
    public function serialize();

    /**
     * Constructs the object and returns it.
     *
     * @return EventInterface
     */
    public static function unserialize($serialized);
}
