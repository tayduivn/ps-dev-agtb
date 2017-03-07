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

namespace Sugarcrm\SugarcrmTestsUnit\IdentityProvider\Authentication\User;

use Sugarcrm\Sugarcrm\IdentityProvider\Authentication\Exception\TemporaryLockedUserException;
use Sugarcrm\Sugarcrm\IdentityProvider\Authentication\User\SugarUserChecker;
use Sugarcrm\Sugarcrm\IdentityProvider\Authentication\User;
use Sugarcrm\Sugarcrm\IdentityProvider\Authentication\Lockout;

/**
 * @coversDefaultClass \Sugarcrm\Sugarcrm\IdentityProvider\Authentication\User\SugarUserChecker
 */
class SugarUserCheckerTest extends \PHPUnit_Framework_TestCase
{

    /**
     * @var Lockout|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $lockout;

    /**
     * @var User|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $user;

    /**
     * @var SugarUserChecker
     */
    protected $checker;

    /**
     * @expectedException \Sugarcrm\Sugarcrm\IdentityProvider\Authentication\Exception\TemporaryLockedUserException
     * @covers ::checkPreAuth
     */
    public function testLockedUser()
    {
        $this->lockout
            ->method('isEnabled')
            ->willReturn(true);
        $this->lockout
            ->method('isUserLocked')
            ->willReturn(true);

        $this->lockout->expects($this->once())
            ->method('throwLockoutException')
            ->with($this->isInstanceOf(User::class))
            ->willThrowException(new TemporaryLockedUserException('test'));

        $this->checker->checkPreAuth($this->user);
    }

    /**
     * @inheritDoc
     */
    protected function setUp()
    {
        parent::setUp();

        $this->lockout = $this->getMockBuilder(Lockout::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->user = $this->getMockBuilder(User::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->user->method('isAccountNonLocked')->willReturn(true);
        $this->user->method('isEnabled')->willReturn(true);
        $this->user->method('isAccountNonExpired')->willReturn(true);


        $this->checker = new SugarUserChecker($this->lockout);
    }
}
