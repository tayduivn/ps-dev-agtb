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

namespace Sugarcrm\Sugarcrm\Notification;

/**
 * Interface EmitterInterface.
 * General interface for all system or custom Notification Emitters.
 * Notification Emitter is an entity that emits Events.
 * @package Sugarcrm\Sugarcrm\Notification
 */
interface EmitterInterface
{
    /**
     * Get an Event by a given string.
     * @param string $string Event identifier.
     * @return EventInterface Event that corresponds a given identifier.
     */
    public function getEventPrototypeByString($string);

    /**
     * Get all event strings.
     * @return array all event strings.
     */
    public function getEventStrings();

    /**
     * // ToDo: EmitterClass is a temporary solution.
     * @return EmitterClass
     */
    public function __toString();
}
