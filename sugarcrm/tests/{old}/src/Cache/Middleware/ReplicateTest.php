<?php declare(strict_types=1);
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

namespace Sugarcrm\SugarcrmTests\Cache\Middleware;

use PHPUnit\Framework\TestCase;
use Sugarcrm\Sugarcrm\Cache;
use Sugarcrm\Sugarcrm\Cache\Middleware\Replicate;

/**
 * @covers \Sugarcrm\Sugarcrm\Cache\Middleware\Replicate
 */
final class ReplicateTest extends TestCase
{
    /**
     * @test
     */
    public function fetchSuccessFromReplicaDoesNotHitSource()
    {
        $replica = $this->createBackend();
        $this->store($replica, 'value');

        $source = $this->createBackend();
        $source->expects($this->never())
            ->method('fetch');

        $middleware = $this->createMiddleware($source, $replica);

        $this->assertSame('value', $middleware->fetch('key'));
    }

    /**
     * @test
     */
    public function fetchSuccessFromSourceIsReplicated()
    {
        $source = $this->createBackend();
        $this->store($source, 'value');

        $replica = $this->createBackend();
        $replica->expects($this->once())
            ->method('store');

        $middleware = $this->createMiddleware($source, $replica);

        $this->assertSame('value', $middleware->fetch('key'));
    }

    /**
     * @test
     */
    public function fetchFailure()
    {
        $source = $this->createBackend();
        $source->expects($this->once())
            ->method('fetch');

        $replica = $this->createBackend();
        $replica->expects($this->once())
            ->method('fetch');

        $middleware = $this->createMiddleware($source, $replica);

        $this->assertNull($middleware->fetch('key', $success));
        $this->assertEmpty($success);
    }

    /**
     * @test
     */
    public function storeIsReplicated()
    {
        $source = $this->createBackend();
        $this->expectStore($source, 'key', 'value', 120);

        $replica = $this->createBackend();
        $this->expectStore($replica, 'key', 'value', 120);

        $middleware = $this->createMiddleware($source, $replica);

        $middleware->store('key', 'value', 120);
    }

    /**
     * @test
     */
    public function deleteIsReplicated()
    {
        $source = $this->createBackend();
        $this->expectDelete($source, 'key');

        $replica = $this->createBackend();
        $this->expectDelete($replica, 'key');

        $middleware = $this->createMiddleware($source, $replica);

        $middleware->delete('key');
    }

    /**
     * @test
     */
    public function clearIsReplicated()
    {
        $source = $this->createBackend();
        $this->expectClear($source);

        $replica = $this->createBackend();
        $this->expectClear($replica);

        $middleware = $this->createMiddleware($source, $replica);

        $middleware->clear();
    }

    private function createMiddleware(Cache $source, Cache $replica) : Replicate
    {
        return new Replicate($source, $replica);
    }

    /**
     * @return Cache|\PHPUnit_Framework_MockObject_MockObject
     */
    private function createBackend() : Cache
    {
        return $this->createMock(Cache::class);
    }

    private function store(Cache $backend, $value) : void
    {
        $backend->expects($this->once())
            ->method('fetch')
            ->willReturnCallback(function ($key, &$success) use ($value) {
                $success = true;

                return $value;
            });
    }

    private function expectStore(Cache $backend, string $key, $value, int $ttl) : void
    {
        $backend->expects($this->once())
            ->method('store')
            ->with($key, $value, $ttl);
    }

    private function expectDelete(Cache $backend, string $key) : void
    {
        $backend->expects($this->once())
            ->method('delete')
            ->with($key);
    }

    private function expectClear(Cache $backend) : void
    {
        $backend->expects($this->once())
            ->method('clear');
    }
}
