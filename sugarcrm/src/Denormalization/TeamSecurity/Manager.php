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

use DBManagerFactory;
use Doctrine\DBAL\DBALException;
use NormalizedTeamSecurity;
use Psr\Log\LoggerInterface;
use SugarBean;
use SugarConfig;
use Sugarcrm\Sugarcrm\Bean\Visibility\Strategy\TeamSecurity\Denormalized;
use Sugarcrm\Sugarcrm\Dbal\Connection;
use Sugarcrm\Sugarcrm\Denormalization\TeamSecurity\Listener\Composite;
use Sugarcrm\Sugarcrm\Denormalization\TeamSecurity\Listener\Invalidator;
use Sugarcrm\Sugarcrm\Denormalization\TeamSecurity\Listener\NullListener;
use Sugarcrm\Sugarcrm\Denormalization\TeamSecurity\Listener\Recorder;
use Sugarcrm\Sugarcrm\Denormalization\TeamSecurity\Listener\Updater;
use Sugarcrm\Sugarcrm\Denormalization\TeamSecurity\Listener\UserOnlyListener;
use Sugarcrm\Sugarcrm\Logger\Factory as LoggerFactory;
use User;

/**
 * Denormalization Manager
 */
class Manager implements Listener
{
    /**
     * $sugar_config to determine if use of denormalized table is enabled
     * @var string
     */
    const CONFIG_KEY = "perfProfile.TeamSecurity";

    // defines if the denormalized data is up to date with the source
    const STATE_UP_TO_DATE = 'up_to_date';
    // defines if currently full rebuild of denormalized table is in progress
    const STATE_REBUILD_RUNNING = 'rebuild_running';

    /**
     * @var self
     */
    private static $instance;

    /**
     * @var Connection
     */
    private $conn;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var boolean
     */
    private $isEnabled;
    private $syncAdminChanges;

    /**
     * @var State
     */
    private $state;

    /**
     * @var TablePair
     */
    private $tables;

    /**
     * @var Listener
     */
    private $listener;

    /**
     * Constructor
     *
     * @param Connection $conn
     * @param LoggerInterface $logger
     * @param SugarConfig $config
     */
    public function __construct(Connection $conn, LoggerInterface $logger, SugarConfig $config)
    {
        $this->conn = $conn;
        $this->logger = $logger;
        $this->isEnabled = $this->getIsEnabledUseDenormOption($config);
        $this->syncAdminChanges = $config->get(self::CONFIG_KEY . '.inline_update');
        $this->state = new State();
        $this->tables = new TablePair(
            'team_sets_users_1',
            'team_sets_users_2',
            $this->state
        );
    }

    /**
     * Check if use_denorm is enabled for any module and returns the value.
     *
     * @return boolean
     */
    private function getIsEnabledUseDenormOption(SugarConfig $config)
    {
        $moduleConfigs = $config->get(self::CONFIG_KEY, array());

        foreach ($moduleConfigs as $value) {
            if (!empty($value['use_denorm'])) {
                return true;
            }
        }

        return false;
    }

    /**
     * Verify if denormalization setup is available for use.
     * @return boolean
     */
    private function isAvailable()
    {
        $hasActiveTable = $this->getActiveTable() !== null;

        if ($this->isEnabled && $hasActiveTable) {
            return true;
        }

        if (!$this->isEnabled && $hasActiveTable) {
            $this->disable();
        }

        if ($this->isEnabled && !$hasActiveTable) {
            $this->logger->critical("Team Security is enabled but the normalized table not setup. Run full rebuild.");
        }

        return false;
    }

    public function createStrategy(User $user, SugarBean $bean, array $options)
    {
        if (!empty($options['use_denorm']) && $this->isAvailable()) {
            return new Denormalized($this->getActiveTable(), $user);
        }

        return (new NormalizedTeamSecurity($bean))->setOptions($options);
    }

    /**
     * Rebuilds denormalized data
     *
     * @return array
     */
    public function rebuild()
    {
        if (!$this->isEnabled) {
            return array(
                true,
                'The use of denormalized table is not enabled. No need to run the job.',
            );
        }

        if ($this->isRebuildRunning()) {
            return array(
                true,
                'Denormalized table rebuild is already running.',
            );
        }

        if ($this->isUpToDate()) {
            return array(
                true,
                'Denormalized data is up to date.',
            );
        }

        try {
            $this->markRebuildRunning();
            $targetTable = $this->tables->getTargetTable();
            $this->doRebuild($targetTable);
            $this->replayChanges($targetTable);
            $this->markUpToDate();
            $this->tables->activate($targetTable);
        } catch (\Exception $e) {
            $this->logger->critical($e);

            return array(
                false,
                sprintf(
                    'Denormalized table rebuild failed with error: %s',
                    $e->getMessage()
                ),
            );
        } finally {
            $this->markRebuildNotRunning();
        }

        return array(
            true,
            'Denormalized table rebuild completed',
        );
    }

    /**
     * @return string
     */
    private function getActiveTable()
    {
        return $this->tables->getActiveTable();
    }

    private function disable()
    {
        $this->tables->deactivate();
    }

    /**
     * @return boolean
     */
    private function isUpToDate()
    {
        return (bool) $this->state->get(self::STATE_UP_TO_DATE);
    }

    private function markUpToDate()
    {
        $this->state->update(self::STATE_UP_TO_DATE, true);
    }

    /**
     * @return boolean
     */
    private function isRebuildRunning()
    {
        return $this->state->get(self::STATE_REBUILD_RUNNING);
    }

    private function markRebuildRunning()
    {
        $this->state->update(self::STATE_REBUILD_RUNNING, true);
    }

    private function markRebuildNotRunning()
    {
        $this->state->update(self::STATE_REBUILD_RUNNING, false);
    }

    /**
     * Get DenormManager instance
     *
     * @return self
     */
    public static function getInstance()
    {
        if (empty(self::$instance)) {
            self::$instance = new self(
                DBManagerFactory::getConnection(),
                LoggerFactory::getLogger('denorm'),
                SugarConfig::getInstance()
            );
        }

        return self::$instance;
    }

    /**
     * Welcome to the world of singletones!
     */
    public static function resetInstance()
    {
        self::$instance = null;
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

    private function createListener()
    {
        $components = [];

        // REMOVE ME: enable team security denormalization to run tests in CI
        if ($this->isEnabled && !$this->isUpToDate()) {
            $this->rebuild();
        }

        if ($this->isUpToDate()) {
            if ($this->isEnabled) {
                if ($this->isAvailable()) {
                    $updater = new Updater(
                        $this->conn,
                        $this->getActiveTable()
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

        if ($this->isRebuildRunning()) {
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
     * Rebuild table
     *
     * @param string $table
     *
     * @throws DBALException
     */
    private function doRebuild($table)
    {
        $this->conn->executeQuery(<<<SQL
DELETE FROM $table
SQL
        );

        $this->conn->executeQuery(<<<SQL
INSERT INTO $table
SELECT
    ts.id AS team_set_id,
    tm.user_id AS user_id
FROM team_sets ts
INNER JOIN team_sets_teams tst
    ON ts.id = tst.team_set_id
INNER JOIN teams t
    ON tst.team_id = t.id
    AND t.deleted = 0
INNER JOIN team_memberships tm
    ON t.id = tm.team_id
    AND tm.deleted = 0
GROUP BY ts.id, tm.user_id
SQL
        );
    }

    /**
     * Replay all actions added to denorm queue table after rebuild.
     *
     * @param string $targetTable
     *
     * @throws DBALException
     */
    private function replayChanges($targetTable)
    {
        $recorder = new Recorder($this->conn);
        $updater = new Updater($this->conn, $targetTable);
        $recorder->replay($updater, $this->logger);
    }
}
