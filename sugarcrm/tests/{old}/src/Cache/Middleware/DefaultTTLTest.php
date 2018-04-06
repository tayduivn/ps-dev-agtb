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
use Sugarcrm\Sugarcrm\Cache\Middleware\DefaultTTL;

/**
 * @covers \Sugarcrm\Sugarcrm\Cache\Middleware\DefaultTTL
 */
final class DefaultTTLTest extends TestCase
{
    /**
     * @test
     */
    public function specificTTL()
    {
        $backend = $this->createBackend(300);
        $middleware = $this->createMiddleware($backend);

        $middleware->store('key', 'value');
    }

    /**
     * @test
     */
    public function defaultTTL()
    {
        $backend = $this->createBackend(5);
        $middleware = $this->createMiddleware($backend);

        $middleware->store('key', 'value', 5);
    }

    private function createBackend(int $expectedTTL) : Cache
    {
        $backend = $this->createMock(Cache::class);
        $backend->expects($this->once())
            ->method('store')
            ->with('key', 'value', $expectedTTL);

        return $backend;
    }

    private function createMiddleware(Cache $backend) : Cache
    {
        return new DefaultTTL($backend, 300);
    }
}
