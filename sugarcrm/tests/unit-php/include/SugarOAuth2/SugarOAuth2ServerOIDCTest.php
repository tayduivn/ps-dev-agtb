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

namespace Sugarcrm\SugarcrmTestsUnit\inc\SugarOAuth2;

use Sugarcrm\Sugarcrm\IdentityProvider\Authentication\AuthProviderBasicManagerBuilder;
use Sugarcrm\Sugarcrm\IdentityProvider\Authentication\AuthProviderOIDCManagerBuilder;
use Sugarcrm\Sugarcrm\IdentityProvider\Authentication\Config;
use Sugarcrm\Sugarcrm\IdentityProvider\Authentication\Token\OIDC\IntrospectToken;
use Sugarcrm\Sugarcrm\IdentityProvider\Authentication\Token\OIDC\JWTBearerToken;
use Sugarcrm\Sugarcrm\IdentityProvider\Authentication\User;
use Symfony\Component\Security\Core\Authentication\AuthenticationProviderManager;
use Symfony\Component\Security\Core\Exception\AuthenticationException;

/**
 * @coversDefaultClass \SugarOAuth2ServerOIDC
 */
class SugarOAuth2ServerOIDCTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \SugarOAuth2StorageOIDC | \PHPUnit_Framework_MockObject_MockObject
     */
    protected $storage;

    /**
     * @var \SugarOAuth2ServerOIDC | \PHPUnit_Framework_MockObject_MockObject
     */
    protected $oAuth2Server;

    /**
     * @var AuthProviderOIDCManagerBuilder | \PHPUnit_Framework_MockObject_MockObject
     */
    protected $authProviderBuilder;

    /**
     * @var AuthProviderBasicManagerBuilder | \PHPUnit_Framework_MockObject_MockObject
     */
    protected $authProviderBasicBuilder;

    /**
     * @var AuthenticationProviderManager | \PHPUnit_Framework_MockObject_MockObject
     */
    protected $authManager;

    /**
     * @var \User | \PHPUnit_Framework_MockObject_MockObject
     */
    protected $sugarUser;

    /**
     * @var User | \PHPUnit_Framework_MockObject_MockObject
     */
    protected $user;

    /**
     * @var array
     */
    protected $inputData = [];

    /** @var  \User | \PHPUnit_Framework_MockObject_MockObject */
    protected $mockedUser;

    /** @var  array */
    protected $beanList;

    protected $sugarConfig;

    /**
     * @var string
     */
    protected $stsAccessToken = '4swYymAtLvC9-pGAo3YKJYkLa-7UWFN-jfp5jxP4GfE.wS2Lih_FhXsbyaeZLgpM_1pOIvhCxr-ZgEQXWcKtNko';

    /**
     * @var string
     */
    protected $sugarAccessToken = '956fc0c6-eb25-491c-aa19-411bde06e238';

    /**
     * @inheritdoc
     */
    public function setUp()
    {
        $this->storage = $this->createMock(\SugarOAuth2StorageOIDC::class);
        $this->oAuth2Server = $this->getMockBuilder(\SugarOAuth2ServerOIDC::class)
                                   ->setConstructorArgs([$this->storage, []])
                                   ->setMethods([
                                       'getAuthProviderBuilder',
                                       'getAuthProviderBasicBuilder',
                                       'genAccessToken',
                                   ])
                                   ->getMock();

        $this->authProviderBuilder = $this->createMock(AuthProviderOIDCManagerBuilder::class);
        $this->authProviderBasicBuilder = $this->createMock(AuthProviderBasicManagerBuilder::class);
        $this->authManager = $this->createMock(AuthenticationProviderManager::class);
        $this->authProviderBuilder->method('buildAuthProviders')->willReturn($this->authManager);
        $this->authProviderBasicBuilder->method('buildAuthProviders')->willReturn($this->authManager);
        $this->sugarUser = $this->createMock(\User::class);
        $this->sugarUser->id = 'testUserId';
        $this->user = new User();
        $this->user->setSugarUser($this->sugarUser);
        $this->inputData = [
            'grant_type' => 'password',
            'scope' => null,
            'username' => 'test',
            'password' => 'test',
            'client_id' => 'client_id',
            'client_secret' => 'client_secret',
        ];

        $this->beanList = isset($GLOBALS['beanList']) ? $GLOBALS['beanList'] : null;
        $GLOBALS['beanList'] = ['Users' => 'User'];

        $this->mockedUser = $this->getMockBuilder(\User::class)
                                 ->disableOriginalConstructor()
                                 ->setMethods(null)
                                 ->getMock();
        $this->mockedUser->id = 'testId';
        $this->mockedUser->module_name = 'Users';
        \BeanFactory::registerBean($this->mockedUser);

        $this->sugarConfig = \SugarConfig::getInstance();
        $this->sugarConfig->_cached_values['oidc_oauth'] = [
            'clientId' => 'testLocal',
            'clientSecret' => 'testLocalSecret',
            'oidcUrl' => 'http://sts.sugarcrm.local',
            'idpUrl' => 'http://sugar.dolbik.local/idm289idp/web/',
            'oidcKeySetId' => 'KeySetName',
            'tid' => 'srn:cluster:sugar:eu:0000000001:tenant',
            'idpServiceName' => 'idm',
        ];
    }

    /**
     * @inheritdoc
     */
    public function tearDown()
    {
        $GLOBALS['beanList'] = $this->beanList;
        \BeanFactory::unregisterBean($this->mockedUser);
        $this->sugarConfig->_cached_values['oidc_oauth'] = [];
    }

    /**
     * @covers ::grantAccessToken
     *
     * @expectedException \OAuth2ServerException
     * @expectedExceptionMessage invalid_client
     */
    public function testGrantAccessTokenWithInvalidClient()
    {
        $this->storage->expects($this->once())
                      ->method('checkClientCredentials')
                      ->with('client_id', 'client_secret')
                      ->willReturn(false);

        $this->storage->expects($this->never())->method('checkRestrictedGrantType');
        $this->storage->expects($this->never())->method('checkUserCredentials');

        $this->oAuth2Server->grantAccessToken($this->inputData);
    }

    /**
     * @covers ::grantAccessToken
     *
     * @expectedException \OAuth2ServerException
     * @expectedExceptionMessage unauthorized_client
     */
    public function testGrantAccessTokenWithInvalidGrantType()
    {
        $this->storage->expects($this->once())
                      ->method('checkClientCredentials')
                      ->with($this->inputData['client_id'], $this->inputData['client_secret'])
                      ->willReturn(true);

        $this->storage->expects($this->once())
                      ->method('checkRestrictedGrantType')
                      ->with($this->inputData['client_id'], $this->inputData['grant_type'])
                      ->willReturn(false);
        $this->storage->expects($this->never())->method('checkUserCredentials');

        $this->oAuth2Server->grantAccessToken($this->inputData);
    }

    /**
     * Provides data for testGrantAccessTokenWithEmptyUsernameOrPassword
     *
     * @return array
     */
    public function grantAccessTokenWithEmptyUsernameOrPasswordProvider()
    {
        return [
            'emptyUsername' => [
                'username' => '',
                'password' => 'test',
            ],
            'emptyPassword' => [
                'username' => 'test',
                'password' => '',
            ],
        ];
    }

    /**
     * @covers ::grantAccessToken
     *
     * @param string $username
     * @param string $password
     *
     * @expectedException \OAuth2ServerException
     * @expectedExceptionMessage invalid_request
     *
     * @dataProvider grantAccessTokenWithEmptyUsernameOrPasswordProvider
     */
    public function testGrantAccessTokenWithEmptyUsernameOrPassword($username, $password)
    {
        $this->inputData['username'] = $username;
        $this->inputData['password'] = $password;

        $this->storage->expects($this->once())
                      ->method('checkClientCredentials')
                      ->with($this->inputData['client_id'], $this->inputData['client_secret'])
                      ->willReturn(true);

        $this->storage->expects($this->once())
                      ->method('checkRestrictedGrantType')
                      ->with($this->inputData['client_id'], $this->inputData['grant_type'])
                      ->willReturn(true);
        $this->storage->expects($this->never())->method('checkUserCredentials');

        $this->oAuth2Server->grantAccessToken($this->inputData);
    }

    /**
     * @covers ::grantAccessToken
     *
     * @expectedException \SugarApiExceptionNeedLogin
     */
    public function testGrantAccessTokenWithInvalidUsernameOrPassword()
    {
        $this->storage->expects($this->once())
                      ->method('checkClientCredentials')
                      ->with($this->inputData['client_id'], $this->inputData['client_secret'])
                      ->willReturn(true);

        $this->storage->expects($this->once())
                      ->method('checkRestrictedGrantType')
                      ->with($this->inputData['client_id'], $this->inputData['grant_type'])
                      ->willReturn(true);

        $this->storage->expects($this->once())
                      ->method('checkUserCredentials')
                      ->with($this->inputData['client_id'], $this->inputData['username'], $this->inputData['password'])
                      ->willThrowException(new \SugarApiExceptionNeedLogin(null));

        $this->oAuth2Server->grantAccessToken($this->inputData);
    }

    /**
     * @covers ::grantAccessToken
     *
     * @expectedException \SugarApiExceptionNeedLogin
     */
    public function testGrantAccessTokenWithValidUsernameOrPasswordUserJWTBearerFlowError()
    {
        $this->storage->expects($this->once())
                      ->method('checkClientCredentials')
                      ->with($this->inputData['client_id'], $this->inputData['client_secret'])
                      ->willReturn(true);

        $this->storage->expects($this->once())
                      ->method('checkRestrictedGrantType')
                      ->with($this->inputData['client_id'], $this->inputData['grant_type'])
                      ->willReturn(true);

        $this->storage->expects($this->once())
                      ->method('checkUserCredentials')
                      ->with($this->inputData['client_id'], $this->inputData['username'], $this->inputData['password'])
                      ->willReturn(['user_id' => 'seed_sally_id', 'scope' => null]);

        $this->oAuth2Server->expects($this->once())->method('getAuthProviderBasicBuilder')->willReturnCallback(
            function ($config) {
                $this->assertInstanceOf(Config::class, $config);
                return $this->authProviderBasicBuilder;
            }
        );

        $this->authManager->expects($this->once())->method('authenticate')->willReturnCallback(
            function ($token) {
                $this->assertInstanceOf(JWTBearerToken::class, $token);
                $this->assertEquals('srn:cluster:iam:eu:0000000001:user:seed_sally_id', $token->getIdentity());
                throw new AuthenticationException();
            }
        );

        $this->oAuth2Server->grantAccessToken($this->inputData);
    }

    /**
     * @covers ::grantAccessToken
     */
    public function testGrantAccessTokenWithValidUsernameOrPasswordUser()
    {
        $this->storage->refreshToken = $this->createMock(\OAuthToken::class);
        $this->storage->refreshToken->expects($this->once())->method('save');

        $this->storage->expects($this->once())
                      ->method('checkClientCredentials')
                      ->with($this->inputData['client_id'], $this->inputData['client_secret'])
                      ->willReturn(true);

        $this->storage->expects($this->once())
                      ->method('checkRestrictedGrantType')
                      ->with($this->inputData['client_id'], $this->inputData['grant_type'])
                      ->willReturn(true);

        $this->storage->expects($this->once())
                      ->method('checkUserCredentials')
                      ->with($this->inputData['client_id'], $this->inputData['username'], $this->inputData['password'])
                      ->willReturn(['user_id' => 'seed_sally_id', 'scope' => null]);

        $this->storage->expects($this->once())
                     ->method('unsetRefreshToken')
                     ->with($this->equalTo('testOldRefreshTokenId'));

        $this->oAuth2Server->expects($this->once())->method('getAuthProviderBasicBuilder')->willReturnCallback(
            function ($config) {
                $this->assertInstanceOf(Config::class, $config);
                return $this->authProviderBasicBuilder;
            }
        );

        $refreshTokenId = 'testRefreshTokenId';
        $this->oAuth2Server->expects($this->once())->method('genAccessToken')->willReturn($refreshTokenId);

        $this->authManager->expects($this->once())->method('authenticate')->willReturnCallback(
            function ($token) {
                $this->assertInstanceOf(JWTBearerToken::class, $token);
                $this->assertEquals('srn:cluster:iam:eu:0000000001:user:seed_sally_id', $token->getIdentity());
                $token->setAttribute('token', 'resultToken');
                $token->setAttribute('expires_in', '1');
                $token->setAttribute('token_type', 'bearer');
                $token->setAttribute('scope', 'offline');
                $token->setAttribute('srn', 'srn:cluster:iam:eu:0000000001:user:seed_sally_id');
                return $token;
            }
        );

        $this->oAuth2Server->setOldRefreshToken('testOldRefreshTokenId');

        $result = $this->oAuth2Server->grantAccessToken($this->inputData);

        $expectedResult = [
            'access_token' => 'resultToken',
            'expires_in' => '1',
            'token_type' => 'bearer',
            'scope' => 'offline',
            'refresh_token' => $refreshTokenId,
        ];
        foreach ($expectedResult as $key => $value) {
            $this->assertEquals($value, $result[$key]);
        }

        $this->assertEquals('resultToken', $this->storage->refreshToken->download_token);
    }

    /**
     * @covers ::verifyAccessToken
     * @covers ::setPlatform
     * @expectedException \OAuth2AuthenticateException
     */
    public function testVerifyAccessTokenWithAuthenticationException()
    {
        $this->oAuth2Server->expects($this->once())->method('getAuthProviderBuilder')->willReturnCallback(
            function ($config) {
                $this->assertInstanceOf(Config::class, $config);
                return $this->authProviderBuilder;
            }
        );

        $this->authManager->expects($this->once())->method('authenticate')->willReturnCallback(
            function ($token) {
                $this->assertInstanceOf(IntrospectToken::class, $token);
                $this->assertEquals('testPlatform', $token->getAttribute('platform'));
                throw new AuthenticationException();
            }
        );

        $this->oAuth2Server->setPlatform('testPlatform');
        $this->oAuth2Server->verifyAccessToken($this->stsAccessToken);
    }

    /**
     * @covers ::verifyAccessToken
     * @covers ::setPlatform
     */
    public function testVerifyAccessTokenWithNotAuthenticationToken()
    {
        $this->oAuth2Server->expects($this->once())->method('getAuthProviderBuilder')->willReturnCallback(
            function ($config) {
                $this->assertInstanceOf(Config::class, $config);
                return $this->authProviderBuilder;
            }
        );

        $this->authManager->expects($this->once())->method('authenticate')->willReturnCallback(
            function ($token) {
                $this->assertInstanceOf(IntrospectToken::class, $token);
                $this->assertEquals('testPlatform', $token->getAttribute('platform'));
                return $token;
            }
        );

        $this->oAuth2Server->setPlatform('testPlatform');
        $result = $this->oAuth2Server->verifyAccessToken($this->stsAccessToken);
        $this->assertEquals([], $result);
    }

    /**
     * @covers ::verifyAccessToken
     * @covers ::setPlatform
     */
    public function testVerifyAccessToken()
    {
        $this->oAuth2Server->expects($this->once())->method('getAuthProviderBuilder')->willReturnCallback(
            function ($config) {
                $this->assertInstanceOf(Config::class, $config);
                return $this->authProviderBuilder;
            }
        );

        $this->authManager->expects($this->once())->method('authenticate')->willReturnCallback(
            function ($token) {
                $this->assertInstanceOf(IntrospectToken::class, $token);
                $this->assertEquals('testPlatform', $token->getAttribute('platform'));
                $token->setUser($this->user);
                $token->setAuthenticated(true);
                $token->setAttribute('client_id', 'testClient');
                $token->setAttribute('exp', '123');
                return $token;
            }
        );

        $this->oAuth2Server->setPlatform('testPlatform');
        $result = $this->oAuth2Server->verifyAccessToken($this->stsAccessToken);
        $this->assertEquals(['client_id' => 'testClient', 'user_id' => 'testUserId', 'expires' => '123'], $result);
    }

    /**
     * @covers ::verifyAccessToken
     */
    public function testVerifyAccessTokenFromPortal()
    {
        $tokenData = [
            'client_id' => 'sugar',
            'user_id' => 'user_id',
            'expires' => (time() + 7200),
        ];

        $this->oAuth2Server->expects($this->never())->method('getAuthProviderBuilder');
        $this->storage->expects($this->once())
            ->method('getAccessToken')
            ->with($this->sugarAccessToken)
            ->willReturn($tokenData);
        $this->assertEquals($tokenData, $this->oAuth2Server->verifyAccessToken($this->sugarAccessToken));
    }
}
