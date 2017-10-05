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

use Sugarcrm\Sugarcrm\IdentityProvider\Authentication\AuthProviderOIDCManagerBuilder;
use Sugarcrm\Sugarcrm\IdentityProvider\Authentication\Config;
use Sugarcrm\Sugarcrm\IdentityProvider\Authentication\Token\OIDC\IntrospectToken;
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
     * @inheritdoc
     */
    public function setUp()
    {
        $this->storage = $this->createMock(\SugarOAuth2StorageOIDC::class);
        $this->oAuth2Server = $this->getMockBuilder(\SugarOAuth2ServerOIDC::class)
                                   ->setConstructorArgs([$this->storage, []])
                                   ->setMethods(['getAuthProviderBuilder'])
                                   ->getMock();

        $this->authProviderBuilder = $this->createMock(AuthProviderOIDCManagerBuilder::class);
        $this->authManager = $this->createMock(AuthenticationProviderManager::class);
        $this->authProviderBuilder->method('buildAuthProviders')->willReturn($this->authManager);
        $this->sugarUser = $this->createMock(\User::class);
        $this->sugarUser->id = 'testUserId';
        $this->user = new User();
        $this->user->setSugarUser($this->sugarUser);
    }

    /**
     * @covers ::grantAccessToken
     *
     * @expectedException \BadMethodCallException
     */
    public function testGrantAccessToken()
    {
        $this->oAuth2Server->grantAccessToken();
    }

    /**
     * @covers ::verifyAccessToken
     * @covers ::setPlatform
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
        $result = $this->oAuth2Server->verifyAccessToken('test');
        $this->assertEquals([], $result);
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
        $result = $this->oAuth2Server->verifyAccessToken('test');
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
        $result = $this->oAuth2Server->verifyAccessToken('test');
        $this->assertEquals(['client_id' => 'testClient', 'user_id' => 'testUserId', 'expires' => '123'], $result);
    }
}
