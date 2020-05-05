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

namespace Sugarcrm\SugarcrmTestsUnit\ProductDefinition\Config\Source;

use LoggerManager;
use PHPUnit\Framework\MockObject\MockObject;
use Sugarcrm\Sugarcrm\ProductDefinition\Config\Source\HttpSource as HttpSource;
use PHPUnit\Framework\TestCase;
use GuzzleHttp\Client as HttpClient;
use Psr\Http\Message\ResponseInterface;
use Symfony\Component\HttpFoundation\Response as SymfonyResponse;

/**
 * @coversDefaultClass \Sugarcrm\Sugarcrm\ProductDefinition\Config\Source\HttpSource
 */
class HttpSourceTest extends TestCase
{
    /**
     * @var MockObject|HttpClient
     */
    protected $httpClient;

    /**
     * @var MockObject|ResponseInterface
     */
    protected $response;

    /**
     * @var MockObject|HttpSource
     */
    protected $source;

    /**
     * @var MockObject|LoggerManager
     */
    protected $logger;

    protected function setUp() : void
    {
        $this->httpClient = $this->createMock(HttpClient::class);
        $this->response = $this->createMock(ResponseInterface::class);
        $this->logger = $this->createMock(LoggerManager::class);

        $this->source = $this->getMockBuilder(HttpSource::class)
            ->setConstructorArgs([
                [
                    'base_uri' => 'http://localhost/',
                    'fallback_version' => '9.0.0',
                ],
            ])
            ->setMethods(['getSugarVersion', 'getLogger'])
            ->getMock();
        $this->source->expects($this->any())->method('getSugarVersion')->willReturn('9.2.0');
        $this->source->expects($this->any())->method('getLogger')->willReturn($this->logger);
        $this->source->setHttpClient($this->httpClient);
    }

    /**
     * @covers ::__construct
     */
    public function testConstructBaseUriMissing()
    {
        $this->expectException(\InvalidArgumentException::class);
        (new HttpSource([]));
    }

    /**
     * @covers ::getDefinition
     */
    public function testGetDefinitionHttpExceptionIsThrowed()
    {
        $this->logger->expects($this->exactly(3))
            ->method('__call');

        $this->httpClient->expects($this->exactly(2))
            ->method('request')
            ->withConsecutive(['GET', '9.2.0'], ['GET', '9.0.0'])
            ->willThrowException(new \Exception('test'));

        $this->response->expects($this->never())
            ->method('getStatusCode');

        $this->assertNull($this->source->getDefinition());
    }

    /**
     * @covers ::getDefinition
     */
    public function testGetDefinitionWrongResponseStatus()
    {
        $this->logger->expects($this->exactly(3))
            ->method('__call');

        $this->httpClient->expects($this->exactly(2))
            ->method('request')
            ->withConsecutive(['GET', '9.2.0'], ['GET', '9.0.0'])
            ->willReturn($this->response);

        $this->response->expects($this->exactly(2))
            ->method('getStatusCode')
            ->willReturn(SymfonyResponse::HTTP_NOT_FOUND);

        $this->assertNull($this->source->getDefinition());
    }

    /**
     * @covers ::getDefinition
     */
    public function testGetDefinition()
    {
        $this->logger->expects($this->exactly(2))
            ->method('__call');

        $this->httpClient->expects($this->exactly(2))
            ->method('request')
            ->withConsecutive(['GET', '9.2.0'], ['GET', '9.0.0'])
            ->willReturn($this->response);

        $this->response->expects($this->exactly(2))
            ->method('getStatusCode')
            ->willReturnOnConsecutiveCalls(SymfonyResponse::HTTP_NOT_FOUND, SymfonyResponse::HTTP_OK);

        $this->response->expects($this->once())
            ->method('getBody')
            ->willReturn('{"key":"value"}');

        $this->assertEquals('{"key":"value"}', $this->source->getDefinition());
    }
}
