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

use Sugarcrm\Sugarcrm\IdentityProvider\Authentication\User;
use Sugarcrm\Sugarcrm\IdentityProvider\Authentication\AuthProviderBasicManagerBuilder;
use Symfony\Component\Security\Core\Authentication\AuthenticationProviderManager;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Symfony\Component\Security\Core\Exception\AuthenticationException;

require_once 'include/utils.php';
require_once 'include/SugarCache/SugarCache.php';

/**
 * @coversDefaultClass  \SugarOAuth2StorageOIDC
 */
class SugarOAuth2StorageOIDCTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var AuthProviderBasicManagerBuilder | \PHPUnit_Framework_MockObject_MockObject
     */
    protected $authProviderBasicBuilder;

    /**
     * @var AuthenticationProviderManager | \PHPUnit_Framework_MockObject_MockObject
     */
    protected $authManager;

    /**
     * @var \SugarOAuth2StorageOIDC | \PHPUnit_Framework_MockObject_MockObject
     */
    protected $storage;

    /**
     * @var \User | \PHPUnit_Framework_MockObject_MockObject
     */
    protected $sugarUser;

    /**
     * @inheritdoc
     */
    protected function setUp()
    {
        $this->authProviderBasicBuilder = $this->createMock(AuthProviderBasicManagerBuilder::class);
        $this->authManager = $this->createMock(AuthenticationProviderManager::class);
        $this->sugarUser = $this->createMock(\User::class);
        $this->sugarUser->id = 'userId';

        $this->storage = $this->getMockBuilder(\SugarOAuth2StorageOIDC::class)
                              ->disableOriginalConstructor()
                              ->setMethods(['getAuthProviderBasicBuilder', 'getTenant'])->getMock();

        $this->storage->method('getAuthProviderBasicBuilder')->willReturn($this->authProviderBasicBuilder);
        $this->storage->method('getTenant')->willReturn('srn:tenant');
        $this->authProviderBasicBuilder->method('buildAuthProviders')->willReturn($this->authManager);

        $GLOBALS['log'] = $this->getMockBuilder(\LoggerManager::class)
                               ->disableOriginalConstructor()
                               ->getMock();
    }

    protected function tearDown()
    {
        unset($GLOBALS['log']);
    }

    /**
     * @covers ::checkUserCredentials
     * @expectedException \SugarApiExceptionNeedLogin
     */
    public function testCheckUserCredentialsAuthenticationException()
    {
        $this->authManager->expects($this->once())->method('authenticate')->willReturnCallback(
            function ($token) {
                $this->assertEquals('user', $token->getUsername());
                $this->assertEquals('password', $token->getCredentials());
                throw new AuthenticationException();
            }
        );
        $this->storage->checkUserCredentials('client_id', 'user', 'password');
    }

    /**
     * @covers ::checkUserCredentials
     * @expectedException \SugarApiExceptionNeedLogin
     */
    public function testCheckUserCredentialsAuthenticationError()
    {
        $this->authManager->expects($this->once())->method('authenticate')->willReturnCallback(
            function ($token) {
                $this->assertEquals('user', $token->getUsername());
                $this->assertEquals('password', $token->getCredentials());
                $token->setAuthenticated(false);
                return $token;
            }
        );
        $this->storage->checkUserCredentials('client_id', 'user', 'password');
    }

    /**
     * @covers ::checkUserCredentials
     */
    public function testCheckUserCredentials()
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
        $result =$this->storage->checkUserCredentials('client_id', 'user', 'password');
        $this->assertEquals(['user_id' => 'userId', 'scope' => null], $result);
    }
}
