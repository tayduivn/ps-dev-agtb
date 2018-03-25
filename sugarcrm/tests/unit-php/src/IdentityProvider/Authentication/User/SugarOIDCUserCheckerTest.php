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

use PHPUnit\Framework\TestCase;
use Sugarcrm\Sugarcrm\IdentityProvider\Authentication\User;
use Sugarcrm\Sugarcrm\IdentityProvider\Authentication\User\SugarOIDCUserChecker;
use Sugarcrm\Sugarcrm\IdentityProvider\Authentication\UserProvider\SugarLocalUserProvider;
use Symfony\Component\Security\Core\Exception\UsernameNotFoundException;

/**
 * @coversDefaultClass Sugarcrm\Sugarcrm\IdentityProvider\Authentication\User\SugarOIDCUserChecker
 */
class SugarOIDCUserCheckerTest extends TestCase
{
    /**
     * @var SugarLocalUserProvider | \PHPUnit_Framework_MockObject_MockObject
     */
    protected $localUserProvider;

    /**
     * @var SugarOIDCUserChecker
     */
    protected $userChecker;

    /**
     * @var User | \PHPUnit_Framework_MockObject_MockObject
     */
    protected $user;

    /**
     * @var User | \PHPUnit_Framework_MockObject_MockObject
     */
    protected $foundUser;

    /**
     * @var \User | \PHPUnit_Framework_MockObject_MockObject
     */
    protected $sugarUser;

    /**
     * @var \EmailAddress | \PHPUnit_Framework_MockObject_MockObject
     */
    protected $emailAddress;

    /**
     * @inheritdoc
     */
    protected function setUp()
    {
        $this->localUserProvider = $this->createMock(SugarLocalUserProvider::class);
        $this->userChecker = new SugarOIDCUserChecker($this->localUserProvider);
        $this->user = $this->createMock(User::class);
        $this->foundUser = $this->createMock(User::class);
        $this->sugarUser = $this->createMock(\User::class);
        $this->emailAddress = $this->createMock(\EmailAddress::class);
        $this->sugarUser->emailAddress = $this->emailAddress;
        $this->user->method('isCredentialsNonExpired')->willReturn(true);
    }

    /**
     * @covers ::checkPostAuth
     */
    public function testCheckPostAuthWithExistingUser()
    {
        $this->user->expects($this->exactly(2))
            ->method('getAttribute')
            ->withConsecutive([$this->equalTo('oidc_data')], [$this->equalTo('oidc_identify')])
            ->willReturnOnConsecutiveCalls(
                ['user_name' => 'test', 'first_name' => 'new_name', 'email' => 'new@test.lh'],
                ['field' => 'id', 'value' => 'seed_sally_id']
            );
        $this->localUserProvider->expects($this->once())
                                ->method('loadUserByField')
                                ->with('seed_sally_id', 'id')
                                ->willReturn($this->foundUser);
        $this->foundUser->expects($this->once())->method('getSugarUser')->willReturn($this->sugarUser);

        $this->sugarUser->first_name = 'old_name';
        $this->emailAddress->expects($this->once())
            ->method('getPrimaryAddress')
            ->with($this->sugarUser)
            ->willReturn('old@test.lh');
        $this->sugarUser->expects($this->once())->method('save');

        $this->emailAddress->expects($this->once())
            ->method('addAddress')
            ->with($this->equalTo('new@test.lh'), $this->isTrue());

        $this->emailAddress->expects($this->once())->method('save');

        $this->user->expects($this->once())->method('setSugarUser')->with($this->sugarUser);
        $this->userChecker->checkPostAuth($this->user);
    }

    /**
     * @covers ::checkPostAuth
     */
    public function testCheckPostAuthWithNotExistingUser()
    {
        $expectedAttributes = [
            'employee_status' => User::USER_EMPLOYEE_STATUS_ACTIVE,
            'status' => User::USER_STATUS_ACTIVE,
            'is_admin' => 0,
            'external_auth_only' => 1,
            'system_generated_password' => 0,
            'id' => 'seed_sally_id',
            'user_name' => 'test',
        ];
        $this->user->expects($this->exactly(2))
            ->method('getAttribute')
            ->withConsecutive([$this->equalTo('oidc_data')], [$this->equalTo('oidc_identify')])
            ->willReturnOnConsecutiveCalls(
                ['user_name' => 'test'],
                ['field' => 'id', 'value' => 'seed_sally_id']
            );
        $this->user->method('getSrn')->willReturn('srn:cluster:idm:eu:0000000001:user:seed_sally_id');
        $this->localUserProvider->expects($this->once())
                                ->method('loadUserByField')
                                ->with('seed_sally_id', 'id')
                                ->willThrowException(new UsernameNotFoundException());
        $this->localUserProvider->expects($this->once())
                                ->method('createUser')
                                ->with('test', $expectedAttributes)
                                ->willReturn($this->sugarUser);
        $this->user->expects($this->once())->method('setSugarUser')->with($this->sugarUser);
        $this->userChecker->checkPostAuth($this->user);
    }
}
