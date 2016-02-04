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

namespace Sugarcrm\Sugarcrm\Notification\MessageBuilder;

use Sugarcrm\Sugarcrm\Notification\EventInterface;

/**
 * Interface MessageBuilderInterface.
 * General interface for all system or custom Notification MessageBuilders.
 * Notification MessageBuilder is an entity that parses and generates messages
 * depending on an event type, recipient and message signature.
 * @package Sugarcrm\Sugarcrm\Notification\MessageBuilder
 */
interface MessageBuilderInterface
{
    /**
     * Basic application-wide MessageBuilder implementation.
     */
    const LEVEL_BASE = 10;

    /**
     * Customized application-wide MessageBuilder implementation.
     */
    const LEVEL_CUSTOMIZED_BASE = 20;

    /**
     * Basic module MessageBuilder implementation.
     */
    const LEVEL_MODULE = 30;

    /**
     * Customized module MessageBuilder implementation.
     */
    const LEVEL_CUSTOMIZED_MODULE = 40;

    /**
     * General MessageBuilder implementation, that is used regardless of all.
     */
    const LEVEL_SUPER_WINNER = 777;

    /**
     * Build a message depending on an event type, recipient and additional data.
     * @param EventInterface $event event to build a message from.
     * @param string $filter subscription filter name by which this user rise up.
     * @param \User $user SugarCRM user who is addressed to receive a message.
     * @param array $messageSignature message signature.
     * @return array complete message pack.
     */
    public function build(EventInterface $event, $filter, \User $user, array $messageSignature);

    /**
     * Get MessageBuilder level.
     * @return int level of MessageBuilder implementation.
     */
    public function getLevel();

    /**
     * Say whether Message Builder can build a message for a given Event or not.
     * @param EventInterface $event Event to test
     * @return boolean true if can build, otherwise false.
     */
    public function supports(EventInterface $event);
}
