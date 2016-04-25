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

namespace Sugarcrm\SugarcrmTests\Dav\Cal\Agent;

use Sabre\DAV\Server;
use Sugarcrm\Sugarcrm\Dav\Cal\Agent\Plugin;
use Sugarcrm\Sugarcrm\Dav\Cal\Agent\Validator;
use Sugarcrm\Sugarcrm\Dav\Cal\Agent\Client;

/**
 * Class PluginTest
 * @covers Sugarcrm\Sugarcrm\Dav\Cal\Agent\Plugin
 */
class PluginTest extends \PHPUnit_Framework_TestCase
{
    /** @var Server|\PHPUnit_Framework_MockObject_MockObject|null $server */
    protected $server = null;

    /** @var Validator|\PHPUnit_Framework_MockObject_MockObject|null $validator */
    protected $validator = null;

    /** @var Client|\PHPUnit_Framework_MockObject_MockObject|null $client */
    protected $client = null;

    /**
     * {@inheritdoc}
     */
    public function setUp()
    {
        $this->server = $this->getMock('Sabre\DAV\Server');
        $this->server->httpRequest = $this->getMock('Sabre\HTTP\Request');

        $this->validator = $this->getMock('Sugarcrm\Sugarcrm\Dav\Cal\Agent\Validator');
        $this->client = $this->getMock('Sugarcrm\Sugarcrm\Dav\Cal\Agent\Client');
    }

    /**
     * Checking plugin name.
     *
     * @covers Sugarcrm\Sugarcrm\Dav\Cal\Agent\Plugin::getPluginName
     */
    public function testGetPluginName()
    {
        $agentPlugin = new Plugin($this->validator, $this->client);
        $this->assertEquals('check-supported-client', $agentPlugin->getPluginName());
    }

    /**
     * Checking subscribe plugin.
     *
     * @covers Sugarcrm\Sugarcrm\Dav\Cal\Agent\Plugin::initialize
     */
    public function testInitialize()
    {
        $agentPlugin = new Plugin($this->validator, $this->client);
        $this->server
            ->expects($this->once())
            ->method('once')
            ->with('beforeMethod', array($agentPlugin, 'checkSupportedClient'));
        $agentPlugin->initialize($this->server);
    }

    /**
     * Checking the detection of unsupported client.
     *
     * @expectedException \Sabre\DAV\Exception\Forbidden
     * @covers Sugarcrm\Sugarcrm\Dav\Cal\Agent\Plugin::checkSupportedClient
     */
    public function testCheckSupportedClientFailure()
    {
        $userAgent = 'full string user agent ' . rand(1000, 9999);
        $clientInfo = array(
            'platformName' => 'platform name ' . rand(1000, 9999),
            'platformVersion' => 'platform version ' . rand(1000, 9999),
            'clientName' => 'client name ' . rand(1000, 9999),
            'clientVersion' => 'client version ' . rand(1000, 9999),
        );

        $this->server->httpRequest->method('getHeader')->with('User-Agent')->willReturn($userAgent);

        $this->client->method('parse')->with($userAgent)->willReturn($clientInfo);
        $this->validator->expects($this->once())->method('isSupported')->with($clientInfo)->willReturn(false);

        /** @var Plugin $agentPlugin */
        $agentPlugin = new Plugin($this->validator, $this->client);
        $agentPlugin->initialize($this->server);
        $agentPlugin->checkSupportedClient();
    }

    /**
     * Checking the detection of supported client.
     *
     * @covers Sugarcrm\Sugarcrm\Dav\Cal\Agent\Plugin::checkSupportedClient
     */
    public function testCheckSupportedClientSuccess()
    {
        $userAgent = 'full string user agent ' . rand(1000, 9999);
        $clientInfo = array(
            'platformName' => 'platform name ' . rand(1000, 9999),
            'platformVersion' => 'platform version ' . rand(1000, 9999),
            'clientName' => 'client name ' . rand(1000, 9999),
            'clientVersion' => 'client version ' . rand(1000, 9999),
        );

        $this->server->httpRequest->method('getHeader')->with('User-Agent')->willReturn($userAgent);

        $this->client->method('parse')->with($userAgent)->willReturn($clientInfo);
        $this->validator->expects($this->once())->method('isSupported')->with($clientInfo)->willReturn(true);

        /** @var Plugin $agentPlugin */
        $agentPlugin = new Plugin($this->validator, $this->client);
        $agentPlugin->initialize($this->server);
        $agentPlugin->checkSupportedClient();
    }
}
