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

namespace Sugarcrm\Sugarcrm\Bean\Visibility\Strategy\TeamSecurity\Denorm;

use DBManagerFactory;
use Doctrine\DBAL\DBALException;
use Psr\Log\LoggerInterface;
use SugarConfig;
use Sugarcrm\Sugarcrm\Bean\Visibility\Strategy\TeamSecurity\Denorm\Listener\Composite;
use Sugarcrm\Sugarcrm\Bean\Visibility\Strategy\TeamSecurity\Denorm\Listener\Invalidator;
use Sugarcrm\Sugarcrm\Bean\Visibility\Strategy\TeamSecurity\Denorm\Listener\NullListener;
use Sugarcrm\Sugarcrm\Bean\Visibility\Strategy\TeamSecurity\Denorm\Listener\Recorder;
use Sugarcrm\Sugarcrm\Bean\Visibility\Strategy\TeamSecurity\Denorm\Listener\Updater;
use Sugarcrm\Sugarcrm\Bean\Visibility\Strategy\TeamSecurity\Denorm\Listener\UserOnlyListener;
use Sugarcrm\Sugarcrm\Bean\Visibility\Strategy\TeamSecurity\Denormalized;
use Sugarcrm\Sugarcrm\Dbal\Connection;
use Sugarcrm\Sugarcrm\Logger\Factory as LoggerFactory;
use User;

/**
 * Denormalization Manager
 */
class Manager
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
     * @var Tables
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
        $this->tables = new Tables(
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
    public function isAvailable()
    {
        $isAvailable = (bool) $this->getActiveTable();

        if (!$this->isEnabled && $isAvailable) {
            $this->disable();

            return false;
        }

        if ($this->getActiveTable()) {
            return true;
        }

        if ($this->isEnabled) {
            $this->logger->critical("Team Security is enabled but the normalized table not setup. Run full rebuild.");
        }

        return false;
    }

    /**
     * @return Listener
     */
    public function getListener()
    {
        if (!$this->listener) {
            $this->listener = $this->createListener();
        }

        return $this->listener;
    }

    public function createStrategy(User $user)
    {
        return new Denormalized($this->getActiveTable(), $user);
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
            $targetTable = $this->tables->getTarget();
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
        return $this->tables->getActive();
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
    AND tst.deleted = 0
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
