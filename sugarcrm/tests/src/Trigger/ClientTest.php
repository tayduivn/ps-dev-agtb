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
use Sugarcrm\SugarcrmTestsUnit\TestReflection;

/**
 * Class ClientTest
 * @package Sugarcrm\SugarcrmTestsUnit\Trigger
 * @coversDefaultClass \Sugarcrm\Sugarcrm\Trigger\Client
 */
class ClientTest extends \PHPUnit_Framework_TestCase
{
    const SITE_URL = 'http://dummy-site';
    const TRIGGER_SERVER_URL = 'http://dummy-trigger-server';
    const TOKEN = 'token';

    /**
     * @param string $triggerServerUrl
     * @param bool $returnIsConfigured
     * @dataProvider providerIsConfigured
     * @covers ::isConfigured
     */
    public function testIsConfigured($triggerServerUrl, $returnIsConfigured)
    {
        $sugarConfig = $this->getSugarConfigMock();
        $sugarConfig->method('get')
            ->with('trigger_server.url')
            ->willReturn($triggerServerUrl);

        $client = $this->getClientMock(array('getSugarConfig'));
        $client->method('getSugarConfig')
            ->willReturn($sugarConfig);

        $this->assertEquals($returnIsConfigured, $client->isConfigured());
    }

    /**
     * (trigger server url, expected value)
     * @return array
     */
    public function providerIsConfigured()
    {
        return array(
            array(static::TRIGGER_SERVER_URL, true),
            array(null, false),
        );
    }

    /**
     * @param boolean $httpClientReturns
     * @param boolean $expected
     * @dataProvider providerIsAvailable
     * @covers ::isAvailable
     */
    public function testIsAvailable($httpClientReturns, $expected)
    {

        $sugarConfig = $this->getSugarConfigMock();
        $sugarConfig->method('get')
            ->with('trigger_server.url')
            ->willReturn(static::TRIGGER_SERVER_URL);

        $httpHelper = $this->getHttpClientMock(array('ping'));
        $httpHelper->expects($this->once())
            ->method('ping')
            ->with(static::TRIGGER_SERVER_URL)
            ->willReturn($httpClientReturns);

        $client = $this->getClientMock(array('getSugarConfig', 'getHttpHelper'));
        $client->method('getSugarConfig')->willReturn($sugarConfig);
        $client->method('getHttpHelper')->willReturn($httpHelper);

        $this->assertEquals($expected, $client->isAvailable());
    }

    /**
     * (HttpClient::ping returned value, expected value)
     * @return array
     */
    public function providerIsAvailable()
    {
        return array(
            array(false, false),
            array(true, true),
        );
    }

    /**
     * @param string|null $storedToken
     * @param string|null $newToken
     * @param int $count
     * @param string $returnToken
     * @dataProvider providerRetrieveToken
     * @covers ::retrieveToken
     */
    public function testRetrieveToken($storedToken, $newToken, $count, $returnToken)
    {
        $adminBean = $this->getMockBuilder('\Administration')
            ->disableOriginalConstructor()
            ->setMethods(array('getConfigForModule', 'saveSetting'))
            ->getMock();

        $adminBean->method('getConfigForModule')
            ->with('auth')
            ->willReturn(array('external_token_trigger' => $storedToken));

        $adminBean->expects($this->exactly($count))
            ->method('saveSetting')
            ->with('auth', 'external_token_trigger', $newToken, 'base')
            ->willReturn(1);

        $client = $this->getClientMock(array('getAdministrationBean', 'createGuid'));

        $client->method('getAdministrationBean')
            ->willReturn($adminBean);

        $client->method('createGuid')
            ->willReturn($newToken);

        $this->assertEquals($returnToken, TestReflection::callProtectedMethod($client, 'retrieveToken'));

    }

    /**
     * (stored token, new generated token, expected Administration::saveSetting number of calls, Client::retrieveToken returns)
     * @return array
     */
    public function providerRetrieveToken()
    {
        return array(
            'token exists' => array('stored-token', null, 0, 'stored-token'),
            'token doesn\'t exists' => array(null, 'new-token', 1, 'new-token'),
        );
    }

    /**
     * @param array $params
     * @param string $method
     * @param array $args
     * @param array|null $tags
     * @param string $encodedMessageToSend
     * @dataProvider providerPush
     * @covers ::push
     */
    public function testPush($params, $method, $args, $tags, $encodedMessageToSend)
    {
        $sugarConfig = $this->getSugarConfigMock();

        $sugarConfig->method('get')->will($this->returnValueMap(array(
            array('trigger_server.url', null, ClientTest::TRIGGER_SERVER_URL),
            array('site_url', null, ClientTest::SITE_URL),

        )));

        $triggerServerPostUrl = static::TRIGGER_SERVER_URL . Client::POST_URI;

        $httpHelper = $this->getHttpClientMock(array('send'));
        $httpHelper->expects($this->once())
            ->method('send')
            ->with(Client::POST_METHOD, $triggerServerPostUrl, $encodedMessageToSend)
            ->willReturn(true);

        $client = $this->getClientMock(array(
            'retrieveToken',
            'getSugarConfig',
            'getHttpHelper',
        ));

        $client->method('retrieveToken')
            ->willReturn(static::TOKEN);

        $client->method('getSugarConfig')
            ->willReturn($sugarConfig);

        $client->method('getHttpHelper')
            ->willReturn($httpHelper);

        $client->push($params['id'], $params['stamp'], $method, $params['uri'], $args, $tags);
    }

    /**
     * (Client::push params, method, args, expected JSON encoded message to HttpHelper::send $args)
     * @return array
     */
    public function providerPush()
    {
        $params = array(
            'id' => 'dummy-id',
            'stamp' => 'dummy-stamp',
            'uri' => '/dummy-uri',
        );

        $args = array(
            'stringArg' => 'dummy-string',
            'intArg' => 1,
        );
        $tags = array(
            'tag 1',
            'tag 2',
        );

        return array(
            'method GET without args' => array(
                $params,
                'get',
                null,
                null,
                $this->getEncodedMessageToPush($params, 'get', null),
            ),
            'method GET with args' => array(
                $params,
                'get',
                $args,
                null,
                $this->getEncodedMessageToPush($params, 'get', $args),
            ),
            'method POST without args' => array(
                $params,
                'post',
                null,
                null,
                $this->getEncodedMessageToPush($params, 'post', null),
            ),
            'method POST with args' => array(
                $params,
                'post',
                $args,
                null,
                $this->getEncodedMessageToPush($params, 'post', $args),
            ),
            'method PUT without args' => array(
                $params,
                'put',
                null,
                null,
                $this->getEncodedMessageToPush($params, 'put', null),
            ),
            'method PUT with args' => array(
                $params,
                'put',
                $args,
                null,
                $this->getEncodedMessageToPush($params, 'put', $args),
            ),
            'method DELETE without args' => array(
                $params,
                'delete',
                null,
                null,
                $this->getEncodedMessageToPush($params, 'delete', null),
            ),
            'method DELETE with args' => array(
                $params,
                'delete',
                $args,
                null,
                $this->getEncodedMessageToPush($params, 'delete', $args),
            ),
            'any method with tags' => array(
                $params,
                'get',
                $args,
                $tags,
                $this->getEncodedMessageToPush($params, 'get', $args, $tags),
            ),
        );
    }

    /**
     * @param array $params
     * @param string $method
     * @param array $args
     * @param array|null $tags
     * @return string
     */
    private function getEncodedMessageToPush($params, $method, $args, $tags = null)
    {
        $encodedMessageToSend = array(
            'url' => static::SITE_URL,
            'id' => $params['id'],
            'token' => static::TOKEN,
            'stamp' => $params['stamp'],
            'trigger' => array(
                'url' => $params['uri'],
                'method' => $method,
            ),
        );

        if ($args && !in_array($method, array('get', 'delete'))) {
            $encodedMessageToSend['trigger']['args'] = $args;
        }

        if ($tags) {
            $encodedMessageToSend['tags'] = $tags;
        }

        return json_encode($encodedMessageToSend);
    }

    /**
     * @param boolean $isConfigured
     * @param boolean $send
     * @param boolean $returned
     * @dataProvider providerCheckIsConfigured
     * @covers ::push
     */
    public function testPushIsConfigured($isConfigured, $send, $returned)
    {
        $sugarConfig = $this->getSugarConfigMock();

        $sugarConfig->method('get')->will($this->returnValueMap(array(
            array('trigger_server.url', null, $isConfigured ? ClientTest::TRIGGER_SERVER_URL : ''),
            array('site_url', null, ClientTest::SITE_URL),

        )));

        $httpHelper = $this->getHttpClientMock(array('send'));
        $httpHelper->expects($this->exactly($send ? 1 : 0))
            ->method('send')
            ->willReturn(true);

        $client = $this->getClientMock(array(
            'retrieveToken',
            'getSugarConfig',
            'getHttpHelper',
        ));

        $client->method('retrieveToken')
            ->willReturn(static::TOKEN);

        $client->method('getSugarConfig')
            ->willReturn($sugarConfig);

        $client->method('getHttpHelper')
            ->willReturn($httpHelper);

        $this->assertEquals($returned, $client->push('dummy-id', 'dummy-stamp', 'get', '/dummy-uri', null, null));
    }

    /**
     * @covers ::delete
     */
    public function testDelete()
    {
        $triggerId = 'dummy-id';
        $sugarConfig = $this->getSugarConfigMock();

        $sugarConfig->method('get')->will($this->returnValueMap(array(
            array('trigger_server.url', null, ClientTest::TRIGGER_SERVER_URL),
            array('site_url', null, ClientTest::SITE_URL),

        )));

        $triggerServerDeleteUrl = static::TRIGGER_SERVER_URL . Client::DELETE_URI;

        $httpHelper = $this->getHttpClientMock(array('send'));
        $httpHelper->expects($this->once())
            ->method('send')
            ->with(Client::DELETE_METHOD, $triggerServerDeleteUrl, $this->getEncodedMessageToDelete($triggerId))
            ->willReturn(true);

        $client = $this->getClientMock(array(
            'retrieveToken',
            'getSugarConfig',
            'getHttpHelper',
        ));

        $client->method('retrieveToken')
            ->willReturn(static::TOKEN);

        $client->method('getSugarConfig')
            ->willReturn($sugarConfig);

        $client->method('getHttpHelper')
            ->willReturn($httpHelper);

        $client->delete($triggerId);
    }

    /**
     * @param string $id
     * @return string
     */
    private function getEncodedMessageToDelete($id)
    {
        return json_encode(array(
            'url' => static::SITE_URL,
            'id' => $id,
            'token' => static::TOKEN,
        ));
    }

    /**
     * @param boolean $isConfigured
     * @param boolean $send
     * @param boolean $returned
     * @dataProvider providerCheckIsConfigured
     * @covers ::delete
     */
    public function testDeleteIsConfigured($isConfigured, $send, $returned)
    {
        $triggerId = 'dummy-id';
        $sugarConfig = $this->getSugarConfigMock();

        $sugarConfig->method('get')->will($this->returnValueMap(array(
            array('trigger_server.url', null, $isConfigured ? ClientTest::TRIGGER_SERVER_URL : ''),
            array('site_url', null, ClientTest::SITE_URL),

        )));

        $httpHelper = $this->getHttpClientMock(array('send'));
        $httpHelper->expects($this->exactly($send ? 1 : 0))
            ->method('send')
            ->willReturn(true);

        $client = $this->getClientMock(array(
            'retrieveToken',
            'getSugarConfig',
            'getHttpHelper',
        ));

        $client->method('retrieveToken')
            ->willReturn(static::TOKEN);

        $client->method('getSugarConfig')
            ->willReturn($sugarConfig);

        $client->method('getHttpHelper')
            ->willReturn($httpHelper);

        $this->assertEquals($returned, $client->delete($triggerId));
    }

    /**
     * @param array $tags
     * @param int $sendCallsCount
     * @param boolean $expected
     * @dataProvider providerDeleteByTags
     * @covers ::deleteByTags
     */
    public function testDeleteByTags($tags, $sendCallsCount, $expected)
    {
        $sugarConfig = $this->getSugarConfigMock();

        $sugarConfig->method('get')->will($this->returnValueMap(array(
            array('trigger_server.url', null, ClientTest::TRIGGER_SERVER_URL),
            array('site_url', null, ClientTest::SITE_URL),

        )));

        $triggerServerDeleteByTagsUrl = static::TRIGGER_SERVER_URL . Client::DELETE_BY_TAGS_URI;

        $httpHelper = $this->getHttpClientMock(array('send'));
        $httpHelper->expects($this->exactly($sendCallsCount))
            ->method('send')
            ->with(
                Client::DELETE_BY_TAGS_METHOD,
                $triggerServerDeleteByTagsUrl,
                $this->getEncodedMessageToDeleteByTags($tags)
            )
            ->willReturn(true);

        $client = $this->getClientMock(array(
            'retrieveToken',
            'getSugarConfig',
            'getHttpHelper',
        ));

        $client->method('retrieveToken')
            ->willReturn(static::TOKEN);

        $client->method('getSugarConfig')
            ->willReturn($sugarConfig);

        $client->method('getHttpHelper')
            ->willReturn($httpHelper);

        $this->assertEquals($expected, $client->deleteByTags($tags));
    }

    /**
     * (array of tags, expected result)
     * @return array
     */
    public function providerDeleteByTags()
    {
        return array(
            'false if $tags is empty' => array(array(), 0, false),
            'true if $tags contains one tag' => array(array('tag'), 1, true),
            'true if $tags contains three tags' => array(array('tag 1', 'tag 2', 'tag 3'), 1, true),
        );
    }

    /**
     * @param array $tags
     * @return string
     */
    private function getEncodedMessageToDeleteByTags($tags)
    {
        return json_encode(array(
            'url' => static::SITE_URL,
            'token' => static::TOKEN,
            'tags' => $tags,
        ));
    }

    /**
     * @param boolean $isConfigured
     * @param boolean $send
     * @param boolean $returned
     * @dataProvider providerCheckIsConfigured
     * @covers ::delete
     */
    public function testDeleteByTagsIsConfigured($isConfigured, $send, $returned)
    {
        $sugarConfig = $this->getSugarConfigMock();

        $sugarConfig->method('get')->will($this->returnValueMap(array(
            array('trigger_server.url', null, $isConfigured ? ClientTest::TRIGGER_SERVER_URL : ''),
            array('site_url', null, ClientTest::SITE_URL),

        )));

        $httpHelper = $this->getHttpClientMock(array('send'));
        $httpHelper->expects($this->exactly($send ? 1 : 0))
            ->method('send')
            ->willReturn(true);

        $client = $this->getClientMock(array(
            'retrieveToken',
            'getSugarConfig',
            'getHttpHelper',
        ));

        $client->method('retrieveToken')
            ->willReturn(static::TOKEN);

        $client->method('getSugarConfig')
            ->willReturn($sugarConfig);

        $client->method('getHttpHelper')
            ->willReturn($httpHelper);

        $this->assertEquals($returned, $client->deleteByTags(array('tag 1', 'tag 2', 'tag 3')));
    }

    /**
     * (is Trigger server configured, is HttpHelper::send() called, returned)
     * @return array
     */
    public function providerCheckIsConfigured()
    {
        return array(
            'Trigger server is not configured' => array(false, false, false),
            'Trigger server is configured' => array(true, true, true),
        );
    }

    /**
     * @param string $url
     * @param int $count
     * @param bool $returnPing
     * @param bool $return
     * @covers ::checkTriggerServerSettings
     * @dataProvider providerCheckTriggerServerSettings
     */
    public function testCheckTriggerServerSettings($url, $count, $returnPing, $return)
    {
        $httpHelper = $this->getHttpClientMock(array('ping'));
        $httpHelper->expects($this->exactly($count))
            ->method('ping')
            ->with($url)
            ->willReturn($returnPing);

        $client = $this->getClientMock(array('getHttpHelper'));

        $client->method('getHttpHelper')
            ->willReturn($httpHelper);

        $this->assertEquals($return, $client->checkTriggerServerSettings($url));
    }

    /**
     * (url, expected HttpHelper::ping number of calls, HttpHelper::ping returns, Client::checkTriggerServerSettings returns)
     * @return array
     */
    public function providerCheckTriggerServerSettings()
    {
        return array(
            'invalid url' => array('dummy-url', 0, null, false),
            'valid url but ping fail' => array('http://dummy-site.com', 1, false, false),
            'valid url and ping success' => array('http://dummy-site.com', 1, true, true),
        );
    }

    /**
     * @covers ::getHttpHelper
     */
    public function testGetHttpHelper()
    {
        $client = new \Sugarcrm\Sugarcrm\Trigger\Client();
        $this->assertInstanceOf(
            'Sugarcrm\\Sugarcrm\\Trigger\\HttpHelper',
            TestReflection::callProtectedMethod($client, 'getHttpHelper')
        );
    }

    /**
     * @covers ::getSugarConfig
     */
    public function testGetSugarConfig()
    {
        $client = new \Sugarcrm\Sugarcrm\Trigger\Client();
        $this->assertInstanceOf('SugarConfig', TestReflection::callProtectedMethod($client, 'getSugarConfig'));
    }

    /**
     * @param string[] $methods
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    protected function getClientMock($methods)
    {
        return $this->getMockBuilder('Sugarcrm\\Sugarcrm\\Trigger\\Client')
            ->setMethods($methods)
            ->getMock();
    }

    /**
     * @param string[] $methods
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    protected function getHttpClientMock($methods)
    {
        return $this->getMockBuilder('Sugarcrm\\Sugarcrm\\Trigger\\HttpHelper')
            ->setMethods($methods)
            ->getMock();
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    protected function getSugarConfigMock()
    {
        return $this->getMockBuilder('SugarConfig')->getMock();
    }
}
