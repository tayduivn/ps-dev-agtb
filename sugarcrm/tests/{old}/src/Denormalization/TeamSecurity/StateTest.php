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

namespace Sugarcrm\SugarcrmTests\Denormalization\TeamSecurity;

use DomainException;
use Psr\Log\LoggerInterface;
use Sugarcrm\Sugarcrm\Denormalization\TeamSecurity\State;
use Sugarcrm\Sugarcrm\Denormalization\TeamSecurity\State\Storage;

/**
 * @covers \Sugarcrm\Sugarcrm\Denormalization\TeamSecurity\State
 */
class StateTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function defaultTableIsNull()
    {
        $state = $this->createState(
            $this->createStorage(null)
        );

        $this->assertNull($state->getActiveTable());
    }

    /**
     * @test
     */
    public function invalidStateIsIgnored()
    {
        $state = $this->createState(
            $this->createStorage('baz')
        );

        $this->assertNull($state->getActiveTable());
    }

    /**
     * @test
     */
    public function validStateIsPreserved()
    {
        $state = $this->createState(
            $this->createStorage('team_sets_users_1')
        );

        $this->assertSame('team_sets_users_1', $state->getActiveTable());
    }

    /**
     * @test
     */
    public function validTableCanBeActivated()
    {
        $state = $this->createStorage(null);
        $state->expects($this->at(1))
            ->method('update')
            ->with(State::STATE_ACTIVE_TABLE, 'team_sets_users_2');
        $state->expects($this->at(2))
            ->method('update')
            ->with(State::STATE_UP_TO_DATE, true);

        $state = $this->createState($state);

        $state->activateTable('team_sets_users_2');

        $this->assertSame('team_sets_users_2', $state->getActiveTable());
    }

    /**
     * @test
     */
    public function invalidTableCanNotBeActivated()
    {
        $state = $this->createState(
            $this->createStorage(null)
        );

        $this->expectException(DomainException::class);
        $state->activateTable('baz');
    }

    /**
     * @test
     * @dataProvider targetIsRotatedProvider
     */
    public function targetIsRotated($activeTable, $expectedTarget)
    {
        $state = $this->createState(
            $this->createStorage($activeTable)
        );

        $this->assertSame($expectedTarget, $state->getTargetTable());
    }

    public static function targetIsRotatedProvider()
    {
        return [
            'null-state' => [null, 'team_sets_users_1'],
            'first-to-second' => ['team_sets_users_1', 'team_sets_users_2'],
            'second-to-first' => ['team_sets_users_2', 'team_sets_users_1'],
        ];
    }

    private function createStorage($activeTable)
    {
        $mock = $this->createMock(Storage::class);
        $mock->expects($this->any())
            ->method('get')
            ->with(State::STATE_ACTIVE_TABLE)
            ->willReturn($activeTable);

        return $mock;
    }

    private function createLogger()
    {
        return $this->createMock(LoggerInterface::class);
    }

    private function createState(Storage $storage)
    {
        return new State(true, $storage, $this->createLogger());
    }
}
