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

require_once 'modules/WebSockets/clients/base/api/WebSocketsApi.php';

use Sugarcrm\Sugarcrm\Socket\Client;

/**
 * Class WebSocketsApiTest
 *
 * @covers WebSocketsApi
 */
class WebSocketsApiTest extends Sugar_PHPUnit_Framework_TestCase
{
    /** @var  SugarTestRestServiceMock */
    protected $api;
    /** @var  User */
    protected $user;
    /** @var  WebSocketsApi|PHPUnit_Framework_MockObject_MockObject */
    protected $webSocketsApi;
    /** @var  Configurator|PHPUnit_Framework_MockObject_MockObject */
    protected $configurator;
    /** @var  Client|PHPUnit_Framework_MockObject_MockObject */
    protected $client;

    /**
     * {@inheritdoc}
     */
    protected function setUp()
    {
        parent::setUp();
        $this->api = SugarTestRestUtilities::getRestServiceMock();

        $this->user = new User;
        $this->user->id = create_guid();
        $this->user->is_admin = true;
        $this->api->user = $this->user;

        $this->webSocketsApi = $this->getMock('WebSocketsApi', array('getConfigurator'));
        $this->configurator = $this->getMock('Configurator');
        $this->webSocketsApi->method('getConfigurator')->willReturn($this->configurator);
        $this->client = $this->getMock('Sugarcrm\Sugarcrm\Socket\Client');
        SugarTestReflection::setProtectedValue('Sugarcrm\Sugarcrm\Socket\Client', 'instance', $this->client);
    }

    /**
     * {@inheritdoc}
     */
    protected function tearDown()
    {
        SugarTestReflection::setProtectedValue('Sugarcrm\Sugarcrm\Socket\Client', 'instance', null);
        parent::tearDown();
    }

    /**
     * Data provider for testConfigGet.
     *
     * @see WebSocketsApiTest::testConfigGet
     * @return array
     */
    public static function configGetProvider()
    {
        return array(
            'defaultConfig' => array(
                'config' => array(),
                'expected' => array(
                    'websockets_client_port' => 3001,
                    'websockets_client_protocol' => 'http',
                    'websockets_client_host' => '',
                    'websockets_server_port' => 2999,
                    'websockets_server_protocol' => 'http',
                    'websockets_server_host' => '',
                ),
            ),
            'httpsConfig' => array(
                'config' => array(
                    'websockets' => array(
                        'client' => array(
                            'url' => 'https://localhost:1999',
                        ),
                        'server' => array(
                            'url' => 'https://localhost:1001',
                        ),
                    ),
                ),
                'expected' => array(
                    'websockets_client_port' => 1999,
                    'websockets_client_protocol' => 'https',
                    'websockets_client_host' => 'localhost',
                    'websockets_server_port' => 1001,
                    'websockets_server_protocol' => 'https',
                    'websockets_server_host' => 'localhost',
                ),
            ),
            'httpConfig' => array(
                'config' => array(
                    'websockets' => array(
                        'client' => array(
                            'url' => 'http://www.domain.com:3999',
                        ),
                        'server' => array(
                            'url' => 'http://www.domain.com:4001',
                        ),
                    ),
                ),
                'expected' => array(
                    'websockets_client_port' => 3999,
                    'websockets_client_protocol' => 'http',
                    'websockets_client_host' => 'www.domain.com',
                    'websockets_server_port' => 4001,
                    'websockets_server_protocol' => 'http',
                    'websockets_server_host' => 'www.domain.com',
                ),
            ),
        );
    }

    /**
     * Should return right data from parsed url string.
     *
     * @covers       WebSocketsApi::configGet
     * @dataProvider configGetProvider
     * @param array $config Admin's configuration
     * @param array $expected Expected data
     */
    public function testConfigGet($config, $expected)
    {
        $this->configurator->config = $config;

        $res = $this->webSocketsApi->configGet($this->api, array());
        $this->assertEquals($expected, $res);
    }

    /**
     * Throws if user is not admin.
     *
     * @covers WebSocketsApi::configGet
     * @expectedException SugarApiExceptionNotAuthorized
     */
    public function testConfigGetThrowsIfUserNotAdmin()
    {
        $this->user->is_admin = false;

        $this->webSocketsApi->configGet($this->api, array());
    }

    /**
     * Data provider for testConfigSave.
     *
     * @see WebSocketsApiTest::testConfigSave
     * @return array
     */
    public static function configSaveProvider()
    {
        return array(
            'emptyConfig' => array(
                'args' => array(
                    'websockets_client_port' => 3001,
                    'websockets_client_protocol' => 'http',
                    'websockets_client_host' => '',
                    'websockets_server_port' => 2999,
                    'websockets_server_protocol' => 'http',
                    'websockets_server_host' => '',
                ),
                'map' => array(
                    array(
                        '',
                        array(
                            'available' => true,
                            'type' => 'client',
                            'isBalancer' => true,
                        ),
                    ),
                    array(
                        '',
                        array(
                            'available' => true,
                            'type' => 'server',
                        ),
                    ),
                ),
                'expected' => array(
                    'client' => array(
                        'url' => '',
                        'isBalancer' => false,
                    ),
                    'server' => array(
                        'url' => '',
                    ),
                ),
            ),
            'workingConfig' => array(
                'args' => array(
                    'websockets_client_port' => 3001,
                    'websockets_client_protocol' => 'http',
                    'websockets_client_host' => 'localhost',
                    'websockets_server_port' => 2999,
                    'websockets_server_protocol' => 'http',
                    'websockets_server_host' => 'localhost',
                ),
                'map' => array(
                    array(
                        'http://localhost:3001',
                        array(
                            'available' => true,
                            'type' => 'client',
                            'isBalancer' => true,
                        ),
                    ),
                    array(
                        'http://localhost:2999',
                        array(
                            'available' => true,
                            'type' => 'server',
                        ),
                    ),
                ),
                'expected' => array(
                    'client' => array(
                        'url' => 'http://localhost:3001',
                        'isBalancer' => true,
                    ),
                    'server' => array(
                        'url' => 'http://localhost:2999',
                    ),
                ),
            ),
        );
    }

    /**
     * Should correctly save config.
     *
     * @covers       WebSocketsApi::configSave
     * @dataProvider configSaveProvider
     * @param array $args Arguments data for save
     * @param array $map Arguments data for checking WebSockets urls
     * @param array $expected Expected data for successful saving
     */
    public function testConfigSave($args, $map, $expected)
    {
        $this->client->method('checkWSSettings')->willReturnMap($map);

        $config = null;
        $configurator = $this->configurator;
        $this->configurator->method('handleOverride')->willReturnCallback(
            function () use (&$config, $configurator) {
                $config = $configurator->config;
            }
        );

        $this->webSocketsApi->configSave($this->api, $args);
        $this->assertEquals($expected, $config['websockets']);
    }

    /**
     * Throws if client port is not isset.
     *
     * @covers WebSocketsApi::configSave
     * @expectedException SugarApiExceptionMissingParameter
     * @expectedExceptionMessage Missing parameter: websockets_client_port
     */
    public function testConfigSaveThrowsIfClientPortNotIsset()
    {
        $args = array(
            'websockets_client_protocol' => 'http',
            'websockets_client_host' => 'localhost',
            'websockets_server_port' => 2999,
            'websockets_server_protocol' => 'http',
            'websockets_server_host' => 'localhost',
        );

        $this->webSocketsApi->configSave($this->api, $args);
    }

    /**
     * Throws if client protocol is not isset.
     *
     * @covers WebSocketsApi::configSave
     * @expectedException SugarApiExceptionMissingParameter
     * @expectedExceptionMessage Missing parameter: websockets_client_protocol
     */
    public function testConfigSaveThrowsIfClientProtocolNotIsset()
    {
        $args = array(
            'websockets_client_port' => 3001,
            'websockets_client_host' => 'localhost',
            'websockets_server_port' => 2999,
            'websockets_server_protocol' => 'http',
            'websockets_server_host' => 'localhost',
        );

        $this->webSocketsApi->configSave($this->api, $args);
    }

    /**
     * Throws if client host is not isset.
     *
     * @covers WebSocketsApi::configSave
     * @expectedException SugarApiExceptionMissingParameter
     * @expectedExceptionMessage Missing parameter: websockets_client_host
     */
    public function testConfigSaveThrowsIfClientHostNotIsset()
    {
        $args = array(
            'websockets_client_port' => 3001,
            'websockets_client_protocol' => 'http',
            'websockets_server_port' => 2999,
            'websockets_server_protocol' => 'http',
            'websockets_server_host' => 'localhost',
        );

        $this->webSocketsApi->configSave($this->api, $args);
    }

    /**
     * Throws if server port is not isset.
     *
     * @covers WebSocketsApi::configSave
     * @expectedException SugarApiExceptionMissingParameter
     * @expectedExceptionMessage Missing parameter: websockets_server_port
     */
    public function testConfigSaveThrowsIfServerPortNotIsset()
    {
        $args = array(
            'websockets_client_port' => 3001,
            'websockets_client_protocol' => 'http',
            'websockets_client_host' => 'localhost',
            'websockets_server_protocol' => 'http',
            'websockets_server_host' => 'localhost',
        );

        $this->webSocketsApi->configSave($this->api, $args);
    }

    /**
     * Throws if server protocol is not isset.
     *
     * @covers WebSocketsApi::configSave
     * @expectedException SugarApiExceptionMissingParameter
     * @expectedExceptionMessage Missing parameter: websockets_server_protocol
     */
    public function testConfigSaveThrowsIfServerProtocolNotIsset()
    {
        $args = array(
            'websockets_client_port' => 3001,
            'websockets_client_protocol' => 'http',
            'websockets_client_host' => 'localhost',
            'websockets_server_port' => 2999,
            'websockets_server_host' => 'localhost',
        );

        $this->webSocketsApi->configSave($this->api, $args);
    }

    /**
     * Throws if server host is not isset.
     *
     * @covers WebSocketsApi::configSave
     * @expectedException SugarApiExceptionMissingParameter
     * @expectedExceptionMessage Missing parameter: websockets_server_host
     */
    public function testConfigSaveThrowsIfServerHostNotIsset()
    {
        $args = array(
            'websockets_client_port' => 3001,
            'websockets_client_protocol' => 'http',
            'websockets_client_host' => 'localhost',
            'websockets_server_protocol' => 'http',
            'websockets_server_port' => 2999,
        );

        $this->webSocketsApi->configSave($this->api, $args);
    }

    /**
     * Data provider for testConfigSaveThrowsIfServerUrlNotChecked.
     *
     * @see WebSocketsApiTest::testConfigSaveThrowsIfServerUrlNotChecked
     * @return array
     */
    public static function configCheckUrlsProvider()
    {
        return array(
            'notCheckedClientUrl' => array(
                'args' => array(
                    'websockets_client_port' => 3001,
                    'websockets_client_protocol' => 'http',
                    'websockets_client_host' => '',
                    'websockets_server_port' => 2999,
                    'websockets_server_protocol' => 'http',
                    'websockets_server_host' => 'localhost',
                ),
                'map' => array(
                    array(
                        'http://localhost:3001',
                        array(
                            'available' => true,
                            'type' => 'client',
                            'isBalancer' => true,
                        ),
                    ),
                    array(
                        'http://localhost:2999',
                        array(
                            'available' => true,
                            'type' => 'server',
                        ),
                    ),
                    array(
                        '',
                        array(
                            'available' => false,
                            'type' => '',
                        ),
                    ),
                ),
            ),
            'notCheckedServerUrl' => array(
                'args' => array(
                    'websockets_client_port' => 3001,
                    'websockets_client_protocol' => 'http',
                    'websockets_client_host' => 'localhost',
                    'websockets_server_port' => 2999,
                    'websockets_server_protocol' => 'http',
                    'websockets_server_host' => '',
                ),
                'map' => array(
                    array(
                        'http://localhost:3001',
                        array(
                            'available' => true,
                            'type' => 'client',
                            'isBalancer' => true,
                        ),
                    ),
                    array(
                        'http://localhost:2999',
                        array(
                            'available' => true,
                            'type' => 'server',
                        ),
                    ),
                    array(
                        '',
                        array(
                            'available' => false,
                            'type' => '',
                        ),
                    ),
                ),
            ),
        );
    }

    /**
     * Throws if client url or server url is not checked.
     *
     * @covers       WebSocketsApi::configSave
     * @dataProvider configCheckUrlsProvider
     * @param array $args Arguments data for save
     * @param array $map Arguments data for checking WebSockets urls
     * @expectedException SugarApiException
     */
    public function testConfigSaveThrowsIfServerUrlNotChecked($args, $map)
    {
        $this->client->method('checkWSSettings')->willReturnMap($map);

        $this->webSocketsApi->configSave($this->api, $args);
    }
}
