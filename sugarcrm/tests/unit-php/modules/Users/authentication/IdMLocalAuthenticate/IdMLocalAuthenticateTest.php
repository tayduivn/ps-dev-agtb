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

use Sugarcrm\Sugarcrm\IdentityProvider\Authentication\AuthProviderManagerBuilder;
use Symfony\Component\Security\Core\Authentication\AuthenticationProviderManager;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
require 'include/utils.php';

/**
 * @coversDefaultClass \IdMLocalAuthenticate
 */
class IdMLocalAuthenticateTest extends PHPUnit_Framework_TestCase
{

    /**
     * @var PHPUnit_Framework_MockObject_MockObject
     */
    protected $idmLocalAuth;

    /**
     * @var PHPUnit_Framework_MockObject_MockObject
     */
    protected $authProviderBuilder;

    /**
     * @var PHPUnit_Framework_MockObject_MockObject
     */
    protected $authProviderManager;

    /**
     * @var PHPUnit_Framework_MockObject_MockObject
     */
    protected $token;

    /**
     * @inheritdoc
     */
    protected function setUp()
    {
        $this->idmLocalAuth = $this->getMockBuilder(IdMLocalAuthenticate::class)
            ->disableOriginalConstructor()
            ->setMethods(['getAuthProviderBuilder'])
            ->getMock();

        $this->authProviderBuilder = $this->createMock(AuthProviderManagerBuilder::class);

        $this->idmLocalAuth->expects($this->once())
            ->method('getAuthProviderBuilder')
            ->willReturn($this->authProviderBuilder);

        $this->authProviderManager = $this->createMock(AuthenticationProviderManager::class);

        $this->authProviderBuilder->expects($this->once())
            ->method('buildAuthProviders')
            ->willReturn($this->authProviderManager);

        $this->token = $this->createMock(UsernamePasswordToken::class);
    }

    /**
     * @covers IdMLocalAuthenticate::loginAuthenticate()
     * @expectedException SugarApiExceptionNeedLogin
     */
    public function testLoginAuthenticateFailure()
    {
        $this->authProviderManager->expects($this->once())
            ->method('authenticate')
            ->willThrowException(new \Exception());

        $this->idmLocalAuth->loginAuthenticate('test', 'test', false, ['passwordEncrypted' => false]);
    }

    /**
     * @covers IdMLocalAuthenticate::loginAuthenticate()
     */
    public function testLoginAuthenticateSuccess()
    {
        $this->authProviderManager->expects($this->once())
            ->method('authenticate')
            ->with($this->isInstanceOf(UsernamePasswordToken::class))
            ->willReturn($this->token);

        $this->token->expects($this->once())
            ->method('isAuthenticated')
            ->willReturn(true);

        $this->assertTrue(
            $this->idmLocalAuth->loginAuthenticate('test', 'test', false, ['passwordEncrypted' => false])
        );
    }
}
