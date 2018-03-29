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

namespace Sugarcrm\SugarcrmTestUnit\modules\Users\authentication\OAuth2Authenticate;

use League\OAuth2\Client\Token\AccessToken;
use OAuth2Authenticate;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use SugarConfig;
use Sugarcrm\Sugarcrm\IdentityProvider\Authentication\AuthProviderBasicManagerBuilder;
use Sugarcrm\Sugarcrm\IdentityProvider\Authentication\OAuth2\Client\Provider\IdmProvider;
use Sugarcrm\Sugarcrm\IdentityProvider\Authentication\User;
use Sugarcrm\Sugarcrm\IdentityProvider\OAuth2StateRegistry;
use Symfony\Component\Security\Core\Authentication\AuthenticationProviderManager;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Symfony\Component\Security\Core\Exception\AuthenticationException;

/**
 * @coversDefaultClass \OAuth2Authenticate
 */
class OAuth2AuthenticateTest extends TestCase
{
    /**
     * @var OAuth2Authenticate | MockObject
     */
    protected $authMock;

    /**
     * @var array|mixed
     */
    protected $savedConfig = [];

    /**
     * @var AuthProviderBasicManagerBuilder|MockObject
     */
    protected $authProviderBasicBuilder;

    /**
     * @var AuthenticationProviderManager | MockObject
     */
    protected $authManager;

    /**
     * @var \User|MockObject
     */
    protected $sugarUser;

    /**
     * @var OAuth2StateRegistry | MockObject
     */
    protected $stateRegistryMock;

    /**
     * @var IdmProvider | MockObject
     */
    protected $idmProviderMock;

    /**
     * set up
     */
    protected function setUp()
    {
        $this->savedConfig['site_url'] = SugarConfig::getInstance()->get('site_url');
        $this->savedConfig['idm_mode'] = SugarConfig::getInstance()->get('idm_mode');
        SugarConfig::getInstance()->_cached_values['site_url'] = 'http://test.sugarcrm.local';

        $this->authMock = $this->getMockBuilder(OAuth2Authenticate::class)
            ->disableOriginalConstructor()
            ->setMethods([
                'getAuthProviderBasicBuilder',
                'getTenant',
                'getIdmProvider',
                'getStateRegistry',
                'createState',
            ])
            ->getMock();

        $this->stateRegistryMock = $this->createMock(OAuth2StateRegistry::class);
        $this->idmProviderMock = $this->createMock(IdmProvider::class);

        $this->sugarUser = $this->createMock(\User::class);
        $this->sugarUser->id = 'userId';

        $this->authProviderBasicBuilder = $this->createMock(AuthProviderBasicManagerBuilder::class);
        $this->authManager = $this->createMock(AuthenticationProviderManager::class);

        $this->authMock->method('getAuthProviderBasicBuilder')->willReturn($this->authProviderBasicBuilder);
        $this->authMock->method('getTenant')->willReturn('srn:tenant');
        $this->authMock->method('getIdmProvider')->willReturn($this->idmProviderMock);
        $this->authMock->method('getStateRegistry')->willReturn($this->stateRegistryMock);
        $this->authProviderBasicBuilder->method('buildAuthProviders')->willReturn($this->authManager);
    }

    /**
     * tear down
     */
    protected function tearDown()
    {
        SugarConfig::getInstance()->_cached_values['site_url'] = $this->savedConfig['site_url'];
        SugarConfig::getInstance()->_cached_values['idm_mode'] = $this->savedConfig['idm_mode'];
    }

    /**
     * @covers ::getLoginUrl
     */
    public function testGetLoginUrlWithValidConfig() : void
    {
        SugarConfig::getInstance()->_cached_values['idm_mode'] = [
            'clientId' => 'testLocal',
            'clientSecret' => 'testLocalSecret',
            'stsUrl' => 'http://sts.sugarcrm.local',
            'idpUrl' => 'http://idp.url',
            'stsKeySetId' => 'keySetId',
            'requestedOAuthScopes' => ['offline', 'profile'],
            'tid' => 'srn:cloud:idp:eu:0000000001:tenant',
        ];
        $expectedQueryData = [
            'state' => 'generated',
            'response_type' => 'code',
            'approval_prompt' => 'auto',
            'redirect_uri' => 'http://test.sugarcrm.local/?module=Users&action=OAuth2CodeExchange',
            'client_id' => 'testLocal',
            'scope' => 'offline profile',
            'tenant_hint' => 'srn:cloud:idp:eu:0000000001:tenant',

        ];
        $expectedUrl = 'http://sts.sugarcrm.local/oauth2/auth?' . http_build_query($expectedQueryData);

        $this->authMock->expects($this->once())->method('createState')->willReturn('generated');
        $this->idmProviderMock->expects($this->once())
            ->method('getAuthorizationUrl')
            ->with([
                'scope' => [
                    'offline',
                    'profile',
                ],
                'state' => 'generated',
                'tenant_hint' => 'srn:cloud:idp:eu:0000000001:tenant',
            ])->willReturn($expectedUrl);
        $this->assertEquals($expectedUrl, $this->authMock->getLoginUrl());
    }

    /**
     * @covers ::getLoginUrl
     *
     * @expectedException \RuntimeException
     */
    public function testGetLoginUrlWithEmptyConfig()
    {
        SugarConfig::getInstance()->_cached_values['idm_mode'] = null;
        $this->authMock->getLoginUrl();
    }

    /**
     * @covers ::getLogoutUrl
     */
    public function testGetLogoutUrl()
    {
        $this->assertFalse($this->authMock->getLogoutUrl());
    }


    /**
     * @covers ::loginAuthenticate
     * @expectedException \Symfony\Component\Security\Core\Exception\AuthenticationException
     */
    public function testLoginAuthenticateAuthenticationException()
    {
        $this->authManager->expects($this->once())->method('authenticate')->willReturnCallback(
            function ($token) {
                $this->assertEquals('user', $token->getUsername());
                $this->assertEquals('password', $token->getCredentials());
                throw new AuthenticationException();
            }
        );
        $this->authMock->loginAuthenticate('user', 'password');
    }

    /**
     * @covers ::loginAuthenticate
     */
    public function testLoginAuthenticateAuthenticationError()
    {
        $this->authManager->expects($this->once())->method('authenticate')->willReturnCallback(
            function ($token) {
                $this->assertEquals('user', $token->getUsername());
                $this->assertEquals('password', $token->getCredentials());
                $token->setAuthenticated(false);
                return $token;
            }
        );
        $this->assertFalse($this->authMock->loginAuthenticate('user', 'password'));
    }

    /**
     * @covers ::loginAuthenticate
     */
    public function testLoginAuthenticate()
    {
        $this->authManager->expects($this->once())->method('authenticate')->willReturnCallback(
            function ($token) {
                $this->assertEquals('user', $token->getUsername());
                $this->assertEquals('password', $token->getCredentials());
                $user = new User();
                $user->setSugarUser($this->sugarUser);
                $token->setUser($user);

                $resultToken = new UsernamePasswordToken(
                    $user,
                    $token->getCredentials(),
                    $token->getProviderKey(),
                    $token->getRoles()
                );

                return $resultToken;
            }
        );
        $result = $this->authMock->loginAuthenticate('user', 'password');
        $this->assertEquals(['user_id' => 'userId', 'scope' => null], $result);
    }
}
