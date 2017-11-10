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
use Sugarcrm\Sugarcrm\Denormalization\TeamSecurity\State;
use Sugarcrm\Sugarcrm\Denormalization\TeamSecurity\Manager;

/**
 * Invalidates denormalized data upon any change
 */
final class Invalidator implements Listener
{
    /**
     * @var State
     */
    private $state;

    /**
     * {@inheritDoc}
     *
     * No need to invalidate the data, since the deleted user is not going to interact with the system anymore.
     * Corresponding records will be removed during full rebuild.
     */
    public function userDeleted($userId)
    {
    }

    /**
     * {@inheritDoc}
     */
    public function teamDeleted($teamId)
    {
        $this->markOutOfDate();
    }

    /**
     * Constructor
     *
     * @param State $state
     */
    public function __construct(State $state)
    {
        $this->state = $state;
    }

    /**
     * {@inheritDoc}
     */
    public function teamSetCreated($teamSetId, array $teamIds)
    {
        $this->markOutOfDate();
    }

    /**
     * {@inheritDoc}
     */
    public function teamSetDeleted($teamSetId)
    {
        $this->markOutOfDate();
    }

    /**
     * {@inheritDoc}
     */
    public function userAddedToTeam($userId, $teamId)
    {
        $this->markOutOfDate();
    }

    /**
     * {@inheritDoc}
     */
    public function userRemovedFromTeam($userId, $teamId)
    {
        $this->markOutOfDate();
    }

    /**
     * Mark the denormalized data out of date. This flag is used to determine
     * if full rebuild should be run during the next scheduler run.
     */
    private function markOutOfDate()
    {
        $this->state->update(Manager::STATE_UP_TO_DATE, false);
    }
}
