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

use Doctrine\DBAL\Connection;
use SplObserver;
use SplSubject;
use Sugarcrm\Sugarcrm\Denormalization\TeamSecurity\Listener;
use Sugarcrm\Sugarcrm\Denormalization\TeamSecurity\Manager;
use Sugarcrm\Sugarcrm\Denormalization\TeamSecurity\State;

/**
 * Listener whose behavior depends on the current system state.
 */
final class StateBasedListener implements Listener, SplObserver
{
    /**
     * @var State
     */
    private $state;

    /**
     * @var Connection
     */
    private $conn;

    /**
     * @var bool
     */
    private $syncAdminChanges;

    /**
     * @var Listener
     */
    private $listener;

    /**
     * Constructor
     *
     * @param State $state
     * @param Connection $conn
     * @param $syncAdminChanges
     */
    public function __construct(State $state, Connection $conn, $syncAdminChanges)
    {
        $this->state = $state;
        $this->conn = $conn;
        $this->syncAdminChanges = $syncAdminChanges;

        $this->state->attach($this);
    }

    /**
     * {@inheritDoc}
     */
    public function userDeleted($userId)
    {
        $this->getListener()->userDeleted($userId);
    }

    /**
     * {@inheritDoc}
     */
    public function teamDeleted($teamId)
    {
        $this->getListener()->teamDeleted($teamId);
    }

    /**
     * {@inheritDoc}
     */
    public function teamSetCreated($teamSetId, array $teamIds)
    {
        $this->getListener()->teamSetCreated($teamSetId, $teamIds);
    }

    /**
     * {@inheritDoc}
     */
    public function teamSetDeleted($teamSetId)
    {
        $this->getListener()->teamSetDeleted($teamSetId);
    }

    /**
     * {@inheritDoc}
     */
    public function userAddedToTeam($userId, $teamId)
    {
        $this->getListener()->userAddedToTeam($userId, $teamId);
    }

    /**
     * {@inheritDoc}
     */
    public function userRemovedFromTeam($userId, $teamId)
    {
        $this->getListener()->userRemovedFromTeam($userId, $teamId);
    }

    /**
     * @return Listener
     */
    private function getListener()
    {
        if (!$this->listener) {
            $this->listener = $this->createListener();
        }

        return $this->listener;
    }

    /**
     * Creates a listener implementation according to the current state
     *
     * @return Listener
     */
    private function createListener()
    {
        $components = [];

        // REMOVE ME: enable team security denormalization to run tests in CI
        if ($this->state->isEnabled() && !$this->state->isUpToDate()) {
            $manager = Manager::getInstance();
            $command = $manager->getRebuildCommand();
            $command();
        }

        if ($this->state->isUpToDate()) {
            if ($this->state->isEnabled()) {
                if ($this->state->isAvailable()) {
                    $updater = new Updater(
                        $this->conn,
                        $this->state->getActiveTable()
                    );

                    if (!$this->syncAdminChanges) {
                        $updater = new UserOnlyListener(
                            $updater,
                            new Invalidator($this->state)
                        );
                    }

                    $components[] = $updater;
                }
            } else {
                $components[] = new Invalidator($this->state);
            }
        }

        if ($this->state->isRebuildRunning()) {
            $components[] = new Recorder($this->conn);
        }

        if (count($components) === 0) {
            return new NullListener();
        }

        if (count($components) === 1) {
            return $components[0];
        }

        return new Composite(...$components);
    }

    /**
     * {@inheritDoc}
     */
    public function update(SplSubject $subject)
    {
        $this->listener = null;
    }
}
