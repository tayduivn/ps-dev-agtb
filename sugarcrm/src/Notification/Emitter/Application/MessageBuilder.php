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
use Sugarcrm\Sugarcrm\Notification\MessageBuilder\MessageBuilderInterface;

/**
 * Class MessageBuilder.
 * Basic application-wide MessageBuilder implementation.
 * @package Sugarcrm\Sugarcrm\Notification\Emitter\Application
 */
class MessageBuilder implements MessageBuilderInterface
{
    /**
     * This is basic application-wide MessageBuilder indicator.
     * @var int
     */
    protected $level = self::LEVEL_BASE;

    /**
     * {@inheritdoc}
     */
    public function build(EventInterface $event, $filter, \User $user, array $messageSignature)
    {
        // ToDo: write actual logic.
        return array();
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
        // ToDo: write actual logic.
        return true;
    }
}
