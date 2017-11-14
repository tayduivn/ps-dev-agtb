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

namespace Sugarcrm\SugarcrmTests\Denormalization\TeamSecurity\Listener;

use DBManagerFactory;
use Doctrine\DBAL\Connection;
use Exception;
use Psr\Log\LoggerInterface;
use Sugarcrm\Sugarcrm\Denormalization\TeamSecurity\Listener;
use Sugarcrm\Sugarcrm\Denormalization\TeamSecurity\Listener\Recorder;
use Sugarcrm\Sugarcrm\Util\Uuid;

/**
 * @covers \Sugarcrm\Sugarcrm\Denormalization\TeamSecurity\Listener\Recorder
 */
class RecorderTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Connection
     */
    private $conn;

    protected function setUp()
    {
        parent::setUp();

        $this->conn = DBManagerFactory::getConnection();
        $this->cleanUp();
    }

    protected function tearDown()
    {
        $this->cleanUp();

        parent::tearDown();
    }

    /**
     * @test
     */
    public function eventsAreRecordedAndReplayed()
    {
        $id1 = Uuid::uuid1();
        $id2 = Uuid::uuid1();
        $id3 = Uuid::uuid1();
        $id4 = Uuid::uuid1();
        $id5 = Uuid::uuid1();
        $id6 = Uuid::uuid1();
        $id7 = Uuid::uuid1();
        $id8 = Uuid::uuid1();

        $recorder = $this->createRecorder();
        $recorder->userAddedToTeam($id1, $id2);
        $recorder->userRemovedFromTeam($id3, $id4);
        $recorder->teamDeleted($id5);
        $recorder->teamSetCreated($id6, [$id7, $id8]);

        $listener = $this->createListener();
        $listener->expects(
            $this->at(0)
        )->method('userAddedToTeam')
            ->with($id1, $id2);
        $listener->expects(
            $this->at(1)
        )->method('userRemovedFromTeam')
            ->with($id3, $id4);
        $listener->expects(
            $this->at(2)
        )->method('teamDeleted')
            ->with($id5);
        $listener->expects(
            $this->at(3)
        )->method('teamSetCreated')
            ->with($id6, [$id7, $id8]);

        $recorder->replay($listener, $this->createLogger());
    }

    /**
     * @test
     */
    public function certainEventsDoNotNeedToBeReplayed()
    {
        $id1 = Uuid::uuid1();
        $id2 = Uuid::uuid1();

        $recorder = $this->createRecorder();
        $recorder->userDeleted($id1);
        $recorder->teamSetDeleted($id2);

        $listener = $this->createListener();
        $listener->expects(
            $this->never()
        )->method('userDeleted');
        $listener->expects(
            $this->never()
        )->method('teamSetDeleted');

        $recorder->replay($listener, $this->createLogger());
    }

    /**
     * @test
     */
    public function eventsAreNotReplayedTwice()
    {
        $id = Uuid::uuid1();

        $recorder = $this->createRecorder();
        $recorder->teamDeleted($id);

        $listener1 = $this->createListener();
        $recorder->replay($listener1, $this->createLogger());

        $listener2 = $this->createListener();
        $listener2->expects(
            $this->never()
        )->method('teamDeleted');

        $recorder->replay($listener2, $this->createLogger());
    }

    /**
     * @test
     */
    public function exceptionInListenerDoesntBreakExecution()
    {
        $id1 = Uuid::uuid1();
        $id2 = Uuid::uuid1();

        $recorder = $this->createRecorder();
        $recorder->teamDeleted($id1);
        $recorder->teamDeleted($id2);

        $listener = $this->createListener();
        $listener->expects(
            $this->at(0)
        )->method('teamDeleted')
            ->willThrowException(new Exception('Something went wrong'));

        $listener->expects(
            $this->at(1)
        )->method('teamDeleted')
            ->with($id2);

        $logger = $this->createLogger();
        $logger->expects(
            $this->once()
        )->method('critical');

        $recorder->replay($listener, $logger);
    }

    private function createRecorder()
    {
        return new Recorder($this->conn);
    }

    private function createListener()
    {
        return $this->createMock(Listener::class);
    }

    private function createLogger()
    {
        return $this->createMock(LoggerInterface::class);
    }

    private function cleanUp()
    {
        $this->conn->executeUpdate('DELETE FROM team_set_events');
    }
}
