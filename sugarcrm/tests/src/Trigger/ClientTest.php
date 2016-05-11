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

namespace Sugarcrm\SugarcrmTests\Trigger;

use Sugarcrm\Sugarcrm\Trigger\Client;
use Sugarcrm\SugarcrmTests\clients\base\api\AdministrationCRYS1259;
use Sugarcrm\SugarcrmTestsUnit\TestReflection;

/**
 * Class ClientTest
 * @package Sugarcrm\SugarcrmTestsUnit\Trigger
 * @covers \Sugarcrm\Sugarcrm\Trigger\Client
 */
class ClientTest extends \PHPUnit_Framework_TestCase
{
    const SITE_URL = 'http://dummy-site';

    /** @var \Sugarcrm\Sugarcrm\Trigger\HttpHelper|\PHPUnit_Framework_MockObject_MockObject */
    protected $httpHelper;

    /** @var \Sugarcrm\Sugarcrm\Trigger\Client|\PHPUnit_Framework_MockObject_MockObject */
    protected $client;

    /** @var \SugarConfig|\PHPUnit_Framework_MockObject_MockObject */
    protected $sugarConfig = null;

    /** @var \LoggerManager|\PHPUnit_Framework_MockObject_MockObject */
    protected $loggerManager = null;

    /**
     * @inheritDoc
     */
    protected function setUp()
    {
        parent::setUp();

        $this->httpHelper = $this->getMock('Sugarcrm\Sugarcrm\Trigger\HttpHelper');
        $this->sugarConfig = $this->getMock('SugarConfig');
        $this->loggerManager = $this->getMockBuilder('LoggerManager')
            ->setMethods(array('error'))
            ->disableOriginalConstructor()
            ->getMock();

        $this->client = $this->getMock(
            'Sugarcrm\Sugarcrm\Trigger\Client',
            array('getSugarConfig', 'getLogger', 'getHttpHelper', 'createGuid')
        );
        $this->client->method('getLogger')->willReturn($this->loggerManager);
        $this->client->method('getSugarConfig')->willReturn($this->sugarConfig);
        $this->client->method('getHttpHelper')->willReturn($this->httpHelper);

        AdministrationCRYS1494::$saveSetting['calls'] = array();
        AdministrationCRYS1494::$getConfigForModule['calls'] = array();
        \BeanFactory::setBeanClass('Administration', '\Sugarcrm\SugarcrmTests\Trigger\AdministrationCRYS1494');
    }

    /**
     * @inheritDoc
     */
    protected function tearDown()
    {
        \BeanFactory::setBeanClass('Administration');

        parent::tearDown();
    }

    /**
     * IsConfigured provider.
     *
     * @see Sugarcrm\SugarcrmTests\Trigger\ClientTest::testIsConfigured
     * @return array
     */
    public static function isConfiguredProvider()
    {
        return array(
            'configuredTriggerServerUrl' => array(
                'triggerServerUrl' => 'http://dummy-trigger-server',
                'expectsIsConfigured' => true,
            ),
            'emptyTriggerServerUrl' => array(
                'triggerServerUrl' => null,
                'expectsIsConfigured' => false,
            ),
        );
    }

    /**
     * Testing is configured.
     *
     * @dataProvider isConfiguredProvider
     * @covers Sugarcrm\Sugarcrm\Trigger\Client::isConfigured
     * @param string $triggerServerUrl trigger server url saved in config.
     * @param bool $expectsIsConfigured expects IsConfigured result.
     */
    public function testIsConfigured($triggerServerUrl, $expectsIsConfigured)
    {
        $this->sugarConfig->method('get')
            ->willReturnMap(array(
                array('trigger_server.url', null, $triggerServerUrl),
            ));

        $this->client->method('getSugarConfig')
            ->willReturn($this->sugarConfig);

        $this->assertEquals($expectsIsConfigured, $this->client->isConfigured());
    }

    /**
     * Testing case when trigger server not configured.
     *
     * @covers Sugarcrm\Sugarcrm\Trigger\Client::push
     */
    public function testPushNotConfigured()
    {
        $this->sugarConfig->method('get')->willReturnMap(array(
            array('trigger_server.url', null, null),
            array('site_url', null, ClientTest::SITE_URL),
        ));

        $this->loggerManager->expects($this->once())
            ->method('error')
            ->with($this->equalTo('Trigger\\Client::push - attempt to use client which is not configured.'));

        $this->httpHelper
            ->expects($this->never())
            ->method('send');

        $result = $this->client->push(rand(1000, 9999), rand(1000, 9999), rand(1000, 9999), rand(1000, 9999));

        $this->assertFalse($result);
    }

    /**
     * Data provider for testPush.
     *
     * @see \Sugarcrm\SugarcrmTests\Trigger\ClientTest::testPush
     * @return array
     */
    public static function providerPush()
    {
        $params = array(
            'id' => 'dummy-id' . rand(1000, 999),
            'stamp' => 'dummy-stamp' . rand(1000, 999),
            'uri' => '/dummy-uri',
        );

        $args = array(
            'stringArg' => 'dummy-string' . rand(1000, 999),
            'intArg' => 132,
        );
        $tags = array(
            'tag 1-' . rand(1000, 999),
            'tag 2-' . rand(1000, 999),
        );

        $token1 = 'token:1:' . rand(1000, 9999);
        $token2 = 'token:2:' . rand(1000, 9999);
        $token3 = 'token:3:' . rand(1000, 9999);

        return array(
            'methodGetWithoutArgumentsRetrieveTokenUrlServerUrlWithSlash' => array(
                'arguments' => array(
                    'pushArguments' => array(
                        'id' => $params['id'],
                        'stamp' => $params['stamp'],
                        'uri' => $params['uri'],
                        'method' => 'get',
                        'arguments' => null,
                        'tags' => null,
                    ),
                    'serverUrl' => 'http://dummy-trigger-server/',
                    'token' => $token3,
                ),
                'getConfigForModule' => array(
                    'arguments' => array('auth', 'base', true),
                    'returns' => null,
                ),
                'saveSetting' => array(
                    'arguments' => array('auth', 'external_token_trigger', $token3, 'base'),
                    'returns' => null,
                ),
                'expected' => array(
                    'method' => Client::POST_METHOD,
                    'serverUrl' => 'http://dummy-trigger-server/',
                    'message' => json_encode(
                        array(
                            'url' => static::SITE_URL,
                            'id' => $params['id'],
                            'stamp' => $params['stamp'],
                            'trigger' => array(
                                'url' => $params['uri'],
                                'method' => 'get',
                            ),
                        )
                    ),
                    'headers' => array(
                        Client::AUTH_TOKEN_HEADER . ': ' . $token3,
                        Client::AUTH_VERSION_HEADER . ': ' . Client::AUTH_VERSION,
                    ),
                ),
            ),
            'methodPostWithArgumentsWithExistingTokenServerUrlWithoutSlash' => array(
                'arguments' => array(
                    'pushArguments' => array(
                        'id' => $params['id'],
                        'stamp' => $params['stamp'],
                        'uri' => $params['uri'],
                        'method' => 'post',
                        'arguments' => $args,
                        'tags' => null,
                    ),
                    'serverUrl' => 'http://dummy-trigger-server.com',
                ),
                'getConfigForModule' => array(
                    'arguments' => array('auth', 'base', true),
                    'returns' => array('external_token_trigger' => $token1),
                ),
                'saveSetting' => array(
                    'arguments' => null,
                    'returns' => null,
                ),
                'expected' => array(
                    'method' => Client::POST_METHOD,
                    'serverUrl' => 'http://dummy-trigger-server.com/',
                    'message' => json_encode(
                        array(
                            'url' => static::SITE_URL,
                            'id' => $params['id'],
                            'stamp' => $params['stamp'],
                            'trigger' => array(
                                'url' => $params['uri'],
                                'method' => 'post',
                                'args' => $args,
                            ),
                        )
                    ),
                    'headers' => array(
                        Client::AUTH_TOKEN_HEADER . ': ' . $token1,
                        Client::AUTH_VERSION_HEADER . ': ' . Client::AUTH_VERSION,
                    ),
                ),
            ),
            'methodGetWithArgumentsWithExistingTokenServerUrlWithSlash' => array(
                'arguments' => array(
                    'pushArguments' => array(
                        'id' => $params['id'],
                        'stamp' => $params['stamp'],
                        'uri' => $params['uri'],
                        'method' => 'get',
                        'arguments' => $args,
                        'tags' => null,
                    ),
                    'serverUrl' => 'http://dummy-trigger-server.com:8080/',
                ),
                'getConfigForModule' => array(
                    'arguments' => array('auth', 'base', true),
                    'returns' => array('external_token_trigger' => $token2),
                ),
                'saveSetting' => array(
                    'arguments' => null,
                    'returns' => null,
                ),
                'expected' => array(
                    'method' => Client::POST_METHOD,
                    'serverUrl' => 'http://dummy-trigger-server.com:8080/',
                    'message' => json_encode(
                        array(
                            'url' => static::SITE_URL,
                            'id' => $params['id'],
                            'stamp' => $params['stamp'],
                            'trigger' => array(
                                'url' => $params['uri'],
                                'method' => 'get',
                            ),
                        )
                    ),
                    'headers' => array(
                        Client::AUTH_TOKEN_HEADER . ': ' . $token2,
                        Client::AUTH_VERSION_HEADER . ': ' . Client::AUTH_VERSION,
                    ),
                ),
            ),
            'methodPostWithTagsWithExistingTokenServerUrlWithoutSlashWithPort' => array(
                'arguments' => array(
                    'pushArguments' => array(
                        'id' => $params['id'],
                        'stamp' => $params['stamp'],
                        'uri' => $params['uri'],
                        'method' => 'post',
                        'arguments' => null,
                        'tags' => $tags,
                    ),
                    'serverUrl' => 'http://dummy-trigger-server.com:8080',
                ),
                'getConfigForModule' => array(
                    'arguments' => array('auth', 'base', true),
                    'returns' => array('external_token_trigger' => $token1),
                ),
                'saveSetting' => array(
                    'arguments' => null,
                    'returns' => null,
                ),
                'expected' => array(
                    'method' => Client::POST_METHOD,
                    'serverUrl' => 'http://dummy-trigger-server.com:8080/',
                    'message' => json_encode(
                        array(
                            'url' => static::SITE_URL,
                            'id' => $params['id'],
                            'stamp' => $params['stamp'],
                            'trigger' => array(
                                'url' => $params['uri'],
                                'method' => 'post',
                            ),
                            'tags' => $tags
                        )
                    ),
                    'headers' => array(
                        Client::AUTH_TOKEN_HEADER . ': ' . $token1,
                        Client::AUTH_VERSION_HEADER . ': ' . Client::AUTH_VERSION,
                    ),
                ),
            ),
        );
    }

    /**
     * Testing adding or updating triggers tasks.
     *
     * @dataProvider providerPush
     * @covers Sugarcrm\Sugarcrm\Trigger\Client::push
     * @param array $arguments all arguments for test(Arguments for push, configured serverUrl, generated token).
     * @param array $getConfigForModule settings for mock simulator \Administration::saveSetting.
     * @param array $saveSetting settings for mock simulator \Administration::getConfigForModule.
     * @param array $expected all all expectations(http method, server url, generated message).
     */
    public function testPush($arguments, $getConfigForModule, $saveSetting, $expected)
    {
        $this->sugarConfig->method('get')->willReturnMap(array(
            array('trigger_server.url', null, $arguments['serverUrl']),
            array('site_url', null, ClientTest::SITE_URL),
        ));

        AdministrationCRYS1494::$saveSetting['returns'] = $saveSetting['returns'];
        AdministrationCRYS1494::$getConfigForModule['returns'] = $getConfigForModule['returns'];

        if (!empty($arguments['token'])) {
            $this->client->method('createGuid')->willReturn($arguments['token']);
        }

        $this->httpHelper
            ->expects($this->once())
            ->method('send')
            ->with(
                $this->equalTo($expected['method']),
                $this->equalTo($expected['serverUrl']),
                $this->equalTo($expected['message']),
                $this->equalTo($expected['headers'])
            )
            ->willReturn(true);

        $result = $this->client->push(
            $arguments['pushArguments']['id'],
            $arguments['pushArguments']['stamp'],
            $arguments['pushArguments']['method'],
            $arguments['pushArguments']['uri'],
            $arguments['pushArguments']['arguments'],
            $arguments['pushArguments']['tags']
        );

        $this->assertTrue($result);

        if (!empty($saveSetting['arguments'])) {
            $this->assertCount(1, AdministrationCRYS1494::$saveSetting['calls']);
            foreach (AdministrationCRYS1494::$saveSetting['calls'][0] as $key => $value) {
                $this->assertEquals($saveSetting['arguments'][$key], $value);
            }
        }

        if (!empty($getConfigForModule['arguments'])) {
            $this->assertCount(1, AdministrationCRYS1494::$getConfigForModule['calls']);

            foreach (AdministrationCRYS1494::$getConfigForModule['calls'][0] as $key => $value) {
                $this->assertEquals($getConfigForModule['arguments'][$key], $value);
            }
        }
    }

    /**
     * Testing case when trigger server not configured.
     *
     * @covers Sugarcrm\Sugarcrm\Trigger\Client::delete
     */
    public function testDeleteNotConfigured()
    {
        $triggerId = 'trigger:id:' . rand(1000, 9999);

        $this->sugarConfig->method('get')->will($this->returnValueMap(array(
            array('trigger_server.url', null, null),
            array('site_url', null, ClientTest::SITE_URL),
        )));

        $this->loggerManager->expects($this->once())
            ->method('error')
            ->with($this->equalTo('Trigger\\Client::push - attempt to use client which is not configured.'));

        $this->httpHelper
            ->expects($this->never())
            ->method('send');

        $result = $this->client->delete($triggerId);

        $this->assertFalse($result);
    }

    /**
     * Data provider for testDelete.
     *
     * @see \Sugarcrm\SugarcrmTests\Trigger\ClientTest::testPush
     * @return array
     */
    public static function deleteProvider()
    {
        $id1 = 'trigger:id:1:' . rand(1000, 9999);
        $id2 = 'trigger:id:2:' . rand(1000, 9999);

        $token1 = 'token:1:' . rand(1000, 9999);
        $token2 = 'token:2:' . rand(1000, 9999);

        return array(
            'retrieveTokenUrlServerUrlWithSlash' => array(
                'arguments' => array(
                    'id' => $id1,
                    'serverUrl' => 'http://dummy-trigger-server/',
                    'token' => $token1,
                ),
                'getConfigForModule' => array(
                    'arguments' => array('auth', 'base', true),
                    'returns' => null,
                ),
                'saveSetting' => array(
                    'arguments' => array('auth', 'external_token_trigger', $token1, 'base'),
                    'returns' => null,
                ),
                'expected' => array(
                    'serverUrl' => 'http://dummy-trigger-server/',
                    'message' => json_encode(
                        array(
                            'url' => static::SITE_URL,
                            'id' => $id1,
                        )
                    ),
                    'headers' => array(
                        Client::AUTH_TOKEN_HEADER . ': ' . $token1,
                        Client::AUTH_VERSION_HEADER . ': ' . Client::AUTH_VERSION,
                    ),
                ),
            ),
            'existingTokenUrlServerUrlWithoutSlash' => array(
                'arguments' => array(
                    'id' => $id2,
                    'serverUrl' => 'http://dummy-trigger-server',
                    'token' => null,
                ),
                'getConfigForModule' => array(
                    'arguments' => array('auth', 'base', true),
                    'returns' => array('external_token_trigger' => $token2),
                ),
                'saveSetting' => array(
                    'arguments' => null,
                    'returns' => null,
                ),
                'expected' => array(
                    'serverUrl' => 'http://dummy-trigger-server/',
                    'message' => json_encode(
                        array(
                            'url' => static::SITE_URL,
                            'id' => $id2,
                        )
                    ),
                    'headers' => array(
                        Client::AUTH_TOKEN_HEADER . ': ' . $token2,
                        Client::AUTH_VERSION_HEADER . ': ' . Client::AUTH_VERSION,
                    ),
                ),
            )
        );
    }

    /**
     * Testing deleting triggers tasks.
     *
     * @covers Sugarcrm\Sugarcrm\Trigger\Client::delete
     * @dataProvider deleteProvider
     * @param array $arguments all arguments for test(Arguments for push, configured serverUrl, generated token).
     * @param array $getConfigForModule settings for mock simulator \Administration::saveSetting.
     * @param array $saveSetting settings for mock simulator \Administration::getConfigForModule.
     * @param array $expected all all expectations(http method, server url, generated message).
     */
    public function testDelete($arguments, $getConfigForModule, $saveSetting, $expected)
    {
        $this->sugarConfig->method('get')->willReturnMap(array(
            array('trigger_server.url', null, $arguments['serverUrl']),
            array('site_url', null, ClientTest::SITE_URL),
        ));

        AdministrationCRYS1494::$saveSetting['returns'] = $saveSetting['returns'];
        AdministrationCRYS1494::$getConfigForModule['returns'] = $getConfigForModule['returns'];

        if (!empty($arguments['token'])) {
            $this->client->method('createGuid')->willReturn($arguments['token']);
        }

        $this->httpHelper
            ->expects($this->once())
            ->method('send')
            ->with(
                $this->equalTo(Client::DELETE_METHOD),
                $this->equalTo($expected['serverUrl']),
                $this->equalTo($expected['message']),
                $this->equalTo($expected['headers'])
            )
            ->willReturn(true);

        $result = $this->client->delete($arguments['id']);

        $this->assertTrue($result);

        if (!empty($saveSetting['arguments'])) {
            $this->assertCount(1, AdministrationCRYS1494::$saveSetting['calls']);

            foreach (AdministrationCRYS1494::$saveSetting['calls'][0] as $key => $value) {
                $this->assertEquals($saveSetting['arguments'][$key], $value);
            }
        }

        if (!empty($getConfigForModule['arguments'])) {
            $this->assertCount(1, AdministrationCRYS1494::$getConfigForModule['calls']);

            foreach (AdministrationCRYS1494::$getConfigForModule['calls'][0] as $key => $value) {
                $this->assertEquals($getConfigForModule['arguments'][$key], $value);
            }
        }
    }

    /**
     * Testing case when trigger server not configured.
     *
     * @covers Sugarcrm\Sugarcrm\Trigger\Client::deleteByTags
     */
    public function testDeleteByTagsNotConfigured()
    {
        $tags = array('tag1' . rand(1000, 9999), 'tag2' . rand(1000, 9999));

        $this->sugarConfig->method('get')->will($this->returnValueMap(array(
            array('trigger_server.url', null, null),
            array('site_url', null, ClientTest::SITE_URL),
        )));

        $this->loggerManager->expects($this->once())
            ->method('error')
            ->with($this->equalTo('Trigger\\Client::push - attempt to use client which is not configured.'));

        $this->httpHelper
            ->expects($this->never())
            ->method('send');

        $result = $this->client->deleteByTags($tags);

        $this->assertFalse($result);
    }

    /**
     * Data provider for testDeleteWitInvalidTags with invalid tags.
     *
     * @see \Sugarcrm\SugarcrmTests\Trigger\ClientTest::testDeleteWitInvalidTags
     * @return array
     */
    public function deleteWitInvalidTagsProvider()
    {
        return array(
            'emptyArray' => array('tags' => array()),
            'string' => array('tags' => 'some:tags:string'),
            'null' => array('tags' => null),
        );
    }

    /**
     * Testing deleteByTags with invalid tags.
     *
     * @covers Sugarcrm\Sugarcrm\Trigger\Client::deleteByTags
     * @dataProvider deleteWitInvalidTagsProvider
     * @param mixed $tags variant of invalid tags.
     */
    public function testDeleteWitInvalidTags($tags)
    {
        $this->sugarConfig->method('get')->will($this->returnValueMap(array(
            array('trigger_server.url', null, 'http://dummy-trigger-server/'),
            array('site_url', null, ClientTest::SITE_URL),
        )));

        $this->httpHelper
            ->expects($this->never())
            ->method('send');

        $result = $this->client->deleteByTags($tags);

        $this->assertFalse($result);
    }

    /**
     * Data provider for testDeleteByTags.
     *
     * @see \Sugarcrm\SugarcrmTests\Trigger\ClientTest::testDeleteByTags
     * @return array
     */
    public static function deleteByTagsProvider()
    {
        $tags1 = array('tag1:1:' . rand(1000, 9999), 'tag1:2:' . rand(1000, 9999));
        $tags2 = array('tag2:1:' . rand(1000, 9999), 'tag2:2:' . rand(1000, 9999));

        $token1 = 'token:1:' . rand(1000, 9999);
        $token2 = 'token:2:' . rand(1000, 9999);

        return array(
            'retrieveTokenUrlServerUrlWithSlash' => array(
                'arguments' => array(
                    'tags' => $tags1,
                    'serverUrl' => 'http://dummy-trigger-server/',
                    'token' => $token1,
                ),
                'getConfigForModule' => array(
                    'arguments' => array('auth', 'base', true),
                    'returns' => null,
                ),
                'saveSetting' => array(
                    'arguments' => array('auth', 'external_token_trigger', $token1, 'base'),
                    'returns' => null,
                ),
                'expected' => array(
                    'serverUrl' => 'http://dummy-trigger-server/' . Client::DELETE_BY_TAGS_URI,
                    'message' => json_encode(
                        array(
                            'url' => static::SITE_URL,
                            'tags' => $tags1,
                        )
                    ),
                    'headers' => array(
                        Client::AUTH_TOKEN_HEADER . ': ' . $token1,
                        Client::AUTH_VERSION_HEADER . ': ' . Client::AUTH_VERSION,
                    ),
                ),
            ),
            'existingTokenUrlServerUrlWithoutSlash' => array(
                'arguments' => array(
                    'tags' => $tags2,
                    'serverUrl' => 'http://dummy-trigger-server',
                    'token' => null,
                ),
                'getConfigForModule' => array(
                    'arguments' => array('auth', 'base', true),
                    'returns' => array('external_token_trigger' => $token2),
                ),
                'saveSetting' => array(
                    'arguments' => null,
                    'returns' => null,
                ),
                'expected' => array(
                    'serverUrl' => 'http://dummy-trigger-server/' . Client::DELETE_BY_TAGS_URI,
                    'message' => json_encode(
                        array(
                            'url' => static::SITE_URL,
                            'tags' => $tags2,
                        )
                    ),
                    'headers' => array(
                        Client::AUTH_TOKEN_HEADER . ': ' . $token2,
                        Client::AUTH_VERSION_HEADER . ': ' . Client::AUTH_VERSION,
                    ),
                ),
            )
        );
    }

    /**
     * Testing deleting triggers tasks.
     *
     * @covers Sugarcrm\Sugarcrm\Trigger\Client::deleteByTags
     * @dataProvider deleteByTagsProvider
     * @param array $arguments all arguments for test(Arguments for push, configured serverUrl, generated token).
     * @param array $getConfigForModule settings for mock simulator \Administration::saveSetting.
     * @param array $saveSetting settings for mock simulator \Administration::getConfigForModule.
     * @param array $expected all all expectations(http method, server url, generated message).
     */
    public function testDeleteByTags($arguments, $getConfigForModule, $saveSetting, $expected)
    {
        $this->sugarConfig->method('get')->will($this->returnValueMap(array(
            array('trigger_server.url', null, $arguments['serverUrl']),
            array('site_url', null, ClientTest::SITE_URL),
        )));

        AdministrationCRYS1494::$saveSetting['returns'] = $saveSetting['returns'];
        AdministrationCRYS1494::$getConfigForModule['returns'] = $getConfigForModule['returns'];

        if (!empty($arguments['token'])) {
            $this->client->method('createGuid')->willReturn($arguments['token']);
        }

        $this->httpHelper
            ->expects($this->once())
            ->method('send')
            ->with(
                $this->equalTo(Client::DELETE_METHOD),
                $this->equalTo($expected['serverUrl']),
                $this->equalTo($expected['message']),
                $this->equalTo($expected['headers'])
            )
            ->willReturn(true);

        $result = $this->client->deleteByTags($arguments['tags']);

        $this->assertTrue($result);

        if (!empty($saveSetting['arguments'])) {
            $this->assertCount(1, AdministrationCRYS1494::$saveSetting['calls']);

            foreach (AdministrationCRYS1494::$saveSetting['calls'][0] as $key => $value) {
                $this->assertEquals($saveSetting['arguments'][$key], $value);
            }
        }

        if (!empty($getConfigForModule['arguments'])) {
            $this->assertCount(1, AdministrationCRYS1494::$getConfigForModule['calls']);

            foreach (AdministrationCRYS1494::$getConfigForModule['calls'][0] as $key => $value) {
                $this->assertEquals($getConfigForModule['arguments'][$key], $value);
            }
        }
    }

    /**
     * Data provider for testCheckTriggerServerSettings.
     *
     * @see \Sugarcrm\SugarcrmTests\Trigger\ClientTest::testCheckTriggerServerSettings
     * @return array
     */
    public function checkTriggerServerSettingsProvider()
    {
        return array(
            'invalidUrl' => array(
                'url' => 'invalid-url',
                'expectedPingCallsCount' => 0,
                'pingReturns' => null,
                'expectedResult' => false,
            ),
            'validUrlButPingFail' => array(
                'url' => 'http://dummy-site.com',
                'expectedPingCallsCount' => 1,
                'pingReturns' => false,
                'expectedResult' => false,
            ),
            'validUrlAndPingSuccess' => array(
                'url' => 'http://dummy-site.com',
                'expectedPingCallsCount' => 1,
                'pingReturns' => true,
                'expectedResult' => true,
            ),
        );
    }

    /**
     * Testing checks trigger server settings.
     *
     * @param string $url url for checking.
     * @param int $expectedPingCallsCount expected count calls of method ping.
     * @param bool $pingReturns method ping will return.
     * @param bool $expectedResult expected result.
     * @covers Sugarcrm\Sugarcrm\Trigger\Client::checkTriggerServerSettings
     * @dataProvider checkTriggerServerSettingsProvider
     */
    public function testCheckTriggerServerSettings($url, $expectedPingCallsCount, $pingReturns, $expectedResult)
    {
        $this->httpHelper->expects($this->exactly($expectedPingCallsCount))
            ->method('ping')
            ->with($url)
            ->willReturn($pingReturns);

        $this->assertEquals($expectedResult, $this->client->checkTriggerServerSettings($url));
    }
}

/**
 * Mock for \Administration.
 */
class AdministrationCRYS1494 extends \Administration
{
    /** @var array */
    public static $getConfigForModule = array(
        'calls' => array(),
        'returns' => null,
    );

    /** @var array */
    public static $saveSetting = array(
        'calls' => array(),
        'returns' => null,
    );

    /**
     * @inheritDoc
     */
    public function __construct()
    {

    }

    /**
     * @inheritDoc
     */
    public function getConfigForModule($module, $platform = 'base', $clean = false)
    {
        static::$getConfigForModule['calls'][] = func_get_args();
        return static::$getConfigForModule['returns'];
    }

    /**
     * @inheritDoc
     */
    public function saveSetting()
    {
        static::$saveSetting['calls'][] = func_get_args();
        return static::$saveSetting['returns'];
    }
}
