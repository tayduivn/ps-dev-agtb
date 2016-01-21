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

namespace Sugarcrm\SugarcrmTests\Socket;

use Sugarcrm\Sugarcrm\Socket\Client;

/**
 * Class SocketClientTest
 * @package Sugarcrm\SugarcrmTests\Socket
 * @coversDefaultClass \Sugarcrm\Sugarcrm\Socket\Client
 */
class SocketClientTest extends \Sugar_PHPUnit_Framework_TestCase
{
    /**
     * Data provider for testRecipient()
     *
     * @return array
     */
    public function recipientsProvider()
    {
        return array(
            'user' => array(
                array(
                    'url' => 'http://come.sugar.url.com/some/path',
                    'type' => Client::RECIPIENT_USER_ID,
                    'id' => 123,
                ),
            ),
            'team' => array(
                array(
                    'url' => 'http://come.sugar.url.com/some/path',
                    'type' => Client::RECIPIENT_TEAM_ID,
                    'id' => 456,
                ),
            ),
            'group' => array(
                array(
                    'url' => 'http://come.sugar.url.com/some/path',
                    'type' => Client::RECIPIENT_USER_TYPE,
                    'id' => 'admin',
                ),
            ),
            'channel_user' => array(
                array(
                    'url' => 'http://come.sugar.url.com/some/path',
                    'channel' => 'channel-home',
                    'type' => Client::RECIPIENT_USER_ID,
                    'id' => 123,
                ),
            ),
            'channel_team' => array(
                array(
                    'url' => 'http://come.sugar.url.com/some/path',
                    'channel' => 'channel-home',
                    'type' => Client::RECIPIENT_TEAM_ID,
                    'id' => 456,
                ),
            ),
            'channel_group' => array(
                array(
                    'url' => 'http://come.sugar.url.com/some/path',
                    'channel' => 'channel-home',
                    'type' => Client::RECIPIENT_USER_TYPE,
                    'id' => 'admin',
                ),
            ),
        );
    }

    /**
     * Data provider for testSendReturnValue()
     *
     * @return array
     */
    public function messageUrlsProvider()
    {
        return array(
            'invalidUrl' => array(false),
            'validUrl' => array(true),
        );
    }

    /**
     * Data provider for testSend()
     *
     * @return array
     */
    public function messageDataProvider()
    {
        return array(
            'messageToUser' => array(
                'test message',
                null,
            ),
            'messageWithData' => array(
                'test message',
                array(
                    'var1' => 123,
                    'var2' => 'test',
                ),
            ),
        );
    }

    /**
     * Tests socket server settings check with invalid url. Socket server availability should be false
     *
     * @covers ::checkWSSettings
     */
    public function testCheckWSSettingsInvalidUrl()
    {
        $url = 'invalid-url';

        $httpHelper = $this->getMock('Sugarcrm\\Sugarcrm\\Socket\\HttpHelper');
        /* @var $client \PHPUnit_Framework_MockObject_MockObject|Client */
        $client = $this->getMock('Sugarcrm\\Sugarcrm\\Socket\\Client', array('getHttpHelper', 'getWSUrl'));

        $client->expects($this->once())->method('getHttpHelper')->willReturn($httpHelper);

        $httpHelper->expects($this->never())->method('ping');
        $httpHelper->expects($this->never())->method('getRemoteData');

        $expectedResult = array(
            'url' => $url,
            'available' => false,
            'type' => false,
            'isBalancer' => false,
        );

        $actualResult = $client->checkWSSettings($url);

        $this->assertEquals($expectedResult, $actualResult);
    }

    /**
     * Tests socket server settings check with unreachable socket server url
     *
     * @covers ::checkWSSettings
     */
    public function testCheckWSSettingsUnreachableUrl()
    {
        $url = 'http://unreachable.host.com';

        $httpHelper = $this->getMock('Sugarcrm\\Sugarcrm\\Socket\\HttpHelper');
        /* @var $client \PHPUnit_Framework_MockObject_MockObject|Client */
        $client = $this->getMock('Sugarcrm\\Sugarcrm\\Socket\\Client', array('getHttpHelper'));

        $client->expects($this->once())->method('getHttpHelper')->willReturn($httpHelper);

        $httpHelper->expects($this->once())->method('ping')->with($this->equalTo($url))->willReturn(false);
        $httpHelper->expects($this->never())->method('getRemoteData');

        $expectedResult = array(
            'url' => $url,
            'available' => false,
            'type' => false,
            'isBalancer' => false,
        );

        $actualResult = $client->checkWSSettings($url);

        $this->assertEquals($expectedResult, $actualResult);
    }

    /**
     * Tests socket server settings check with valid and reachable url. Socket server availability should be true
     *
     * @covers ::checkWSSettings
     */
    public function testCheckWSSettingsValidClientUrl()
    {
        $url = 'http://dummy.net';
        $remoteData = array(
            'type' => 'client',
        );

        $expectedResult = array(
            'url' => $url,
            'type' => 'client',
            'available' => true,
            'isBalancer' => false,
        );

        $httpHelper = $this->getMock('Sugarcrm\\Sugarcrm\\Socket\\HttpHelper');
        /* @var $client \PHPUnit_Framework_MockObject_MockObject|Client */
        $client = $this->getMock('Sugarcrm\\Sugarcrm\\Socket\\Client', array('getHttpHelper'));

        $client->expects($this->once())->method('getHttpHelper')->willReturn($httpHelper);

        $httpHelper->expects($this->once())->method('ping')->with($this->equalTo($url))->willReturn(true);
        $httpHelper->expects($this->once())->method('getRemoteData')->with($this->equalTo($url))->willReturn($remoteData);

        $actualResult = $client->checkWSSettings($url);

        $this->assertEquals($expectedResult, $actualResult);
    }

    /**
     * Tests socket server settings check with valid and reachable url. Socket server availability should be true
     *
     * @covers ::checkWSSettings
     */
    public function testCheckWSSettingsValidServerUrl()
    {
        $url = 'http://dummy.net';
        $remoteData = array(
            'type' => 'server',
        );

        $expectedResult = array(
            'url' => $url,
            'type' => 'server',
            'available' => true,
            'isBalancer' => false,
        );

        $httpHelper = $this->getMock('Sugarcrm\\Sugarcrm\\Socket\\HttpHelper');
        /* @var $client \PHPUnit_Framework_MockObject_MockObject|Client */
        $client = $this->getMock('Sugarcrm\\Sugarcrm\\Socket\\Client', array('getHttpHelper'));

        $client->expects($this->once())->method('getHttpHelper')->willReturn($httpHelper);

        $httpHelper->expects($this->once())->method('ping')->with($this->equalTo($url))->willReturn(true);
        $httpHelper->expects($this->once())->method('getRemoteData')->with($this->equalTo($url))->willReturn($remoteData);

        $actualResult = $client->checkWSSettings($url);

        $this->assertEquals($expectedResult, $actualResult);
    }

    /**
     * Tests socket server settings check with invalid socket server type in response
     *
     * @covers ::checkWSSettings
     */
    public function testCheckWSSettingsInvalidType()
    {
        $url = 'http://dummy.net';
        $remoteData = array(
            'type' => 'invalid',
        );

        $expectedResult = array(
            'url' => $url,
            'type' => false,
            'available' => false,
            'isBalancer' => false,
        );

        $httpHelper = $this->getMock('Sugarcrm\\Sugarcrm\\Socket\\HttpHelper');
        /* @var $client \PHPUnit_Framework_MockObject_MockObject|Client */
        $client = $this->getMock('Sugarcrm\\Sugarcrm\\Socket\\Client', array('getHttpHelper'));

        $client->expects($this->once())->method('getHttpHelper')->willReturn($httpHelper);

        $httpHelper->expects($this->once())->method('ping')->with($this->equalTo($url))->willReturn(true);
        $httpHelper->expects($this->once())->method('getRemoteData')->with($this->equalTo($url))->willReturn($remoteData);

        $actualResult = $client->checkWSSettings($url);

        $this->assertEquals($expectedResult, $actualResult);
    }

    /**
     * Tests socket server settings check with invalid socket server response
     *
     * @covers ::checkWSSettings
     */
    public function testCheckWSSettingsInvalidResponse()
    {
        $url = 'http://dummy.net/invalid_response';
        $remoteData = 'invalid response';

        $expectedResult = array(
            'url' => $url,
            'type' => false,
            'available' => false,
            'isBalancer' => false,
        );

        $httpHelper = $this->getMock('Sugarcrm\\Sugarcrm\\Socket\\HttpHelper');
        /* @var $client \PHPUnit_Framework_MockObject_MockObject|Client */
        $client = $this->getMock('Sugarcrm\\Sugarcrm\\Socket\\Client', array('getHttpHelper'));

        $client->expects($this->once())->method('getHttpHelper')->willReturn($httpHelper);

        $httpHelper->expects($this->once())->method('ping')->with($this->equalTo($url))->willReturn(true);
        $httpHelper->expects($this->once())->method('getRemoteData')->with($this->equalTo($url))->willReturn($remoteData);

        $actualResult = $client->checkWSSettings($url);

        $this->assertEquals($expectedResult, $actualResult);
    }

    /**
     * Tests socket server settings check with balanced urls
     *
     * @covers ::checkWSSettings
     */
    public function testCheckWSSettingsBalancerValid()
    {
        $url = 'http://balanced.dummy.net';
        $finalUrl = 'http://dummy.net';
        $balancerResponse = array(
            'type' => 'balancer',
            'location' => $finalUrl,
        );
        $finalResponse = array(
            'type' => 'client',
        );

        $expectedResult = array(
            'url' => $url,
            'type' => 'client',
            'available' => true,
            'isBalancer' => true,
        );

        $httpHelper = $this->getMock('Sugarcrm\\Sugarcrm\\Socket\\HttpHelper');
        /* @var $client \PHPUnit_Framework_MockObject_MockObject|Client */
        $client = $this->getMock('Sugarcrm\\Sugarcrm\\Socket\\Client', array('getHttpHelper'));

        $client->expects($this->once())->method('getHttpHelper')->willReturn($httpHelper);

        $httpHelper->expects($this->once())->method('ping')->with($this->equalTo($url))->willReturn(true);

        $httpHelper->expects($this->at(1))->method('getRemoteData')->with($this->equalTo($url))->willReturn($balancerResponse);
        $httpHelper->expects($this->at(2))->method('getRemoteData')->with($this->equalTo($balancerResponse['location']))->willReturn($finalResponse);

        $actualResult = $client->checkWSSettings($url);

        $this->assertEquals($expectedResult, $actualResult);
    }

    /**
     * Tests socket server settings check with invalid server response at balanced url
     *
     * @covers ::checkWSSettings
     */
    public function testCheckWSSettingsBalancerInvalidResponse()
    {
        $url = 'http://balanced.dummy.net';
        $balancerResponse = array(
            'type' => 'balancer',
            'location' => 'http://dummy.net/invalid_response',
        );

        $expectedResult = array(
            'url' => $url,
            'available' => false,
            'type' => false,
            'isBalancer' => true,
        );

        $httpHelper = $this->getMock('Sugarcrm\\Sugarcrm\\Socket\\HttpHelper');
        /* @var $client \PHPUnit_Framework_MockObject_MockObject|Client */
        $client = $this->getMock('Sugarcrm\\Sugarcrm\\Socket\\Client', array('getHttpHelper'));

        $client->expects($this->once())->method('getHttpHelper')->willReturn($httpHelper);

        $httpHelper->expects($this->once())->method('ping')->with($this->equalTo($url))->willReturn(true);

        $httpHelper->expects($this->at(1))->method('getRemoteData')->with($this->equalTo($url))->willReturn($balancerResponse);
        $httpHelper->expects($this->at(2))->method('getRemoteData')->with($this->equalTo($balancerResponse['location']))->willReturn('invalid response data');

        $actualResult = $client->checkWSSettings($url);

        $this->assertEquals($expectedResult, $actualResult);
    }

    /**
     * Tests if send() method returns correct operation status
     *
     * @dataProvider messageUrlsProvider
     * @covers       ::send
     * @param bool $expectedResult - expected response from send() method
     */
    public function testSendReturnValue($expectedResult)
    {
        $messageToSend = 'dummy message';

        $httpHelper = $this->getMock('Sugarcrm\\Sugarcrm\\Socket\\HttpHelper');
        /* @var $client \PHPUnit_Framework_MockObject_MockObject|Client */
        $client = $this->getMock(
            'Sugarcrm\\Sugarcrm\\Socket\\Client',
            array('getHttpHelper', 'getSugarConfig', 'retrieveToken')
        );

        $config = $this->getMock('SugarConfig');

        $client->expects($this->any())->method('getHttpHelper')->willReturn($httpHelper);
        $client->expects($this->any())->method('getSugarConfig')->willReturn($config);

        $httpHelper->expects($this->any())->method('getRemoteData')->willReturn($expectedResult);
        $httpHelper->expects($this->atLeastOnce())->method('isSuccess')->willReturn($expectedResult);

        $actualResult = $client->send($messageToSend);
        $this->assertEquals($expectedResult, $actualResult);
    }

    /**
     * Tests sending message with data payload to socket server
     *
     * @dataProvider messageDataProvider
     * @covers       ::send
     * @param string $message - message to be sent
     * @param array|null $args - data payload to be sent
     */
    public function testSend($message, $args)
    {
        $httpHelper = $this->getMock('Sugarcrm\\Sugarcrm\\Socket\\HttpHelper');
        /* @var $client \PHPUnit_Framework_MockObject_MockObject|Client */
        $client = $this->getMock(
            'Sugarcrm\\Sugarcrm\\Socket\\Client',
            array('getHttpHelper', 'getSugarConfig', 'retrieveToken')
        );

        $config = $this->getMock('SugarConfig');

        $client->expects($this->any())->method('getHttpHelper')->willReturn($httpHelper);
        $client->expects($this->any())->method('getSugarConfig')->willReturn($config);

        $httpHelper->expects($this->once())
            ->method('getRemoteData')
            ->with(
                $this->anything(),
                $this->callback(function ($val) use ($message, $args) {
                    $data = json_decode($val, true);
                    $messagePassed = (isset($data['data']['message']) && $message == $data['data']['message']);
                    $argsPassed = (array_key_exists('args', $data['data']) && $args == $data['data']['args']);
                    return $messagePassed && $argsPassed;
                })
            );

        $client->send($message, $args);
    }

    /**
     * Tests correct recipient transfer to httpHelper
     *
     * @dataProvider recipientsProvider
     * @param array $expectedTo
     */
    public function testRecipient($expectedTo)
    {
        $message = 'test-message';

        $httpHelper = $this->getMock('Sugarcrm\\Sugarcrm\\Socket\\HttpHelper');
        /* @var $client \PHPUnit_Framework_MockObject_MockObject|Client */
        $client = $this->getMock(
            'Sugarcrm\\Sugarcrm\\Socket\\Client',
            array('getHttpHelper', 'getSugarConfig', 'retrieveToken')
        );

        $client->recipient($expectedTo['type'], $expectedTo['id']);
        if (isset($expectedTo['channel']) && $expectedTo['channel']) {
            $client->channel($expectedTo['channel']);
        }

        $config = $this->getMockBuilder('SugarConfig')
            ->setMethods(array('get'))
            ->getMock();

        $config->expects($this->any())
            ->method('get')
            ->will($this->returnCallback(function ($arg) use ($expectedTo) {
                $map = array(
                    'site_url' => $expectedTo['url'],
                    'websockets.server.url' => 'http://someValue',
                );
                return $map[$arg];
            }));

        $client->expects($this->any())->method('getHttpHelper')->willReturn($httpHelper);
        $client->expects($this->any())->method('getSugarConfig')->willReturn($config);

        $httpHelper->expects($this->once())
            ->method('getRemoteData')
            ->with(
                $this->anything(),
                $this->callback(function ($val) use ($expectedTo) {
                    $actualData = json_decode($val, true);
                    return 0 == count(array_diff($expectedTo, $actualData['to']));
                })
            );

        $client->send($message);
    }

    /**
     * Tests correct default recipient transfer to httpHelper
     */
    public function testDefaultRecipient()
    {
        $message = 'test-message';

        $httpHelper = $this->getMock('Sugarcrm\\Sugarcrm\\Socket\\HttpHelper');
        /* @var $client \PHPUnit_Framework_MockObject_MockObject|Client */
        $client = $this->getMock(
            'Sugarcrm\\Sugarcrm\\Socket\\Client',
            array('getHttpHelper', 'getSugarConfig', 'retrieveToken')
        );

        $config = $this->getMock('SugarConfig');

        $client->expects($this->any())->method('getHttpHelper')->willReturn($httpHelper);
        $client->expects($this->any())->method('getSugarConfig')->willReturn($config);

        $actualData = '';
        $httpHelper->expects($this->once())
            ->method('getRemoteData')
            ->with(
                $this->anything(),
                $this->callback(function ($val) use ($actualData) {
                    $actualData = json_decode($val, true);
                    return $actualData['to']['type'] == Client::RECIPIENT_ALL
                    && is_null($actualData['to']['id'])
                    && is_null($actualData['to']['channel']);
                })
            );

        $client->send($message);
    }


    /**
     * Tests getInstance() factory method
     *
     * @covers ::getInstance
     */
    public function testGetInstance()
    {
        $this->assertInstanceOf('Sugarcrm\\Sugarcrm\\Socket\\Client', Client::getInstance());
    }

    /**
     * Tests getHttpHelper() factory method
     *
     * @covers ::getHttpHelper
     */
    public function testGetHttpHelper()
    {
        $this->assertInstanceOf(
            'Sugarcrm\\Sugarcrm\\Socket\\HttpHelper',
            \SugarTestReflection::callProtectedMethod(new Client(), 'getHttpHelper')
        );
    }

    /**
     * Tests getAdministrationBean() factory method
     *
     * @covers ::getAdministrationBean
     */
    public function testgetAdministrationBean()
    {
        $this->assertInstanceOf(
            'Administration',
            \SugarTestReflection::callProtectedMethod(new Client(), 'getAdministrationBean')
        );
    }

    /**
     * Tests getSugarConfig() factory method
     *
     * @covers ::getSugarConfig
     */
    public function testGetSugarConfig()
    {
        $this->assertInstanceOf(
            'SugarConfig',
            \SugarTestReflection::callProtectedMethod(new Client(), 'getSugarConfig')
        );
    }

    /**
     * Tests auth token retrieve (token is stored in DB)
     *
     * @covers ::retrieveToken
     */
    public function testRetrieveToken()
    {
        $config = array(
            'external_token_socket' => 'sample-token',
        );

        $client = $this->getMock('Sugarcrm\\Sugarcrm\\Socket\\Client', array('getAdministrationBean'));
        $admin = $this->getMock('Administration', array('getConfigForModule', 'saveSetting'));

        $admin->expects($this->atLeastOnce())->method('getConfigForModule')->willReturn($config);
        $client->expects($this->atLeastOnce())->method('getAdministrationBean')->willReturn($admin);
        $admin->expects($this->never())->method('saveSetting');

        $token = \SugarTestReflection::callProtectedMethod($client, 'retrieveToken');
        $this->assertEquals($config['external_token_socket'], $token);
    }

    /**
     * Code run in daemon, should not use cache in memory.
     */
    public function testIsClearSettingsCache()
    {
        $config = array(
            'external_token_socket' => 'sample-token',
        );
        $admin = $this->getMock('Administration', array('getConfigForModule', 'saveSetting'));
        $admin->expects($this->atLeastOnce())->method('getConfigForModule')
            ->with($this->equalTo('auth'), $this->equalTo('base'), $this->isTrue())
            ->willReturn($config);

        $client = $this->getMock('Sugarcrm\\Sugarcrm\\Socket\\Client', array('getAdministrationBean'));

        $client->expects($this->atLeastOnce())->method('getAdministrationBean')->willReturn($admin);

        \SugarTestReflection::callProtectedMethod($client, 'retrieveToken');
    }

    /**
     * Tests auth token retrieve (token is generated)
     *
     * @covers ::retrieveToken
     */
    public function testGenerateToken()
    {
        $config = array(
            'external_token_socket' => '',
        );

        $client = $this->getMock('Sugarcrm\\Sugarcrm\\Socket\\Client', array('getAdministrationBean'));
        $admin = $this->getMock('Administration', array('getConfigForModule', 'saveSetting'));

        $token = '';
        $admin->expects($this->once())
            ->method('saveSetting')
            ->with(
                $this->anything(),
                $this->anything(),
                $this->callback(function ($val) use (&$token) {
                    $token = $val;
                    return !empty($token);
                }),
                $this->anything()
            );

        $admin->expects($this->atLeastOnce())->method('getConfigForModule')->willReturn($config);
        $client->expects($this->atLeastOnce())->method('getAdministrationBean')->willReturn($admin);

        $newToken = \SugarTestReflection::callProtectedMethod($client, 'retrieveToken');
        $this->assertEquals($token, $newToken);
    }

    /**
     * Tests correct auth token transfer to httpHelper
     */
    public function testUseToken()
    {
        $dummyToken = 'test-token';

        $httpHelper = $this->getMock('Sugarcrm\\Sugarcrm\\Socket\\HttpHelper');
        /* @var $client \PHPUnit_Framework_MockObject_MockObject|Client */
        $client = $this->getMock(
            'Sugarcrm\\Sugarcrm\\Socket\\Client',
            array('getHttpHelper', 'getSugarConfig', 'retrieveToken')
        );

        $config = $this->getMock('SugarConfig');

        $client->expects($this->any())->method('getHttpHelper')->willReturn($httpHelper);
        $client->expects($this->any())->method('getSugarConfig')->willReturn($config);
        $client->expects($this->any())->method('retrieveToken')->willReturn($dummyToken);

        $httpHelper->expects($this->once())
            ->method('getRemoteData')
            ->with(
                $this->anything(),
                $this->callback(function ($val) use ($dummyToken) {
                    $actualData = json_decode($val, true);
                    return $actualData['token'] == $dummyToken;
                })
            );

        $client->send('test');
    }

    /**
     * Tests correct sugar instance url transfer to httpHelper
     */
    public function testInstanceUrl()
    {
        $url = 'http://dummy.net';

        $httpHelper = $this->getMock('Sugarcrm\\Sugarcrm\\Socket\\HttpHelper');
        /* @var $client \PHPUnit_Framework_MockObject_MockObject|Client */
        $client = $this->getMock(
            'Sugarcrm\\Sugarcrm\\Socket\\Client',
            array('getHttpHelper', 'getSugarConfig', 'retrieveToken')
        );

        $config = $this->getMock('SugarConfig');

        $client->expects($this->any())->method('getHttpHelper')->willReturn($httpHelper);
        $client->expects($this->any())->method('getSugarConfig')->willReturn($config);

        $config->expects($this->at(0))->method('get')->with($this->equalTo('site_url'))->willReturn($url);

        $httpHelper->expects($this->once())
            ->method('getRemoteData')
            ->with(
                $this->anything(),
                $this->callback(function ($val) use ($url) {
                    $actualData = json_decode($val, true);
                    return $actualData['to']['url'] == $url;
                })
            );

        $client->send('test');
    }

    /**
     * Tests correct socket server url transfer to httpHelper
     */
    public function testWebSocketServerUrl()
    {
        $serverUrl = 'http://server.dummy';

        $httpHelper = $this->getMock('Sugarcrm\\Sugarcrm\\Socket\\HttpHelper');
        /* @var $client \PHPUnit_Framework_MockObject_MockObject|Client */
        $client = $this->getMock(
            'Sugarcrm\\Sugarcrm\\Socket\\Client',
            array('getHttpHelper', 'getSugarConfig', 'retrieveToken')
        );

        $config = $this->getMock('SugarConfig');

        $client->expects($this->any())->method('getHttpHelper')->willReturn($httpHelper);
        $client->expects($this->any())->method('getSugarConfig')->willReturn($config);

        $config->expects($this->at(1))
            ->method('get')
            ->with($this->equalTo('websockets.server.url'))
            ->willReturn($serverUrl);

        $httpHelper->expects($this->once())
            ->method('getRemoteData')
            ->with($this->equalTo($serverUrl . '/forward'), $this->anything());

        $client->send('test');
    }
}
