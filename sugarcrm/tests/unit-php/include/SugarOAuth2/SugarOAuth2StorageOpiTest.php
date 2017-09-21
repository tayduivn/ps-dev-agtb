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
use Sugarcrm\Sugarcrm\IdentityProvider\Authentication\UserProvider\SugarLocalUserProvider;
use Symfony\Component\Security\Core\Exception\UsernameNotFoundException;

/**
 * @coversDefaultClass \SugarOAuth2StorageOpi
 */
class SugarOAuth2StorageOpiTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var SugarLocalUserProvider | \PHPUnit_Framework_MockObject_MockObject
     */
    protected $userProviderMock;

    /**
     * @var \SugarOAuth2StorageOpi | \PHPUnit_Framework_MockObject_MockObject
     */
    protected $sugarOAuth2StorageOpiMock;

    /**
     * @var \User | \PHPUnit_Framework_MockObject_MockObject
     */
    protected $sugarUser;

    /**
     * @var User | \PHPUnit_Framework_MockObject_MockObject
     */
    protected $identityUser;

    protected function setUp()
    {
        $this->userProviderMock = $this->createMock(SugarLocalUserProvider::class);

        $this->sugarOAuth2StorageOpiMock = $this->getMockBuilder(\SugarOAuth2StorageOpi::class)
                                                ->disableOriginalConstructor()
                                                ->setMethods(['getLocalUserProvider'])
                                                ->getMock();
        $this->sugarOAuth2StorageOpiMock->method('getLocalUserProvider')->willReturn($this->userProviderMock);
        $this->sugarUser = $this->createMock(\User::class);
        $this->identityUser = $this->createMock(User::class);
    }

    /**
     * Provides data for testLoadUserFromNameForExistingUser
     * @return array
     */
    public function loadUserFromNameForExistingUserProvider()
    {
        return [
            'localUser' => [
                'username' => 'testUser',
                'extraData' => [
                    'ext' => [
                        'amr' => ['PROVIDER_KEY_LOCAL'],
                    ],
                ],
                'expectedField' => 'user_name',
            ],
            'samlUser' => [
                'username' => 'testUser',
                'extraData' => [
                    'ext' => [
                        'amr' => ['PROVIDER_KEY_SAML'],
                    ],
                ],
                'expectedField' => 'email',
            ],
        ];
    }

    /**
     * @param $username
     * @param $extraData
     * @param $expectedField
     *
     * @covers ::loadUserFromName
     * @dataProvider loadUserFromNameForExistingUserProvider
     */
    public function testLoadUserFromNameForExistingUser($username, $extraData, $expectedField)
    {
        $this->userProviderMock->expects($this->once())
                               ->method('loadUserByField')
                               ->with($username, $expectedField)
                               ->willReturn($this->identityUser);
        $this->identityUser->expects($this->once())->method('getSugarUser')->willReturn($this->sugarUser);

        $user = $this->sugarOAuth2StorageOpiMock->loadUserFromName($username, $extraData);
        $this->assertEquals($this->sugarUser, $user);
    }

    /**
     * @covers ::loadUserFromName
     *
     * @expectedException \Symfony\Component\Security\Core\Exception\UsernameNotFoundException
     */
    public function testLoadUserFromNameForNoExistingLocalUser()
    {
        $username = 'testUser';
        $extraData = [
            'ext' => [
                'amr' => ['PROVIDER_KEY_LOCAL'],
            ],
        ];

        $this->userProviderMock->expects($this->once())
                               ->method('loadUserByField')
                               ->with($username, 'user_name')
                               ->willThrowException(new UsernameNotFoundException());

        $this->sugarOAuth2StorageOpiMock->loadUserFromName($username, $extraData);
    }

    /**
     * @covers ::loadUserFromName
     */
    public function testLoadUserFromNameForNoExistingSamlUser()
    {
        $username = 'testUser';
        $extraData = [
            'ext' => [
                'amr' => ['PROVIDER_KEY_SAML'],
            ],
        ];
        $userAttributes = [
            'user_name' => $username,
            'last_name' => $username,
            'email' => $username,
            'employee_status' => User::USER_EMPLOYEE_STATUS_ACTIVE,
            'status' => User::USER_STATUS_ACTIVE,
            'is_admin' => 0,
            'external_auth_only' => 1,
            'system_generated_password' => 0,
        ];

        $this->userProviderMock->expects($this->once())
                               ->method('loadUserByField')
                               ->with($username, 'email')
                               ->willThrowException(new UsernameNotFoundException());

        $this->userProviderMock->expects($this->once())
                               ->method('createUser')
                               ->with($username, $userAttributes)
                               ->willReturn($this->sugarUser);

        $user = $this->sugarOAuth2StorageOpiMock->loadUserFromName($username, $extraData);
        $this->assertEquals($this->sugarUser, $user);
    }
}
