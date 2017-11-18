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

use Psr\Log\LoggerInterface;
use SplSubject;
use Sugarcrm\Sugarcrm\Denormalization\TeamSecurity\Listener;
use Sugarcrm\Sugarcrm\Denormalization\TeamSecurity\Listener\Builder;
use Sugarcrm\Sugarcrm\Denormalization\TeamSecurity\Listener\Proxy;
use Sugarcrm\Sugarcrm\Util\Uuid;

/**
 * @covers \Sugarcrm\Sugarcrm\Denormalization\TeamSecurity\Listener\Proxy
 */
class ProxyTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function userDeleted()
    {
        $id = Uuid::uuid1();

        $listener = $this->createProxy('userDeleted', $id);
        $listener->userDeleted($id);
    }

    /**
     * @test
     */
    public function teamDeleted()
    {
        $id = Uuid::uuid1();

        $listener = $this->createProxy('teamDeleted', $id);
        $listener->teamDeleted($id);
    }

    /**
     * @test
     */
    public function teamSetCreated()
    {
        $id1 = Uuid::uuid1();
        $id2 = Uuid::uuid1();
        $id3 = Uuid::uuid1();

        $listener = $this->createProxy('teamSetCreated', $id1, [$id2, $id3]);
        $listener->teamSetCreated($id1, [$id2, $id3]);
    }

    /**
     * @test
     */
    public function teamSetDeleted()
    {
        $id = Uuid::uuid1();

        $listener = $this->createProxy('teamSetDeleted', $id);
        $listener->teamSetDeleted($id);
    }

    /**
     * @test
     */
    public function userAddedToTeam()
    {
        $id1 = Uuid::uuid1();
        $id2 = Uuid::uuid1();

        $listener = $this->createProxy('userAddedToTeam', $id1, $id2);
        $listener->userAddedToTeam($id1, $id2);
    }

    /**
     * @test
     */
    public function userRemovedFromTeam()
    {
        $id1 = Uuid::uuid1();
        $id2 = Uuid::uuid1();

        $listener = $this->createProxy('userRemovedFromTeam', $id1, $id2);
        $listener->userRemovedFromTeam($id1, $id2);
    }

    /**
     * @test
     */
    public function updateAndToString()
    {
        $listener1 = $this->createNamedListener('Test1()');
        $listener2 = $this->createNamedListener('Test2()');

        $builder = $this->createMock(Builder::class);
        $builder->expects($this->exactly(2))
            ->method('createListener')
            ->will(
                $this->onConsecutiveCalls($listener1, $listener2)
            );

        $proxy = new Proxy($builder, $this->createMock(LoggerInterface::class));

        $this->assertSame('Test1()', (string) $proxy);

        $subject = $this->createMock(SplSubject::class);
        $proxy->update($subject);

        $this->assertSame('Test2()', (string) $proxy);
    }

    private function createProxy($method, ...$args)
    {
        $listener = $this->createMock(Listener::class);
        $listener->expects($this->once())
            ->method($method)
            ->with(...$args);

        $builder = $this->createMock(Builder::class);
        $builder->expects($this->once())
            ->method('createListener')
            ->willReturn($listener);

        return new Proxy($builder, $this->createMock(LoggerInterface::class));
    }

    private function createNamedListener($name)
    {
        $listener = $this->createMock(Listener::class);
        $listener->method('__toString')
            ->willReturn($name);

        return $listener;
    }
}
