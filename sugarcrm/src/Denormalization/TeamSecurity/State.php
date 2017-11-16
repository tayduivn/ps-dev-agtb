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

namespace Sugarcrm\Sugarcrm\Denormalization\TeamSecurity;

use DomainException;
use Psr\Log\LoggerInterface;
use SplObjectStorage;
use SplObserver;
use SplSubject;
use Sugarcrm\Sugarcrm\Denormalization\TeamSecurity\State\Storage;

class State implements SplSubject
{
    // defines if the denormalized data is up to date with the source
    const STATE_UP_TO_DATE = 'up_to_date';
    // defines if currently full rebuild of denormalized table is in progress
    const STATE_REBUILD_RUNNING = 'rebuild_running';
    const STATE_ACTIVE_TABLE = 'active_table';

    /**#@+
     * @var bool
     */
    private $isEnabled;
    private $shouldHandleAdminUpdatesInline;
    /**#@-*/

    /**#@+
     * @var string
     */
    private $table1 = 'team_sets_users_1';
    private $table2 = 'team_sets_users_2';
    /**#@-*/

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var State
     */
    private $storage;

    /**
     * @var string|null
     */
    private $activeTable;

    /**
     * @var SplObjectStorage|SplObserver[]
     */
    private $observers;

    public function __construct($isEnabled, $shouldHandleAdminUpdatesInline, Storage $storage, LoggerInterface $logger)
    {
        $this->isEnabled = $isEnabled;
        $this->shouldHandleAdminUpdatesInline = $shouldHandleAdminUpdatesInline;

        $this->storage = $storage;
        $this->logger = $logger;

        $activeTable = $this->storage->get(self::STATE_ACTIVE_TABLE);

        if ($activeTable !== null && $this->isValidTable($activeTable)) {
            $this->activeTable = $activeTable;
        }

        $this->observers = new SplObjectStorage();
    }

    public function isEnabled()
    {
        return $this->isEnabled;
    }

    public function shouldHandleAdminUpdatesInline()
    {
        return $this->shouldHandleAdminUpdatesInline;
    }

    /**
     * Verify if denormalization setup is available for use.
     * @return boolean
     */
    public function isAvailable()
    {
        $hasActiveTable = $this->activeTable !== null;

        if ($this->isEnabled && $hasActiveTable) {
            return true;
        }

        if (!$this->isEnabled && $hasActiveTable) {
            $this->deactivate();
        }

        if ($this->isEnabled && !$hasActiveTable) {
            $this->logger->critical("Team Security is enabled but the normalized table not setup. Run full rebuild.");
        }

        return false;
    }

    public function getActiveTable()
    {
        return $this->activeTable;
    }

    public function getTargetTable()
    {
        if ($this->activeTable === $this->table1) {
            return $this->table2;
        }

        return $this->table1;
    }

    public function activateTable($table)
    {
        if (!$this->isValidTable($table)) {
            throw new DomainException('Invalid table name');
        }

        $this->activeTable = $table;
        $this->update(self::STATE_ACTIVE_TABLE, $table);
        $this->update(self::STATE_UP_TO_DATE, true);
    }

    private function deactivate()
    {
        $this->activeTable = null;
        $this->update(self::STATE_ACTIVE_TABLE, null);
    }

    private function isValidTable($table)
    {
        return $table === $this->table1
            || $table === $this->table2;
    }

    /**
     * @return boolean
     */
    public function isUpToDate()
    {
        return (bool) $this->storage->get(self::STATE_UP_TO_DATE);
    }

    /**
     * Mark the denormalized data out of date. This flag is used to determine
     * if full rebuild should be run during the next scheduler run.
     */
    public function markOutOfDate()
    {
        $this->update(self::STATE_UP_TO_DATE, false);
    }

    /**
     * @return boolean
     */
    public function isRebuildRunning()
    {
        return $this->storage->get(self::STATE_REBUILD_RUNNING);
    }

    public function markRebuildRunning()
    {
        $this->update(self::STATE_REBUILD_RUNNING, true);
    }

    public function markRebuildNotRunning()
    {
        $this->update(self::STATE_REBUILD_RUNNING, false);
    }

    private function update($var, $value)
    {
        $this->storage->update($var, $value);
        $this->notify();
    }

    /**
     * {@inheritDoc}
     */
    public function attach(SplObserver $observer)
    {
        $this->observers->attach($observer);
    }

    /**
     * {@inheritDoc}
     */
    public function detach(SplObserver $observer)
    {
        $this->observers->detach($observer);
    }

    /**
     * {@inheritDoc}
     */
    public function notify()
    {
        foreach ($this->observers as $observer) {
            $observer->update($this);
        }
    }
}
