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

namespace Sugarcrm\SugarcrmTests\modules\Administration;

use AdministrationController;
use Configurator;
use Sugarcrm\Sugarcrm\Socket\Client as SocketClient;
use Sugarcrm\Sugarcrm\Trigger\Client as TriggerClient;

require_once 'modules/Administration/controller.php';

class AdministrationControllerCRYS1262Test extends \Sugar_PHPUnit_Framework_TestCase
{
    /** @var AdministrationController|\PHPUnit_Framework_MockObject_MockObject */
    protected $controller = null;

    /** @var Configurator|\PHPUnit_Framework_MockObject_MockObject */
    protected $cfg = null;

    /** @var SocketClient|\PHPUnit_Framework_MockObject_MockObject */
    protected $socketClient = null;

    /** @var TriggerClient|\PHPUnit_Framework_MockObject_MockObject */
    protected $triggerClient = null;

    /** @var array */
    protected $backup = array(
        '_REQUEST' => array(),
        'SocketClient' => '',
        'TriggerClient' => '',
        'site_url' => '',
    );

    /**
     * @inheritDoc
     */
    protected function setUp()
    {
        $this->backup['site_url'] = $GLOBALS['sugar_config']['site_url'];
        $this->backup['_REQUEST'] = $_REQUEST;
        $this->backup['SocketClient'] = \SugarTestReflection::getProtectedValue('Sugarcrm\Sugarcrm\Socket\Client', 'instance');
        $this->backup['TriggerClient'] = \SugarTestReflection::getProtectedValue('Sugarcrm\Sugarcrm\Trigger\Client', 'instance');

        $this->controller = $this->getMock('AdministrationController', array('getConfigurator'), array(), '', false);

        $this->cfg = $this->getMock('Configurator', array('handleOverride'), array(), '', false);
        $this->controller->expects($this->any())->method('getConfigurator')->willReturn($this->cfg);

        $this->triggerClient = $this->getMock('Sugarcrm\Sugarcrm\Trigger\Client', array('checkTriggerServerSettings'));
        \SugarTestReflection::setProtectedValue('Sugarcrm\Sugarcrm\Trigger\Client', 'instance', $this->triggerClient);

        $this->socketClient = $this->getMock('Sugarcrm\Sugarcrm\Socket\Client', array('checkWSSettings'));
        \SugarTestReflection::setProtectedValue('Sugarcrm\Sugarcrm\Socket\Client', 'instance', $this->socketClient);

        \SugarTestHelper::setUp('mod_strings', array('Administration'));
        parent::setUp();
    }

    /**
     * @inheritDoc
     */
    protected function tearDown()
    {
        parent::tearDown();
        \SugarTestHelper::tearDown();

        \SugarTestReflection::setProtectedValue('Sugarcrm\Sugarcrm\Trigger\Client', 'instance', $this->backup['TriggerClient']);
        \SugarTestReflection::setProtectedValue('Sugarcrm\Sugarcrm\Socket\Client', 'instance', $this->backup['SocketClient']);
        $_REQUEST = $this->backup['_REQUEST'];
        $GLOBALS['sugar_config']['site_url'] = $this->backup['site_url'];
    }

    /**
     * Data provider for testActionSaveWebSocketsConfiguration
     *
     * @see AdministrationControllerCRYS1262Test::testActionSaveWebSocketsConfiguration
     * @return array
     */
    public static function actionSaveWebSocketsConfigurationProvider()
    {
        $instanceUrl = 'http://instance.url';
        $clientUrl = 'http://socket.url:' . rand(1000, 9000);
        $serverUrl = 'http://socket.url:' . rand(1000, 9000);

        return array(
            'savesEmptyValues' => array(
                'instanceUrl' => $instanceUrl,
                'clientUrl' => '',
                'serverUrl' => '',
                'checkResultMap' => array(),
                'expectedResult' => array(
                    'status' => true,
                ),
                'expectedConfig' => array(
                    'websockets' => array(
                        'server' => array(
                            'url' => '',
                        ),
                        'client' => array(
                            'url' => '',
                            'balancer' => false,
                        ),
                    ),
                ),
            ),
            'savesValidValues' => array(
                'instanceUrl' => $instanceUrl,
                'clientUrl' => $clientUrl,
                'serverUrl' => $serverUrl,
                'checkResultMap' => array(
                    array($clientUrl, array(
                        'url' => $clientUrl,
                        'type' => 'client',
                        'available' => true,
                        'isBalancer' => false,
                    )),
                    array($serverUrl, array(
                        'url' => $serverUrl,
                        'type' => 'server',
                        'available' => true,
                        'isBalancer' => false,
                    )),
                ),
                'expectedResult' => array(
                    'status' => true,
                ),
                'expectedConfig' => array(
                    'websockets' => array(
                        'server' => array(
                            'url' => $serverUrl,
                        ),
                        'client' => array(
                            'url' => $clientUrl,
                            'balancer' => false,
                        ),
                    ),
                ),
            ),
            'savesValidValuesAndBalancer' => array(
                'instanceUrl' => $instanceUrl,
                'clientUrl' => $clientUrl,
                'serverUrl' => $serverUrl,
                'checkResultMap' => array(
                    array($clientUrl, array(
                        'url' => $clientUrl,
                        'type' => 'client',
                        'available' => true,
                        'isBalancer' => true,
                    )),
                    array($serverUrl, array(
                        'url' => $serverUrl,
                        'type' => 'server',
                        'available' => true,
                        'isBalancer' => false,
                    )),
                ),
                'expectedResult' => array(
                    'status' => true,
                ),
                'expectedConfig' => array(
                    'websockets' => array(
                        'server' => array(
                            'url' => $serverUrl,
                        ),
                        'client' => array(
                            'url' => $clientUrl,
                            'balancer' => true,
                        ),
                    ),
                ),
            ),
            'errorOnEmptyClient' => array(
                'instanceUrl' => $instanceUrl,
                'clientUrl' => '',
                'serverUrl' => $serverUrl,
                'checkResultMap' => array(
                    array($serverUrl, array(
                        'url' => $serverUrl,
                        'type' => 'server',
                        'available' => true,
                        'isBalancer' => false,
                    )),
                ),
                'expectedResult' => array(
                    'status' => false,
                    'errMsg' => 'WebSocket Client URL required',
                ),
                'expectedConfig' => array(),
            ),
            'errorOnEmptyServer' => array(
                'instanceUrl' => $instanceUrl,
                'clientUrl' => $clientUrl,
                'serverUrl' => '',
                'checkResultMap' => array(
                    array($clientUrl, array(
                        'url' => $clientUrl,
                        'type' => 'client',
                        'available' => true,
                        'isBalancer' => false,
                    )),
                ),
                'expectedResult' => array(
                    'status' => false,
                    'errMsg' => 'WebSocket Server URL required',
                ),
                'expectedConfig' => array(),
            ),
            'errorOnInvalidClientType' => array(
                'instanceUrl' => $instanceUrl,
                'clientUrl' => $clientUrl,
                'serverUrl' => $serverUrl,
                'checkResultMap' => array(
                    array($clientUrl, array(
                        'url' => $clientUrl,
                        'type' => 'server',
                        'available' => true,
                        'isBalancer' => false,
                    )),
                    array($serverUrl, array(
                        'url' => $serverUrl,
                        'type' => 'server',
                        'available' => true,
                        'isBalancer' => false,
                    )),
                ),
                'expectedResult' => array(
                    'status' => false,
                    'errMsg' => 'Unable to connect to WebSocket Client URL',
                ),
                'expectedConfig' => array(),
            ),
            'errorOnInvalidServerType' => array(
                'instanceUrl' => $instanceUrl,
                'clientUrl' => $clientUrl,
                'serverUrl' => $serverUrl,
                'checkResultMap' => array(
                    array($clientUrl, array(
                        'url' => $clientUrl,
                        'type' => 'client',
                        'available' => true,
                        'isBalancer' => false,
                    )),
                    array($serverUrl, array(
                        'url' => $serverUrl,
                        'type' => 'client',
                        'available' => true,
                        'isBalancer' => false,
                    )),
                ),
                'expectedResult' => array(
                    'status' => false,
                    'errMsg' => 'Unable to connect to WebSocket Server URL',
                ),
                'expectedConfig' => array(),
            ),
            'errorOnInvalidClient' => array(
                'instanceUrl' => $instanceUrl,
                'clientUrl' => $clientUrl,
                'serverUrl' => $serverUrl,
                'checkResultMap' => array(
                    array($clientUrl, array(
                        'url' => $clientUrl,
                        'type' => '',
                        'available' => false,
                        'isBalancer' => false,
                    )),
                    array($serverUrl, array(
                        'url' => $serverUrl,
                        'type' => 'server',
                        'available' => true,
                        'isBalancer' => false,
                    )),
                ),
                'expectedResult' => array(
                    'status' => false,
                    'errMsg' => 'Unable to connect to WebSocket Client URL',
                ),
                'expectedConfig' => array(),
            ),
            'errorOnInvalidServer' => array(
                'instanceUrl' => $instanceUrl,
                'clientUrl' => $clientUrl,
                'serverUrl' => $serverUrl,
                'checkResultMap' => array(
                    array($clientUrl, array(
                        'url' => $clientUrl,
                        'type' => 'client',
                        'available' => true,
                        'isBalancer' => false,
                    )),
                    array($serverUrl, array(
                        'url' => $serverUrl,
                        'type' => '',
                        'available' => false,
                        'isBalancer' => false,
                    )),
                ),
                'expectedResult' => array(
                    'status' => false,
                    'errMsg' => 'Unable to connect to WebSocket Server URL',
                ),
                'expectedConfig' => array(),
            ),
            'savesAndShowsWarningWhenClientIsLocalhost' => array(
                'instanceUrl' => $instanceUrl,
                'clientUrl' => 'http://localhost:3001',
                'serverUrl' => $serverUrl,
                'checkResultMap' => array(
                    array('http://localhost:3001', array(
                        'url' => 'http://localhost:3001',
                        'type' => 'client',
                        'available' => true,
                        'isBalancer' => false,
                    )),
                    array($serverUrl, array(
                        'url' => $serverUrl,
                        'type' => 'server',
                        'available' => true,
                        'isBalancer' => false,
                    )),
                ),
                'expectedResult' => array(
                    'status' => true,
                    'warnMsg' => 'Client URL of Socket Server points to local host. It will work only on local machine.',
                ),
                'expectedConfig' => array(
                    'websockets' => array(
                        'server' => array(
                            'url' => $serverUrl,
                        ),
                        'client' => array(
                            'url' => 'http://localhost:3001',
                            'balancer' => false,
                        ),
                    ),
                ),
            ),
            'savesAndShowsWarningWhenServerIsNotLocalhostButInstanceIs' => array(
                'instanceUrl' => 'http://localhost',
                'clientUrl' => $clientUrl,
                'serverUrl' => $serverUrl,
                'checkResultMap' => array(
                    array($clientUrl, array(
                        'url' => $clientUrl,
                        'type' => 'client',
                        'available' => true,
                        'isBalancer' => false,
                    )),
                    array($serverUrl, array(
                        'url' => $serverUrl,
                        'type' => 'server',
                        'available' => true,
                        'isBalancer' => false,
                    )),
                ),
                'expectedResult' => array(
                    'status' => true,
                    'warnMsg' => 'URL of the instance points to local host but Socket Server is located remotely and can\'t reach the instance.',
                ),
                'expectedConfig' => array(
                    'websockets' => array(
                        'server' => array(
                            'url' => $serverUrl,
                        ),
                        'client' => array(
                            'url' => $clientUrl,
                            'balancer' => false,
                        ),
                    ),
                ),
            ),
            'savesAndDoesNotShowsWarningWhenServerIsLocalhostAndInstanceIs' => array(
                'instanceUrl' => 'http://localhost',
                'clientUrl' => $clientUrl,
                'serverUrl' => 'http://localhost:2999',
                'checkResultMap' => array(
                    array($clientUrl, array(
                        'url' => $clientUrl,
                        'type' => 'client',
                        'available' => true,
                        'isBalancer' => false,
                    )),
                    array('http://localhost:2999', array(
                        'url' => 'http://localhost:2999',
                        'type' => 'server',
                        'available' => true,
                        'isBalancer' => false,
                    )),
                ),
                'expectedResult' => array(
                    'status' => true,
                ),
                'expectedConfig' => array(
                    'websockets' => array(
                        'server' => array(
                            'url' => 'http://localhost:2999',
                        ),
                        'client' => array(
                            'url' => $clientUrl,
                            'balancer' => false,
                        ),
                    ),
                ),
            ),
        );
    }

    /**
     * Covers all cases by data provider
     *
     * @covers AdministrationController::action_saveWebSocketsConfiguration
     * @dataProvider actionSaveWebSocketsConfigurationProvider
     * @param string $instanceUrl
     * @param string $clientUrl
     * @param string $serverUrl
     * @param array $checkResultMap
     * @param bool $expectedResult
     * @param array $expectedConfig
     */
    public function testActionSaveWebSocketsConfiguration($instanceUrl, $clientUrl, $serverUrl, $checkResultMap, $expectedResult, $expectedConfig)
    {
        $GLOBALS['sugar_config']['site_url'] = $instanceUrl;
        $_REQUEST['websocket_client_url'] = $clientUrl;
        $_REQUEST['websocket_server_url'] = $serverUrl;

        $checkResultMap[] = array('', array(
            'url' => '',
            'type' => '',
            'available' => false,
            'isBalancer' => false,
        ));
        $this->socketClient
            ->method('checkWSSettings')
            ->willReturnMap($checkResultMap);

        $config = array();
        $cfg = $this->cfg;
        $condition = $this->never();
        if ($expectedConfig) {
            $condition = $this->atLeastOnce();
        }
        $this->cfg->expects($condition)->method('handleOverride')->willReturnCallback(function() use ($cfg, &$config) {
            $config = $cfg->config;
        });

        $this->controller->action_saveWebSocketsConfiguration();
        $output = $this->getActualOutput();
        $this->expectOutputString($output); // Hack to shut up phpunit
        $result = json_decode($output, true);

        $this->assertEquals($expectedResult, $result);
        if ($expectedConfig) {
            $this->assertEquals($expectedConfig, $config);
        }
    }

    /**
     * Data provider for testActionSaveTriggerServerConfiguration
     *
     * @see AdministrationControllerCRYS1262Test::testActionSaveTriggerServerConfiguration
     * @return array
     */
    public static function actionSaveTriggerServerConfigurationProvider()
    {
        $instanceUrl = 'http://instance.url';
        $triggerUrl = 'http://trigger.url:' . rand(1000, 9000);
        return array(
            'savesEmptyValues' => array(
                'instanceUrl' => $instanceUrl,
                'triggerUrl' => '',
                'checkResultMap' => array(),
                'expectedResult' => array(
                    'status' => true,
                ),
                'expectedConfig' => array(
                    'trigger_server' => array(
                        'url' => '',
                    ),
                ),
            ),
            'savesValidValues' => array(
                'instanceUrl' => $instanceUrl,
                'triggerUrl' => $triggerUrl,
                'checkResultMap' => array(
                    array($triggerUrl, true),
                ),
                'expectedResult' => array(
                    'status' => true,
                ),
                'expectedConfig' => array(
                    'trigger_server' => array(
                        'url' => $triggerUrl,
                    ),
                ),
            ),
            'errorOnInvalidUrl' => array(
                'instanceUrl' => $instanceUrl,
                'triggerUrl' => $triggerUrl,
                'checkResultMap' => array(
                    array($triggerUrl, false),
                ),
                'expectedResult' => array(
                    'status' => false,
                    'errMsg' => 'Unable to connect to Trigger Server',
                ),
                'expectedConfig' => array(),
            ),
            'savesAndShowsWarningWhenTriggerIsNotLocalhostButInstanceIs' => array(
                'instanceUrl' => 'http://localhost',
                'triggerUrl' => $triggerUrl,
                'checkResultMap' => array(
                    array($triggerUrl, true),
                ),
                'expectedResult' => array(
                    'status' => true,
                    'warnMsg' => 'URL of the instance points to local host but Trigger Server is located remotely and can\'t reach the instance.',
                ),
                'expectedConfig' => array(
                    'trigger_server' => array(
                        'url' => $triggerUrl,
                    ),
                ),
            ),
            'savesAndDoesNotShowsWarningWhenTriggerIsLocalhostAndInstanceIs' => array(
                'instanceUrl' => 'http://localhost',
                'triggerUrl' => 'http://localhost:3000',
                'checkResultMap' => array(
                    array('http://localhost:3000', true),
                ),
                'expectedResult' => array(
                    'status' => true,
                ),
                'expectedConfig' => array(
                    'trigger_server' => array(
                        'url' => 'http://localhost:3000',
                    ),
                ),
            ),
        );
    }

    /**
     * Covers all cases by data provider
     *
     * @covers AdministrationController::action_saveTriggerServerConfiguration
     * @dataProvider actionSaveTriggerServerConfigurationProvider
     * @param string $instanceUrl
     * @param string $triggerUrl
     * @param array $checkResultMap
     * @param bool $expectedResult
     * @param array $expectedConfig
     */
    public function testActionSaveTriggerServerConfiguration($instanceUrl, $triggerUrl, $checkResultMap, $expectedResult, $expectedConfig)
    {
        $GLOBALS['sugar_config']['site_url'] = $instanceUrl;
        $_REQUEST['trigger_server_url'] = $triggerUrl;

        $checkResultMap[] = array('', false);
        $this->triggerClient
            ->method('checkTriggerServerSettings')
            ->willReturnMap($checkResultMap);

        $config = array();
        $cfg = $this->cfg;
        $condition = $this->never();
        if ($expectedConfig) {
            $condition = $this->atLeastOnce();
        }
        $this->cfg->expects($condition)->method('handleOverride')->willReturnCallback(function() use ($cfg, &$config) {
            $config = $cfg->config;
        });

        $this->controller->action_saveTriggerServerConfiguration();
        $output = $this->getActualOutput();
        $this->expectOutputString($output); // Hack to shut up phpunit
        $result = json_decode($output, true);

        $this->assertEquals($expectedResult, $result);
        if ($expectedConfig) {
            $this->assertEquals($expectedConfig, $config);
        }
    }
}
