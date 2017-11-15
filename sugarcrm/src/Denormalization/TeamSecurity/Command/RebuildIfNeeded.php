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

namespace Sugarcrm\Sugarcrm\Denormalization\TeamSecurity\Command;

use Doctrine\DBAL\DBALException;
use Doctrine\DBAL\Connection;
use Psr\Log\LoggerInterface;
use Sugarcrm\Sugarcrm\Denormalization\TeamSecurity\Listener\Recorder;
use Sugarcrm\Sugarcrm\Denormalization\TeamSecurity\Listener\Updater;
use Sugarcrm\Sugarcrm\Denormalization\TeamSecurity\State;

/**
 * Performs full denormalized data rebuild, if needed
 */
final class RebuildIfNeeded
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
     * @var LoggerInterface
     */
    private $logger;

    /**
     * Constructor
     *
     * @param State $state
     * @param Connection $conn
     * @param LoggerInterface $logger
     */
    public function __construct(State $state, Connection $conn, LoggerInterface $logger)
    {
        $this->state = $state;
        $this->conn = $conn;
        $this->logger = $logger;
    }

    /**
     * Rebuilds denormalized data
     *
     * @return array
     */
    public function __invoke()
    {
        if (!$this->state->isEnabled()) {
            return array(
                true,
                'The use of denormalized table is not enabled. No need to run the job.',
            );
        }

        if ($this->state->isRebuildRunning()) {
            return array(
                true,
                'Denormalized table rebuild is already running.',
            );
        }

        if ($this->state->isUpToDate()) {
            return array(
                true,
                'Denormalized data is up to date.',
            );
        }

        try {
            $targetTable = $this->state->getTargetTable();
            $this->state->markRebuildRunning();
            $this->rebuild($targetTable);
            $this->replayChanges($targetTable);
            $this->state->activateTable($targetTable);
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
            $this->state->markRebuildNotRunning();
        }

        return array(
            true,
            'Denormalized table rebuild completed',
        );
    }

    /**
     * Rebuild table
     *
     * @param string $table
     *
     * @throws DBALException
     */
    private function rebuild($table)
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
