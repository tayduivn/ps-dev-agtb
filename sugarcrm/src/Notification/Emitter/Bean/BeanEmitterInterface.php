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

use Sugarcrm\Sugarcrm\Notification\EmitterInterface;

/**
 * General interface for all Bean Notification Emitters.
 * Notification Emitter is an entity that emits BeanEvents.
 *
 * Interface BeanEmitterInterface
 * @package Notification
 */
interface BeanEmitterInterface extends EmitterInterface
{

    /**
     * @param Emitter $parent parent emitter
     */
    public function __construct(Emitter $parent);

    /**
     * Bean events detector
     * Should delegate logic to its own bean emitter
     *
     * @param \SugarBean $bean target bean
     * @param string $event Triggered logic hooks event
     * @param array $arguments Optional arguments
     */
    public function exec(\SugarBean $bean, $event, $arguments);
}
