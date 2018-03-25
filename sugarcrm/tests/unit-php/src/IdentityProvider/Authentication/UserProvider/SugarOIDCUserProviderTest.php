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

namespace Sugarcrm\SugarcrmTestUnit\IdentityProvider\Authentication\UserProvider;

use PHPUnit\Framework\TestCase;
use Sugarcrm\Sugarcrm\IdentityProvider\Authentication\User;
use Sugarcrm\Sugarcrm\IdentityProvider\Authentication\UserProvider\SugarLocalUserProvider;
use Sugarcrm\Sugarcrm\IdentityProvider\Authentication\UserProvider\SugarOIDCUserProvider;

/**
 * @coversDefaultClass Sugarcrm\Sugarcrm\IdentityProvider\Authentication\UserProvider\SugarOIDCUserProvider
 */
class SugarOIDCUserProviderTest extends TestCase
{
    /**
     * @var SugarOIDCUserProvider
     */
    protected $userProvider = null;

    /**
     * @var SugarLocalUserProvider | \PHPUnit_Framework_MockObject_MockObject
     */
    protected $localUserProvider = null;

    protected function setUp()
    {
        $this->localUserProvider = $this->createMock(SugarLocalUserProvider::class);
        $this->userProvider = new SugarOIDCUserProvider($this->localUserProvider);
    }

    /**
     * @covers ::loadUserByUsername
     */
    public function testLoadUserByUsername()
    {
        $user = $this->userProvider->loadUserByUsername('testUser');
        $this->assertInstanceOf(User::class, $user);
        $this->assertEquals('testUser', $user->getUsername());
    }

    /**
     * @covers ::refreshUser
     */
    public function testRefreshUser()
    {
        $user = new User('testUser');
        $refreshedUser = $this->userProvider->refreshUser($user);
        $this->assertEquals($user, $refreshedUser);
    }

    /**
     * Provides data for testSupportsClass
     * @return array
     */
    public function supportsClassProvider()
    {
        return [
            'validClass' => [
                'className' => User::class,
                'expectedResult' => true,
            ],
            'invalidClass' => [
                'className' => \User::class,
                'expectedResult' => false,
            ],
        ];
    }

    /**
     * @param $className
     * @param $expectedResult
     *
     * @dataProvider supportsClassProvider
     *
     * @covers ::supportsClass
     */
    public function testSupportsClass($className, $expectedResult)
    {
        $this->assertEquals($expectedResult, $this->userProvider->supportsClass($className));
    }

    /**
     * @covers ::loadUserByField
     */
    public function testLoadUserByField()
    {
        $this->localUserProvider->expects($this->once())->method('loadUserByField')->with('value', 'field');
        $this->userProvider->loadUserByField('value', 'field');
    }

    /**
     * @covers ::loadUserBySrn
     */
    public function testLoadUserBySrn()
    {
        $srn = 'srn:cluster:idm:eu:0000000001:user:seed_sally_id';
        $user = $this->userProvider->loadUserBySrn($srn);
        $this->assertEquals($srn, $user->getSrn());
    }
}
