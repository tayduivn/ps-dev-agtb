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

use Sugarcrm\Sugarcrm\IdentityProvider\Authentication\Listener\Success\UserPasswordListener;
use Sugarcrm\Sugarcrm\IdentityProvider\Authentication\User;

use Symfony\Component\Security\Core\Event\AuthenticationEvent;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;

/**
 * @coversDefaultClass Sugarcrm\Sugarcrm\IdentityProvider\Authentication\Listener\Success\UserPasswordListener
 */
class UserPasswordListenerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
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
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $config;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $timeDate;

    /**
     * @var AuthenticationEvent
     */
    protected $event;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $user;

    /**
     * @inheritdoc
     */
    public function setUp()
    {
        parent::setUp();

        $this->sugarUser = $this->createMock(\User::class);

        $this->user = $this->createMock(User::class);
        $this->user->expects($this->any())
            ->method('getSugarUser')
            ->willReturn($this->sugarUser);

        $this->token = $this->createMock(UsernamePasswordToken::class);
        $this->token->expects($this->once())
            ->method('getUser')
            ->willReturn($this->user);

        $this->event = new AuthenticationEvent($this->token);
        $this->config = $this->createMock(\SugarConfig::class);
        $this->timeDate = $this->createMock(\TimeDate::class);

        $this->listener = $this->getMockBuilder(UserPasswordListener::class)
            ->setMethods(['getTimeDate', 'getSugarConfig', 'setSessionVariable'])
            ->getMock();

        $this->listener->expects($this->any())
            ->method('getTimeDate')
            ->willReturn($this->timeDate);

        $this->listener->expects($this->any())
            ->method('getSugarConfig')
            ->willReturn($this->config);
    }

    /**
     * @covers ::execute
     */
    public function testExecuteCheckTimeLastDateExistNotExpired()
    {
        $this->listener->expects($this->never())
            ->method('setSessionVariable');

        $now = $this->createMock(\SugarDateTime::class);
        $now->ts = 1;

        $lastChange = $this->createMock(\SugarDateTime::class);
        $lastChange->ts = 2;
        $lastChange->expects($this->once())
            ->method('get')
            ->with($this->equalTo('+1 days'))
            ->willReturnSelf();

        $this->user->expects($this->exactly(2))
            ->method('getPasswordType')
            ->willReturn(User::PASSWORD_TYPE_USER);

        $this->config->expects($this->any())
            ->method('get')
            ->withConsecutive(
                [$this->equalTo('passwordsetting.' . User::PASSWORD_TYPE_USER . 'expiration'), $this->identicalTo(0)],
                [$this->equalTo('passwordsetting.' . User::PASSWORD_TYPE_USER . 'expirationtype'), $this->equalTo(1)],
                [$this->equalTo('passwordsetting.' . User::PASSWORD_TYPE_USER . 'expirationtime'), $this->equalTo(1)]
            )
            ->willReturnOnConsecutiveCalls(
                User::PASSWORD_EXPIRATION_TYPE_TIME,
                1,
                1
            );

        $this->timeDate->expects($this->once())
            ->method('nowDb')
            ->willReturn($now);

        $this->user->expects($this->once())
            ->method('getPasswordLastChangeDate')
            ->willReturn($lastChange);

        $this->timeDate->expects($this->once())
            ->method('fromUser')
            ->with($this->isInstanceOf(\SugarDateTime::class), $this->isInstanceOf(\User::class))
            ->willReturn($lastChange);

        $this->listener->execute($this->event);
    }

    /**
     * @covers ::execute
     */
    public function testExecuteCheckTimeLastDateNotExistExpired()
    {
        $now = $this->createMock(\SugarDateTime::class);
        $now->ts = 1;

        $lastChange = $this->createMock(\SugarDateTime::class);
        $lastChange->ts = 0;
        $lastChange->expects($this->once())
            ->method('get')
            ->with($this->equalTo('+1 days'))
            ->willReturnSelf();

        $this->user->expects($this->exactly(2))
            ->method('getPasswordType')
            ->willReturn(User::PASSWORD_TYPE_USER);

        $this->config->expects($this->exactly(3))
            ->method('get')
            ->withConsecutive(
                [$this->equalTo('passwordsetting.' . User::PASSWORD_TYPE_USER . 'expiration'), $this->identicalTo(0)],
                [$this->equalTo('passwordsetting.' . User::PASSWORD_TYPE_USER . 'expirationtype'), $this->equalTo(1)],
                [$this->equalTo('passwordsetting.' . User::PASSWORD_TYPE_USER . 'expirationtime'), $this->equalTo(1)]
            )
            ->willReturnOnConsecutiveCalls(
                User::PASSWORD_EXPIRATION_TYPE_TIME,
                1,
                1
            );

        $this->timeDate->expects($this->once())
            ->method('nowDb')
            ->willReturn($now);

        $this->user->expects($this->once())
            ->method('getPasswordLastChangeDate')
            ->willReturn(null);

        $this->user->expects($this->once())
            ->method('setPasswordLastChangeDate')
            ->with($this->isInstanceOf(\SugarDateTime::class));

        $this->user->expects($this->once())
            ->method('allowUpdateDateModified')
            ->with($this->isFalse());

        $this->sugarUser->expects($this->once())
            ->method('save');

        $this->timeDate->expects($this->once())
            ->method('fromDb')
            ->with($this->isInstanceOf(\SugarDateTime::class))
            ->willReturn($lastChange);

        $this->listener->expects($this->exactly(2))
            ->method('setSessionVariable')
            ->withConsecutive(
                [$this->equalTo('expiration_label'), $this->equalTo('LBL_PASSWORD_EXPIRATION_TIME')],
                [$this->equalTo('hasExpiredPassword'), $this->equalTo('1')]
            );

        $this->listener->execute($this->event);
    }
    /**
     * @covers ::execute
     */
    public function testExecuteCheckAttempts()
    {
        $this->user->expects($this->exactly(2))
            ->method('getPasswordType')
            ->willReturn(User::PASSWORD_TYPE_USER);

        $this->config->expects($this->exactly(2))
            ->method('get')
            ->withConsecutive(
                [$this->equalTo('passwordsetting.' . User::PASSWORD_TYPE_USER . 'expiration'), $this->identicalTo(0)],
                [$this->equalTo('passwordsetting.' . User::PASSWORD_TYPE_USER . 'expirationlogin'), $this->isNull()]
            )
            ->willReturnOnConsecutiveCalls(
                User::PASSWORD_EXPIRATION_TYPE_LOGIN,
                0
            );

        $this->sugarUser->expects($this->once())
            ->method('getPreference')
            ->with($this->equalTo('loginexpiration'))
            ->willReturn(1);

        $this->sugarUser->expects($this->once())
            ->method('setPreference')
            ->with($this->equalTo('loginexpiration'), $this->equalTo(2));

        $this->user->expects($this->once())
            ->method('allowUpdateDateModified')
            ->with($this->isFalse());

        $this->sugarUser->expects($this->once())
            ->method('save');

        $this->listener->expects($this->exactly(2))
            ->method('setSessionVariable')
            ->withConsecutive(
                [$this->equalTo('expiration_label'), $this->equalTo('LBL_PASSWORD_EXPIRATION_LOGIN')],
                [$this->equalTo('hasExpiredPassword'), $this->equalTo('1')]
            );

        $this->listener->execute($this->event);
    }
}
