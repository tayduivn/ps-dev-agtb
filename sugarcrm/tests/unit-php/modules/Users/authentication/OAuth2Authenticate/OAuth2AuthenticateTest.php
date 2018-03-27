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

use OAuth2Authenticate;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use SugarConfig;
use Sugarcrm\Sugarcrm\IdentityProvider\Authentication\AuthProviderBasicManagerBuilder;
use Sugarcrm\Sugarcrm\IdentityProvider\Authentication\User;
use Symfony\Component\Security\Core\Authentication\AuthenticationProviderManager;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Symfony\Component\Security\Core\Exception\AuthenticationException;

/**
 * @coversDefaultClass \OAuth2Authenticate
 */
class OAuth2AuthenticateTest extends TestCase
{
    /**
     * @var OAuth2Authenticate
     */
    protected $auth;

    /**
     * @var MockObject
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
     * @var AuthenticationProviderManager|MockObject
     */
    protected $authManager;

    /**
     * @var \User|MockObject
     */
    protected $sugarUser;

    /**
     * set up
     */
    protected function setUp()
    {
        $this->savedConfig['site_url'] = SugarConfig::getInstance()->get('site_url');
        $this->savedConfig['idm_mode'] = SugarConfig::getInstance()->get('idm_mode');
        SugarConfig::getInstance()->_cached_values['site_url'] = 'http://test.sugarcrm.local';

        $this->auth = new OAuth2Authenticate();
        $this->authMock = $this->getMockBuilder(OAuth2Authenticate::class)
            ->disableOriginalConstructor()
            ->setMethods(['getAuthProviderBasicBuilder', 'getTenant'])->getMock();

        $this->sugarUser = $this->createMock(\User::class);
        $this->sugarUser->id = 'userId';

        $this->authProviderBasicBuilder = $this->createMock(AuthProviderBasicManagerBuilder::class);
        $this->authManager = $this->createMock(AuthenticationProviderManager::class);

        $this->authMock->method('getAuthProviderBasicBuilder')->willReturn($this->authProviderBasicBuilder);
        $this->authMock->method('getTenant')->willReturn('srn:tenant');
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
    public function testGetLoginUrlWithValidConfig()
    {
        SugarConfig::getInstance()->_cached_values['idm_mode'] = [
            'clientId' => 'testLocal',
            'clientSecret' => 'testLocalSecret',
            'redirectUri' => '',
            'stsUrl' => 'http://sts.sugarcrm.local',
            'idpUrl' => 'http://idp.url',
            'stsKeySetId' => 'keySetId',
        ];
        $this->assertEquals('http://sts.sugarcrm.local', $this->auth->getLoginUrl());
    }

    /**
     * @covers ::getLoginUrl
     *
     * @expectedException \RuntimeException
     */
    public function testGetLoginUrlWithEmptyConfig()
    {
        SugarConfig::getInstance()->_cached_values['idm_mode'] = null;
        $this->auth->getLoginUrl();
    }

    /**
     * @covers ::getLogoutUrl
     */
    public function testGetLogoutUrl()
    {
        $this->assertFalse($this->auth->getLogoutUrl());
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
