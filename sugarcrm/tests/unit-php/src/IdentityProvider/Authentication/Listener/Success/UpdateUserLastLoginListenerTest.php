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

use Sugarcrm\Sugarcrm\IdentityProvider\Authentication\Listener\Success\UpdateUserLastLoginListener;
use Symfony\Component\Security\Core\Event\AuthenticationEvent;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Sugarcrm\Sugarcrm\IdentityProvider\Authentication\User;

/**
 * @coversDefaultClass Sugarcrm\Sugarcrm\IdentityProvider\Authentication\Listener\Success\UpdateUserLastLoginListener
 */
class UpdateUserLastLoginListenerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var UpdateUserLastLoginListener
     */
    protected $listener;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $token;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $sugarUser;

    /**
     * @var AuthenticationEvent
     */
    protected $event;

    /**
     * @var User
     */
    protected $user;

    /**
     * @inheritdoc
     */
    public function setUp()
    {
        parent::setUp();

        $this->sugarUser = $this->createMock(\User::class);

        $this->user = new User('test', 'test', []);
        $this->user->setSugarUser($this->sugarUser);

        $this->token = $this->createMock(UsernamePasswordToken::class);
        $this->token->expects($this->once())
            ->method('getUser')
            ->willReturn($this->user);

        $this->event = new AuthenticationEvent($this->token);
        $this->listener = new UpdateUserLastLoginListener();
    }

    /**
     * @covers ::execute
     */
    public function testExecute()
    {
        $this->sugarUser->expects($this->once())
            ->method('updateLastLogin')
            ->willReturn(true);
        $this->listener->execute($this->event);
    }
}
