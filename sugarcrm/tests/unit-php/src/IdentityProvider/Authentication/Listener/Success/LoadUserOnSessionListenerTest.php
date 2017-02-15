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
namespace Sugarcrm\SugarcrmTestUnit\IdentityProvider\Authentication\Listener\Success;

use Sugarcrm\Sugarcrm\IdentityProvider\Authentication\Listener\Success\LoadUserOnSessionListener;
use Sugarcrm\Sugarcrm\IdentityProvider\Authentication\User;
use Symfony\Component\Security\Core\Event\AuthenticationEvent;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;

/**
 * @coversDefaultClass Sugarcrm\Sugarcrm\IdentityProvider\Authentication\Listener\Success\LoadUserOnSessionListener
 */
class LoadUserOnSessionListenerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @covers ::execute
     */
    public function testExecute()
    {
        $user = $this->createMock(User::class);

        $token = $this->createMock(UsernamePasswordToken::class);
        $token->expects($this->once())
            ->method('getUser')
            ->willReturn($user);

        $event = $this->createMock(AuthenticationEvent::class);
        $event->expects($this->once())
            ->method('getAuthenticationToken')
            ->willReturn($token);

        $listener = $this->getMockBuilder(LoadUserOnSessionListener::class)
            ->setMethods(['setGlobalUser', 'setSessionUserId'])
            ->getMock();
        $listener->expects($this->once())
            ->method('setGlobalUser')
            ->with($this->isInstanceOf(User::class))
            ->willReturn(true);
        $listener->expects($this->once())
            ->method('setSessionUserId')
            ->with($this->isInstanceOf(User::class))
            ->willReturn(true);
        $listener->execute($event);
    }
}
