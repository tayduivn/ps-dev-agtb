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

namespace Sugarcrm\SugarcrmTests;

use PHPUnit\Framework\TestCase;
use Sugarcrm\Sugarcrm\Cache;

/**
 * @coversNothing
 */
abstract class CacheTest extends TestCase
{
    /**
     * @var Cache
     */
    protected $backend;

    protected function setUp()
    {
        try {
            $this->backend = $this->newInstance();
        } catch (\RuntimeException $e) {
            $this->markTestSkipped($e->getMessage());
        }
    }

    abstract protected function newInstance() : Cache;

    /**
     * @test
     * @dataProvider storeAndFetchProvider
     */
    public function storeAndFetch(string $key, $value)
    {
        $this->assertValueNotCached($this->backend, $key);

        $this->backend->store($key, $value);

        $this->assertValueCached($value, $this->backend, $key);
    }

    public static function storeAndFetchProvider()
    {
        return [
            'string' => [
                'string-key',
                'Hello, World!',
            ],
            'int' => [
                'int-key',
                1024,
            ],
            'array' => [
                'array-key',
                ['key' => 'value'],
            ],
            'binary' => [
                'binary-key',
                base64_decode('R0lGODlhAQABAIAAAP///wAAACH5BAAAAAAALAAAAAABAAEAAAICRAEAOw=='),
            ],
            'null' => [
                'null-key',
                null,
            ],
            'false' => [
                'false-key',
                false,
            ],
        ];
    }

    /**
     * @test
     */
    public function delete()
    {
        $this->backend->store('key1', 'X');
        $this->backend->store('key2', 'Y');

        $this->backend->delete('key1');

        $this->assertValueNotCached($this->backend, 'key1');
        $this->assertValueCached('Y', $this->backend, 'key2');
    }

    /**
     * @test
     */
    public function clear()
    {
        $this->backend->store('key', 'value');
        $this->backend->clear();

        $this->assertValueNotCached($this->backend, 'key');
    }

    /**
     * @test
     */
    public function expiration()
    {
        $this->backend->store('key', 'value', 1);
        usleep(1200000);

        $this->assertValueNotCached($this->backend, 'key');
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
