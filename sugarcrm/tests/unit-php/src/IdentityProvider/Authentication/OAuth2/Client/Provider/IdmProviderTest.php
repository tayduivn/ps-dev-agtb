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

namespace Sugarcrm\SugarcrmTestsUnit\IdentityProvider\Authentication\OAuth2\Client\Provider;

use League\OAuth2\Client\Grant\ClientCredentials;
use Sugarcrm\Sugarcrm\League\OAuth2\Client\Grant\JwtBearer;
use Sugarcrm\Sugarcrm\IdentityProvider\Authentication\OAuth2\Client\Provider\IdmProvider;
use Psr\Http\Message\RequestInterface;
use League\OAuth2\Client\Token\AccessToken;
use League\OAuth2\Client\Tool\RequestFactory;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;

/**
 * @coversDefaultClass Sugarcrm\Sugarcrm\League\OAuth2\Client\Provider\HttpBasicAuth\GenericProvider
 */
class IdmProviderTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var RequestFactory | \PHPUnit_Framework_MockObject_MockObject
     */
    protected $requestFactory;

    /**
     * @var RequestInterface | \PHPUnit_Framework_MockObject_MockObject
     */
    protected $request;

    /**
     * @var array
     */
    protected $oidcConfig;

    /**
     * @var \SugarCacheAbstract
     */
    protected $sugarCache;

    /**
     * @inheritdoc
     */
    protected function setUp()
    {
        $this->requestFactory = $this->getMockBuilder(RequestFactory::class)
                                     ->disableOriginalConstructor()
                                     ->setMethods(['getRequestWithOptions'])
                                     ->getMock();

        $this->request = $this->createMock(RequestInterface::class);
        $this->sugarCache = $this->createMock(\SugarCacheMemory::class);

        $this->oidcConfig = [
            'clientId' => 'srn:test',
            'clientSecret' => 'testSecret',
            'redirectUri' => '',
            'urlAuthorize' => 'http://testUrlAuth',
            'urlAccessToken' => 'http://testUrlAccessToken',
            'urlResourceOwnerDetails' => 'http://testUrlResourceOwnerDetails',
            'urlUserInfo' => 'http:://testUrlUserInfo',
            'keySetId' => 'testSet',
            'urlKeys' => 'http://sts.sugarcrm.local/keys/testSet',
            'idpUrl' => 'http://idp.test',
            'caching' => [
                'ttl' => [
                    'userInfo' => 12,
                    'introspectToken' => 15,
                    'keySet' => 3600,
                ],
            ],
        ];
    }
    public function getRequiredOptionsProvider()
    {
        return [
            'missingClientSecret' => [
                [
                    'clientId' => 'testLocal',
                    'redirectUri' => '',
                    'urlAuthorize' => 'http://sts.sugarcrm.local/oauth2/auth',
                    'urlAccessToken' => 'http://sts.sugarcrm.local/oauth2/token',
                    'urlResourceOwnerDetails' => 'http://sts.sugarcrm.local/.well-known/jwks.json',
                    'urlUserInfo' => 'http:://testUrlUserInfo',
                    'keySetId' => 'test',
                    'urlKeys' => 'http://sts.sugarcrm.local/keys/test',
                    'idpUrl' => 'http://idp.test',
                ],
            ],
            'missingClientId' => [
                [
                    'clientSecret' => 'test',
                    'redirectUri' => '',
                    'urlAuthorize' => 'http://sts.sugarcrm.local/oauth2/auth',
                    'urlAccessToken' => 'http://sts.sugarcrm.local/oauth2/token',
                    'urlResourceOwnerDetails' => 'http://sts.sugarcrm.local/.well-known/jwks.json',
                    'urlUserInfo' => 'http:://testUrlUserInfo',
                    'keySetId' => 'test',
                    'urlKeys' => 'http://sts.sugarcrm.local/keys/test',
                    'idpUrl' => 'http://idp.test',
                ],
            ],
            'missingKeySetId' => [
                [
                    'clientId' => 'testLocal',
                    'clientSecret' => 'test',
                    'redirectUri' => '',
                    'urlAuthorize' => 'http://sts.sugarcrm.local/oauth2/auth',
                    'urlAccessToken' => 'http://sts.sugarcrm.local/oauth2/token',
                    'urlResourceOwnerDetails' => 'http://sts.sugarcrm.local/.well-known/jwks.json',
                    'urlKeys' => 'http://sts.sugarcrm.local/keys/test',
                    'urlUserInfo' => 'http:://testUrlUserInfo',
                    'idpUrl' => 'http://idp.test',
                ],
            ],
            'missingUrlKeys' => [
                [
                    'clientId' => 'testLocal',
                    'clientSecret' => 'test',
                    'redirectUri' => '',
                    'urlAuthorize' => 'http://sts.sugarcrm.local/oauth2/auth',
                    'urlAccessToken' => 'http://sts.sugarcrm.local/oauth2/token',
                    'urlResourceOwnerDetails' => 'http://sts.sugarcrm.local/.well-known/jwks.json',
                    'urlUserInfo' => 'http:://testUrlUserInfo',
                    'keySetId' => 'test',
                    'idpUrl' => 'http://idp.test',
                ],
            ],
            'missingIdpUrl' => [
                [
                    'clientId' => 'testLocal',
                    'clientSecret' => 'test',
                    'redirectUri' => '',
                    'urlAuthorize' => 'http://sts.sugarcrm.local/oauth2/auth',
                    'urlAccessToken' => 'http://sts.sugarcrm.local/oauth2/token',
                    'urlResourceOwnerDetails' => 'http://sts.sugarcrm.local/.well-known/jwks.json',
                    'urlUserInfo' => 'http:://testUrlUserInfo',
                    'keySetId' => 'test',
                    'urlKeys' => 'http://sts.sugarcrm.local/keys/test',
                ],
            ],
            'missingUserInfoUrl' => [
                [
                    'clientId' => 'testLocal',
                    'clientSecret' => 'test',
                    'redirectUri' => '',
                    'urlAuthorize' => 'http://sts.sugarcrm.local/oauth2/auth',
                    'urlAccessToken' => 'http://sts.sugarcrm.local/oauth2/token',
                    'urlResourceOwnerDetails' => 'http://sts.sugarcrm.local/.well-known/jwks.json',
                    'keySetId' => 'test',
                    'urlKeys' => 'http://sts.sugarcrm.local/keys/test',
                    'idpUrl' => 'http://idp.test',
                ],
            ],
        ];
    }

    /**
     * @covers ::getRequiredOptions
     * @expectedException \InvalidArgumentException
     *
     * @dataProvider getRequiredOptionsProvider
     */
    public function testGetRequiredOptions(array $options)
    {
        new IdmProvider($options);
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
                'client_id' => 'srn:test',
                'client_secret' => 'testSecret',
                'redirect_uri'  => '',
                'grant_type' => 'client_credentials',
            ]);

        $response = $this->createMock(RequestInterface::class);

        $provider = $this->getMockBuilder(IdmProvider::class)
            ->enableOriginalConstructor()
            ->setConstructorArgs([$this->oidcConfig])
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
                $this->assertEquals('Basic ' . base64_encode(sprintf('%s:%s', urlencode('srn:test'), urlencode('testSecret'))), $options['headers']['Authorization']);
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

        $response = ['sub' => 'max'];

        $this->requestFactory->expects($this->once())
                             ->method('getRequestWithOptions')
                             ->with(
                                 $this->equalTo(IdmProvider::METHOD_POST),
                                 $this->equalTo($authUrl),
                                 $this->callback(function ($options) {
                                     $this->assertEquals(
                                         'Basic c3JuJTNBdGVzdDp0ZXN0U2VjcmV0',
                                         $options['headers']['Authorization']
                                     );
                                     $this->assertEquals('token=token', $options['body']);
                                     return true;
                                 })
                             )
                             ->willReturn($this->request);

        $provider = $this->getMockBuilder(IdmProvider::class)
            ->enableOriginalConstructor()
            ->setConstructorArgs([$this->oidcConfig])
            ->setMethods([
                'getResourceOwnerDetailsUrl',
                'getRequestFactory',
                'getParsedResponse',
                'getSugarCache',
            ])
            ->getMock();
        $provider->method('getSugarCache')->willReturn($this->sugarCache);

        $provider->expects($this->once())
            ->method('getResourceOwnerDetailsUrl')
            ->willReturn($authUrl);

        $provider->expects($this->once())
            ->method('getRequestFactory')
            ->willReturn($this->requestFactory);

        $provider->expects($this->once())
            ->method('getParsedResponse')
            ->with($this->isInstanceOf(RequestInterface::class))
            ->willReturn($response);

        $this->sugarCache->expects($this->once())
            ->method('set')
            ->with('oidc_introspect_token_' . md5('token'), $response, 15);

        $provider->introspectToken($token);
    }

    /**
     * @covers ::introspectToken
     */
    public function testIntrospectTokenCanUseCacheAndNotCallRemote()
    {
        $token = new AccessToken(['access_token' => 'token']);

        $provider = $this->getMockBuilder(IdmProvider::class)
            ->enableOriginalConstructor()
            ->setConstructorArgs([$this->oidcConfig])
            ->setMethods(['getParsedResponse', 'getSugarCache'])
            ->getMock();
        $provider->method('getSugarCache')->willReturn($this->sugarCache);

        $this->sugarCache->method('get')
            ->with('oidc_introspect_token_' . md5('token'))
            ->willReturn('some-introspect-response');

        $provider->expects($this->never())->method('getParsedResponse');
        $this->sugarCache->expects($this->never())->method('set');

        $provider->introspectToken($token);
    }

    /**
     * @covers ::remoteIdpAuthenticate
     */
    public function testRemoteIdpAuthenticate()
    {
        $expectedResult = ['result' => 'success'];
        $accessToken = new AccessToken(['access_token' => 'testToken', 'expires_in' => '900']);

        $provider = $this->getMockBuilder(IdmProvider::class)
                         ->enableOriginalConstructor()
                         ->setConstructorArgs([$this->oidcConfig])
                         ->setMethods(
                             ['getRequestWithOptions', 'getRequestFactory', 'getParsedResponse', 'getAccessToken']
                         )
                         ->getMock();

        $provider->expects($this->once())
                 ->method('getRequestFactory')
                 ->willReturn($this->requestFactory);

        $provider->expects($this->once())
                 ->method('getAccessToken')
                 ->with('client_credentials', ['scope' => 'idp.auth.password'])
                 ->willReturn($accessToken);

        $this->requestFactory->expects($this->once())
                ->method('getRequestWithOptions')
                ->with(
                    $this->equalTo(IdmProvider::METHOD_POST),
                    $this->equalTo('http://idp.test/authenticate'),
                    $this->callback(function ($options) {
                        $this->assertEquals('Bearer testToken', $options['headers']['Authorization']);
                        $this->assertEquals('user_name=test&password=test1&tid=srn%3Atenant', $options['body']);
                        return true;
                    })
                )
                ->willReturn($this->request);

        $provider->expects($this->once())
                 ->method('getParsedResponse')
                 ->with($this->request)
                 ->willReturn($expectedResult);

        $this->assertEquals($expectedResult, $provider->remoteIdpAuthenticate('test', 'test1', 'srn:tenant'));
    }

    /**
     * @covers ::getJwtBearerAccessToken
     */
    public function testGetJwtBearerAccessToken()
    {
        $provider = $this->getMockBuilder(IdmProvider::class)
                         ->enableOriginalConstructor()
                         ->setConstructorArgs([$this->oidcConfig])
                         ->setMethods(['getAccessToken'])
                         ->getMock();

        $provider->expects($this->once())->method('getAccessToken')->willReturnCallback(
            function ($token, $options) {
                $this->assertInstanceOf(JwtBearer::class, $token);
                $this->assertEquals(
                    ['scope' => 'offline', 'assertion' => 'assertion'],
                    $options
                );
            }
        );
        $provider->getJwtBearerAccessToken('assertion');
    }

    /**
     * @covers ::getKeySet
     */
    public function testGetKeySet()
    {
        $expectedKeys = [
            'keys' => [
                ['private'],
                ['public'],
            ],
        ];
        $expectedResult = [
            'keys' => [
                ['private'],
                ['public'],
            ],
            'keySetId' => 'testSet',
            'clientId' => 'srn:test',
        ];
        $provider = $this->getMockBuilder(IdmProvider::class)
                         ->enableOriginalConstructor()
                         ->setConstructorArgs([$this->oidcConfig])
                         ->setMethods([
                             'getAccessToken',
                             'getAuthenticatedRequest',
                             'getParsedResponse',
                             'getSugarCache',
                         ])
                         ->getMock();

        $provider->method('getSugarCache')->willReturn($this->sugarCache);

        $accessToken = new AccessToken(['access_token' => 'testToken', 'expires_in' => '900']);

        $this->sugarCache->expects($this->once())
            ->method('get')
            ->with('oidc_key_set')
            ->willReturn(null);

        $provider->expects($this->once())
                 ->method('getAccessToken')
                 ->with('client_credentials', ['scope' => 'hydra.keys.get'])
                 ->willReturn($accessToken);

        $provider->expects($this->once())
                 ->method('getAuthenticatedRequest')
                 ->with(
                     IdmProvider::METHOD_GET,
                     'http://sts.sugarcrm.local/keys/testSet',
                     $accessToken,
                     ['scope' => 'hydra.keys.get']
                 )->willReturn($this->request);

        $provider->expects($this->once())
                 ->method('getParsedResponse')
                 ->with($this->request)
                 ->willReturn($expectedKeys);

        $this->sugarCache->expects($this->once())
                         ->method('set')
                         ->with('oidc_key_set', $expectedKeys['keys'], 3600);

        $this->assertEquals($expectedResult, $provider->getKeySet());
    }

    /**
     * @covers ::getKeySet
     */
    public function testGetKeySetCanUseCacheAndNotCallRemote()
    {
        $provider = $this->getMockBuilder(IdmProvider::class)
            ->enableOriginalConstructor()
            ->setConstructorArgs([$this->oidcConfig])
            ->setMethods(['getAccessToken', 'getParsedResponse', 'getSugarCache'])
            ->getMock();
        $provider->method('getSugarCache')->willReturn($this->sugarCache);

        $this->sugarCache->method('get')
            ->with('oidc_key_set')
            ->willReturn([['private'], ['public']]);

        $provider->expects($this->never())->method('getAccessToken');
        $provider->expects($this->never())->method('getParsedResponse');
        $this->sugarCache->expects($this->never())->method('set');

        $provider->getKeySet();
    }

    /**
     * @return array
     */
    public function delayExponentialProvider()
    {
        return [
            [0, 0],
            [1, 1000],
            [2, 2000],
            [3, 4000],
            [4, 8000],
            [5, 16000],
        ];
    }

    /**
     * @covers ::retryDelayExponential
     * @param int $attempt
     * @param int $delay
     *
     * @dataProvider delayExponentialProvider
     */
    public function testRetryDelayExponential($attempt, $delay)
    {
        $provider = $this->getMockBuilder(IdmProvider::class)
            ->disableOriginalConstructor()
            ->setMethods(['verifyGrant'])
            ->getMock();

        $function = $provider->retryDelayExponential();
        $this->assertEquals($delay, $function($attempt));
    }

    /**
     * @return array
     */
    public function delayLinearProvider()
    {
        return [
            [0, 0],
            [1, 1000],
            [2, 2000],
            [3, 3000],
            [4, 4000],
            [5, 5000],
        ];
    }

    /**
     * @covers ::retryDelayLinear
     * @param int $attempt
     * @param int $delay
     *
     * @dataProvider delayLinearProvider
     */
    public function testRetryDelayLinear($attempt, $delay)
    {
        $provider = $this->getMockBuilder(IdmProvider::class)
            ->disableOriginalConstructor()
            ->setMethods(['verifyGrant'])
            ->getMock();

        $function = $provider->retryDelayLinear();
        $this->assertEquals($delay, $function($attempt));
    }

    /**
     * @return array
     */
    public function getDelayStrategyProvider()
    {
        return [
            [[], 5000],
            [
                [
                    'http_client' => [
                        'delay_strategy' => '',
                    ],
                ],
                5000,
            ],
            [
                [
                    'http_client' => [
                        'delay_strategy' => 'linear',
                    ],
                ],
                5000,
            ],
            [
                [
                    'http_client' => [
                        'delay_strategy' => 'exponential',
                    ],
                ],
                16000,
            ],
            [
                [
                    'http_client' => [
                        'delay_strategy' => 'some_weird_unknown_strategy',
                    ],
                ],
                5000,
            ],
        ];
    }

    /**
     * @covers ::getDelayStrategy
     * @param array $config
     * @param int $expectedDelay for attempt = 5
     *
     * @dataProvider getDelayStrategyProvider
     */
    public function testGetDelayStrategy($config, $expectedDelay)
    {
        $provider = $this->getMockBuilder(IdmProvider::class)
            ->disableOriginalConstructor()
            ->setMethods(['verifyGrant'])
            ->getMock();

        $function = $provider->getDelayStrategy($config);
        $this->assertEquals($expectedDelay, $function(5));
    }

    /**
     * @return array
     */
    public function retryDeciderProvider()
    {
        return [
            'ZeroMaxAttemptsZeroAttempt' => [0, 0, 500, false],
            'ZeroMaxAttempts' => [0, 2, 500, false],
            'MaxAttemptsMoreThanCurrentAttempt' => [3, 2, 500, true],
            'MaxAttemptsLessThanCurrentAttempt' => [2, 3, 500, false],
            'MaxAttemptsEqualsCurrentAttempt' => [3, 3, 500, false], // it's a zero-based start
            'Code102' => [2, 0, 102, false],
            'Code10' => [2, 0, 200, false],
            'Code404' => [2, 0, 404, false],
            'Code302' => [2, 0, 302, false],
            'Code500' => [2, 0, 500, true],
            'Code502' => [2, 0, 502, true],
            'Code503' => [2, 0, 504, true],
        ];
    }

    /**
     * @covers ::retryDecider
     * @param int $attempts
     * @param int $currentAttempt
     * @param int $responseCode
     * @param bool $continueRetry
     *
     * @dataProvider retryDeciderProvider
     */
    public function testRetryDecider($attempts, $currentAttempt, $responseCode, $continueRetry)
    {
        $provider = $this->getMockBuilder(IdmProvider::class)
            ->disableOriginalConstructor()
            ->setMethods(['verifyGrant'])
            ->getMock();

        $request = $this->getMockBuilder(Request::class)
            ->disableOriginalConstructor()
            ->getMock();

        $response = $this->getMockBuilder(Response::class)
            ->disableOriginalConstructor()
            ->getMock();
        $response->method('getStatusCode')->willReturn($responseCode);

        $decider = $provider->retryDecider($attempts);
        $this->assertEquals($continueRetry, $decider($currentAttempt, $request, $response));
    }

    /**
     * @covers ::__construct
     */
    public function testProviderUsesOwnHttpClient()
    {
        $provider = $this->getMockBuilder(IdmProvider::class)
            ->enableOriginalConstructor()
            ->setConstructorArgs([[
                'clientId' => 'test',
                'clientSecret' => 'testSecret',
                'redirectUri' => '',
                'urlAuthorize' => '',
                'urlAccessToken' => 'http://testUrlAccessToken',
                'urlResourceOwnerDetails' => 'http://testUrlResourceOwnerDetails',
                'urlUserInfo' => 'http:://testUrlUserInfo',
                'keySetId' => 'test',
                'urlKeys' => 'http://sts.sugarcrm.local/keys/test',
                'idpUrl' => 'http://idp.test',
                'http_client' => [
                    'retry_count' => 5,
                    'delay_strategy' => 'exponential',
                ],
            ]])
            ->setMethods(['verifyGrant'])
            ->getMock();

        $httpClient = $provider->getHttpClient();
        $this->assertArrayHasKey('handler', $httpClient->getConfig());
        $this->assertRegexp('/retryDecider.*?Function/', (string)$httpClient->getConfig()['handler']);
    }

    /**
     * @covers ::getUserInfo
     */
    public function testGetUserInfo()
    {
        $token = new AccessToken(['access_token' => 'token']);

        $response = [
            'preferred_username' => 'test',
            'status' => 0,
        ];

        /** @var IdmProvider | \PHPUnit_Framework_MockObject_MockObject $provider */
        $provider = $this->getMockBuilder(IdmProvider::class)
            ->setConstructorArgs([$this->oidcConfig])
            ->setMethods(['getRequestFactory', 'getParsedResponse', 'getSugarCache'])
            ->getMock();
        $provider->method('getSugarCache')->willReturn($this->sugarCache);

        $provider->expects($this->once())
            ->method('getRequestFactory')
            ->willReturn($this->requestFactory);

        $this->requestFactory->expects($this->once())
            ->method('getRequestWithOptions')
            ->with(
                $this->equalTo(IdmProvider::METHOD_POST),
                $this->equalTo('http:://testUrlUserInfo'),
                $this->isType('array')
            )
            ->willReturn($this->request);

        $provider->expects($this->once())
            ->method('getParsedResponse')
            ->with($this->request)
            ->willReturn($response);

        $this->sugarCache->expects($this->once())
            ->method('set')
            ->with('oidc_user_info_' . md5('token'), $response, 12);

        $result = $provider->getUserInfo($token);
        $this->assertEquals('test', $result['preferred_username']);
        $this->assertEquals(0, $result['status']);
    }

    /**
     * @covers ::getUserInfo
     */
    public function testGetUserInfoCanUseCacheAndNotCallRemote()
    {
        $token = new AccessToken(['access_token' => 'token']);

        $provider = $this->getMockBuilder(IdmProvider::class)
            ->enableOriginalConstructor()
            ->setConstructorArgs([$this->oidcConfig])
            ->setMethods(['getParsedResponse', 'getSugarCache'])
            ->getMock();
        $provider->method('getSugarCache')->willReturn($this->sugarCache);

        $this->sugarCache->method('get')
            ->with('oidc_user_info_' . md5('token'))
            ->willReturn('some-user-info');

        $provider->expects($this->never())->method('getParsedResponse');
        $this->sugarCache->expects($this->never())->method('set');

        $provider->getUserInfo($token);
    }

    public function setCacheDoesNotStoreDataIfTTLIsNotCorrectProvider()
    {
        return [
            [0],
            [null],
        ];
    }

    /**
     * @covers ::setCache
     *
     * @dataProvider setCacheDoesNotStoreDataIfTTLIsNotCorrectProvider
     *
     * @param mixed $ttl
     */
    public function testSetCacheDoesNotStoreDataIfTTLIsNotCorrect($ttl)
    {
        $token = new AccessToken(['access_token' => 'token']);

        $this->oidcConfig['caching']['ttl']['userInfo'] = $ttl;

        $provider = $this->getMockBuilder(IdmProvider::class)
            ->enableOriginalConstructor()
            ->setConstructorArgs([$this->oidcConfig])
            ->setMethods(['getParsedResponse', 'getSugarCache'])
            ->getMock();
        $provider->method('getSugarCache')->willReturn($this->sugarCache);
        $provider->method('getParsedResponse')->willReturn('some-data');

        $this->sugarCache->expects($this->never())->method('set');

        $provider->getUserInfo($token);
    }
}
