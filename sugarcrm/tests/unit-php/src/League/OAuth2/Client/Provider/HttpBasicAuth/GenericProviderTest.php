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
use Sugarcrm\Sugarcrm\League\OAuth2\Client\Grant\JwtBearer;
use Sugarcrm\Sugarcrm\League\OAuth2\Client\Provider\HttpBasicAuth\GenericProvider;
use Psr\Http\Message\RequestInterface;
use League\OAuth2\Client\Token\AccessToken;
use League\OAuth2\Client\Tool\RequestFactory;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;

/**
 * @coversDefaultClass Sugarcrm\Sugarcrm\League\OAuth2\Client\Provider\HttpBasicAuth\GenericProvider
 */
class GenericProviderTest extends \PHPUnit_Framework_TestCase
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
     * @inheritdoc
     */
    protected function setUp()
    {
        $this->requestFactory = $this->getMockBuilder(RequestFactory::class)
                                     ->disableOriginalConstructor()
                                     ->setMethods(['getRequestWithOptions'])
                                     ->getMock();

        $this->request = $this->createMock(RequestInterface::class);

        $this->oidcConfig = [
            'clientId' => 'test',
            'clientSecret' => 'testSecret',
            'redirectUri' => '',
            'urlAuthorize' => 'http://testUrlAuth',
            'urlAccessToken' => 'http://testUrlAccessToken',
            'urlResourceOwnerDetails' => 'http://testUrlResourceOwnerDetails',
            'keySetId' => 'testSet',
            'urlKeys' => 'http://sts.sugarcrm.local/keys/testSet',
            'idpUrl' => 'http://idp.test',
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
                    'keySetId' => 'test',
                    'urlKeys' => 'http://sts.sugarcrm.local/keys/test',
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
        new GenericProvider($options);
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

        $this->requestFactory->expects($this->once())
                             ->method('getRequestWithOptions')
                             ->with(
                                 $this->equalTo(GenericProvider::METHOD_POST),
                                 $this->equalTo($authUrl),
                                 $this->callback(function ($options) {
                                     $this->assertEquals(
                                         'Basic dGVzdDp0ZXN0U2VjcmV0',
                                         $options['headers']['Authorization']
                                     );
                                     $this->assertEquals('token=token', $options['body']);
                                     return true;
                                 })
                             )
                             ->willReturn($this->request);

        $provider = $this->getMockBuilder(GenericProvider::class)
            ->enableOriginalConstructor()
            ->setConstructorArgs([$this->oidcConfig])
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
            ->willReturn($this->requestFactory);

        $provider->expects($this->once())
            ->method('getParsedResponse')
            ->with($this->isInstanceOf(RequestInterface::class))
            ->willReturn(['sub' => 'max']);

        $provider->introspectToken($token);
    }

    /**
     * @covers ::remoteIdpAuthenticate
     */
    public function testRemoteIdpAuthenticate()
    {
        $expectedResult = ['result' => 'success'];
        $accessToken = new AccessToken(['access_token' => 'testToken', 'expires_in' => '900']);

        $provider = $this->getMockBuilder(GenericProvider::class)
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
                 ->with('client_credentials', ['scope' => 'offline'])
                 ->willReturn($accessToken);

        $this->requestFactory->expects($this->once())
                ->method('getRequestWithOptions')
                ->with(
                    $this->equalTo(GenericProvider::METHOD_POST),
                    $this->equalTo('http://idp.test/authenticate'),
                    $this->callback(function ($options) {
                        $this->assertEquals('Bearer testToken', $options['headers']['Authorization']);
                        $this->assertEquals('user_name=test&password=test1', $options['body']);
                        return true;
                    })
                )
                ->willReturn($this->request);

        $provider->expects($this->once())
                 ->method('getParsedResponse')
                 ->with($this->request)
                 ->willReturn($expectedResult);

        $this->assertEquals($expectedResult, $provider->remoteIdpAuthenticate('test', 'test1'));
    }

    /**
     * @covers ::getJwtBearerAccessToken
     */
    public function testGetJwtBearerAccessToken()
    {
        $provider = $this->getMockBuilder(GenericProvider::class)
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
            'clientId' => 'test',
        ];
        $provider = $this->getMockBuilder(GenericProvider::class)
                         ->enableOriginalConstructor()
                         ->setConstructorArgs([$this->oidcConfig])
                         ->setMethods(['getAccessToken', 'getAuthenticatedRequest', 'getParsedResponse'])
                         ->getMock();

        $accessToken = new AccessToken(['access_token' => 'testToken', 'expires_in' => '900']);

        $provider->expects($this->once())
                 ->method('getAccessToken')
                 ->with('client_credentials', ['scope' => 'hydra.keys.get'])
                 ->willReturn($accessToken);

        $provider->expects($this->once())
                 ->method('getAuthenticatedRequest')
                 ->with(
                     GenericProvider::METHOD_GET,
                     'http://sts.sugarcrm.local/keys/testSet',
                     $accessToken,
                     ['scope' => 'hydra.keys.get']
                 )->willReturn($this->request);

        $provider->expects($this->once())
                 ->method('getParsedResponse')
                 ->with($this->request)
                 ->willReturn($expectedKeys);

        $this->assertEquals($expectedResult, $provider->getKeySet());
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
        $provider = $this->getMockBuilder(GenericProvider::class)
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
        $provider = $this->getMockBuilder(GenericProvider::class)
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
        $provider = $this->getMockBuilder(GenericProvider::class)
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
        $provider = $this->getMockBuilder(GenericProvider::class)
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
        $provider = $this->getMockBuilder(GenericProvider::class)
            ->enableOriginalConstructor()
            ->setConstructorArgs([[
                'clientId' => 'test',
                'clientSecret' => 'testSecret',
                'redirectUri' => '',
                'urlAuthorize' => '',
                'urlAccessToken' => 'http://testUrlAccessToken',
                'urlResourceOwnerDetails' => 'http://testUrlResourceOwnerDetails',
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
}
