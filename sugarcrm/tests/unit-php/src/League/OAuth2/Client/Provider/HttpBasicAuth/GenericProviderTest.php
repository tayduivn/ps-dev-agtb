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

namespace Sugarcrm\SugarcrmTestsUnit\League\OAuth2\Client\Provider\HttpBasicAuth;

use League\OAuth2\Client\Grant\ClientCredentials;
use Sugarcrm\Sugarcrm\League\OAuth2\Client\Provider\HttpBasicAuth\GenericProvider;
use Psr\Http\Message\RequestInterface;
use League\OAuth2\Client\Token\AccessToken;
use League\OAuth2\Client\Tool\RequestFactory;

/**
 * @coversDefaultClass Sugarcrm\Sugarcrm\League\OAuth2\Client\Provider\HttpBasicAuth\GenericProvider
 */
class GenericProviderTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @covers ::getRequiredOptions
     * @expectedException \InvalidArgumentException
     */
    public function testGetRequiredOptions()
    {
        new GenericProvider([
            'clientId' => 'testLocal',
            'redirectUri' => '',
            'urlAuthorize' => 'http://sts.sugarcrm.local/oauth2/auth',
            'urlAccessToken' => 'http://sts.sugarcrm.local/oauth2/token',
            'urlResourceOwnerDetails' => 'http://sts.sugarcrm.local/.well-known/jwks.json',
        ]);
    }

    /**
     * @covers ::getAccessTokenOptions
     */
    public function testGetAccessTokenOptions()
    {
        $authUrl = 'http://testUrlAuth';

        $grant = $this->getMockBuilder(ClientCredentials::class)
            ->setMethods(['prepareRequestParameters'])
            ->disableOriginalConstructor()
            ->getMock();

        $grant->expects($this->once())
            ->method('prepareRequestParameters')
            ->with($this->isType('array'), $this->isType('array'))
            ->willReturn([
                'client_id' => 'test',
                'client_secret' => 'testSecret',
                'redirect_uri'  => '',
                'grant_type' => 'client_credentials',
            ]);

        $response = $this->createMock(RequestInterface::class);

        $provider = $this->getMockBuilder(GenericProvider::class)
            ->enableOriginalConstructor()
            ->setConstructorArgs([[
                'clientId' => 'test',
                'clientSecret' => 'testSecret',
                'redirectUri' => '',
                'urlAuthorize' => $authUrl,
                'urlAccessToken' => 'http://testUrlAccessToken',
                'urlResourceOwnerDetails' => 'http://testUrlResourceOwnerDetails',
            ]])
            ->setMethods([
                'verifyGrant',
                'getAccessTokenUrl',
                'getRequest',
                'getParsedResponse',
                'prepareAccessTokenResponse',
                'createAccessToken',
            ])
            ->getMock();

        $provider->expects($this->once())
            ->method('verifyGrant')
            ->willReturn($grant);

        $provider->expects($this->once())
            ->method('getAccessTokenUrl')
            ->willReturn($authUrl);

        $provider->expects($this->once())
            ->method('getRequest')
            ->with($this->equalTo('POST'), $this->equalTo($authUrl), $this->callback(function ($options) {
                $this->assertArrayHasKey('headers', $options);
                $this->assertArrayHasKey('Authorization', $options['headers']);
                $this->assertEquals('Basic ' . base64_encode('test:testSecret'), $options['headers']['Authorization']);
                return true;
            }))
            ->willReturn($response);

        $provider->expects($this->once())->method('getParsedResponse')->willReturn([]);
        $provider->expects($this->once())->method('prepareAccessTokenResponse')->willReturn([]);
        $provider->expects($this->once())->method('createAccessToken');

        $provider->getAccessToken('client_credentials');
    }

    /**
     * @covers ::introspectToken
     */
    public function testIntrospectToken()
    {
        $authUrl = 'http://testUrlAuth';

        $token = new AccessToken(['access_token' => 'token']);
        $request = $this->createMock(RequestInterface::class);

        $factory = $this->getMockBuilder(RequestFactory::class)
            ->disableOriginalConstructor()
            ->setMethods(['getRequestWithOptions'])
            ->getMock();

        $factory->expects($this->once())
            ->method('getRequestWithOptions')
            ->with(
                $this->equalTo(GenericProvider::METHOD_POST),
                $this->equalTo($authUrl),
                $this->callback(function ($options) {
                    $this->assertEquals('Basic dGVzdDp0ZXN0U2VjcmV0', $options['headers']['Authorization']);
                    $this->assertEquals('token=token', $options['body']);
                    return true;
                })
            )
            ->willReturn($request);

        $provider = $this->getMockBuilder(GenericProvider::class)
            ->enableOriginalConstructor()
            ->setConstructorArgs([[
                'clientId' => 'test',
                'clientSecret' => 'testSecret',
                'redirectUri' => '',
                'urlAuthorize' => $authUrl,
                'urlAccessToken' => 'http://testUrlAccessToken',
                'urlResourceOwnerDetails' => 'http://testUrlResourceOwnerDetails',
            ]])
            ->setMethods([
                'getResourceOwnerDetailsUrl',
                'getRequestFactory',
                'getParsedResponse',
            ])
            ->getMock();

        $provider->expects($this->once())
            ->method('getResourceOwnerDetailsUrl')
            ->willReturn($authUrl);

        $provider->expects($this->once())
            ->method('getRequestFactory')
            ->willReturn($factory);

        $provider->expects($this->once())
            ->method('getParsedResponse')
            ->with($this->isInstanceOf(RequestInterface::class))
            ->willReturn(['sub' => 'max']);

        $provider->introspectToken($token);
    }
}
