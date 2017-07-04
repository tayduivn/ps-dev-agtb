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

namespace Sugarcrm\SugarcrmTestUnit\IdentityProvider\Authentication\User;

use Sugarcrm\Sugarcrm\IdentityProvider\Authentication\Lockout;
use Sugarcrm\Sugarcrm\IdentityProvider\Authentication\User;
use Sugarcrm\Sugarcrm\IdentityProvider\Authentication\User\LocalUserChecker;

/**
 * @coversDefaultClass \Sugarcrm\Sugarcrm\IdentityProvider\Authentication\User\LocalUserChecker
 */
class LocalUserCheckerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \User | \PHPUnit_Framework_MockObject_MockObject
     */
    protected $sugarUser;

    /**
     * @var User | \PHPUnit_Framework_MockObject_MockObject
     */
    protected $user;

    /**
     * @var Lockout | \PHPUnit_Framework_MockObject_MockObject
     */
    protected $lockout;


    protected function setUp()
    {
        $this->sugarUser = $this->createMock(\User::class);
        $this->user = $this->createMock(User::class);
        $this->lockout = $this->createMock(Lockout::class);

        parent::setUp();
    }

    /**
     * @return array
     */
    public function withExternalOnlyAuthProvider()
    {
        return [
            'int' => [1],
            'bool' => [true],
            'string' => ['true'],
        ];
    }

    /**
     * @covers ::checkPreAuth
     * @dataProvider withExternalOnlyAuthProvider
     *
     * @param mixed $externalAuthOnlyValue
     *
     * @expectedException \Sugarcrm\Sugarcrm\IdentityProvider\Authentication\Exception\InvalidUserException
     */
    public function testCheckPreAuthOfUserWithExternalAuthOnly($externalAuthOnlyValue)
    {
        $this->sugarUser->external_auth_only = $externalAuthOnlyValue;

        $this->user->method('getSugarUser')->willReturn($this->sugarUser);
        $this->user->method('isAccountNonLocked')->willReturn(true);
        $this->user->method('isAccountNonExpired')->willReturn(true);
        $this->user->method('isEnabled')->willReturn(true);

        $this->lockout->method('isEnabled')->willReturn(false);

        $checker = new LocalUserChecker($this->lockout);

        $checker->checkPreAuth($this->user);
    }

    /**
     * @return array
     */
    public function withoutExternalOnlyAuthProvider()
    {
        return [
            'zero' => [0],
            'null' => [null],
            'false' => [false],
        ];
    }

    /**
     * @covers ::checkPreAuth
     * @dataProvider withoutExternalOnlyAuthProvider
     *
     * @param mixed $externalAuthOnlyValue
     */
    public function testAuthenticateUserWithoutWithExternalAuthOnly($externalAuthOnlyValue)
    {
        $this->sugarUser->external_auth_only = $externalAuthOnlyValue;

        $this->user->method('getSugarUser')->willReturn($this->sugarUser);
        $this->user->method('isAccountNonLocked')->willReturn(true);
        $this->user->method('isAccountNonExpired')->willReturn(true);
        $this->user->method('isEnabled')->willReturn(true);

        $this->lockout->method('isEnabled')->willReturn(false);

        $checker = new LocalUserChecker($this->lockout);

        $this->assertEmpty($checker->checkPreAuth($this->user));
    }
}
