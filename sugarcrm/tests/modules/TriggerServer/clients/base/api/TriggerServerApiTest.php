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

require_once 'modules/TriggerServer/clients/base/api/TriggerServerApi.php';

use Sugarcrm\Sugarcrm\Trigger\Client;

/**
 * Class TriggerServerApiTest
 *
 * @covers TriggerServerApi
 */
class TriggerServerApiTest extends Sugar_PHPUnit_Framework_TestCase
{
    /** @var  SugarTestRestServiceMock */
    protected $api;
    /** @var  User */
    protected $user;
    /** @var  TriggerServerApi|PHPUnit_Framework_MockObject_MockObject */
    protected $triggerServerApi;
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

        $this->triggerServerApi = $this->getMock('TriggerServerApi', array('getConfigurator'));
        $this->configurator = $this->getMock('Configurator');
        $this->triggerServerApi->method('getConfigurator')->willReturn($this->configurator);
        $this->client = $this->getMock('Sugarcrm\Sugarcrm\Trigger\Client');
        SugarTestReflection::setProtectedValue('Sugarcrm\Sugarcrm\Trigger\Client', 'instance', $this->client);
    }

    /**
     * {@inheritdoc}
     */
    protected function tearDown()
    {
        SugarTestReflection::setProtectedValue('Sugarcrm\Sugarcrm\Trigger\Client', 'instance', null);
        parent::tearDown();
    }

    /**
     * Data provider for testConfigGet.
     *
     * @see TriggerServerApiTest::testConfigGet
     * @return array
     */
    public static function configGetProvider()
    {
        return array(
            'defaultConfig' => array(
                'config' => array(),
                'expected' => array(
                    'triggerserver_port' => 3000,
                    'triggerserver_protocol' => 'http',
                    'triggerserver_host' => '',
                ),
            ),
            'httpsConfig' => array(
                'config' => array(
                    'trigger_server' => array(
                        'url' => 'https://localhost:5000',
                    ),
                ),
                'expected' => array(
                    'triggerserver_port' => 5000,
                    'triggerserver_protocol' => 'https',
                    'triggerserver_host' => 'localhost',
                ),
            ),
            'httpConfig' => array(
                'config' => array(
                    'trigger_server' => array(
                        'url' => 'http://www.domain.com:3000',
                    ),
                ),
                'expected' => array(
                    'triggerserver_port' => 3000,
                    'triggerserver_protocol' => 'http',
                    'triggerserver_host' => 'www.domain.com',
                ),
            ),
        );
    }

    /**
     * Should return right data from parsed url string.
     *
     * @covers       TriggerServerApi::configGet
     * @dataProvider configGetProvider
     * @param array $config Admin's configuration
     * @param array $expected Expected data
     */
    public function testConfigGet($config, $expected)
    {
        $this->configurator->config = $config;

        $res = $this->triggerServerApi->configGet($this->api, array());
        $this->assertEquals($expected, $res);
    }

    /**
     * Throws if user is not admin.
     *
     * @covers       TriggerServerApi::configGet
     * @expectedException SugarApiExceptionNotAuthorized
     */
    public function testConfigGetThrowsIfUserNotAdmin()
    {
        $this->user->is_admin = false;

        $this->triggerServerApi->configGet($this->api, array());
    }

    /**
     * Data provider for testConfigSave.
     *
     * @see TriggerServerApiTest::testConfigSave
     * @return array
     */
    public static function configSaveProvider()
    {
        return array(
            'emptyConfig' => array(
                'args' => array(
                    'triggerserver_port' => 3000,
                    'triggerserver_protocol' => 'http',
                    'triggerserver_host' => '',
                ),
                'expected' => '',
            ),
            'workingConfig' => array(
                'args' => array(
                    'triggerserver_port' => 3000,
                    'triggerserver_protocol' => 'http',
                    'triggerserver_host' => 'localhost',
                ),
                'expected' => 'http://localhost:3000',
            ),
        );
    }

    /**
     * Should correctly save config.
     *
     * @covers       TriggerServerApi::configSave
     * @dataProvider configSaveProvider
     * @param array $args Arguments to save
     * @param array $expected Expected data
     */
    public function testConfigSave($args, $expected)
    {
        $this->client->method('checkTriggerServerSettings')->willReturn(true);

        $config = null;
        $configurator = $this->configurator;
        $this->configurator->method('handleOverride')->willReturnCallback(
            function () use (&$config, $configurator) {
                $config = $configurator->config;
            }
        );

        $this->triggerServerApi->configSave($this->api, $args);
        $this->assertEquals($expected, $config['trigger_server']['url']);
    }

    /**
     * Throws if port is not isset.
     *
     * @covers       TriggerServerApi::configSave
     * @expectedException SugarApiExceptionMissingParameter
     * @expectedExceptionMessage Missing parameter: triggerserver_port
     */
    public function testConfigSaveThrowsIfPortNotIsset()
    {
        $args = array(
            'triggerserver_protocol' => 'http',
            'triggerserver_host' => 'localhost',
        );

        $this->triggerServerApi->configSave($this->api, $args);
    }

    /**
     * Throws if protocol is not isset.
     *
     * @covers       TriggerServerApi::configSave
     * @expectedException SugarApiExceptionMissingParameter
     * @expectedExceptionMessage Missing parameter: triggerserver_protocol
     */
    public function testConfigSaveThrowsIfProtocolNotIsset()
    {
        $args = array(
            'triggerserver_port' => 3000,
            'triggerserver_host' => 'localhost',
        );

        $this->triggerServerApi->configSave($this->api, $args);
    }

    /**
     * Throws if host is not isset.
     *
     * @covers       TriggerServerApi::configSave
     * @expectedException SugarApiExceptionMissingParameter
     * @expectedExceptionMessage Missing parameter: triggerserver_host
     */
    public function testConfigSaveThrowsIfHostNotIsset()
    {
        $args = array(
            'triggerserver_port' => 3000,
            'triggerserver_protocol' => 'http',
        );

        $this->triggerServerApi->configSave($this->api, $args);
    }

    /**
     * Throws if server url is not checked.
     *
     * @covers       TriggerServerApi::configSave
     * @expectedException SugarApiException
     */
    public function testConfigSaveThrowsIfServerNotChecked()
    {
        $args = array(
            'triggerserver_port' => 3000,
            'triggerserver_protocol' => 'http',
            'triggerserver_host' => 'localhost',
        );

        $this->client->method('checkTriggerServerSettings')->willReturn(false);
        $this->triggerServerApi->configSave($this->api, $args);
    }
}
