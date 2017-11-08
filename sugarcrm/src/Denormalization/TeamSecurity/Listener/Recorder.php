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
use Doctrine\DBAL\DBALException;
use Psr\Log\LoggerInterface;
use Rhumsaa\Uuid\Uuid;
use Sugarcrm\Sugarcrm\Denormalization\TeamSecurity\Listener;
use TimeDate;

/**
 * Records all method invocations for future replay.
 */
class Recorder implements Listener
{
    /**
     * @var string
     */
    private $table = 'team_set_events';

    /**
     * @var Connection
     */
    private $conn;

    /**
     * Constructor
     *
     * @param Connection $conn
     */
    public function __construct(Connection $conn)
    {
        $this->conn = $conn;
    }

    /**
     * {@inheritDoc}
     */
    public function teamSetCreated($teamSetId, array $teamIds)
    {
        $this->record(__FUNCTION__, $teamSetId, $teamIds);
    }

    /**
     * {@inheritDoc}
     */
    public function teamSetReplaced($teamSetId, $replacementId)
    {
        $this->record(__FUNCTION__, $teamSetId, $replacementId);
    }

    /**
     * {@inheritDoc}
     */
    public function teamSetDeleted($teamSetId)
    {
        $this->record(__FUNCTION__, $teamSetId);
    }

    /**
     * {@inheritDoc}
     */
    public function userAddedToTeam($userId, $teamId)
    {
        $this->record(__FUNCTION__, $userId, $teamId);
    }

    /**
     * {@inheritDoc}
     */
    public function userRemovedFromTeam($userId, $teamId)
    {
        $this->record(__FUNCTION__, $userId, $teamId);
    }

    /**
     * Records method invocation
     *
     * @param string $method
     * @param array $arguments
     *
     * @throws DBALException
     */
    private function record($method, ...$arguments)
    {
        $query = sprintf(
            <<<SQL
INSERT INTO %s (id, action, params, date_created) VALUES (?, ?, ?, ?)
SQL
            ,
            $this->table
        );

        $this->conn->executeUpdate($query, [
            Uuid::uuid1(),
            $method,
            json_encode($arguments),
            TimeDate::getInstance()->nowDb(),
        ]);
    }

    /**
     * Replays recorded method invocations on the given listener
     *
     * @param Listener $listener
     * @param LoggerInterface $logger
     *
     * @throws DBALException
     */
    public function replay(Listener $listener, LoggerInterface $logger)
    {
        $query = sprintf(
            <<<SQL
SELECT id, action, params FROM %s ORDER BY id
SQL
            ,
            $this->table
        );

        $stmt = $this->conn->executeQuery($query);

        while ($row = $stmt->fetch()) {
            try {
                $listener->{$row['action']}(...json_decode($row['params']));
            } catch (\Exception $e) {
                $logger->critical(sprintf(
                    'Unable to replay a change in team security model (%s in %s in on line %d)',
                    $e->getMessage(),
                    $e->getFile(),
                    $e->getLine()
                ));
            } finally {
                $this->delete($row['id']);
            }
        }
    }

    /**
     * Deletes a replayed record
     *
     * @param string $id
     *
     * @throws DBALException
     */
    private function delete($id)
    {
        $query = sprintf(
            <<<SQL
DELETE FROM %s WHERE id = ?
SQL
            ,
            $this->table
        );

        $this->conn->executeUpdate($query, [$id]);
    }
}