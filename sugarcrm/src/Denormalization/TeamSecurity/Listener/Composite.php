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

namespace Sugarcrm\Sugarcrm\Denormalization\TeamSecurity\Listener;

use Sugarcrm\Sugarcrm\Denormalization\TeamSecurity\Listener;

/**
 * Composite listener
 *
 * Replays invocations on all underlying listeners.
 */
final class Composite implements Listener
{
    /**
     * @var Listener[]
     */
    private $listeners;

    /**
     * Constructor
     *
     * @param Listener[] $listeners
     */
    public function __construct(Listener ...$listeners)
    {
        $this->listeners = $listeners;
    }

    /**
     * {@inheritDoc}
     */
    public function teamSetCreated($teamSetId, array $teamIds)
    {
        $this->invoke(function (Listener $listener) use ($teamSetId, $teamIds) {
            $listener->teamSetCreated($teamSetId, $teamIds);
        });
    }

    /**
     * {@inheritDoc}
     */
    public function teamSetDeleted($teamSetId)
    {
        $this->invoke(function (Listener $listener) use ($teamSetId) {
            $listener->teamSetDeleted($teamSetId);
        });
    }

    /**
     * {@inheritDoc}
     */
    public function teamDeleted($teamId)
    {
        $this->invoke(function (Listener $listener) use ($teamId) {
            $listener->teamDeleted($teamId);
        });
    }

    /**
     * {@inheritDoc}
     */
    public function userAddedToTeam($userId, $teamId)
    {
        $this->invoke(function (Listener $listener) use ($userId, $teamId) {
            $listener->userAddedToTeam($userId, $teamId);
        });
    }

    /**
     * {@inheritDoc}
     */
    public function userRemovedFromTeam($userId, $teamId)
    {
        $this->invoke(function (Listener $listener) use ($userId, $teamId) {
            $listener->userRemovedFromTeam($userId, $teamId);
        });
    }

    /**
     * Invokes the given callback on all underlying listeners
     *
     * @param callable $callback
     */
    private function invoke(callable $callback)
    {
        array_walk($this->listeners, $callback);
    }
}
