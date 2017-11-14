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
use Sugarcrm\Sugarcrm\Denormalization\TeamSecurity\State;
use Sugarcrm\Sugarcrm\Denormalization\TeamSecurity\TablePair;

/**
 * @covers \Sugarcrm\Sugarcrm\Denormalization\TeamSecurity\TablePair
 */
class TablePairTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function defaultTableIsNull()
    {
        $pair = $this->createTables(
            $this->createState(null)
        );

        $this->assertNull($pair->getActiveTable());
    }

    /**
     * @test
     */
    public function invalidStateIsIgnored()
    {
        $pair = $this->createTables(
            $this->createState('baz')
        );

        $this->assertNull($pair->getActiveTable());
    }

    /**
     * @test
     */
    public function validStateIsPreserved()
    {
        $pair = $this->createTables(
            $this->createState('foo')
        );

        $this->assertSame('foo', $pair->getActiveTable());
    }

    /**
     * @test
     */
    public function validTableCanBeActivated()
    {
        $state = $this->createState(null);
        $state->expects($this->once())
            ->method('update')
            ->with(TablePair::STATE_VARIABLE, 'bar');

        $pair = $this->createTables($state);

        $pair->activate('bar');

        $this->assertSame('bar', $pair->getActiveTable());
    }

    /**
     * @test
     */
    public function invalidTableCanNotBeActivated()
    {
        $pair = $this->createTables(
            $this->createState(null)
        );

        $this->expectException(DomainException::class);
        $pair->activate('baz');
    }

    /**
     * @test
     */
    public function tableCanBeDeactivated()
    {
        $state = $this->createState('foo');
        $state->expects($this->once())
            ->method('update')
            ->with(TablePair::STATE_VARIABLE, null);

        $pair = $this->createTables($state);

        $pair->deactivate();

        $this->assertNull($pair->getActiveTable());
    }

    /**
     * @test
     * @dataProvider targetIsRotatedProvider
     */
    public function targetIsRotated($activeTable, $expectedTarget)
    {
        $pair = $this->createTables(
            $this->createState($activeTable)
        );

        $this->assertSame($expectedTarget, $pair->getTargetTable());
    }

    public static function targetIsRotatedProvider()
    {
        return [
            'null-state' => [null, 'foo'],
            'first-to-second' => ['foo', 'bar'],
            'second-to-first' => ['bar', 'foo'],
        ];
    }

    private function createTables(State $state)
    {
        return new TablePair('foo', 'bar', $state);
    }

    private function createState($activeTable)
    {
        $mock = $this->createMock(State::class);
        $mock->expects($this->any())
            ->method('get')
            ->with(TablePair::STATE_VARIABLE)
            ->willReturn($activeTable);

        return $mock;
    }
}
