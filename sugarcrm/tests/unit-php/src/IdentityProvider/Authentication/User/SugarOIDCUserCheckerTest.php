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

use Sugarcrm\Sugarcrm\IdentityProvider\Authentication\User\SugarOIDCUserChecker;
use Sugarcrm\Sugarcrm\IdentityProvider\Authentication\UserProvider\SugarLocalUserProvider;
use Sugarcrm\Sugarcrm\IdentityProvider\Authentication\User;
use Symfony\Component\Security\Core\Exception\UsernameNotFoundException;

/**
 * @coversDefaultClass Sugarcrm\Sugarcrm\IdentityProvider\Authentication\User\SugarOIDCUserChecker
 */
class SugarOIDCUserCheckerTest extends \PHPUnit_Framework_TestCase
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
     * @var string
     */
    protected $userName = 'testUser';

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
        $this->user->method('isCredentialsNonExpired')->willReturn(true);
    }

    /**
     * Provides data for testCheckPostAuthWithExistingUser and testCheckPostAuthWithNotExistingUser
     * @return array
     */
    public function checkPostAuthWithExistingUserProvider()
    {
        return [
            'localUser' => [
                'externalAuthInfo' => [
                    'amr' => ['PROVIDER_KEY_LOCAL'],
                ],
                'expectedSugarField' => 'user_name',
            ],
            'samlUser' => [
                'externalAuthInfo' => [
                    'amr' => ['PROVIDER_KEY_SAML'],
                ],
                'expectedSugarField' => 'email',
            ],
        ];
    }

    /**
     * @param array $externalAuthInfo
     * @param $expectedSugarField
     *
     * @dataProvider checkPostAuthWithExistingUserProvider
     *
     * @covers ::checkPostAuth
     */
    public function testCheckPostAuthWithExistingUser(array $externalAuthInfo, $expectedSugarField)
    {
        $this->user->expects($this->once())
                   ->method('getAttribute')
                   ->with('ext')
                   ->willReturn($externalAuthInfo);
        $this->user->expects($this->once())->method('getUsername')->willReturn($this->userName);
        $this->localUserProvider->expects($this->once())
                                ->method('loadUserByField')
                                ->with($this->userName, $expectedSugarField)
                                ->willReturn($this->foundUser);
        $this->foundUser->expects($this->once())->method('getSugarUser')->willReturn($this->sugarUser);
        $this->user->expects($this->once())->method('setSugarUser')->with($this->sugarUser);
        $this->userChecker->checkPostAuth($this->user);
    }

    /**
     * @param array $externalAuthInfo
     * @param $expectedSugarField
     *
     * @dataProvider checkPostAuthWithExistingUserProvider
     *
     * @covers ::checkPostAuth
     */
    public function testCheckPostAuthWithNotExistingUser(array $externalAuthInfo, $expectedSugarField)
    {
        $expectedAttributes = [
            'user_name' => $this->userName,
            'last_name' => $this->userName,
            'email' => $this->userName,
            'employee_status' => User::USER_EMPLOYEE_STATUS_ACTIVE,
            'status' => User::USER_STATUS_ACTIVE,
            'is_admin' => 0,
            'external_auth_only' => 1,
            'system_generated_password' => 0,
        ];
        $this->user->expects($this->once())
                   ->method('getAttribute')
                   ->with('ext')
                   ->willReturn($externalAuthInfo);
        $this->user->expects($this->once())->method('getUsername')->willReturn($this->userName);
        $this->localUserProvider->expects($this->once())
                                ->method('loadUserByField')
                                ->with($this->userName, $expectedSugarField)
                                ->willThrowException(new UsernameNotFoundException());
        $this->localUserProvider->expects($this->once())
                                ->method('createUser')
                                ->with($this->userName, $expectedAttributes)
                                ->willReturn($this->sugarUser);
        $this->user->expects($this->once())->method('setSugarUser')->with($this->sugarUser);
        $this->userChecker->checkPostAuth($this->user);
    }
}
