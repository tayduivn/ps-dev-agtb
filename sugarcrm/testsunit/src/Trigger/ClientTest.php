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

namespace Sugarcrm\SugarcrmTestsUnit\Trigger;

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
        $sugarConfig = $this->getSugarConfigMock(array('get'));
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
            array(null, false)
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
            ->willReturn(array('trigger_server_token' => $storedToken));

        $adminBean->expects($this->exactly($count))
            ->method('saveSetting')
            ->with('auth', 'trigger_server_token', $newToken, 'base')
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
            'token doesn\'t exists' => array(null, 'new-token', 1, 'new-token')
        );
    }

    /**
     * @param array $params
     * @param string $method
     * @param array $args
     * @param string $encodedMessageToSend
     * @dataProvider providerPush
     * @covers ::push
     */
    public function testPush($params, $method, $args, $encodedMessageToSend)
    {
        $sugarConfig = $this->getSugarConfigMock(array('get'));
        $sugarConfig->method('get')
            ->with($this->logicalOr(
                $this->equalTo('site_url'),
                $this->equalTo('trigger_server.url')
            ))
            ->willReturnCallback(function($setting){
                if ($setting === 'site_url') {
                    return ClientTest::SITE_URL;
                } else {
                    return ClientTest::TRIGGER_SERVER_URL;
                }
            });

        $httpHelper = $this->getHttpClientMock(array('send'));
        $httpHelper->expects($this->once())
            ->method('send')
            ->with('post', static::TRIGGER_SERVER_URL, $encodedMessageToSend)
            ->willReturn(true);

        $client = $this->getClientMock(array('retrieveToken', 'getSugarConfig', 'getHttpHelper'));

        $client->method('retrieveToken')
            ->willReturn(static::TOKEN);

        $client->method('getSugarConfig')
            ->willReturn($sugarConfig);

        $client->method('getHttpHelper')
            ->willReturn($httpHelper);

        $client->push($params['id'], $params['stamp'], $method, $params['uri'], $args);
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
            'intArg' => 1
        );

        return array(
            'method GET without args' => array($params, 'get', null, $this->getEncodedMessageToPush($params,'get', null)),
            'method GET with args' => array($params, 'get', $args, $this->getEncodedMessageToPush($params,'get', $args)),
            'method POST without args' => array($params, 'post', null, $this->getEncodedMessageToPush($params,'post', null)),
            'method POST with args' => array($params, 'post', $args, $this->getEncodedMessageToPush($params,'post', $args)),
            'method PUT without args' => array($params, 'put', null, $this->getEncodedMessageToPush($params,'put', null)),
            'method PUT with args' => array($params, 'put', $args, $this->getEncodedMessageToPush($params,'put', $args)),
            'method DELETE without args' => array($params, 'delete', null, $this->getEncodedMessageToPush($params,'delete', null)),
            'method DELETE with args' => array($params, 'delete', $args, $this->getEncodedMessageToPush($params,'delete', $args))
        );
    }

    /**
     * @param array $params
     * @param string $method
     * @param array $args
     * @return string
     */
    private function getEncodedMessageToPush($params, $method, $args)
    {
        $encodedMessageToSend = array(
            'url' => static::SITE_URL,
            'id' => $params['id'],
            'token' => static::TOKEN,
            'stamp' => $params['stamp'],
            'trigger' => array(
                'url' => $params['uri'],
                'method' => $method
            )
        );

        if ($args) {
            if (in_array($method, array('get', 'delete'))) {
                $encodedMessageToSend['trigger']['url'] .= '?'.http_build_query($args);
            } else {
                $encodedMessageToSend['trigger']['args'] = $args;
            }
        }

        return json_encode($encodedMessageToSend);
    }

    /**
     * @covers ::delete
     */
    public function testDelete()
    {
        $triggerId = 'dummy-id';
        $sugarConfig = $this->getSugarConfigMock(array('get'));
        $sugarConfig->method('get')
            ->with($this->logicalOr(
                $this->equalTo('site_url'),
                $this->equalTo('trigger_server.url')
            ))
            ->willReturnCallback(function($setting){
                if ($setting === 'site_url') {
                    return ClientTest::SITE_URL;
                } else {
                    return ClientTest::TRIGGER_SERVER_URL;
                }
            });

        $httpHelper = $this->getHttpClientMock(array('send'));
        $httpHelper->expects($this->once())
            ->method('send')
            ->with('delete', static::TRIGGER_SERVER_URL, $this->getEncodedMessageToDelete($triggerId))
            ->willReturn(true);

        $client = $this->getClientMock(array('retrieveToken', 'getSugarConfig', 'getHttpHelper'));

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
            'token' => static::TOKEN
        ));
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
            'valid url and ping success' => array('http://dummy-site.com', 1, true, true)
        );
    }

    /**
     * @covers ::getHttpHelper
     */
    public function testGetHttpHelper()
    {
        $client = new \Sugarcrm\Sugarcrm\Trigger\Client();
        $this->assertInstanceOf('\Sugarcrm\Sugarcrm\Trigger\HttpHelper', TestReflection::callProtectedMethod($client, 'getHttpHelper'));
    }

    /**
     * @covers ::getSugarConfig
     */
    public function testGetSugarConfig()
    {
        $client = new \Sugarcrm\Sugarcrm\Trigger\Client();
        $this->assertInstanceOf('\SugarConfig', TestReflection::callProtectedMethod($client, 'getSugarConfig'));
    }

    /**
     * @param string[] $methods
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    protected function getClientMock($methods)
    {
        return $this->getMockBuilder('\Sugarcrm\Sugarcrm\Trigger\Client')
            ->setMethods($methods)
            ->getMock();
    }

    /**
     * @param string[] $methods
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    protected function getHttpClientMock($methods)
    {
        return $this->getMockBuilder('\Sugarcrm\Sugarcrm\Trigger\HttpHelper')
            ->setMethods($methods)
            ->getMock();
    }
    /**
     * @param string[] $methods
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    protected function getSugarConfigMock($methods)
    {
        return $this->getMockBuilder('\SugarConfig')
            ->setMethods($methods)
            ->getMock();
    }

}
