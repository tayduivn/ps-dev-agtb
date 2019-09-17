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

namespace Sugarcrm\SugarcrmTestsUnit\ProductDefinition\Config;

use PHPUnit\Framework\TestCase;
use Sugarcrm\Sugarcrm\ProductDefinition\Config\Cache\CacheInterface;
use Sugarcrm\Sugarcrm\ProductDefinition\Config\Source\SourceInterface;
use Sugarcrm\Sugarcrm\ProductDefinition\Config\Config;

/**
 * @coversDefaultClass \Sugarcrm\Sugarcrm\ProductDefinition\Config\Config
 */
class ConfigTest extends TestCase
{
    /**
     * @var \PHPUnit\Framework\MockObject\MockObject | \SugarConfig
     */
    protected $sugarConfig;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject | SourceInterface
     */
    protected $source;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject | CacheInterface
     */
    protected $cache;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject | Config
     */
    protected $config;

    /**
     * @codeCoverageIgnore
     */
    protected function setUp()
    {
        $this->source = $this->createMock(SourceInterface::class);
        $this->cache = $this->createMock(CacheInterface::class);

        $this->config = $this->getMockBuilder(Config::class)
            ->setConstructorArgs([\SugarConfig::getInstance()])
            ->setMethods(['getSource', 'getCache', 'isInstallInProgress'])
            ->getMock();
    }

    /**
     * @covers ::__construct
     */
    public function testGetProductDefinitionWrongType()
    {
        $this->expectException(\InvalidArgumentException::class);

        $sugarConfig = $this->createMock(\SugarConfig::class);
        $sugarConfig->expects($this->once())
            ->method('get')
            ->with('product_definition', $this->anything())
            ->willReturn(['type' => '']);

        (new Config($sugarConfig));
    }

    /**
     * @covers ::getProductDefinition
     */
    public function testGetProductDefinitionIsInstallInProgress()
    {
        $this->config->expects($this->once())
            ->method('isInstallInProgress')
            ->willReturn(true);

        $this->config->expects($this->never())
            ->method('getCache')
            ->willReturn($this->cache);

        $this->assertEquals([], $this->config->getProductDefinition());
    }


    /**
     * @covers ::getProductDefinition
     */
    public function testGetProductDefinitionCurrentVersionFromCache()
    {
        $this->config->expects($this->once())
            ->method('isInstallInProgress')
            ->willReturn(false);

        $this->config->expects($this->once())
            ->method('getCache')
            ->willReturn($this->cache);
        $this->cache->expects($this->once())
            ->method('getCurrentDefinition')
            ->willReturn('{"key":"value"}');

        $this->assertEquals(['key' => 'value'], $this->config->getProductDefinition());
    }

    /**
     * @covers ::getProductDefinition
     */
    public function testGetProductDefinitionNewVersionFromSource()
    {
        $this->config->expects($this->once())
            ->method('isInstallInProgress')
            ->willReturn(false);

        $this->config->expects($this->exactly(2))
            ->method('getCache')
            ->willReturn($this->cache);

        $this->cache->expects($this->once())
            ->method('getCurrentDefinition')
            ->willReturn(null);

        $this->cache->expects($this->once())
            ->method('set');

        $this->config->expects($this->once())
            ->method('getSource')
            ->willReturn($this->source);

        $this->source->expects($this->once())
            ->method('getDefinition')
            ->willReturn('{"key1":"value"}');

        $this->assertEquals(['key1' => 'value'], $this->config->getProductDefinition());
    }

    /**
     * @covers ::getProductDefinition
     */
    public function testGetProductDefinitionPreviousVersionFromCache()
    {
        $this->config->expects($this->once())
            ->method('isInstallInProgress')
            ->willReturn(false);

        $this->config->expects($this->exactly(2))
            ->method('getCache')
            ->willReturn($this->cache);

        $this->cache->expects($this->once())
            ->method('getCurrentDefinition')
            ->willReturn(null);

        $this->cache->expects($this->never())
            ->method('set');

        $this->cache->expects($this->once())
            ->method('getPreviousDefinition')
            ->willReturn('{"key2":"value"}');

        $this->config->expects($this->once())
            ->method('getSource')
            ->willReturn($this->source);

        $this->source->expects($this->once())
            ->method('getDefinition')
            ->willReturn(null);

        $this->assertEquals(['key2' => 'value'], $this->config->getProductDefinition());
    }
}
