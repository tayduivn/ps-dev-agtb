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
    /**#@+
     * State parameters
     */
    const STATE_UP_TO_DATE = 'up_to_date';
    const STATE_REBUILD_RUNNING = 'rebuild_running';
    const STATE_ACTIVE_TABLE = 'active_table';
    /**#@-*/

    /**#@+
     * Configuration parameters
     *
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

    /**
     * Constructor
     *
     * @param bool $isEnabled
     * @param bool $shouldHandleAdminUpdatesInline
     * @param Storage $storage
     * @param LoggerInterface $logger
     */
    public function __construct($isEnabled, $shouldHandleAdminUpdatesInline, Storage $storage, LoggerInterface $logger)
    {
        $this->isEnabled = $isEnabled;
        $this->shouldHandleAdminUpdatesInline = $shouldHandleAdminUpdatesInline;

        $this->storage = $storage;
        $this->logger = $logger;
        $this->observers = new SplObjectStorage();

        $activeTable = $this->storage->get(self::STATE_ACTIVE_TABLE);

        if ($activeTable !== null) {
            if (!$this->isValidTable($activeTable)) {
                $activeTable = null;
            } elseif (!$isEnabled) {
                $this->deactivate();
                $activeTable = null;
            }
        } elseif ($isEnabled) {
            $logger->critical('Denormalization is enabled but the denormalized data is unavailable.');
        }

        $this->activeTable = $activeTable;
    }

    /**
     * Returns whether the usage of denormalized data is enabled by configuration
     *
     * @return bool
     */
    public function isEnabled()
    {
        return $this->isEnabled;
    }

    /**
     * Returns whether the inline handling of admin updates is enabled by configuration
     *
     * @return bool
     */
    public function shouldHandleAdminUpdatesInline()
    {
        return $this->shouldHandleAdminUpdatesInline;
    }

    /**
     * Returns whether the denormalizated data is available for use
     *
     * @return boolean
     */
    public function isAvailable()
    {
        return $this->activeTable !== null;
    }

    /**
     * Returns the name of the table containing denormalizated data or NULL if the data is unavailable
     *
     * @return string|null
     */
    public function getActiveTable()
    {
        return $this->activeTable;
    }

    /**
     * Returns the name of the table which should be used for storing denormalized data during full rebuild
     *
     * @return string|null
     */
    public function getTargetTable()
    {
        if ($this->activeTable === $this->table1) {
            return $this->table2;
        }

        return $this->table1;
    }

    /**
     * Activates the given table. This table will be used for reads and inline updates.
     *
     * @param string $table
     */
    public function activateTable($table)
    {
        if (!$this->isValidTable($table)) {
            throw new DomainException(sprintf(
                'The table should be either %s or %s, %s given',
                $this->table1,
                $this->table2,
                $table
            ));
        }

        $this->activeTable = $table;
        $this->update(self::STATE_ACTIVE_TABLE, $table);
        $this->update(self::STATE_UP_TO_DATE, true);
    }

    /**
     * Deactivates the usage of denormalized data
     */
    private function deactivate()
    {
        $this->update(self::STATE_ACTIVE_TABLE, null);
    }

    /**
     * Validates table name
     *
     * @param string $table
     * @return bool
     */
    private function isValidTable($table)
    {
        return $table === $this->table1
            || $table === $this->table2;
    }

    /**
     * Returns whether the denormalized data is up to date
     *
     * @return boolean
     */
    public function isUpToDate()
    {
        return (bool) $this->storage->get(self::STATE_UP_TO_DATE);
    }

    /**
     * Mark the denormalized data out of date
     */
    public function markOutOfDate()
    {
        $this->update(self::STATE_UP_TO_DATE, false);
    }

    /**
     * Returns whether a full rebuild of the denormalized data is currently running
     *
     * @return boolean
     */
    public function isRebuildRunning()
    {
        return (bool) $this->storage->get(self::STATE_REBUILD_RUNNING);
    }

    /**
     */
    public function markRebuildRunning()
    {
        $this->update(self::STATE_REBUILD_RUNNING, true);
    }

    /**
     */
    public function markRebuildNotRunning()
    {
        $this->update(self::STATE_REBUILD_RUNNING, false);
    }

    /**
     * Updates the given state parameter
     *
     * @param string $var
     * @param mixed $value
     */
    private function update($var, $value)
    {
        $oldValue = $this->storage->get($var);

        if ($oldValue === $value) {
            $this->logger->warning(sprintf(
                'Unexpected state transition. State parameter %s is already %s',
                $var,
                var_export($value, true)
            ));

            return;
        }

        $this->logger->info(sprintf(
            'State parameter %s changed from %s to %s.',
            $var,
            var_export($oldValue, true),
            var_export($value, true)
        ));

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
