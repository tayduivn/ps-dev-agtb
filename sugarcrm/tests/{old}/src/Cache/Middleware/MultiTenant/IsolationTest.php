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

namespace Sugarcrm\SugarcrmTests\Cache\Middleware\MultiTenant;

use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use Rhumsaa\Uuid\Uuid;
use Sugarcrm\Sugarcrm\Cache;
use Sugarcrm\Sugarcrm\Cache\Backend\InMemory as InMemoryBackend;
use Sugarcrm\Sugarcrm\Cache\Middleware\MultiTenant;
use Sugarcrm\Sugarcrm\Cache\Middleware\MultiTenant\KeyStorage\InMemory as InMemoryKeyStorage;

/**
 * @covers \Sugarcrm\Sugarcrm\Cache\Middleware\MultiTenant
 * @uses \Sugarcrm\Sugarcrm\Cache\Backend\InMemory
 * @uses \Sugarcrm\Sugarcrm\Cache\Middleware\MultiTenant\KeyStorage\InMemory
 */
final class IsolationTest extends TestCase
{
    /**#@+
     * @var Cache
     */
    private $tenant1;
    private $tenant2;
    /**#@-*/

    protected function setUp()
    {
        $instanceKey1 = Uuid::uuid4()->toString();
        $instanceKey2 = Uuid::uuid4()->toString();
        $keyStorage1 = new InMemoryKeyStorage();
        $keyStorage2 = new InMemoryKeyStorage();
        $backend = new InMemoryBackend();
        $logger = $this->createMock(LoggerInterface::class);

        $this->tenant1 = new MultiTenant($instanceKey1, $keyStorage1, $backend, $logger);
        $this->tenant2 = new MultiTenant($instanceKey2, $keyStorage2, $backend, $logger);
    }

    /**
     * @test
     */
    public function fetchStore()
    {
        $this->tenant1->store('key', 'value1');
        $this->tenant2->store('key', 'value2');

        $this->assertValueCached('value1', $this->tenant1, 'key');
        $this->assertValueCached('value2', $this->tenant2, 'key');
    }

    /**
     * @test
     */
    public function delete()
    {
        $this->tenant1->store('key', 'value1');
        $this->tenant2->store('key', 'value2');

        $this->tenant1->delete('key');

        $this->assertValueNotCached($this->tenant1, 'key');
        $this->assertValueCached('value2', $this->tenant2, 'key');
    }

    /**
     * @test
     */
    public function clear()
    {
        $this->tenant1->store('key', 'value1');
        $this->tenant2->store('key', 'value2');

        $this->tenant1->clear();

        $this->assertValueNotCached($this->tenant1, 'key');
        $this->assertValueCached('value2', $this->tenant2, 'key');
    }

    private function assertValueCached($value, Cache $cache, string $key) : void
    {
        $this->assertEquals($value, $cache->fetch($key, $success));
        $this->assertTrue($success);
    }

    private function assertValueNotCached(Cache $cache, string $key) : void
    {
        $this->assertNull($cache->fetch($key, $success));
        $this->assertFalse($success);
    }
}
